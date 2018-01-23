<?php
/**
 *
 * Auto Database Restore. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, blitze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Translated By : Bassel Taha Alhitary - www.alhitary.net
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
	'AUTO_REFRESH'				=> 'التحديث التلقائي للصفحة ؟',
	'ACP_SETTING_SAVED'			=> 'تم حفظ الإعدادات بنجاح',
	'CREATE_NEW_BACKUP'			=> 'إنشاء نسخة إحتياطية جديدة',
	'RESTORE_CUSTOM'			=> 'تخصيص',
	'RESTORE_FREQUENCY_0'		=> 'تعطيل',
	'RESTORE_FREQUENCY_15'		=> 'كل 15 دقيقة',
	'RESTORE_FREQUENCY_30'		=> 'كل 30 دقيقة',
	'RESTORE_FREQUENCY_60'		=> 'كل ساعة',
	'SELECT_FREQUENCY'			=> 'تكرار عملية الإستعادة ',
	'SHOW_RESTORE_NOTICE'		=> 'اظهار شريط التنبيه في أعلى المنتدى ؟',
));
