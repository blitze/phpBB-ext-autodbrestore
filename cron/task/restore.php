<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\cron\task;

/**
 * Auto Database Restore cron task.
 */
class restore extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config Config object
	 */
	public function __construct($cache, \phpbb\config\config $config, $db, $logger, $user, $phpbb_root_path, $php_ext, $db_file_path = '')
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->logger = $logger;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->db_file_path = $db_file_path ?: $this->phpbb_root_path . 'store/';
	}

	/**
	 * Runs this cron task.
	 *
	 * @return void
	 */
	public function run()
	{
		// Run your cron actions here...
		include($this->phpbb_root_path . 'includes/acp/acp_database.' . $this->php_ext);

		$this->restore_db();

		// Update the cron task run time here if it hasn't
		// already been done by your cron actions.
		$this->config->set('blitze_autodbrestore_cron_last_run', time(), false);
	}

	/**
	 * Returns whether this cron task can run, given current board configuration.
	 *
	 * For example, a cron task that prunes forums can only run when
	 * forum pruning is enabled.
	 *
	 * @return bool
	 */
	public function is_runnable()
	{
		return !empty($this->config['blitze_autodbrestore_file']) && $this->config['blitze_autodbrestore_frequency'];
	}

	/**
	 * Returns whether this cron task should run now, because enough time
	 * has passed since it was last run.
	 *
	 * @return bool
	 */
	public function should_run()
	{
		return $this->config['blitze_autodbrestore_cron_last_run'] < time() - ($this->config['blitze_autodbrestore_frequency'] * 60);
	}

	protected function restore_db()
	{
		$file = $this->config['blitze_autodbrestore_file'];

		if (!preg_match('#^backup_\d{10,}_[a-z\d]{16}\.(sql(?:\.(?:gz|bz2))?)$#', $file, $matches))
		{
			return false;
		}

		$file_name = $this->db_file_path . $matches[0];

		if (!file_exists($file_name) || !is_readable($file_name))
		{
			return false;
		}

		switch ($matches[1])
		{
			case 'sql':
				$fp = fopen($file_name, 'rb');
				$read = 'fread';
				$seek = 'fseek';
				$eof = 'feof';
				$close = 'fclose';
				$fgetd = 'fgetd';
			break;

			case 'sql.bz2':
				$fp = bzopen($file_name, 'r');
				$read = 'bzread';
				$seek = '';
				$eof = 'feof';
				$close = 'bzclose';
				$fgetd = 'fgetd_seekless';
			break;

			case 'sql.gz':
				$fp = gzopen($file_name, 'rb');
				$read = 'gzread';
				$seek = 'gzseek';
				$eof = 'gzeof';
				$close = 'gzclose';
				$fgetd = 'fgetd';
			break;
		}

		switch ($this->db->get_sql_layer())
		{
			case 'mysql':
			case 'mysql4':
			case 'mysqli':
			case 'sqlite3':
				while (($sql = $fgetd($fp, ";\n", $read, $seek, $eof)) !== false)
				{
					$this->db->sql_query($sql);
				}
			break;

			case 'postgres':
				$delim = ";\n";
				while (($sql = $fgetd($fp, $delim, $read, $seek, $eof)) !== false)
				{
					$query = trim($sql);

					if (substr($query, 0, 13) == 'CREATE DOMAIN')
					{
						list(, , $domain) = explode(' ', $query);
						$sql = "SELECT domain_name
							FROM information_schema.domains
							WHERE domain_name = '$domain';";
						$result = $this->db->sql_query($sql);
						if (!$this->db->sql_fetchrow($result))
						{
							$this->db->sql_query($query);
						}
						$this->db->sql_freeresult($result);
					}
					else
					{
						$this->db->sql_query($query);
					}

					if (substr($query, 0, 4) == 'COPY')
					{
						while (($sub = $fgetd($fp, "\n", $read, $seek, $eof)) !== '\.')
						{
							if ($sub === false)
							{
								return false;
							}
							pg_put_line($this->db->get_db_connect_id(), $sub . "\n");
						}
						pg_put_line($this->db->get_db_connect_id(), "\\.\n");
						pg_end_copy($this->db->get_db_connect_id());
					}
				}
			break;

			case 'oracle':
				while (($sql = $fgetd($fp, "/\n", $read, $seek, $eof)) !== false)
				{
					$this->db->sql_query($sql);
				}
			break;

			case 'mssql_odbc':
			case 'mssqlnative':
				while (($sql = $fgetd($fp, "GO\n", $read, $seek, $eof)) !== false)
				{
					$this->db->sql_query($sql);
				}
			break;
		}

		$close($fp);

		// Purge the cache due to updated data
		$this->cache->purge();

		$this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DB_RESTORE');
	}
}
