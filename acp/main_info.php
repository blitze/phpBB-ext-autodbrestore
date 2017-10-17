<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\acp;

/**
 * Auto Database Restore ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\blitze\autodbrestore\acp\main_module',
			'title'		=> 'ACP_AUTODBRESTORE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'SETTINGS',
					'auth'	=> 'ext_blitze/autodbrestore && acl_a_board',
					'cat'	=> array('ACP_AUTODBRESTORE')
				),
			),
		);
	}
}
