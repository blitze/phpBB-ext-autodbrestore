<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\tests\event;

use phpbb\event\data;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;

class listener_test extends \phpbb_test_case
{
	/** @var \blitze\autodbrestore\services\config */
	protected $config;

	/** @var array */
	protected $tpl_data;

	/**
	 * the autodbrestore config.php file auto generated
	 * we remove it after the tests are completed
	 */
	static public function tearDownAfterClass()
	{
		global $phpbb_root_path, $phpEx;

		parent::tearDownAfterClass();

		$fs = new \phpbb\filesystem\filesystem();
		$fs->remove($phpbb_root_path . 'ext/blitze/autodbrestore/tests/event/config.' . $phpEx);
	}

	/**
	 * Create the listener object
	 *
	 * @return \blitze\autodbrestore\event\listener
	 */
	protected function get_listener()
	{
		global $phpbb_root_path, $phpEx;

		$filesystem = new \phpbb\filesystem\filesystem();

		$language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$language->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode('-', func_get_args());
			});

		$tpl_data = array();
		$template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		$this->tpl_data =& $tpl_data;
		$template->expects($this->any())
			->method('assign_vars')
			->will($this->returnCallback(function($data) use (&$tpl_data) {
				$tpl_data = $data;
			}));

		$config_file = __DIR__ . '/config.' . $phpEx;
		$this->config = new \blitze\autodbrestore\services\config($filesystem, $phpbb_root_path, $config_file);

		return new \blitze\autodbrestore\event\listener($language, $template, $this->config);
	}

	/**
	* Test the event listener is constructed correctly
	*/
	public function test_construct()
	{
		$listener = $this->get_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $listener);
	}

	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		$listeners = array(
			'core.user_setup',
			'core.page_header',
			'core.adm_page_header',
		);

		$this->assertEquals($listeners, array_keys(\blitze\autodbrestore\event\listener::getSubscribedEvents()));
	}

	/**
	 * @return array
	 */
	public function load_common_language_test_data()
	{
		return array(
			array(
				array(),
				array(
					array(
						'ext_name' => 'blitze/autodbrestore',
						'lang_set' => 'common',
					),
				),
			),
			array(
				array(
					array(
						'ext_name' => 'phpbb/pages',
						'lang_set' => 'pages_common',
					),
				),
				array(
					array(
						'ext_name' => 'phpbb/pages',
						'lang_set' => 'pages_common',
					),
					array(
						'ext_name' => 'blitze/autodbrestore',
						'lang_set' => 'common',
					),
				),
			),
		);
	}

	/**
	 * @dataProvider load_common_language_test_data
	 *
	 * @param array $lang_set_ext
	 * @param array $expected_contains
	 */
	public function test_load_common_language(array $lang_set_ext, array $expected_contains)
	{
		$listener = $this->get_listener();

		$dispatcher = new EventDispatcher();
		$dispatcher->addListener('core.user_setup', array($listener, 'load_common_language'));

		$event_data = array('lang_set_ext');
		$event = new data(compact($event_data));
		$dispatcher->dispatch('core.user_setup', $event);

		$lang_set_ext = $event->get_data_filtered($event_data);
		$lang_set_ext = $lang_set_ext['lang_set_ext'];

		foreach ($expected_contains as $expected)
		{
			$this->assertContains($expected, $lang_set_ext);
		}
	}

	/**
	 * Data set for test_show_notice
	 *
	 * @return array
	 */
	public function show_notice_test_data()
	{
		return array(
			array(
				array(),
				array(),
			),
			array(
				array(
					'backup_file' => '',
					'restore_frequency' => 60,
					'cron_last_run' => 0,
				),
				array(),
			),
			array(
				array(
					'backup_file' => 'backup.sql',
					'restore_frequency' => 15,
					'cron_last_run' => 123456789,
				),
				array(
					'AUTODBRESTORE_FREQUENCY'	=> 15,
					'AUTODBRESTORE_LASTRUN'		=> 123456789,
					'AUTODBRESTORE_NOTICE'		=> 'AUTODBRESTORE_NOTICE-15',
				),
			),
		);
	}

	/**
	 * Test the load_permission_language event
	 *
	 * @dataProvider show_notice_test_data
	 * @param array $config_data
	 * @param array $expected
	 */
	public function test_show_notice(array $config_data, array $expected)
	{
		$listener = $this->get_listener();

		$this->config->set_settings($config_data);

		$listener->show_notice();

		$this->assertSame($expected, $this->tpl_data);
	}
}
