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
	'ACP_APS_POINTS_COPY'				=> 'Copy %s from',
	'ACP_APS_POINTS_COPY_EMPTY_FROM'	=> 'You have to select a “from” forum',
	'ACP_APS_POINTS_COPY_EXPLAIN'		=> 'If you select to copy %1$s, the forum will have the same %1$s as the one you select here. This will overwrite any %1$s you have previously set for this forum with the %1$s of the forum you select here. If no forum is selected, the current %1$s will be kept.',
	'ACP_APS_POINTS_COPY_NOT'			=> 'Do not copy %s',
	'ACP_APS_POINTS_COPY_SUCCESS'		=> 'You have successfully copied the %s.',
	'ACP_APS_POINTS_COPY_TITLE'			=> 'Copy %s',

	'ACP_APS_POINTS_RESET'				=> 'Reset %s',
	'ACP_APS_POINTS_RESET_CONFIRM'		=> 'Are you sure you wish to to reset the %s for this forum?',
	'ACP_APS_POINTS_RESET_EXPLAIN'		=> 'If you select to reset %1$s, all values for this forum will be set to 0. This will overwrite any %1$s you have previously set for this forum or any forum you selected below to copy %1$s from.',
	'ACP_APS_POINTS_RESET_SUCCESS'		=> 'You have successfully reset the %s.',

	'APS_POINTS_DIFF'					=> '%s difference',
	'APS_POINTS_OLD'					=> 'Old %s',
	'APS_POINTS_NEW'					=> 'New %s',

	# Global
	'APS_POINTS_REGISTER'				=> 'Registered',
	'APS_POINTS_BIRTHDAY'				=> 'Celebrated their birthday',
	'APS_POINTS_BIRTHDAY_DESC'			=> 'This action is ran through the system cron once a day. <br /><samp>ACP &raquo; General &raquo; Server settings &raquo; Run periodic tasks from system cron',
	'APS_POINTS_MOD_WARN'				=> 'Warned a user',
	'APS_POINTS_USER_WARN'				=> 'Received a warning',
	'APS_POINTS_PM'						=> 'Created a private message',
	'APS_POINTS_PM_PER_RECIPIENT'		=> 'Per recipient',

	# Misc
	'ACP_APS_POINTS_MISC'				=> 'Miscellaneous',
	'APS_POINTS_PER_VOTE'				=> 'Per option voted for',
	'APS_POINTS_VOTE_ADDED'				=> 'Voted in a poll',
	'APS_POINTS_VOTE_REMOVED'			=> 'Removed a vote',
	'APS_POINTS_VOTE_AMOUNT'			=> 'Amount of options voted for',

	# Topics / Posts
	'APS_POINTS_POST'					=> 'Created a post',
	'APS_POINTS_TOPIC'					=> 'Created a topic',
	'APS_POINTS_STICKY'					=> 'Created a sticky',
	'APS_POINTS_ANNOUNCE'				=> 'Created an announcement',
	'APS_POINTS_GLOBAL'					=> 'Created a global announcement',
	'APS_POINTS_PER_CHAR'				=> 'Per character',
	'APS_POINTS_PER_CHAR_DESC'			=> 'The text is stripped from BBCodes before counting the characters.',
	'APS_POINTS_PER_WORD'				=> 'Per word',
	'APS_POINTS_PER_WORD_DESC'			=> 'The text is stripped from BBCodes before counting the words.',
	'APS_POINTS_ATTACH_HAS'				=> 'Including attachment(s)',
	'APS_POINTS_ATTACH_PER'				=> 'Per included attachment',
	'APS_POINTS_PER_QUOTE'				=> 'Per quote',
	'APS_POINTS_PER_QUOTE_DESC'			=> 'Only the outer most quotes are counted and only if there is an author provided.',
	'APS_POINTS_POLL_HAS'				=> 'Included a poll',
	'APS_POINTS_POLL_OPTION'			=> 'Per included poll option',
	'APS_POINTS_EDIT'					=> 'Edited their post',
	'APS_POINTS_DELETE'					=> 'Deleted their post',
	'APS_POINTS_DELETE_SOFT'			=> 'Soft deleted their post',
	'APS_POINTS_BUMP'					=> 'Bumped a topic',

	# Topic types
	'ACP_APS_TOPIC_TYPES'				=> 'Topic types',

	'APS_POINTS_MOD_NORMAL_STICKY'		=> 'Made a topic a sticky',
	'APS_POINTS_MOD_NORMAL_ANNOUNCE'	=> 'Made a topic an announcement',
	'APS_POINTS_MOD_NORMAL_GLOBAL'		=> 'Made a topic a global announcement',
	'APS_POINTS_MOD_STICKY_NORMAL'		=> 'Made a sticky a normal topic',
	'APS_POINTS_MOD_STICKY_ANNOUNCE'	=> 'Made a sticky an announcement',
	'APS_POINTS_MOD_STICKY_GLOBAL'		=> 'Made a sticky a global announcement',
	'APS_POINTS_MOD_ANNOUNCE_NORMAL'	=> 'Made an announcement a normal topic',
	'APS_POINTS_MOD_ANNOUNCE_STICKY'	=> 'Made an announcement a sticky',
	'APS_POINTS_MOD_ANNOUNCE_GLOBAL'	=> 'Made an announcement a global announcement',
	'APS_POINTS_MOD_GLOBAL_NORMAL'		=> 'Made a global announcement a normal topic',
	'APS_POINTS_MOD_GLOBAL_STICKY'		=> 'Made a global announcement a sticky',
	'APS_POINTS_MOD_GLOBAL_ANNOUNCE'	=> 'Made a global announcement an announcement',

	'APS_POINTS_USER_NORMAL_STICKY'		=> 'Their topic was made a sticky',
	'APS_POINTS_USER_NORMAL_ANNOUNCE'	=> 'Their topic was made an announcement',
	'APS_POINTS_USER_NORMAL_GLOBAL'		=> 'Their topic was made a global announcement',
	'APS_POINTS_USER_STICKY_NORMAL'		=> 'Their sticky was made a normal topic',
	'APS_POINTS_USER_STICKY_ANNOUNCE'	=> 'Their sticky was made an announcement',
	'APS_POINTS_USER_STICKY_GLOBAL'		=> 'Their sticky was made a global announcement',
	'APS_POINTS_USER_ANNOUNCE_NORMAL'	=> 'Their announcement was made a normal topic',
	'APS_POINTS_USER_ANNOUNCE_STICKY'	=> 'Their announcement was made a sticky',
	'APS_POINTS_USER_ANNOUNCE_GLOBAL'	=> 'Their announcement was made a global announcement',
	'APS_POINTS_USER_GLOBAL_NORMAL'		=> 'Their global announcement was made a normal topic',
	'APS_POINTS_USER_GLOBAL_STICKY'		=> 'Their global announcement was made a sticky',
	'APS_POINTS_USER_GLOBAL_ANNOUNCE'	=> 'Their global announcement was made an announcement',

	# Moderation
	'APS_POINTS_MOD_COPY'				=> 'Copied a topic from this forum',
	'APS_POINTS_USER_COPY'				=> 'Their topic got copied from this forum',

	'APS_POINTS_MOD_CHANGE'				=> 'Changed a post’s author',
	'APS_POINTS_USER_CHANGE_FROM'		=> 'Removed as a post’s author',
	'APS_POINTS_USER_CHANGE_TO'			=> 'Became a post’s author',

	'APS_POINTS_MOD_DELETE_POST'		=> 'Deleted a post',
	'APS_POINTS_USER_DELETE_POST'		=> 'Their post got deleted',
	'APS_POINTS_MOD_DELETE_SOFT_POST'	=> 'Soft deleted a post',
	'APS_POINTS_USER_DELETE_SOFT_POST'	=> 'Their post got soft deleted',
	'APS_POINTS_MOD_DELETE_TOPIC'		=> 'Deleted a topic',
	'APS_POINTS_USER_DELETE_TOPIC'		=> 'Their topic got deleted',
	'APS_POINTS_MOD_DELETE_SOFT_TOPIC'	=> 'Soft deleted a topic',
	'APS_POINTS_USER_DELETE_SOFT_TOPIC'	=> 'Their topic got soft deleted',

	'APS_POINTS_MOD_EDIT'				=> 'Edited a post',
	'APS_POINTS_USER_EDIT'				=> 'Their post got edited',

	'APS_POINTS_MOD_LOCK'				=> 'Locked a topic',
	'APS_POINTS_USER_LOCK'				=> 'Their topic got locked',
	'APS_POINTS_MOD_LOCK_POST'			=> 'Locked a post',
	'APS_POINTS_USER_LOCK_POST'			=> 'Their post got locked',
	'APS_POINTS_MOD_UNLOCK'				=> 'Unlocked a topic',
	'APS_POINTS_USER_UNLOCK'			=> 'Their topic got unlocked',
	'APS_POINTS_MOD_UNLOCK_POST'		=> 'Unlocked a post',
	'APS_POINTS_USER_UNLOCK_POST'		=> 'Their post got unlocked',

	'APS_POINTS_MOD_MERGE'				=> 'Merged a topic',
	'APS_POINTS_MOD_MERGE_DESC'			=> 'This will also trigger the “delete” action on the topics that are being merged into an other.',
	'APS_POINTS_USER_MERGE'				=> 'Their topic got merged',
	'APS_POINTS_USER_MERGE_DESC'		=> 'This will also trigger the “delete” action on the topics that are being merged into an other.',

	'APS_POINTS_MOD_MOVE_POST'			=> 'Moved a post',
	'APS_POINTS_MOD_MOVE_POST_DESC'		=> 'Moved values are for moving <strong>from</strong> this forum, not <em>to</em>.',
	'APS_POINTS_USER_MOVE_POST'			=> 'Their post got moved',
	'APS_POINTS_MOD_MOVE_TOPIC'			=> 'Moved a topic',
	'APS_POINTS_USER_MOVE_TOPIC'		=> 'Their topic got moved',

	'APS_POINTS_MOD_APPROVE'			=> 'Approved a post',
	'APS_POINTS_MOD_DISAPPROVE'			=> 'Disapproved a post',
	'APS_POINTS_MOD_RESTORE'			=> 'Restored a post',
	'APS_POINTS_USER_APPROVE'			=> 'Their post is approved',
	'APS_POINTS_USER_DISAPPROVE'		=> 'Their post is disapproved',
	'APS_POINTS_USER_RESTORE'			=> 'Their post is restored',

	'APS_POINTS_USER_ADJUSTED'			=> 'Adjusted by moderator',
]);
