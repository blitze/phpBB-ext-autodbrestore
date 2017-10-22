<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\tests\cron;

use blitze\autodbrestore\services\restorer;
use blitze\autodbrestore\cron\task\restore;

class restore_test extends \phpbb_database_test_case
{
	protected $db;
	protected $task_name = 'blitze.autodbrestore.cron.task.restore';

	/**
	 * Define the extension to be tested.
	 *
	 * @return string[]
	 */
	protected static function setup_extensions()
	{
		return array('blitze/autodbrestore');
	}

	/**
	 * Load required fixtures.
	 *
	 * @return mixed
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/smilies.xml');
	}

	/**
	 * the autodbrestore config.php file auto generated
	 * we remove it after the tests are completed
	 */
	static public function tearDownAfterClass()
	{
		global $phpbb_root_path, $phpEx;

		parent::tearDownAfterClass();

		$fs = new \phpbb\filesystem\filesystem();
		$fs->remove($phpbb_root_path . 'ext/blitze/autodbrestore/tests/cron/fixtures/config.' . $phpEx);
	}

	/**
	 * Create the cron manager
	 *
	 * @return \phpbb\cron\manager
	 */
	protected function create_cron_manager($tasks)
	{
		global $phpbb_root_path, $phpEx;

		$mock_config = new \phpbb\config\config(array(
			'force_server_vars' => false,
			'enable_mod_rewrite' => '',
		));

		$mock_router = $this->getMockBuilder('\phpbb\routing\router')
			->setMethods(array('setContext', 'generate'))
			->disableOriginalConstructor()
			->getMock();
		$mock_router->method('setContext')
			->willReturn(true);
		$mock_router->method('generate')
			->willReturn('foobar');

		$request = new \phpbb\request\request();
		$request->enable_super_globals();

		$routing_helper = new \phpbb\routing\helper(
			$mock_config,
			$mock_router,
			new \phpbb\symfony_request($request),
			$request,
			new \phpbb\filesystem\filesystem(),
			$phpbb_root_path,
			$phpEx
		);

		return new \phpbb\cron\manager($tasks, $routing_helper, $phpbb_root_path, $phpEx);
	}

	/**
	 * Create the cron task
	 *
	 * @return \blitze\autodbrestore\cron\restore
	 */
	protected function create_cron_task()
	{
		global $cache, $phpbb_root_path, $phpEx;

		$cache = new \phpbb_mock_cache();

		$this->db = $this->new_dbal();

		$filesystem = new \phpbb\filesystem\filesystem();

		$logger = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$language = new \phpbb\language\language($lang_loader);

		$user = new \phpbb\user($language, '\phpbb\datetime');
		$user->data['user_id'] = 2;

		$layer = $this->db->get_sql_layer();
		$db_type = (in_array($layer, array('postgres', 'sqlite3'))) ? $layer : 'mysql';
		$restorer = new restorer($this->db, $phpbb_root_path, $phpEx, dirname(__FILE__) . "/fixtures/$db_type/");

		$this->config_file = __DIR__ . '/fixtures/config.' . $phpEx;
		$autodbrestore_config = new \blitze\autodbrestore\services\config($filesystem, $phpbb_root_path, $this->config_file);

		$autodbrestore_config->set_settings(array(
			'backup_file' => 'backup_1508169244_bd0498f98633ec67.sql',
			'restore_frequency' => 60,
			'cron_last_run' => 0,
		));

		$task = new restore($cache, $logger, $user, $autodbrestore_config, $restorer);

		// this is normally called automatically in the yaml service config
		// but we have to do it manually here
		$task->set_name($this->task_name);

		return $task;
	}

	/**
	 * Test if task manager can find our task
	 */
	public function test_that_cron_task_is_discoverable()
	{
		$restore_task = $this->create_cron_task();
		$cron_manager = $this->create_cron_manager(array($restore_task));

		$task = $cron_manager->find_task($this->task_name);
		$this->assertInstanceOf('\phpbb\cron\task\wrapper', $task);
		$this->assertEquals($this->task_name, $task->get_name());
	}

	/**
	 * @return void
	 */
	public function test_restore()
	{
		$task = $this->create_cron_task();

		// the task should be runnable
		$this->assertTrue($task->is_runnable());

		// the task should be ready to run initially
		$this->assertTrue($task->should_run());

		// initial database state
		$this->assertEquals(0, $this->get_data_count());

		// initial config state
		$config = include($this->config_file);
		$this->assertEquals(0, $config['cron_last_run']);

		// run the task
		$task->run();

		// After run cron trask, we should now have 1 user
		$this->assertEquals(1, $this->get_data_count());

		// config file should now be updated with new last run time
		$config = include($this->config_file);
		$this->assertNotEquals(0, $config['cron_last_run']);

		// after successful run, the task should not be ready to run again until enough time has elapsed
		$this->assertFalse($task->should_run());
	}

	/**
	 * @return int
	 */
	protected function get_data_count()
	{
		$result = $this->db->sql_query('SELECT COUNT(*) as total FROM phpbb_smilies');
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}
}
