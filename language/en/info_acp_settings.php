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
	'ACP_AUTODBRESTORE'			=> 'Auto Database Restore',
	'ACP_SETTING_SAVED'			=> 'Your settings have been saved',
	'CREATE_NEW_BACKUP'			=> 'Create new backup',
	'SELECT_FREQUENCY'			=> 'Select restore frequency',
	'RESTORE_FREQUENCY_0'		=> 'Disabled',
	'RESTORE_FREQUENCY_15'		=> 'Every 15 minutes',
	'RESTORE_FREQUENCY_30'		=> 'Every 30 minutes',
	'RESTORE_FREQUENCY_60'		=> 'Every hour',
	'RESTORE_CUSTOM'			=> 'Custom',
));
