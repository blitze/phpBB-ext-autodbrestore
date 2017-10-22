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
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\autodbrestore\services\config */
	protected $config;

	/** @var string phpBB admin path */
	protected $phpbb_admin_path;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	/** @var string */
	public $u_action;

	/**
	 * main_module constructor.
	 */
	public function __construct()
	{
		global $request, $template, $user, $phpbb_container, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->config = $phpbb_container->get('blitze.autodbrestore.config');
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
	}

	/**
	 * @return void
	 */
	public function main()
	{
		$this->tpl_name = 'acp_settings';
		$this->page_title = 'ACP_TITLE';

		$form_name = 'blitze/autodbrestore';

		$this->save_settings($form_name);

		include($this->phpbb_root_path . 'includes/acp/acp_database.' . $this->php_ext);

		$acp = new \acp_database();
		$acp->main('', 'restore');

		add_form_key($form_name);

		$this->template->assign_vars(array(
			'CONFIG'			=> $this->config->get_settings(),
			'U_ACTION'			=> $this->u_action,
			'U_CREATE_BACKUP'	=> append_sid("{$this->phpbb_admin_path}index." . $this->php_ext, 'i=acp_database&amp;mode=backup'),
		));
	}

	/**
	 * @param string $form_name
	 * @return void
	 */
	protected function save_settings($form_name)
	{
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$this->config->save(array(
				'backup_file'		=> $this->request->variable('file', ''),
				'restore_frequency'	=> $this->request->variable('frequency', 0),
			));

			trigger_error($this->user->lang('ACP_SETTING_SAVED') . adm_back_link($this->u_action));
		}
	}
}
