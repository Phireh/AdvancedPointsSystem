<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/**
 * Some characters you may want to copy&paste: ’ » “ ” …
 */
$lang = array_merge($lang, [
	'APS_NOTIFICATION_ADJUSTED'	=> '<strong>Your %1$s were adjusted:</strong> %2$s',
	'APS_VIEWING_POINTS_PAGE'	=> 'Viewing the %s page',

	'APS_POINTS_TOO_LOW'		=> 'You do not have enough %s to perform this action.',
	'APS_POINTS_ACTION_COST'	=> 'The cost of this action is %s',
]);
