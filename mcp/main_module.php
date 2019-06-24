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
 * phpBB Studio - Advanced Points System MCP module.
 */
class main_module
{
	/** @var string MCP Page title */
	var $page_title;

	/** @var string MCP Page template */
	var $tpl_name;

	/** @var string Custom form action */
	var $u_action;

	function main($id, $mode)
	{
		/** @var \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container */
		global $phpbb_container;

		/** @var \phpbbstudio\aps\core\functions $functions */
		$functions = $phpbb_container->get('phpbbstudio.aps.functions');

		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');

		/** @var \phpbbstudio\aps\controller\mcp_controller $mcp_controller */
		$mcp_controller = $phpbb_container->get('phpbbstudio.aps.controller.mcp');

		// Set page title and template
		$this->tpl_name = 'mcp/mcp_aps_' . $mode;
		$this->page_title = $language->lang('MCP_APS_POINTS_' . utf8_strtoupper($mode), $functions->get_name());

		// Make the custom form action available in the controller and handle the mode
		$mcp_controller->set_page_url($this->u_action)->{$mode}();
	}
}
