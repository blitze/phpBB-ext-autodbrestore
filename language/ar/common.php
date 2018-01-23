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
	'AUTODBRESTORE_NOTICE'		=> 'سيتم إزالة أي بيانات مُضافة جديدة إلى الموقع وإستعادة بعض أو جميع بيانات هذا الموقع تلقائياً كل %s دقائق !',
));
