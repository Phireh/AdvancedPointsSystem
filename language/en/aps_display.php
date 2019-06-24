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
	'APS_OVERVIEW'	=> 'Overview',
	'APS_SUCCESS'	=> 'Success',
	'APS_TOP_USERS'	=> 'Top users',

	'APS_ADJUST_USER_POINTS'	=> 'Adjust user %s',

	'APS_POINTS_ACTION'					=> '%s action',
	'APS_POINTS_ACTION_SEARCH'			=> 'Search %s action',
	'APS_POINTS_ACTION_TIME'			=> '%s action time',
	'APS_POINTS_ACTIONS'				=> '%s actions',
	'APS_POINTS_ACTIONS_ALL'			=> 'All %s actions',
	'APS_POINTS_ACTIONS_NONE'			=> 'There are no %s actions yet.',
	'APS_POINTS_ACTIONS_PAGE'			=> 'Actions per page',
	'APS_POINTS_ACTIONS_TOTAL'			=> [
		1 => '%2$d %1$s action',
		2 => '%2$d %1$s actions',
	],

	'APS_POINTS_BLOCK_ADD'			=> '%s block was added!',
	'APS_POINTS_BLOCK_DELETE'		=> '%s block was removed!',
	'APS_POINTS_BLOCK_MOVE'			=> '%s block was moved!',
	'APS_POINTS_BLOCK_NO'			=> 'No blocks',
	'APS_POINTS_BLOCK_NONE'			=> 'It looks like you do not have any blocks added.',
	'APS_POINTS_BLOCK_NO_CONTENT'	=> 'Oops! Looks like something went wrong.<br />This block does not have any content!<br /><br />The required <code>{% block content %}...{% endblock %}</code> is missing!',

	'APS_POINTS_FORMAT'	=> '%s format',

	'APS_POINTS_MAX'	=> 'Maximum %s',
	'APS_POINTS_MIN'	=> 'Minimum %s',

	'APS_POINTS_NAME'	=> 'Name',

	'APS_POINTS_DATA_EMPTY'	=> 'No %s data to display',
	'APS_POINTS_GAINED'		=> '%s gained',
	'APS_POINTS_GLOBAL'		=> 'Global',
	'APS_POINTS_GROWTH'		=> '%s growth',
	'APS_POINTS_LOST'		=> '%s lost',
	'APS_POINTS_TRADE_OFF'	=> '%s trade off',
	'APS_POINTS_PER_FORUM'	=> '%s per forum',
	'APS_POINTS_PER_GROUP'	=> '%s per group',

	'APS_RANDOM_USER'	=> 'Random user',

	'APS_RECENT_ADJUSTMENTS'	=> 'Recent adjustments',
	'APS_RECENT_ATTACHMENTS'	=> 'Recent attachments',
	'APS_RECENT_POLL'			=> 'Recent poll',
]);
