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
	protected $template;
	protected $user;
	protected $config_file;

	static public function setUpBeforeClass()
	{
		global $phpbb_root_path, $phpEx;

		parent::setUpBeforeClass();

		$fs = new \phpbb\filesystem\filesystem();
		$fs->rename($phpbb_root_path . 'store', $phpbb_root_path . 'store.temp');
		$fs->mirror($phpbb_root_path . 'ext/blitze/autodbrestore/tests/acp/fixtures/store', $phpbb_root_path . 'store');
	}

	static public function tearDownAfterClass()
	{
		global $phpbb_root_path, $phpEx;

		parent::tearDownAfterClass();

		$fs = new \phpbb\filesystem\filesystem();
		$fs->remove($phpbb_root_path . 'store');
		$fs->rename($phpbb_root_path . 'store.temp', $phpbb_root_path . 'store');
		$fs->remove($phpbb_root_path . 'ext/blitze/autodbrestore/tests/acp/fixtures/config.' . $phpEx);
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
	 * @param bool $submitted
	 * @return \blitze\autodbrestore\acp\main_module
	 */
	public function get_module(array $variable_map, $submitted = false)
	{
		global $config, $db, $request, $template, $user, $phpbb_container, $phpbb_dispatcher, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$config = new \phpbb\config\config(array(
			'form_token_lifetime' => -1,
		));

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$language = new \phpbb\language\language($lang_loader);

		$user = new \phpbb\user($language, '\phpbb\datetime');
		$user->timezone = new \DateTimeZone('UTC');
		$user->data['user_id'] = 2;
		$this->user = &$user;

		$request = $this->getMock('\phpbb\request\request_interface');
		$request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap($variable_map));
		$request->expects($this->any())
			->method('is_set_post')
			->will($this->returnValueMap(array(
				array('submit', $submitted),
				array('form_token', true),
				array('creation_time', true),
			)));

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

		$phpbb_container = new \phpbb_mock_container_builder();

		$factory = new \phpbb\db\tools\factory();
		$filesystem = new \phpbb\filesystem\filesystem();

		$this->config_file = __DIR__ . '/fixtures/config.' . $phpEx;
		$settings = new \blitze\autodbrestore\services\settings($filesystem, $phpbb_root_path, $this->config_file);

		$settings->set_settings(array(
			'backup_file' => 'backup_1508169244_bd0498f98633ec67.sql',
			'restore_frequency' => 60,
			'cron_last_run' => 123456789,
		));

		$phpbb_container->set('dbal.tools', $factory->get($db));
		$phpbb_container->set('blitze.autodbrestore.settings', $settings);

		return new \blitze\autodbrestore\acp\main_module();
	}

	/**
	 * Test the main method
	 * @return void
	 */
	public function test_module()
	{
		$module = $this->get_module(array());
		$module->u_action = 'u_action';
		$module->main();

		$this->assertEquals(array(
			'MODE' => 'restore',
			'files'	=> array(array(
				'FILE' => 'backup_1508169244_bd0498f98633ec67.sql',
				'NAME' => '16-10-2017 15:54:04',
				'SUPPORTED' => 1,
			)),
			'CONFIG' => array(
				'backup_file' => 'backup_1508169244_bd0498f98633ec67.sql',
				'restore_frequency' => 60,
				'cron_last_run' => 123456789,
			),
			'U_ACTION' => 'u_action',
			'U_CREATE_BACKUP' => 'index.php?i=acp_database&amp;mode=backup',
		), $this->template->assign_display('settings'));
	}

	/**
	 * @return array
	 */
	public function save_settings_test_data() {
		return array(
			array(
				array(
					array('file', '', false, request_interface::REQUEST, 'new_backup_file.sql'),
					array('frequency', 0, false, request_interface::REQUEST, 25),
					array('form_token', '', false, \phpbb\request\request_interface::REQUEST, sha1(0 . 'blitze/autodbrestore')),
					array('creation_time', 0, false, \phpbb\request\request_interface::REQUEST, 0),
				),
				array(
					'backup_file' => 'new_backup_file.sql',
					'restore_frequency' => 25,
					'cron_last_run' => 123456789,
				),
				E_USER_NOTICE,
				'ACP_SETTING_SAVED',
			),
			array(
				array(
					array('file', '', false, request_interface::REQUEST, 'new_backup_file.sql'),
					array('frequency', 0, false, request_interface::REQUEST, 25),
				),
				array(
					'backup_file' => 'backup_1507249280_586447177a42ff9d.sql.gz',
					'restore_frequency' => 60,
					'cron_last_run' => 123456789,
				),
				E_USER_WARNING,
				'FORM_INVALID',
			),
		);
	}

	/**
	 * Test save settings
	 * @param array $variable_map
	 * @param array $expected_config
	 * @param string $error
	 * @param string $message
	 * @dataProvider save_settings_test_data
	 */
	public function test_save_settings($variable_map, $expected_config, $error, $message)
	{
		$module = $this->get_module($variable_map, true);

		$this->setExpectedTriggerError($error, $message);

		$reflection = new \ReflectionClass($module);
		$method = $reflection->getMethod('save_settings');
		$method->setAccessible(true);

		$parameters = array('blitze/autodbrestore');
		$method->invokeArgs($module, $parameters);

		$this->assertEquals($expected_config, include($this->config_file));
	}
}
