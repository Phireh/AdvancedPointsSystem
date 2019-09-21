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
	'APS_DISABLE_EXTENDED'	=> 'Disabling the Advanced Points System is not possible as it is still being extended by an other extension. Extension name: %s',
	'APS_PHPBB_VERSION'		=> 'Minimum phpBB version required is %1$s but less than %2$s',
	'APS_UP_INSTALLED'		=> 'The extension “dmzx/ultimatepoints” is not compatible with this one!',
]);
