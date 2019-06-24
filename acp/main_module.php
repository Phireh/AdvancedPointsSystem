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
 * phpBB Studio - Advanced Points System ACP module.
 */
class main_module
{
	/** @var string ACP Page title */
	public $page_title;

	/** @var string ACP Page template */
	public $tpl_name;

	/** @var string Custom form action */
	public $u_action;

	/** @var \phpbb\language\language */
	protected $lang;

	public function main($id, $mode)
	{
		/** @var \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container */
		global $phpbb_container;

		/** @var \phpbbstudio\aps\controller\acp_controller $controller */
		$controller = $phpbb_container->get('phpbbstudio.aps.controller.acp');

		/** @var \phpbbstudio\aps\core\functions $functions */
		$functions = $phpbb_container->get('phpbbstudio.aps.functions');

		$this->lang = $phpbb_container->get('language');

		// Set the page title and template
		$this->tpl_name = 'aps_' . $mode;
		$this->page_title = $this->lang->lang('ACP_APS_POINTS') . ' &bull; ' . $this->lang->lang('ACP_APS_MODE_' . utf8_strtoupper($mode), $functions->get_name());

		// Make the custom form action available in the controller and handle the mode
		$controller->set_page_url($this->u_action)->{$mode}();
	}

	/**
	 * Build configuration template for custom points actions.
	 *
	 * @param  string	$action		The custom points action
	 * @return string				The configuration template
	 * @access public
	 */
	public function set_action($action)
	{
		return '<label><a class="aps-button-green" href="' . $this->u_action . '&action='.  $action . '" data-ajax="true">' . $this->lang->lang('RUN') . '</a></label>';
	}

	/**
	 * Build configuration template for the points separator.
	 *
	 * @param  string	$value		The config value
	 * @return string				The HTML formatted select options
	 * @access public
	 */
	public function build_separator_select($value)
	{
		$space = htmlspecialchars('&nbsp;');
		$narrow = htmlspecialchars('&#8239;');

		$separators = [
			','		=> 'ACP_APS_SEPARATOR_COMMA',
			'.'		=> 'ACP_APS_SEPARATOR_PERIOD',
			'-'		=> 'ACP_APS_SEPARATOR_DASH',
			'_'		=> 'ACP_APS_SEPARATOR_UNDERSCORE',
			$space	=> 'ACP_APS_SEPARATOR_SPACE',
			$narrow	=> 'ACP_APS_SEPARATOR_SPACE_NARROW',
		];

		return build_select($separators, $value);
	}

	/**
	 * Build configuration template for the points icon.
	 *
	 * @param  string	$value		The config value
	 * @param  string	$key		The config key
	 * @return string				The configuration template
	 * @access public
	 */
	public function build_position_radio($value, $key = '')
	{
		$position_array = [0 => 'ACP_APS_POINTS_ICON_POSITION_LEFT', 1 => 'ACP_APS_POINTS_ICON_POSITION_RIGHT'];

		return h_radio("config[{$key}]", $position_array, $value, $key);
	}

	/**
	 * Build configuration template for the points decimals.
	 *
	 * @param  string	$value		The config value
	 * @return string				The configuration template
	 * @access public
	 */
	public function build_decimal_select($value)
	{
		$options = '';

		for ($i = 0; $i <= 2; $i++)
		{
			$options .= '<option value="' . $i . ((int) $value === $i ? '" selected' : '"') . '>' . $i . '</option>';
		}

		return $options;
	}
}
