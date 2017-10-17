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
	public function effectively_installed()
	{
		//return isset($this->config['blitze_autodbrestore_frequency']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('blitze_autodbrestore_file', '')),
			array('config.add', array('blitze_autodbrestore_frequency', 60)),

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
