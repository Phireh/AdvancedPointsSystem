<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\acp;

/**
 * phpBB Studio - Advanced Points System ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\phpbbstudio\aps\acp\main_module',
			'title'		=> 'ACP_APS_POINTS',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_APS_MODE_SETTINGS',
					'auth'	=> 'ext_phpbbstudio/aps && acl_a_aps_settings',
					'cat'	=> ['ACP_APS_POINTS']
				],
				'display'	=> [
					'title'	=> 'ACP_APS_MODE_DISPLAY',
					'auth'	=> 'ext_phpbbstudio/aps && acl_a_aps_display',
					'cat'	=> ['ACP_APS_POINTS'],
				],
				'points'	=> [
					'title'	=> 'ACP_APS_MODE_POINTS',
					'auth'	=> 'ext_phpbbstudio/aps && (acl_a_aps_points || acl_a_aps_reasons)',
					'cat'	=> ['ACP_APS_POINTS'],
				],
				'logs'		=> [
					'title'	=> 'ACP_APS_MODE_LOGS',
					'auth'	=> 'ext_phpbbstudio/aps && acl_a_aps_logs',
					'cat'	=> ['ACP_APS_POINTS'],
				]
			],
		];
	}
}
