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
 * phpBB Studio - Advanced Points System action: Queue
 */
class queue extends base
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
		return 'queue';
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
			'aps_mod_restore'			=> 'APS_POINTS_MOD_RESTORE',
			'aps_mod_approve'			=> 'APS_POINTS_MOD_APPROVE',
			'aps_mod_disapprove'		=> 'APS_POINTS_MOD_DISAPPROVE',
			'aps_user_restore'			=> 'APS_POINTS_USER_RESTORE',
			'aps_user_approve'			=> 'APS_POINTS_USER_APPROVE',
			'aps_user_disapprove'		=> 'APS_POINTS_USER_DISAPPROVE',
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
		$action = $data['mode'];
		$s_post = isset($data['post_info']);
		$posts = $s_post ? $data['post_info'] : $data['topic_info'];

		$key_user = 'aps_user_' . $action;
		$key_mod = 'aps_mod_' . $action;
		$strings = $this->get_data();

		foreach ($posts as $post_id => $post_data)
		{
			$user_id = $s_post ? $post_data['poster_id'] : $post_data['topic_poster'];
			$topic_id = $post_data['topic_id'];
			$forum_id = $post_data['forum_id'];

			$logs = [];

			$points = $logs[$strings[$key_user]] = $values[$forum_id][$key_user];

			switch ($action)
			{
				case 'approve':
					$this->approve($user_id, $post_id);
				break;
				case 'disapprove':
					$this->disapprove($user_id, $post_id);
				break;
			}

			$this->add($user_id, [
				'forum_id'	=> $forum_id,
				'topic_id'	=> $topic_id,
				'post_id'	=> $post_id,
				'points'	=> $points,
				'logs'		=> $logs,
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
