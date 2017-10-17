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
	/**
	 * Create the listener object
	 *
	 * @param array $config_data
	 * @return \blitze\autodbrestore\event\listener
	 */
	protected function get_listener(array $config_data = array())
	{
		global $phpbb_root_path, $phpEx;

		$config = new \phpbb\config\config($config_data);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$language = new \phpbb\language\language($lang_loader);

		$tpl_data = array();
		$template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		$this->tpl_data =& $tpl_data;
		$template->expects($this->any())
			->method('assign_vars')
			->will($this->returnCallback(function($data) use (&$tpl_data) {
				$tpl_data = $data;
			}));

		return new \blitze\autodbrestore\event\listener($config, $language, $template);
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
	 * Data set for test_add_permissions
	 *
	 * @return array
	 */
	public function show_notice_test_data()
	{
		return array(
			array(
				array(),
				array(
					'AUTO_DB_RESTORE'			=> false,
					'AUTO_DB_RESTORE_NOTICE'	=> 'AUTODBRESTORE_NOTICE',
				),
			),
			array(
				array(
					'blitze_autodbrestore_file' => 'backup.sql',
					'blitze_autodbrestore_frequency' => 15,
				),
				array(
					'AUTO_DB_RESTORE'			=> true,
					'AUTO_DB_RESTORE_NOTICE'	=> 'AUTODBRESTORE_NOTICE',
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
		$listener = $this->get_listener($config_data);
		$listener->show_notice();

		$this->assertSame($expected, $this->tpl_data);
	}
}
