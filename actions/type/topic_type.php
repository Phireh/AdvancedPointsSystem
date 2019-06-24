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
 * phpBB Studio - Advanced Points System action: Topic type
 */
class topic_type extends base
{
	/**
	 * Get action name.
	 *
	 * @return string			The name of the action this type belongs to
	 * @access public
	 */
	public function get_action()
	{
		return 'topic_type';
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
		return 'ACP_APS_TOPIC_TYPES';
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
			'aps_mod_normal_sticky'		=> 'APS_POINTS_MOD_NORMAL_STICKY',
			'aps_mod_normal_announce'	=> 'APS_POINTS_MOD_NORMAL_ANNOUNCE',
			'aps_mod_normal_global'		=> 'APS_POINTS_MOD_NORMAL_GLOBAL',
			'aps_mod_sticky_normal'		=> 'APS_POINTS_MOD_STICKY_NORMAL',
			'aps_mod_sticky_announce'	=> 'APS_POINTS_MOD_STICKY_ANNOUNCE',
			'aps_mod_sticky_global'		=> 'APS_POINTS_MOD_STICKY_GLOBAL',
			'aps_mod_announce_normal'	=> 'APS_POINTS_MOD_ANNOUNCE_NORMAL',
			'aps_mod_announce_sticky'	=> 'APS_POINTS_MOD_ANNOUNCE_STICKY',
			'aps_mod_announce_global'	=> 'APS_POINTS_MOD_ANNOUNCE_GLOBAL',
			'aps_mod_global_normal'		=> 'APS_POINTS_MOD_GLOBAL_NORMAL',
			'aps_mod_global_sticky'		=> 'APS_POINTS_MOD_GLOBAL_STICKY',
			'aps_mod_global_announce'	=> 'APS_POINTS_MOD_GLOBAL_ANNOUNCE',

			'aps_user_normal_sticky'	=> 'APS_POINTS_USER_NORMAL_STICKY',
			'aps_user_normal_announce'	=> 'APS_POINTS_USER_NORMAL_ANNOUNCE',
			'aps_user_normal_global'	=> 'APS_POINTS_USER_NORMAL_GLOBAL',
			'aps_user_sticky_normal'	=> 'APS_POINTS_USER_STICKY_NORMAL',
			'aps_user_sticky_announce'	=> 'APS_POINTS_USER_STICKY_ANNOUNCE',
			'aps_user_sticky_global'	=> 'APS_POINTS_USER_STICKY_GLOBAL',
			'aps_user_announce_normal'	=> 'APS_POINTS_USER_ANNOUNCE_NORMAL',
			'aps_user_announce_sticky'	=> 'APS_POINTS_USER_ANNOUNCE_STICKY',
			'aps_user_announce_global'	=> 'APS_POINTS_USER_ANNOUNCE_GLOBAL',
			'aps_user_global_normal'	=> 'APS_POINTS_USER_GLOBAL_NORMAL',
			'aps_user_global_sticky'	=> 'APS_POINTS_USER_GLOBAL_STICKY',
			'aps_user_global_announce'	=> 'APS_POINTS_USER_GLOBAL_ANNOUNCE',
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
		$topic_id = (int) $data['data']['topic_id'];
		$post_id = (int) $data['data']['post_id'];
		$poster_id = (int) $data['post_data']['topic_poster'];
		$type_from = (int) $data['type_from'];
		$type_to = (int) $data['type_to'];

		$types = [
			POST_NORMAL		=> 'normal',
			POST_STICKY		=> 'sticky',
			POST_ANNOUNCE	=> 'announce',
			POST_GLOBAL		=> 'global',
		];

		// Get some base variables
		$value = $values[$forum_id];
		$logs = $this->get_data();

		foreach (array_keys($this->users) as $user_id)
		{
			$action = ($user_id == $poster_id) ? 'aps_user_' : 'aps_mod_';
			$action .= $types[$type_from]  . '_' . $types[$type_to];

			$points = [
				'points'	=> (double) $value[$action],
				'forum_id'	=> (int) $forum_id,
				'topic_id'	=> (int) $topic_id,
				'post_id'	=> (int) $post_id,
				'logs'		=> [$logs[$action] => $value[$action]],
			];

			$this->add($user_id, $points);
		}
	}
}
