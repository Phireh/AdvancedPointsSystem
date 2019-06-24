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
 * phpBB Studio - Advanced Points System action: Edit
 */
class edit extends base
{
	/**
	 * Get action name.
	 *
	 * @return string			The name of the action this type belongs to
	 * @access public
	 */
	public function get_action()
	{
		return 'edit';
	}

	/**
	 * Get global state.
	 *
	 * @return bool				If this type is global or local (per-forum basis)
	 * @access public
	 */
	public function is_global()
	{
		return false;
	}

	/**
	 * Get type category under which it will be listed in the ACP.
	 *
	 * @return string			The name of the category this type belongs to
	 * @access public
	 */
	public function get_category()
	{
		return 'MODERATE';
	}

	/**
	 * Get type data.
	 *
	 * @return array			An array of value names and their language string
	 * @access public
	 */
	public function get_data()
	{
		return [
			'aps_mod_edit'	=> 'APS_POINTS_MOD_EDIT',
			'aps_user_edit'	=> 'APS_POINTS_USER_EDIT',
		];
	}

	/**
	 * Calculate points for this type.
	 *
	 * @param  array	$data	The data available from the $event that triggered this action
	 * @param  array	$values	The point values available, indexed per forum_id and 0 for global values
	 * @retrun void
	 */
	public function calculate($data, $values)
	{
		// Grab the data we need from the event
		$forum_id = (int) $data['data']['forum_id'];
		$poster_id = (int) $data['data']['poster_id'];

		// Get some base variables
		$value = $values[$forum_id];
		$logs = $this->get_data();

		foreach (array_keys($this->users) as $user_id)
		{
			$action = ($user_id == $poster_id) ? 'aps_user_edit' : 'aps_mod_edit';

			$points = [
				'points'	=> (double) $value[$action],
				'forum_id'	=> (int) $forum_id,
				'logs'		=> [$logs[$action] => $value[$action]],
			];

			$this->add($user_id, $points);
		}
	}
}
