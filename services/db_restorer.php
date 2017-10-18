<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General protected License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\services;

class db_restorer
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $db_file_path;

	/** @var array */
	private static $file_type_params = array(
		'sql'		=> array(
			'open'		=> 'fopen',
			'mode'		=> 'rb',
			'read'		=> 'fread',
			'seek'		=> 'fseek',
			'eof'		=> 'feof',
			'close'		=> 'fclose',
			'fgetd'		=> 'fgetd',
		),

		'sql.bz2'	=> array(
			'open'		=> 'bzopen',
			'mode'		=> 'r',
			'read'		=> 'bzread',
			'seek'		=> '',
			'eof'		=> 'feof',
			'close'		=> 'bzclose',
			'fgetd'		=> 'fgetd_seekless',
		),

		'sql.gz'	=> array(
			'open'		=> 'gzopen',
			'mode'		=> 'rb',
			'read'		=> 'gzread',
			'seek'		=> 'gzseek',
			'eof'		=> 'gzeof',
			'close'		=> 'gzclose',
			'fgetd'		=> 'fgetd',
		),
	);

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db	 				Database connection
	 * @param string								$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string								$php_ext			php file extension
	 * @param string								$db_file_path		path to backup file
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, $phpbb_root_path, $php_ext, $db_file_path = '')
	{
		$this->db = $db;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->db_file_path = $db_file_path ?: $this->phpbb_root_path . 'store/';
	}

	/**
	 * @param string $file
	 * @return void
	 */
	public function run($file)
	{
		if (preg_match('#^backup_\d{10,}_[a-z\d]{16}\.(sql(?:\.(?:gz|bz2))?)$#', $file, $matches))
		{
			$file_name = $this->db_file_path . $matches[0];

			if (file_exists($file_name) && is_readable($file_name))
			{
				include($this->phpbb_root_path . 'includes/acp/acp_database.' . $this->php_ext);

				$this->do_run($file_name, $matches[1]);
			}
		}
	}

	/**
	 * @param string $file_name
	 * @param string $file_type
	 * @return void
	 */
	protected function do_run($file_name, $file_type)
	{
		$params = self::$file_type_params[$file_type];
		$method = $this->db->get_sql_layer();

		if (method_exists($this, $method))
		{
			$fp = $params['open']($file_name, $params['mode']);
			$params['fp'] = $fp;
			$this->$method($params);
			$params['close']($fp);
		}
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function mysql(array $params)
	{
		extract($params);
		while (($sql = $fgetd($fp, ";\n", $read, $seek, $eof)) !== false)
		{
			$this->db->sql_query($sql);
		}
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function mysql4(array $params)
	{
		$this->mysql($params);
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function mysqli(array $params)
	{
		$this->mysql($params);
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function sqlite3(array $params)
	{
		$this->mysql($params);
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function oracle(array $params)
	{
		extract($params);
		while (($sql = $fgetd($fp, "/\n", $read, $seek, $eof)) !== false)
		{
			$this->db->sql_query($sql);
		}
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function mssql_odbc(array $params)
	{
		extract($params);
		while (($sql = $fgetd($fp, "GO\n", $read, $seek, $eof)) !== false)
		{
			$this->db->sql_query($sql);
		}
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function mssqlnative(array $params)
	{
		$this->mssql_odbc($params);
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function postgres(array $params)
	{
		extract($params);

		$delim = ";\n";
		while (($sql = $fgetd($fp, $delim, $read, $seek, $eof)) !== false)
		{
			$query = trim($sql);

			$this->postgres_run_query($query);
			$this->postgres_copy_data($query, $fgetd, $read, $seek, $eof, $fp);
		}
	}

	/**
	 * @param string $query
	 * @return void
	 */
	protected function postgres_run_query($query)
	{
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
	}

	/**
	 * @param string $query
	 * @param string $fgetd
	 * @param string $read
	 * @param string $seek
	 * @param string $eof
	 * @param resource $fp
	 * @return void|false
	 */
	protected function postgres_copy_data($query, $fgetd, $read, $seek, $eof, $fp)
	{
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
}
