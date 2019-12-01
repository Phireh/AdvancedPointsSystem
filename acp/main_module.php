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

use Symfony\Component\DependencyInjection\ContainerInterface;

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

	/** @var ContainerInterface */
	protected $container;

	/** @var \phpbb\language\language */
	protected $language;

	public function main($id, $mode)
	{
		/** @var ContainerInterface $phpbb_container */
		global $phpbb_container;

		$this->container = $phpbb_container;
		$this->language = $this->container->get('language');

		/** @var \phpbbstudio\aps\controller\acp_controller $controller */
		$controller = $this->container->get('phpbbstudio.aps.controller.acp');

		/** @var \phpbbstudio\aps\core\functions $functions */
		$functions = $this->container->get('phpbbstudio.aps.functions');

		// Set the page title and template
		$this->tpl_name = 'aps_' . $mode;
		$this->page_title = $this->language->lang('ACP_APS_POINTS') . ' &bull; ' . $this->language->lang('ACP_APS_MODE_' . utf8_strtoupper($mode), $functions->get_name());

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
		return '<label><a class="aps-button-green" href="' . $this->u_action . '&action='.  $action . '" data-ajax="true">' . $this->language->lang('RUN') . '</a></label>';
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
	 * Build configuration template for the points icon image.
	 *
	 * @param  string	$value		The config value
	 * @param  string	$key		The config key
	 * @return string				The configuration template
	 * @access public
	 */
	public function build_icon_image_select($value, $key = '')
	{
		$directory = $this->container->getParameter('core.root_path') . '/images';

		$files	= array_diff(scandir($directory), ['.', '..']);
		$images	= array_filter($files, function($file) use ($directory)
		{
			$file = "{$directory}/{$file}";

			return is_file($file) && filesize($file) && preg_match('#\.gif|jpg|jpeg|png|svg$#i', $file);
		});

		$select = '<select id="' . $key . '" name="config[' . $key . ']">';
		$select .= '<option value="">' . $this->language->lang('ACP_APS_POINTS_ICON_IMG_NO') . '</option>';

		foreach ($images as $image)
		{
			$selected = $value === $image;

			$select .= '<option value="' . $image . ($selected ? '" selected="selected' : '') . '">' . $image . '</option>';
		}

		$select .= '</select>';

		return $select;
	}

	/**
	 * Build configuration template for the points icon position.
	 *
	 * @param  string	$value		The config value
	 * @param  string	$key		The config key
	 * @return string				The configuration template
	 * @access public
	 */
	public function build_position_radio($value, $key = '')
	{
		$html = '';
		$s_id = false;

		$positions = [0 => 'ACP_APS_POINTS_ICON_POSITION_LEFT', 1 => 'ACP_APS_POINTS_ICON_POSITION_RIGHT'];

		foreach ($positions as $val => $title)
		{
			$check = $value === $val ? ' checked' : '';
			$id = $s_id ? ' id="' . $key . '"' : '';

			$html .= '<label>';
			$html .= '<input class="radio aps-radio"' . $id . ' name="config[' . $key . ']" type="radio" value="' . $val . '"' . $check . '>';
			$html .= '<span class="aps-button-blue">' . $this->language->lang($title) . '</span>';
			$html .= '</label>';

			$s_id = true;
		}

		return $html;
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
