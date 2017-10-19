<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\tests\acp;

use phpbb\request\request_interface;

require_once dirname(__FILE__) . '../../../../../../includes/functions_acp.php';

class main_module_test extends \phpbb_database_test_case
{
	protected $config;
	protected $template;

	static public function setUpBeforeClass()
	{
		global $phpbb_root_path;

		parent::setUpBeforeClass();

		$fs = new \phpbb\filesystem\filesystem();
		$fs->rename($phpbb_root_path . 'store', $phpbb_root_path . 'store.temp');
		$fs->mirror($phpbb_root_path . 'ext/blitze/autodbrestore/tests/acp/fixtures/store', $phpbb_root_path . 'store');
	}

	static public function tearDownAfterClass()
	{
		global $phpbb_root_path;

		parent::tearDownAfterClass();

		$fs = new \phpbb\filesystem\filesystem();
		$fs->remove($phpbb_root_path . 'store');
		$fs->rename($phpbb_root_path . 'store.temp', $phpbb_root_path . 'store');
	}

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
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/empty.xml');
	}

	/**
	 * Get the main_module object
	 *
	 * @param array $variable_map
	 * @param array $db_text
	 * @param bool $submitted
	 * @return \blitze\autodbrestore\acp\main_module
	 */
	public function get_module(array $variable_map, array $db_text = array(), $submitted = false)
	{
		global $config, $db, $request, $template, $user, $phpbb_container, $phpbb_dispatcher, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$phpbb_container = new \phpbb_mock_container_builder();
		$factory = new \phpbb\db\tools\factory();
		$phpbb_container->set('dbal.tools', $factory->get($db));

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$language = new \phpbb\language\language($lang_loader);

		$user = new \phpbb\user($language, '\phpbb\datetime');
		$user->timezone = new \DateTimeZone('UTC');

		$config = new \phpbb\config\config(array(
			'blitze_autodbrestore_file' => 'backup_1508169244_bd0498f98633ec67.sql',
			'blitze_autodbrestore_frequency' => 60,
		));
		$this->config = &$config;

		$request = $this->getMock('\phpbb\request\request_interface');
		$request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap($variable_map));
		$request->expects($this->any())
			->method('is_set_post')
			->willReturn($submitted);

		$tpl_data = array();
		$template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
		$template->expects($this->any())
			->method('assign_vars')
			->will($this->returnCallback(function($data) use (&$tpl_data) {
				$tpl_data = array_merge($tpl_data, $data);
			}));
		$template->expects($this->any())
			->method('assign_block_vars')
			->will($this->returnCallback(function($key, $data) use (&$tpl_data) {
				$tpl_data[$key][] = $data;
			}));
		$template->expects($this->any())
			->method('assign_display')
			->will($this->returnCallback(function() use (&$tpl_data) {
				return $tpl_data;
			}));
		$this->template =& $template;

		$module = $this->getMock('\blitze\autodbrestore\acp\main_module', array(
			'check_form_key',
			'trigger_error',
		));

		$module->expects($this->any())
			->method('trigger_error')
			->willReturn('false');
		$module->expects($this->any())
			->method('check_form_key')
			->willReturn('true');

		return $module;
	}

	/**
	 * Test the main method
	 * @return void
	 */
	public function test_module()
	{
		$module = $this->get_module(array());

		$module->main();

		$result = $this->template->assign_display('settings');
		unset($result['U_ACTION']);

		$this->assertEquals($result, array(
			'MODE' => 'restore',
			'files'	=> array(array(
				'FILE' => 'backup_1508169244_bd0498f98633ec67.sql',
				'NAME' => '16-10-2017 15:54:04',
				'SUPPORTED' => 1,
			)),
			'DB_FILE' => 'backup_1508169244_bd0498f98633ec67.sql',
			'FREQUENCY' => 60,
			'U_CREATE_BACKUP' => 'index.php?i=acp_database&amp;mode=backup',
		));
	}

	/**
	 * Test save settings
	 */
	public function test_save_settings()
	{
		$expected_file = 'new_backup_file.sql';
		$expected_frequency = 25;

		$variable_map = array(
			array('file', '', false, request_interface::REQUEST, $expected_file),
			array('frequency', 0, false, request_interface::REQUEST, $expected_frequency),
		);

		$module = $this->get_module($variable_map, array(), true);
		$reflection = new \ReflectionClass($module);
		$method = $reflection->getMethod('save_settings');
		$method->setAccessible(true);

		$parameters = array('form_key');
		$method->invokeArgs($module, $parameters);

		$this->assertEquals($expected_file, $this->config['blitze_autodbrestore_file']);
		$this->assertEquals($expected_frequency, $this->config['blitze_autodbrestore_frequency']);
	}
}
