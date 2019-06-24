<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\actions\type;

/**
 * phpBB Studio - Advanced Points System action: Base
 */
interface action
{
	/**
	 * Get action name.
	 *
	 * @abstract				This function MUST be present in all extending classes
	 * @return string			The name of the action this type belongs to
	 * @access public
	 */
	public function get_action();

	/**
	 * Get global state.
	 *
	 * @abstract				This function MUST be present in all extending classes
	 * @return bool				If this type is global or local (per-forum basis)
	 * @access public
	 */
	public function is_global();

	/**
	 * Get type category under which it will be listed in the ACP.
	 *
	 * @abstract				This function MUST be present in all extending classes
	 * @return string			The name of the category this type belongs to
	 * @access public
	 */
	public function get_category();

	/**
	 * Get type data.
	 *
	 * @abstract				This function MUST be present in all extending classes
	 * @return array			An array of value names and their language string
	 * @access public
	 */
	public function get_data();

	/**
	 * Calculate points for this type.
	 *
	 * @abstract				This function MUST be present in all extending classes
	 * @param  array	$data	The data available from the $event that triggered this action
	 * @param  array	$values	The point values available, indexed per forum_id and 0 for global values
	 * @return void
	 */
	public function calculate($data, $values);

	/**
	 * Get type value names.
	 *
	 * @return array			An array of value names, the keys from get_data()
	 * @access public
	 */
	public function get_fields();

	/**
	 * Set APS functions.
	 *
	 * @param  \phpbbstudio\aps\core\functions	$functions		Common APS functions
	 * @return void
	 * @access public
	 */
	public function set_functions($functions);

	/**
	 * Set users that are available for this action.
	 *
	 * @param  array	$users
	 * @return void
	 * @access public
	 */
	public function set_users($users);

	/**
	 * Get the calculated points from this type for a given user identifier.
	 *
	 * @param  int		$user_id	The user identifier
	 * @return array				The calculated points array(s)
	 * @access public
	 */
	public function get_points($user_id);

	/**
	 * Get the post identifiers that need to be approved for this user.
	 *
	 * @param  int		$user_id	The user identifier
	 * @return array				Array of post identifiers to approve for this user
	 * @access protected
	 */
	public function get_approve($user_id);

	/**
	 * Get the post identifiers that need to be disapproved for this user.
	 *
	 * @param  int		$user_id	The user identifier
	 * @return array				Array of post identifiers to disapprove for this user
	 * @access protected
	 */
	public function get_disapprove($user_id);
}
