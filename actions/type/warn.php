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
 * phpBB Studio - Advanced Points System action: Warn
 */
class warn extends base
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
		return 'warn';
	}

	/**
	 * Get global state.
	 *
	 * @return bool				If this type is global or local (per-forum basis)
	 * @access public
	 */
	public function is_global()
	{
		return true;
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
			'aps_mod_warn'	=> 'APS_POINTS_MOD_WARN',
			'aps_user_warn'	=> 'APS_POINTS_USER_WARN',
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
		$value = $values[0];
		$logs = $this->get_data();

		foreach (array_keys($this->users) as $user_id)
		{
			$mode = $user_id == $this->user->data['user_id'] ? 'mod' : 'user';

			$points = [
				'points'	=> (double) $value['aps_' . $mode . '_warn'],
				'logs'		=> [$logs['aps_' . $mode . '_warn'] => $value['aps_' . $mode . '_warn']],
			];

			$this->add($user_id, $points);
		}
	}
}
