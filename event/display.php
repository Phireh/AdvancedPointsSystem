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
 * phpBB Studio - Advanced Points System Event listener: Display.
 */
class display implements EventSubscriberInterface
{
	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param  \phpbbstudio\aps\core\functions	$functions	APS Core functions
	 * @param  \phpbb\controller\helper			$helper		Controller helper object
	 * @param  \phpbb\language\language			$language	Language object
	 * @param  \phpbb\template\template			$template	Template object
	 * @param  string							$php_ext	php File extension
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbbstudio\aps\core\functions $functions,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		$php_ext
	)
	{
		$this->functions	= $functions;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->template		= $template;

		$this->php_ext		= $php_ext;
	}

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
			'core.user_setup'						=> 'load_lang',
			'core.page_header_after'				=> 'display_links',

			'core.viewonline_overwrite_location'	=> 'view_online',

			'core.ucp_pm_view_message'				=> 'display_pm',
			'core.viewtopic_post_rowset_data'		=> 'set_post',
			'core.viewtopic_cache_user_data'		=> 'cache_post',
			'core.viewtopic_modify_post_row'		=> 'display_post',
			'core.memberlist_prepare_profile_data'	=> 'display_profile',
		];
	}

	/**
	 * Load language during user set up.
	 *
	 * @event  core.user_setup
	 * @return void
	 * @access public
	 */
	public function load_lang()
	{
		$this->language->add_lang('aps_common', 'phpbbstudio/aps');
	}

	public function display_links()
	{
		$locations = array_filter($this->functions->get_link_locations());

		if ($locations)
		{
			$this->template->assign_vars(array_combine(array_map(function($key) {
				return 'S_APS_' . strtoupper($key);
			}, array_keys($locations)), $locations));
		}
	}

	/**
	 * Display the points page when viewing the Who is Online page.
	 *
	 * @event  core.viewonline_overwrite_location
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function view_online(\phpbb\event\data $event)
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/points') === 0)
		{
			$event['location'] = $this->language->lang('APS_VIEWING_POINTS_PAGE', $this->functions->get_name());
			$event['location_url'] = $this->helper->route('phpbbstudio_aps_display');
		}
	}

	/**
	 * Display the user points when viewing a Private Message.
	 *
	 * @event  core.ucp_pm_view_message
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function display_pm(\phpbb\event\data $event)
	{
		$event['msg_data'] = array_merge($event['msg_data'], [
			'AUTHOR_POINTS'	=> $event['user_info']['user_points'],
		]);
	}

	/**
	 * Set the user points after being retrieved from the database.
	 *
	 * @event  core.viewtopic_post_rowset_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function set_post(\phpbb\event\data $event)
	{
		$event['rowset_data'] = array_merge($event['rowset_data'], [
			'user_points' => $event['row']['user_points'],
		]);
	}

	/**
	 * Cache the user points when displaying a post.
	 *
	 * @event  core.viewtopic_cache_user_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function cache_post(\phpbb\event\data $event)
	{
		$event['user_cache_data'] = array_merge($event['user_cache_data'], [
			'user_points' => $event['row']['user_points'],
		]);
	}

	/**
	 * Display the user points when displaying a post.
	 *
	 * @event  core.viewtopic_modify_post_row
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function display_post(\phpbb\event\data $event)
	{
		$event['post_row'] = array_merge($event['post_row'], [
			'POSTER_POINTS' => $event['user_cache'][$event['poster_id']]['user_points'],
		]);
	}

	/**
	 * Display the user points when display a profile.
	 *
	 * @event  core.memberlist_prepare_profile_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function display_profile(\phpbb\event\data $event)
	{
		$event['template_data'] = array_merge($event['template_data'], [
			'USER_POINTS'	=> $event['data']['user_points'],
		]);
	}
}
