<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\points;

/**
 * phpBB Studio - Advanced Points System distributor.
 */
class distributor
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbbstudio\aps\core\log */
	protected $log;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\aps\points\valuator */
	protected $valuator;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\config\config					$config			Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db				Database object
	 * @param  \phpbbstudio\aps\core\functions		$functions		APS Core functions
	 * @param  \phpbbstudio\aps\core\log			$log			APS Log object
	 * @param  \phpbb\user							$user			User object
	 * @param  \phpbbstudio\aps\points\valuator		$valuator		APS Valuator object
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbbstudio\aps\core\functions $functions,
		\phpbbstudio\aps\core\log $log,
		\phpbb\user $user,
		valuator $valuator
	)
	{
		$this->config		= $config;
		$this->db			= $db;
		$this->functions	= $functions;
		$this->log			= $log;
		$this->user			= $user;
		$this->valuator		= $valuator;
	}

	/**
	 * Distribute a user's points.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  double	$points			The points gained
	 * @param  array	$logs			The logs array
	 * @param  null		$user_points	The current user's points, if available. Has to be null as 0 is a valid current points value.
	 * @return bool						Whether the user points were updated or not
	 * @access public
	 */
	public function distribute($user_id, $points, $logs, $user_points = null)
	{
		// Calculate the new total for this user
		$total = $this->total($user_id, $points, $user_points);

		// If logging was successful
		if ($this->log->add_multi($logs))
		{
			// Update the points for this user
			return $this->update_points($total, $user_id);
		}

		// Points were not updated, return false (logs were invalid)
		return false;
	}

	/**
	 * Update a user's points.
	 *
	 * @param  double	$points			The user points
	 * @param  int		$user_id		The user identifier
	 * @return bool						Whether or not the user's row was updated
	 * @access public
	 */
	public function update_points($points, $user_id = 0)
	{
		$user_id = $user_id ? $user_id : $this->user->data['user_id'];

		$sql = 'UPDATE ' . $this->functions->table('users') . '
				SET user_points = ' . (double) $points . '
				WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	/**
	 * Approve log entries and distribute the on-hold points for a certain user.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  array	$post_ids		The post identifiers
	 * @param  null		$user_points	The current user's points, if available. Has to be null as 0 is a valid current points value.
	 * @return void
	 * @access public
	 */
	public function approve($user_id, array $post_ids, $user_points = null)
	{
		// Get points gained from the log entries
		$points = $this->log->get_values($user_id, $post_ids, false);

		// Equate the points gained to a single value
		$points = $this->functions->equate_array($points);

		// Calculate the new total for this user
		$total = $this->total($user_id, $user_points, $points);

		$this->db->sql_transaction('begin');

		// Approve the log entries
		$this->log->approve($user_id, $post_ids);

		// Update the points for this user
		$this->update_points($total, $user_id);

		$this->db->sql_transaction('commit');
	}

	/**
	 * Disapprove log entries for a certain user.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  array	$post_ids		The post identifiers
	 * @return void
	 * @access public
	 */
	public function disapprove($user_id, array $post_ids)
	{
		// Delete the log entries
		$this->log->delete([
			'log_approved'	=> (bool) false,
			'user_id'		=> (int) $user_id,
			'post_id'		=> [
				'IN'	=> (array) $post_ids,
			],
		]);
	}

	/**
	 * Calculate the new total (current points + gained points) for a specific user.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  double	$points			The user's gained points
	 * @param  double	$user_points	The user's current points
	 * @return double					The new total for this user
	 * @access public
	 */
	public function total($user_id, $points, $user_points = null)
	{
		// If the current user's points is null, get it from the database
		if ($user_points === null)
		{
			$user_points = $this->valuator->user((int) $user_id);
		}

		// Calculate the new total for this user
		$total = $this->functions->equate_points($user_points, $points);

		// Check total boundaries (not higher than X, not lower than X)
		$total = $this->functions->boundaries($total);

		return $total;
	}
}
