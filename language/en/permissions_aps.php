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
	'ACL_CAT_PHPBB_STUDIO'		=> 'phpBB Studio',

	'ACL_A_APS_LOGS'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the logs',
	'ACL_A_APS_POINTS'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the points',
	'ACL_A_APS_REASONS'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the reasons',
	'ACL_A_APS_DISPLAY'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the display',
	'ACL_A_APS_SETTINGS'		=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the settings',

	'ACL_M_APS_ADJUST_CUSTOM'	=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can adjust a user’s points with a custom action',
	'ACL_M_APS_ADJUST_REASON'	=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can adjust a user’s points with a predefined reason',

	'ACL_U_APS_VIEW_BUILD'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can view their augmentation<br /><em>Augmentation is the “build up” of the total points.</em>',
	'ACL_U_APS_VIEW_BUILD_OTHER'	=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can view other users’ augmentation<br /><em>This requires “Can view their augmentation” to be set to Yes.</em>',
	'ACL_U_APS_VIEW_MOD'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can view the moderator',
	'ACL_U_APS_VIEW_LOGS'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can view their logs',
	'ACL_U_APS_VIEW_LOGS_OTHER'		=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can view other users’ logs<br /><em>This requires “Can view their logs” to be set to Yes.</em>',
]);
