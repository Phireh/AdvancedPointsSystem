<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * phpBB Studio - Advanced Points System Event listener.
 */
class modules implements EventSubscriberInterface
{
	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @static
	 * @return array
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.modify_module_row' =>	'module_names',
		];
	}

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\language\language */
	protected $lang;

	/**
	 * Constructor.
	 *
	 * @param  \phpbbstudio\aps\core\functions	$functions	APS Core functions
	 * @param  \phpbb\language\language			$lang		Language object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbbstudio\aps\core\functions $functions, \phpbb\language\language $lang)
	{
		$this->functions	= $functions;
		$this->lang			= $lang;
	}

	/**
	 * Localise the APS module titles.
	 *
	 * @event  core.modify_module_row
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function module_names($event)
	{
		$module = $event['module_row'];

		$langname = $module['langname'];

		switch ($langname)
		{
			case 'ACP_APS_MODE_POINTS':
			case 'MCP_APS_POINTS':
			case 'UCP_APS_POINTS':
				$module['lang'] = $this->lang->lang($langname, ucfirst($this->functions->get_name()));
			break;

			default:
				return;
			break;
		}

		$event['module_row'] = $module;
	}
}
