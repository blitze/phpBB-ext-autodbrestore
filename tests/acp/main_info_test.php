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

use blitze\autodbrestore\acp\main_info;

class main_info_test extends \phpbb_test_case
{
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
	 * Test the module method
	 */
	public function test_module()
	{
		$module = new main_info();
		$info = $module->module();

		$expected = array(
			'filename' => '\blitze\autodbrestore\acp\main_module',
			'parent' => array('ACP_AUTODBRESTORE'),
			'mode' => 'SETTINGS',
			'auth' => 'ext_blitze/autodbrestore && acl_a_board',
		);

		$result = array(
			'filename' => $info['filename'],
			'parent' => $info['modes']['settings']['cat'],
			'mode' => $info['modes']['settings']['title'],
			'auth' => $info['modes']['settings']['auth'],
		);

		$this->assertEquals($expected, $result);
	}
}
