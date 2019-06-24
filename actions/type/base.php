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
 * phpBB Studio - Advanced Points System action: Base interface
 */
abstract class base implements action
{
	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var array The users available for this action */
	protected $users;

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_action();

	/**
	 * {@inheritdoc}
	 */
	abstract public function is_global();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_category();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_data();

	/**
	 * {@inheritdoc}
	 */
	abstract public function calculate($data, $values);

	/**
	 * {@inheritdoc}
	 */
	public function get_fields()
	{
		return array_keys($this->get_data());
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_functions($functions)
	{
		$this->functions = $functions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_users($users)
	{
		$this->users = $users;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_points($user_id)
	{
		return $this->users[(int) $user_id]['points'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_approve($user_id)
	{
		return $this->users[(int) $user_id]['approve'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_disapprove($user_id)
	{
		return $this->users[(int) $user_id]['disapprove'];
	}

	/**
	 * Adds a points array from calculation to the provided user id.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  array	$points_array	The points array to add
	 * @return void
	 * @access protected
	 */
	protected function add($user_id, array $points_array)
	{
		// Make sure everything is set
		$array = array_merge([
			'approved'	=> true,
			'forum_id'	=> 0,
			'topic_id'	=> 0,
			'post_id'	=> 0,
			'points'	=> 0.00,
			'logs'		=> [],
		], $points_array);

		$this->users[(int) $user_id]['points'][] = $array;
	}

	/**
	 * Adds a post id to the array of logs to approve.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  int		$post_id		The post identifier
	 * @return void
	 * @access protected
	 */
	protected function approve($user_id, $post_id)
	{
		$this->users[(int) $user_id]['approve'][] = (int) $post_id;
	}

	/**
	 * Adds a post id to the array of logs to disapprove.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  int		$post_id		The post identifier
	 * @return void
	 * @access protected
	 */
	protected function disapprove($user_id, $post_id)
	{
		$this->users[(int) $user_id]['disapprove'][] = (int) $post_id;
	}

	/**
	 * Equate two numbers.
	 *
	 * @param  double	$a			The first number
	 * @param  double	$b			The second number
	 * @param  string	$operator	The equation operator
	 * @return double				The result of the equation
	 * @access protected
	 */
	protected function equate($a, $b, $operator = '+')
	{
		return $this->functions->equate_points($a, $b, $operator);
	}
}
