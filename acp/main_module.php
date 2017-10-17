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
 * Auto Database Restore ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$this->tpl_name = 'acp_settings';
		$this->page_title = 'ACP_TITLE';

		$form_name = 'blitze/autodbrestore';

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$config->set('blitze_autodbrestore_file', $request->variable('file', ''));
			$config->set('blitze_autodbrestore_frequency', $request->variable('frequency', 0));

			trigger_error($user->lang('ACP_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		include($phpbb_root_path . 'includes/acp/acp_database.' . $phpEx);

		$acp = new \acp_database();
		$acp->main('', 'restore');

		add_form_key($form_name);

		$template->assign_vars(array(
			'DB_FILE'			=> $config['blitze_autodbrestore_file'],
			'FREQUENCY'			=> $config['blitze_autodbrestore_frequency'],
			'U_ACTION'			=> $this->u_action,
			'U_CREATE_BACKUP'	=> append_sid("{$phpbb_admin_path}index." . $phpEx, 'i=acp_database&amp;mode=backup'),
		));
	}
}
