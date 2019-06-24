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
 * phpBB Studio - Advanced Points System action: Lock
 */
class lock extends base
{
	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\user	$user	User object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	/**
	 * Get action name.
	 *
	 * @return string			The name of the action this type belongs to
	 * @access public
	 */
	public function get_action()
	{
		return 'lock';
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
			'aps_mod_lock'			=> 'APS_POINTS_MOD_LOCK',
			'aps_user_lock'			=> 'APS_POINTS_USER_LOCK',
			'aps_mod_lock_post'		=> 'APS_POINTS_MOD_LOCK_POST',
			'aps_user_lock_post'	=> 'APS_POINTS_USER_LOCK_POST',
			'aps_mod_unlock'		=> 'APS_POINTS_MOD_UNLOCK',
			'aps_user_unlock'		=> 'APS_POINTS_USER_UNLOCK',
			'aps_mod_unlock_post'	=> 'APS_POINTS_MOD_UNLOCK_POST',
			'aps_user_unlock_post'	=> 'APS_POINTS_USER_UNLOCK_POST',
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
		$action = $data['action'];
		$posts = $data['data'];
		$s_topic = in_array($action, ['lock', 'unlock']);

		$key_user = 'aps_user_' . $action;
		$key_mod = 'aps_mod_' . $action;
		$strings = $this->get_data();

		foreach ($posts as $post_data)
		{
			$forum_id = $post_data['forum_id'];
			$topic_id = $post_data['topic_id'];
			$post_id = $s_topic ? $post_data['topic_first_post_id'] : $post_data['post_id'];
			$user_id = $s_topic ? $post_data['topic_poster'] : $post_data['poster_id'];

			$this->add($user_id, [
				'forum_id'	=> $forum_id,
				'topic_id'	=> $topic_id,
				'post_id'	=> $post_id,
				'points'	=> $values[$forum_id][$key_user],
				'logs'		=> [
					$strings[$key_user] => $values[$forum_id][$key_user],
				],
			]);

			$this->add($this->user->data['user_id'], [
				'forum_id'	=> $forum_id,
				'topic_id'	=> $topic_id,
				'post_id'	=> $post_id,
				'points'	=> $values[$forum_id][$key_mod],
				'logs'		=> [
					$strings[$key_mod] => $values[$forum_id][$key_mod],
				],
			]);
		}
	}
}
