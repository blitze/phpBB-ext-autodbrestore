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
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\autodbrestore\services\config */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language					$language		Language object
	 * @param \phpbb\template\template					$template		Template object
	 * @param \blitze\autodbrestore\services\config		$config			Autodbrestore config object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\template\template $template, \blitze\autodbrestore\services\config $config)
	{
		$this->language = $language;
		$this->template = $template;
		$this->config = $config;
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
			'AUTO_DB_RESTORE'			=> $this->config->is_ready(),
			'AUTO_DB_RESTORE_NOTICE'	=> $this->language->lang('AUTODBRESTORE_NOTICE', $this->config->get('restore_frequency')),
		));
	}
}
