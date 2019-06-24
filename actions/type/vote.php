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
 * phpBB Studio - Advanced Points System action: Vote
 */
class vote extends base
{
	/**
	 * Get action name.
	 *
	 * @return string			The name of the action this type belongs to
	 * @access public
	 */
	public function get_action()
	{
		return 'vote';
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
		return 'ACP_APS_POINTS_MISC';
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
			'aps_vote'		=> 'APS_POINTS_PER_VOTE',
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
		$votes = $data['vote_counts'];
		$options = $data['poll_info'];
		$forum_id = $data['forum_id'];
		$topic_id = $data['topic_data']['topic_id'];
		$value = $values[$forum_id]['aps_vote'];

		$i = 0;

		foreach ($options as $option)
		{
			$new = $votes[$option['poll_option_id']];
			$old = $option['poll_option_total'];

			if ($new > $old)
			{
				$i++;
			}
			else if ($new < $old)
			{
				$i--;
			}
		}

		if ($i !== 0)
		{
			$points = $this->equate($value, $i, '*');

			foreach ($this->users as $user_id => $user_data)
			{
				$string = $points > 0 ? 'APS_POINTS_VOTE_ADDED' : 'APS_POINTS_VOTE_REMOVED';

				$this->add($user_id, [
					'forum_id'	=> $forum_id,
					'topic_id'	=> $topic_id,
					'post_id'	=> 0,
					'points'	=> $points,
					'logs'		=> [
						$string => $points,
						'APS_POINTS_VOTE_AMOUNT' => $i,
					],
				]);
			}
		}
	}
}
