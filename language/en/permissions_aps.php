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

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ACL_CAT_PHPBB_STUDIO'		=> 'phpBB Studio',

	'ACL_A_APS_LOGS'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the logs',
	'ACL_A_APS_POINTS'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the points',
	'ACL_A_APS_REASONS'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the reasons',
	'ACL_A_APS_DISPLAY'			=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the display',
	'ACL_A_APS_SETTINGS'		=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can manage the settings',

	'ACL_M_APS_ADJUST_CUSTOM'	=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can adjust a user’s points with a custom action',
	'ACL_M_APS_ADJUST_REASON'	=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can adjust a user’s points with a predefined reason',

	'ACL_U_APS_VIEW_BUILD'		=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can view the augmentation<br /><em>Augmentation is the “build up” of the total points.</em>',
	'ACL_U_APS_VIEW_MOD'		=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can view the moderator',
	'ACL_U_APS_VIEW_LOGS'		=> '<strong><abbr title="Advanced Points System">APS</abbr></strong> - Can view the logs',
]);
