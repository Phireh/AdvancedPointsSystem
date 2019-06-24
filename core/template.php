<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\core;

/**
 * phpBB Studio - Advanced Points System twig extension.
 */
class template extends \Twig_Extension
{
	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/**
	 * Constructor.
	 *
	 * @param  \phpbbstudio\aps\core\functions	$functions	APS Core functions
	 * @return void
	 * @access public
	 */
	public function __construct(functions $functions)
	{
		$this->functions = $functions;
	}

	/**
	 * Get the name of this extension
	 *
	 * @return string
	 * @access public
	 */
	public function getName()
	{
		return 'phpbbstudio_aps';
	}

	/**
	 * Returns a list of global functions to add to the existing list.
	 *
	 * @return array An array of global functions
	 * @access public
	 */
	public function getFunctions()
	{
		return [
			// Template functions prefixed with "aps_" come here
			new \Twig_SimpleFunction('aps_*', [$this, 'aps_handle']),
		];
	}

	/**
	 * Handle the called template function.
	 *
	 * @param  string	$function		The APS Core function name
	 * @param  mixed	$points			First parameter from the called template function
	 * @param  bool		$boolean		Second parameter from the called template function
	 * @return mixed
	 * @access public
	 */
	public function aps_handle($function, $points = 0, $boolean = true)
	{
		switch ($function)
		{
			case 'auth':
				return $this->functions->get_auth($points, $boolean);
			break;

			case 'config':
				return $this->functions->get_config($points);
			break;

			case 'display':
				return $this->functions->display_points($points, $boolean);
			break;

			case 'format':
				return $this->functions->format_points($points);
			break;

			case 'icon':
				return $this->functions->get_icon();
			break;

			case 'name';
				return $this->functions->get_name();
			break;

			case 'step':
				return $this->functions->get_step();
			break;

			default:
				return '';
			break;
		}
	}
}
