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
	'MCP_APS_POINTS'		=> '%s',
	'MCP_APS_CHANGE'		=> 'Change',
	'MCP_APS_FRONT'			=> 'Front',
	'MCP_APS_LOGS'			=> 'Logs',

	'MCP_APS_LATEST_ADJUSTED'		=> 'Latest %d adjustments',
	'MCP_APS_USERS_TOP'				=> 'Top %d users',
	'MCP_APS_USERS_BOTTOM'			=> 'Bottom %d users',

	'MCP_APS_POINTS_CURRENT'				=> 'Current %s',
	'MCP_APS_POINTS_CHANGE'					=> 'Change %s',

	'MCP_APS_POINTS_USER_CHANGE'			=> 'Are you sure you want to adjust this user’s %s?',
	'MCP_APS_POINTS_USER_CHANGE_SUCCESS'	=> 'The %s for this user have successfully been adjusted.',
]);
