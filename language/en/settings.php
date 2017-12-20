<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'AUTO_REFRESH'				=> 'Auto refresh to run cron?',
	'ACP_SETTING_SAVED'			=> 'Your settings have been saved',
	'CREATE_NEW_BACKUP'			=> 'Create new backup',
	'RESTORE_CUSTOM'			=> 'Custom',
	'RESTORE_FREQUENCY_0'		=> 'Disabled',
	'RESTORE_FREQUENCY_15'		=> 'Every 15 minutes',
	'RESTORE_FREQUENCY_30'		=> 'Every 30 minutes',
	'RESTORE_FREQUENCY_60'		=> 'Every hour',
	'SELECT_FREQUENCY'			=> 'Select restore frequency',
	'SHOW_RESTORE_NOTICE'		=> 'Show notice on front page?',
));
