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
	// Logs mode
	'ACP_APS_LOGS_EXPLAIN'			=> 'This lists all %s actions across the board. There are various sorting and searching options available.',

	'ACP_APS_LOGS_DELETED'			=> [
		1 => 'You have successfully deleted the log entry.',
		2 => 'You have successfully deleted the log entries.',
	],

	// Points mode
	'ACP_APS_POINTS_EXPLAIN'		=> 'Here you can set %1$s values for global actions. You can also manage the preset reasons used in adjusting a user’s %1$s.',
	'ACP_APS_POINTS_SUCCESS'		=> 'Advanced Points System %s updated successfully.',

	'ACP_APS_REASON_ADD'			=> 'Add reason',
	'ACP_APS_REASON_EDIT'			=> 'Edit reason',
	'ACP_APS_REASON_DELETE'			=> 'Delete reason',
	'ACP_APS_REASON_DELETE_CONFIRM'	=> 'Are you sure you wish to delete this reason?',
	'ACP_APS_REASON_DELETE_SUCCESS'	=> 'You have successfully deleted this reason.',
	'ACP_APS_REASON_SAVED'			=> 'The reason has successfully been saved.',

	'ACP_APS_REASON_EMPTY_SUBJECT'	=> 'The reason subject can not be empty.',
	'ACP_APS_REASON_EMPTY_POINTS'	=> 'The reason %s can not be empty.',

	// Display mode
	'ACP_APS_DISPLAY_EXPLAIN'		=> 'Here you can determine the availability of display blocks and define some functionality.',
	'ACP_APS_DISPLAY_SUCCESS'		=> 'Advanced Points System display settings updated successfully.',

	'ACP_APS_DISPLAY_TOP_COUNT'			=> 'Top users count',
	'ACP_APS_DISPLAY_TOP_COUNT_DESC'	=> 'The default amount of users to show for the “Top users” block.',
	'ACP_APS_DISPLAY_TOP_CHANGE'		=> 'Allow changing top user count',
	'ACP_APS_DISPLAY_TOP_CHANGE_DESC'	=> 'Whether users are allowed to increase the “Top users” count.',
	'ACP_APS_DISPLAY_ADJUSTMENTS'		=> 'Adjustments count',
	'ACP_APS_DISPLAY_ADJUSTMENTS_DESC'	=> 'The default amount of adjustments to show for the “Recent adjustments” block.',
	'ACP_APS_DISPLAY_GRAPH_TIME'		=> 'Graph animation time',
	'ACP_APS_DISPLAY_GRAPH_TIME_DESC'	=> 'The default animation time in milliseconds when displaying graph blocks.',

	// Settings mode
	'ACP_APS_SETTINGS_EXPLAIN'		=> 'Here you can determine the basic %1$s settings of your board, give it a fitting name and formatting, and among other settings adjust the default values for minimum and maximum %1$s.',
	'ACP_APS_SETTINGS_SUCCESS'		=> 'Advanced Points System settings updated successfully.',

	'ACP_APS_POINTS_CLEAN'			=> 'Clean %s table',
	'ACP_APS_POINTS_CLEAN_CONFIRM'	=> 'Are you sure you wish to clean the %s table?',
	'ACP_APS_POINTS_CLEAN_SUCCESS'	=> 'You have successfully cleaned the %s table.',

	'ACP_APS_POINTS_COPY_EMPTY'			=> 'You need to select at least 1 “from” forum and one “to” forum.',
	'ACP_APS_POINTS_COPY_TO'			=> 'Copy %s to',

	'ACP_APS_POINTS_DECIMALS'			=> 'Decimal amount',

	'ACP_APS_POINTS_DISPLAY_PM'				=> 'Display on view private message page',
	'ACP_APS_POINTS_DISPLAY_PM_DESC'		=> 'Should %s be displayed in the mini-profile on the private message page.',
	'ACP_APS_POINTS_DISPLAY_POST'			=> 'Display on viewtopic page',
	'ACP_APS_POINTS_DISPLAY_POST_DESC'		=> 'Should %s be displayed in the mini-profile on the topic page.',
	'ACP_APS_POINTS_DISPLAY_PROFILE'		=> 'Display on profile page',
	'ACP_APS_POINTS_DISPLAY_PROFILE_DESC'	=> 'Should %s be displayed in a user’s profile page.',

	'ACP_APS_POINTS_EXCLUDE_CHARS'		=> 'Exclude characters',
	'ACP_APS_POINTS_EXCLUDE_CHARS_DESC'	=> 'This will not count the characters from the excluded words when calculating %s.',
	'ACP_APS_POINTS_EXCLUDE_WORDS'		=> 'Exclude words',
	'ACP_APS_POINTS_EXCLUDE_WORDS_DESC'	=> 'This will not count the words with equal or less than X characters when calculating %s.',

	'ACP_APS_POINTS_ICON'					=> 'Icon',
	'ACP_APS_POINTS_ICON_POSITION'			=> 'Icon position',
	'ACP_APS_POINTS_ICON_POSITION_LEFT'		=> 'Left',
	'ACP_APS_POINTS_ICON_POSITION_RIGHT'	=> 'Right',

	'ACP_APS_POINTS_MIN'				=> 'Minimum user %s',
	'ACP_APS_POINTS_MIN_DESC'			=> 'If set, users’ %s can not go lower than this amount.',
	'ACP_APS_POINTS_MAX'				=> 'Maximum user %s',
	'ACP_APS_POINTS_MAX_DESC'			=> 'If set, users’ %s can not go higher than this amount.',

	'ACP_APS_POINTS_PER_PAGE'			=> '%s actions per page',
	'ACP_APS_POINTS_PER_PAGE_DESC'		=> 'The amount of %s actions that should be displayed per page.',

	'ACP_APS_POINTS_SAFE_MODE'			=> 'Safe mode',
	'ACP_APS_POINTS_SAFE_MODE_DESC'		=> 'Turning this on will catch and log any errors during point calculations.<br />When testing and developing custom actions this should be turned <strong>off</strong>.',

	'ACP_APS_FORMATTING'				=> 'Formatting',

	'ACP_APS_POINTS_NAMES'				=> 'Points names',

	'ACP_APS_CHAIN_SETTINGS'			=> 'Chain settings',
	'ACP_APS_CHAIN_MERGE_DELETE'		=> 'When “merging” also trigger “delete”',
	'ACP_APS_CHAIN_MERGE_DELETE_DESC'	=> 'If a topic is merged into an other, the initial topic will be deleted.<br />This determines if %s should be calculated for the delete action.',
	'ACP_APS_CHAIN_MERGE_MOVE'			=> 'When “merging” also trigger “move”',
	'ACP_APS_CHAIN_MERGE_MOVE_DESC'		=> 'If a topic is merged into an other, the initial topic’s posts will be moved.<br />This determines if %s should be calculated for the move action.',
	'ACP_APS_CHAIN_WARN_PM'				=> 'When “warning” also trigger “pm”',
	'ACP_APS_CHAIN_WARN_PM_DESC'		=> 'If a user is warned and <samp>“Notify user”</samp> is checked, a private message is send.<br />This determines if %s should be calculated for the private message action.',

	'ACP_APS_CHARACTERS'				=> 'character(s)',

	'ACP_APS_SEPARATOR_DEC'				=> 'Decimal separator',
	'ACP_APS_SEPARATOR_THOU'			=> 'Thousands separator',
	'ACP_APS_SEPARATOR_COMMA'			=> 'Comma',
	'ACP_APS_SEPARATOR_PERIOD'			=> 'Period',
	'ACP_APS_SEPARATOR_DASH'			=> 'Dash',
	'ACP_APS_SEPARATOR_UNDERSCORE'		=> 'Underscore',
	'ACP_APS_SEPARATOR_SPACE'			=> 'Space',
	'ACP_APS_SEPARATOR_SPACE_NARROW'	=> 'Narrow space',
]);
