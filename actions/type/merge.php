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
 * phpBB Studio - Advanced Points System action: Merge
 */
class merge extends base
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
		$this->user	= $user;
	}

	/**
	 * Get action name.
	 *
	 * @return string			The name of the action this type belongs to
	 * @access public
	 */
	public function get_action()
	{
		return 'merge';
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
			'aps_mod_merge'		=> 'APS_POINTS_MOD_MERGE',
			'aps_user_merge'	=> 'APS_POINTS_USER_MERGE',
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
		$topics = $data['all_topic_data'];
		$topic_id = $data['to_topic_id'];
		$forum_id = $topics[$topic_id]['forum_id'];

		$values = $values[$forum_id];
		$strings = $this->get_data();

		foreach ($topics as $topic)
		{
			$this->add($topic['topic_poster'], [
				'forum_id'	=> (int) $forum_id,
				'topic_id'	=> (int) $topic_id,
				'points'	=> $values['aps_user_merge'],
				'logs'		=> [$strings['aps_user_merge'] => $values['aps_user_merge']],
			]);

			$this->add($this->user->data['user_id'], [
				'forum_id'	=> (int) $forum_id,
				'topic_id'	=> (int) $topic_id,
				'points'	=> $values['aps_mod_merge'],
				'logs'		=> [$strings['aps_mod_merge'] => $values['aps_mod_merge']],
			]);
		}
	}
}
