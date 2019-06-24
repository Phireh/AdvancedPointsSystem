<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\notification\type;

/**
 * phpBB Studio - Advanced Points System notification
 */
class adjust extends \phpbb\notification\type\base
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/**
	 * Set the auth object.
	 *
	 * @param  \phpbb\auth\auth		$auth	Auth object
	 * @return void
	 * @access public
	 */
	public function set_auth(\phpbb\auth\auth $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Set the controller helper object.
	 *
	 * @param  \phpbb\controller\helper	$helper		Controller helper object
	 * @return void
	 * @access public
	 */
	public function set_controller_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	/**
	 * Set the user loader object.
	 *
	 * @param  \phpbb\user_loader	$user_loader	User loader object
	 * @return void
	 * @access public
	 */
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	 * Get notification type name.
	 *
	 * @return string			The notification name as defined in services.yml
	 * @access public
	 */
	public function get_type()
	{
		return 'phpbbstudio.aps.notification.type.adjust';
	}

	/**
	 * Notification option data (for outputting to the user).
	 *
	 * @var bool|array False if the service should use it's default data
	 * 					Array of data (including keys 'id', 'lang', and 'group')
	 * @access public
	 * @static
	 */
	public static $notification_option = false;

	/**
	 * Is this type available to the current user.
	 * (defines whether or not it will be shown in the UCP Edit notification options)
	 *
	 * @return bool				True/False: whether or not this is available to the user
	 * @access public
	 */
	public function is_available()
	{
		return false;
	}

	/**
	 * Get the id of the notification.
	 *
	 * @param  array	$data	The notification type specific data
	 * @return int				Identifier of the notification
	 * @access public
	 */
	public static function get_item_id($data)
	{
		return $data['notification_id'];
	}

	/**
	 * Get the id of the parent.
	 *
	 * @param  array	$data	The type notification specific data
	 * @return int				Identifier of the parent
	 * @access public
	 */
	public static function get_item_parent_id($data)
	{
		// No parent
		return 0;
	}

	/**
	 * Find the users who want to receive notifications.
	 *
	 * @param array $data		The type specific data
	 * @param array $options	Options for finding users for notification
	 * 				  ignore_users => array of users and user types that should not receive notifications from this type
	 *               				  because they've already been notified
	 * 				  e.g.: array(2 => array(''), 3 => array('', 'email'), ...)
	 * @return array		Array of user identifiers with their notification method(s)
	 * @access public
	 */
	public function find_users_for_notification($data, $options = [])
	{
		// Return an array of users to be notified, storing the user_ids as the array keys
		return [
			$data['user_id'] => $this->notification_manager->get_default_methods(),
		];
	}

	/**
	 * Users needed to query before this notification can be displayed.
	 *
	 * @return array		Array of user identifiers to query.
	 * @access public
	 */
	public function users_to_query()
	{
		if ($this->auth->acl_get('u_aps_view_mod'))
		{
			return [$this->get_data('moderator_id')];
		}

		return [];
	}

	/**
	 * Get the user's avatar.
	 *
	 * @return string		The HTML formatted avatar
	 */
	public function get_avatar()
	{
		return $this->auth->acl_get('u_aps_view_mod') ? $this->user_loader->get_avatar($this->get_data('moderator_id'), false, true) : '';
	}

	/**
	 * Get the HTML formatted title of this notification.
	 *
	 * @return string		The HTML formatted title
	 * @access public
	 */
	public function get_title()
	{
		return $this->language->lang('APS_NOTIFICATION_ADJUSTED', $this->get_data('name'), $this->get_data('points'));
	}

	/**
	 * Get the HTML formatted reference of the notification.
	 *
	 * @return string		The HTML formatted reference
	 * @access public
	 */
	public function get_reference()
	{
		if ($reason = $this->get_data('reason'))
		{
			$moderator = $this->auth->acl_get('u_aps_view_mod') ? $this->get_data('moderator') : $this->language->lang('MODERATOR');
			return $moderator . $this->language->lang('COLON') . ' '. $this->language->lang('NOTIFICATION_REFERENCE', censor_text($reason));
		}

		return '';
	}

	/**
	 * Get the url to this item.
	 *
	 * @return string		URL to the APS Display page
	 * @access public
	 */
	public function get_url()
	{
		return $this->helper->route('phpbbstudio_aps_display');
	}

	/**
	 * Get email template.
	 *
	 * @return string|bool	Whether or not this notification has an email option template
	 * @access public
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * Get email template variables.
	 *
	 * @return array		Array of variables that can be used in the email template
	 * @access public
	 */
	public function get_email_template_variables()
	{
		return [];
	}

	/**
	 * Function for preparing the data for insertion in an SQL query.
	 * (The service handles insertion)
	 *
	 * @param  array	$data				The type specific data
	 * @param  array	$pre_create_data	Data from pre_create_insert_array()
	 * @return void
	 * @access public
	 */
	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('name', $data['name']);
		$this->set_data('points', $data['points']);
		$this->set_data('reason', $data['reason']);
		$this->set_data('moderator', $data['moderator']);
		$this->set_data('moderator_id', $data['moderator_id']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
