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
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log_interface */
	protected $logger;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\autodbrestore\services\settings */
	protected $settings;

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
		global $phpbb_log, $request, $template, $user, $phpbb_container, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$this->language = $phpbb_container->get('language');
		$this->logger = $phpbb_log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->settings = $phpbb_container->get('blitze.autodbrestore.settings');
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
	}

	/**
	 * @return void
	 */
	public function main()
	{
		$this->language->add_lang('settings', 'blitze/autodbrestore');

		$this->tpl_name = 'acp_settings';
		$this->page_title = 'ACP_AUTODBRESTORE';

		$form_name = 'blitze/autodbrestore';

		$this->save_settings($form_name);

		include($this->phpbb_root_path . 'includes/acp/acp_database.' . $this->php_ext);

		$acp = new \acp_database();
		$acp->main('', 'restore');

		add_form_key($form_name);

		$this->template->assign_vars(array(
			'CONFIG'			=> $this->settings->get_settings(),
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

			$this->settings->save(array(
				'backup_file'		=> $this->request->variable('file', ''),
				'restore_frequency'	=> $this->request->variable('frequency', 0),
				'auto_refresh'		=> $this->request->variable('auto_refresh', true),
				'show_notice'		=> $this->request->variable('show_notice', true),
			));

			$this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_AUTODBRESTORE_UPDATED');

			trigger_error($this->user->lang('ACP_SETTING_SAVED') . adm_back_link($this->u_action));
		}
	}
}
