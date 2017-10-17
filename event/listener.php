<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace blitze\autodbrestore\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config			$config			Config object
	 * @param \phpbb\language\language		$language		Language object
	 * @param \phpbb\template\template		$template		Template object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\template\template $template)
	{
		$this->config = $config;
		$this->language = $language;
		$this->template = $template;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.user_setup'		=> 'load_common_language',
			'core.page_header'		=> 'show_notice',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function load_common_language(\phpbb\event\data $event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'blitze/autodbrestore',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * @return void
	 */
	public function show_notice()
	{
		$this->template->assign_vars(array(
			'AUTO_DB_RESTORE'			=> $this->is_enabled(),
			'AUTO_DB_RESTORE_NOTICE'	=> $this->language->lang('AUTODBRESTORE_NOTICE', $this->config['blitze_autodbrestore_frequency']),
		));
	}

	/**
	 * @return bool
	 */
	protected function is_enabled()
	{
		return ($this->config['blitze_autodbrestore_file'] && $this->config['blitze_autodbrestore_frequency']);
	}
}
