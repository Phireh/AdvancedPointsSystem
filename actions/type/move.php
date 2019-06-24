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
 * phpBB Studio - Advanced Points System action: Move
 */
class move extends base
{
	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\user	$user	User object
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
		return 'move';
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
			'aps_mod_move_post'		=> 'APS_POINTS_MOD_MOVE_POST',
			'aps_user_move_post'	=> 'APS_POINTS_USER_MOVE_POST',
			'aps_mod_move_topic'	=> 'APS_POINTS_MOD_MOVE_TOPIC',
			'aps_user_move_topic'	=> 'APS_POINTS_USER_MOVE_TOPIC',
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
		$s_topic = $action === 'topic';
		$posts = $s_topic ? $data['topics'] : $data['posts'];

		$key_user = 'aps_user_move_' . $action;
		$key_mod = 'aps_mod_move_' . $action;
		$strings = $this->get_data();

		foreach ($posts as $post)
		{
			$forum_id = $post['forum_id'];
			$topic_id = $post['topic_id'];
			$post_id = $s_topic ? $post['topic_first_post_id'] : $post['post_id'];
			$user_id = $s_topic ? $post['topic_poster'] : $post['poster_id'];

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
