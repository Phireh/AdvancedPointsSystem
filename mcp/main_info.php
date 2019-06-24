<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\mcp;

/**
 * phpBB Studio - Advanced Points System MCP module info.
 */
class main_info
{
	function module()
	{
		return [
			'filename'	=> '\phpbbstudio\aps\mcp\main_module',
			'title'		=> 'MCP_APS_POINTS',
			'modes'		=> [
				'front'	=> [
					'title'	=> 'MCP_APS_FRONT',
					'auth'	=> 'ext_phpbbstudio/aps',
					'cat'	=> ['MCP_APS_POINTS']
				],
				'change'	=> [
					'title'	=> 'MCP_APS_CHANGE',
					'auth'	=> 'ext_phpbbstudio/aps && (acl_m_aps_adjust_custom || acl_m_aps_adjust_reason)',
					'cat'	=> ['MCP_APS_POINTS']
				],
				'logs'	=> [
					'title'	=> 'MCP_APS_LOGS',
					'auth'	=> 'ext_phpbbstudio/aps && acl_u_aps_view_logs',
					'cat'	=> ['MCP_APS_POINTS']
				],
			],
		];
	}
}
