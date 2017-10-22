<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v321');
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_AUTODBRESTORE'
			)),
			array('module.add', array(
				'acp',
				'ACP_AUTODBRESTORE',
				array(
					'module_basename'	=> '\blitze\autodbrestore\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
