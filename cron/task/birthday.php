<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\cron\task;

/**
 * phpBB Studio - Advanced Points System birthday cron.
 */
class birthday extends \phpbb\cron\task\base
{
	/**
	 * How often the cron should run (in seconds).
	 * @var int		86400		One day in seconds
	 */
	protected $cron_frequency = 86400;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbbstudio\aps\actions\manager */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\config\config					$config		Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db			Database object
	 * @param  \phpbbstudio\aps\core\functions		$functions	APS Core functions
	 * @param  \phpbbstudio\aps\actions\manager		$manager	APS Actions manager object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbbstudio\aps\core\functions $functions, \phpbbstudio\aps\actions\manager $manager)
	{
		$this->config		= $config;
		$this->db			= $db;
		$this->functions	= $functions;
		$this->manager		= $manager;
	}

	/**
	 * Runs this cron task.
	 *
	 * @return void
	 * @access public
	 */
	public function run()
	{
		$user_ids = $data = [];
		$data['birthdays'] = [];

		// Set the default timezone
		date_default_timezone_set($this->config['board_timezone']);

		// Get current day and month (no leading zero)
		$day = date('j');
		$month = date('n');

		// Birthdays are stored with a leading space if only one digit: " 8- 6-1990".
		$data['day'] = strlen($day) === 1 ? ' ' . $day : $day;
		$data['month'] = strlen($month) === 1 ? ' ' . $month : $month;

		// Build a SQL like expression: DD-MM-%
		$birthday = $data['day'] . '-' . $data['month'] . '-' . $this->db->get_any_char();

		// Select all the user identifiers that are celebrating their birthday today
		$sql = 'SELECT user_id, user_birthday
				FROM ' . $this->functions->table('users') . '
				WHERE user_type <> ' . USER_IGNORE . '
					AND user_birthday ' . $this->db->sql_like_expression($birthday);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_ids[] = $row['user_id'];

			$data['birthdays'][(int) $row['user_id']] = $row['user_birthday'];
		}
		$this->db->sql_freeresult($result);

		// Calculate the points!
		$this->manager->trigger('birthday', $user_ids, $data);

		// Update the cron task run time here
		$this->config->set('aps_birthday_last_run', time(), false);
	}

	/**
	 * Returns whether this cron task can run, given current board configuration.
	 *
	 * @return bool
	 * @access public
	 */
	public function is_runnable()
	{
		return true;
	}

	/**
	 * Returns whether this cron task should run now, because enough time
	 * has passed since it was last run.
	 *
	 * @return bool
	 * @access public
	 */
	public function should_run()
	{
		return $this->config['aps_birthday_last_run'] < time() - $this->cron_frequency;
	}
}
