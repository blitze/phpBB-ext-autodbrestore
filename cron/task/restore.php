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
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\autodbrestore\services\config */
	protected $config;

	/** @var \blitze\autodbrestore\services\restorer */
	protected $restorer;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\driver\driver_interface			$cache			Cache driver interface
	 * @param \phpbb\log\log_interface						$logger			phpBB logger
	 * @param \phpbb\user									$user			User object
	 * @param \blitze\autodbrestore\services\config			$config			Autodbrestore config object
	 * @param \blitze\autodbrestore\services\restorer		$restorer		Restores db to specified file
	 */
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\log\log_interface $logger, \phpbb\user $user, \blitze\autodbrestore\services\config $config, \blitze\autodbrestore\services\restorer $restorer)
	{
		$this->cache = $cache;
		$this->logger = $logger;
		$this->user = $user;
		$this->config = $config;
		$this->restorer = $restorer;
	}

	/**
	 * Runs this cron task.
	 *
	 * @return void
	 */
	public function run()
	{
		// Run your cron actions here...
		$this->restorer->run($this->config->get('backup_file'));

		// Purge the cache due to updated data
		$this->cache->purge();

		$this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DB_RESTORE');

		// Update the cron task run time here if it hasn't
		// already been done by your cron actions.
		$this->config->save(array(
			'cron_last_run' => time(),
		));
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
		return $this->config->is_ready();
	}

	/**
	 * Returns whether this cron task should run now, because enough time
	 * has passed since it was last run.
	 *
	 * @return bool
	 */
	public function should_run()
	{
		return $this->config->get('cron_last_run') < time() - ($this->config->get('restore_frequency') * 60);
	}
}
