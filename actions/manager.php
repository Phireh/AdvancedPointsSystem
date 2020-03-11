<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\actions;

/**
 * phpBB Studio - Advanced Points System points actions manager.
 */
class manager
{
	/** @var \phpbb\di\service_collection Array of action types from the service collection */
	protected $actions;

	/** @var \phpbbstudio\aps\points\distributor */
	protected $distributor;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbbstudio\aps\points\valuator */
	protected $valuator;

	/** @var \phpbb\user */
	protected $user;

	/** @var array Array of points fields for the triggered action types */
	protected $fields;

	/** @var array Array of action types that are triggered */
	protected $types;

	/** @var array Array of users that can receive points for the triggered action */
	protected $users;

	/** @var array Array of point values required for the triggered action types */
	protected $values;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\di\service_collection			$actions		Array of action types from the service collection
	 * @param \phpbbstudio\aps\points\distributor	$distributor	APS Distributor object
	 * @param \phpbbstudio\aps\core\functions		$functions		APS Core functions
	 * @param \phpbb\language\language				$lang			Language object
	 * @param \phpbb\log\log						$log			phpBB Log object
	 * @param \phpbbstudio\aps\points\valuator		$valuator		APS Valuator
	 * @param \phpbb\user							$user			User object
	 * @return void
	 * @access public
	 */
	public function __construct($actions, \phpbbstudio\aps\points\distributor $distributor, \phpbbstudio\aps\core\functions $functions, \phpbb\language\language $lang, \phpbb\log\log $log, \phpbbstudio\aps\points\valuator $valuator, \phpbb\user $user)
	{
		$this->distributor	= $distributor;
		$this->functions	= $functions;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->valuator		= $valuator;
		$this->user			= $user;

		$this->actions		= $actions;
	}

	/**
	 * Get the APS Distributor object.
	 *
	 * @return \phpbbstudio\aps\points\distributor
	 * @access public
	 */
	public function get_distributor()
	{
		return $this->distributor;
	}

	/**
	 * Get the APS Core functions.
	 *
	 * @return \phpbbstudio\aps\core\functions
	 * @access public
	 */
	public function get_functions()
	{
		return $this->functions;
	}

	/**
	 * Get the APS Valuator.
	 *
	 * @return \phpbbstudio\aps\points\valuator
	 * @access public
	 */
	public function get_valuator()
	{
		return $this->valuator;
	}

	/**
	 * Get the localised points name.
	 *
	 * @return string		The localised points name
	 * @access public
	 */
	public function get_name()
	{
		return $this->functions->get_name();
	}

	/**
	 * Clean an array from a listener, turns an object into an array.
	 *
	 * @param  mixed	$event	The event to clean
	 * @return array			The event data
	 * @access public
	 */
	public function clean_event($event)
	{
		if ($event instanceof \phpbb\event\data)
		{
			return (array) $event->get_data();
		}
		else
		{
			return (array) $event;
		}
	}

	/**
	 * Get all values for the provided key in an array.
	 *
	 * @param  array	$array	The array to retrieve the values from
	 * @param  string	$key	The keys of which to return the values
	 * @return array			Array of unique integers
	 * @access public
	 */
	public function get_identifiers(array $array, $key)
	{
		return (array) array_map('intval', array_unique(array_filter(array_column($array, $key))));
	}

	/**
	 * Trigger a point action and calculate users' points.
	 *
	 * This is the "main" function for this extension.
	 *
	 * $action
	 * 	Calling this with an $action will trigger all action type which have their get_action() set to $action.
	 *
	 * $user_ids
	 * 	User identifiers are for the users who can receive points by this action, this user ($this->user) is
	 * 	automatically added to the list. If it was already present in the list, it's filtered out.
	 *
	 * $event
	 * 	An array with data that was available from the event (or any other occurrence) that triggered this action.
	 * 	For instance, phpBB's event object that is available in a listener. If indeed phpBB's event object is
	 * 	send it is automatically 'cleaned', which means the object is turned into an array.
	 *
	 * $forum_ids
	 * 	A list of forum identifiers for which the point values should be retrieved, as those values are necessary
	 * 	to require the amount of points for the users. If it's left empty it will assume that the triggered action
	 * 	is a "global" action, which means the forum_id = 0.
	 *
	 * @param  string		$action			The action to trigger
	 * @param  array|int	$user_ids		The user identifiers that can receive points
	 * @param  array		$event			The event data
	 * @param  array|int	$forum_ids		The forum identifiers (array or single value)
	 * @return void
	 * @access public
	 */
	public function trigger($action, $user_ids = [], $event = [], $forum_ids = 0)
	{
		// 1. Initialise arrays
		$this->initialise_arrays();

		// 2. Get action types
		$this->get_types_and_fields($action);

		// 3. Get values
		$this->get_values($forum_ids);

		// 4. Set users
		$this->set_users($user_ids);

		// 5. Calculate
		$this->calculate($this->clean_event($event));

		// 6. Distribute
		$this->distribute();
	}

	/**
	 * Initialise the array fields used by this points manager.
	 *
	 * Has to be declared per each trigger() call, as otherwise types are carried over in chained calls.
	 *
	 * @return void
	 * @access protected
	 */
	protected function initialise_arrays()
	{
		// Array of points fields for the triggered action types
		$this->fields = [0 => [], 1 => []];

		// Array of action types that are triggered
		$this->types = [];

		// Array of users that can receive points for the triggered action
		$this->users = [];

		// Array of point values required for the triggered action types
		$this->values = [];
	}

	/**
	 * Get all action types and their fields for the trigger action.
	 *
	 * $types
	 * 	While the $this->actions array holds ALL the registered action types,
	 * 	only a certain few are required. Only the required once are added to $this->types.
	 *
	 * $fields
	 * 	Each action type has an array of point value keys with a language string as value.
	 *  Those keys are used for storing the points values set by the Administrator in the database.
	 *  Therefore a list is generated with all the fields that need to be retrieved from the database.
	 *
	 * @param  string	$action		The action that is triggered
	 * @return void
	 * @access protected
	 */
	protected function get_types_and_fields($action)
	{
		/** @var \phpbbstudio\aps\actions\type\action $type */
		foreach ($this->actions as $name => $type)
		{
			// Only add action types that are listed under this $action
			if ($type->get_action() === $action)
			{
				// Add this service to the action types
				$this->types[$name] = $type;

				// Get scope: 0 = local | 1 = global
				$key = (int) $type->is_global();

				// Get the type fields indexed by the scope
				$this->fields[$key] = array_merge($type->get_fields(), $this->fields[$key]);
			}
		}
	}

	/**
	 * Get all point values required by the triggered action types from the database.
	 *
	 * $values
	 * 	Get all point values from the database that are in the $fields array and
	 * 	have their forum identifier set to one provided in the $forum_ids array.
	 * 	The values array will contain all point values indexed by the forum identifier,
	 * 	if the fields are global, the forum identifier is set to 0.
	 *
	 * @param  array|int	$forum_ids	The forum identifiers
	 * @return void
	 * @access protected
	 */
	protected function get_values($forum_ids)
	{
		// Create array filled with integers
		$forum_ids = is_array($forum_ids) ? array_map('intval', $forum_ids) : [(int) $forum_ids];

		// Make sure there are only unique and non-empty forum identifiers
		$forum_ids = array_unique($forum_ids);

		$this->values = $this->valuator->get_points($this->fields, $forum_ids);
	}

	/**
	 * Set all users available for receiving points by the triggered action.
	 *
	 * $user_ids
	 * 	The array of user identifiers provided from the place where the action is triggered.
	 * 	This user's ($this->user) identifier is automatically added.
	 *
	 * $users
	 * 	The array of users that are able to receive points, with a base array to make sure all keys are set,
	 * 	aswell as all the users' current points.
	 *
	 * @param  array|int	$user_ids	The user identifiers
	 * @return void
	 * @access protected
	 */
	protected function set_users($user_ids)
	{
		// Create array filled with integers
		$user_ids = is_array($user_ids) ? array_map('intval', $user_ids) : [(int) $user_ids];

		// Make sure to include this user ($this->user)
		$user_ids[] = (int) $this->user->data['user_id'];

		// Make sure only unique users are set
		$user_ids = array_unique(array_filter($user_ids));

		// If there is only one user, that will be this user, so no need to query
		if (count($user_ids) === 1)
		{
			// Build the base user array for this user
			$this->users[(int) $this->user->data['user_id']] = $this->user_array($this->user->data['user_points']);
		}
		else
		{
			// Grab all the current point values for these users
			$user_points = $this->valuator->users($user_ids);

			foreach ($user_ids as $user_id)
			{
				if (isset($user_points[$user_id]))
				{
					// Build the base user arrays
					$this->users[$user_id] = $this->user_array($user_points[$user_id]);
				}
			}
		}

		// Lets make sure the anonymous user is never used
		unset($this->users[ANONYMOUS]);
	}

	/**
	 * Let all the required action types calculate their user points.
	 *
	 * @param  array	$data		Array of event data
	 * @return void
	 * @access protected
	 */
	protected function calculate(array $data)
	{
		/** @var \phpbbstudio\aps\actions\type\action $type */
		foreach ($this->types as $type)
		{
			// Make the functions object available
			$type->set_functions($this->functions);

			// Set the users
			$type->set_users($this->users);

			// Check if APS is in Safe Mode
			if ($this->functions->safe_mode())
			{
				// If so, catch any exceptions and log them
				try
				{
					$type->calculate($data, $this->values);
				}
				catch (\Exception $e)
				{
					// Catch any error in the action type and log it!
					$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_CALCULATION_ERROR', time(), [$e->getMessage(), $e->getFile(), $e->getLine(), $this->functions->get_name()]);
				}
			}
			else
			{
				// If not, calculate and let the exceptions do their thing.
				$type->calculate($data, $this->values);
			}

			// Iterate over all the users
			foreach (array_keys($this->users) as $user_id)
			{
				// Get all received points for this user from this action type
				$this->users[$user_id]['actions'][] = $type->get_points($user_id);

				// Check for logs that need approving
				if ($approve = $type->get_approve($user_id))
				{
					$this->users[$user_id]['approve'] = array_merge($this->users[$user_id]['approve'], $approve);
				}

				// Check for logs that need disapproving
				if ($disapprove = $type->get_disapprove($user_id))
				{
					$this->users[$user_id]['disapprove'] = array_merge($this->users[$user_id]['disapprove'], $disapprove);
				}
			}
		}
	}

	/**
	 * Distribute the points gained for all the users
	 *
	 * @return void
	 * @access protected
	 */
	protected function distribute()
	{
		// Iterate over all the users
		foreach ($this->users as $user_id => $user_row)
		{
			// Iterate over all the action types
			foreach ($user_row['actions'] as $actions)
			{
				// Iterate over the arrays added per action type
				foreach ($actions as $action)
				{
					if ($action['approved'])
					{
						// Calculate the total points gained for this user
						$this->functions->equate_reference($this->users[$user_id]['total'], $action['points']);
					}

					// Grab the post identifier, as we group the logs per post id
					$post_id = (int) $action['post_id'];

					// Set the logs for this user
					$this->set_logs($user_id, $post_id, $action);
				}
			}

			// And send it off: update user points and add log entries
			$this->distributor->distribute(
				$user_id,
				$this->users[$user_id]['total'],
				$this->users[$user_id]['logs'],
				$this->users[$user_id]['current']
			);

			// Approve logs
			if ($user_row['approve'])
			{
				$user_points = $this->functions->equate_points($this->users[$user_id]['total'], $this->users[$user_id]['current']);

				$this->distributor->approve($user_id, array_unique(array_filter($user_row['approve'])), $user_points);
			}

			// Disapprove logs
			if ($user_row['disapprove'])
			{
				$this->distributor->disapprove($user_id, array_unique(array_filter($user_row['disapprove'])));
			}
		}
	}

	/**
	 * Set the log entries for this user and index per post identifier.
	 *
	 * @param  int		$user_id	The user identifier
	 * @param  int		$post_id	The post identifier
	 * @param  array	$row		The log array
	 * @return void
	 * @access protected
	 */
	protected function set_logs($user_id, $post_id, array $row)
	{
		// Get the logs in a local variable for easier coding
		$logs = $this->users[$user_id]['logs'];

		// Filter out the empty values except the first key
		if (empty($logs[$post_id]))
		{
			$first = array_splice($row['logs'], 0, 1);
			$row['logs'] = array_filter($row['logs']);
			$row['logs'] = $first + $row['logs'];
		}
		else
		{
			$row['logs'] = array_filter($row['logs']);
		}

		// If there are no logs entries yet under this post identifier
		if (empty($logs[$post_id]))
		{
			$logs[$post_id] = [
				'action'		=> (string) $this->main_log($row['logs']),
				'actions'		=> (array) $row['logs'],
				'approved'		=> (bool) $row['approved'],
				'forum_id'		=> (int) $row['forum_id'],
				'topic_id'		=> (int) $row['topic_id'],
				'post_id'		=> (int) $row['post_id'],
				'user_id'		=> (int) $user_id,
				'reportee_id'	=> (int) $this->user->data['user_id'],
				'reportee_ip'	=> (string) $this->user->ip,
				'points_old'	=> (double) $this->users[$user_id]['current'],
				'points_sum'	=> (double) $row['points'],
				'points_new'	=> (double) $this->functions->equate_points($this->users[$user_id]['current'], $row['points']),
			];
		}
		else
		{
			// Else there already exists log entries under this post identifier, so merge this one in
			$this->merge_logs($logs[$post_id]['actions'], $row['logs']);

			// Equate (by reference) the points gained ('sum') and the new total ('new').
			$this->functions->equate_reference($logs[$post_id]['points_sum'], $row['points']);
			$this->functions->equate_reference($logs[$post_id]['points_new'], $row['points']);
		}

		// Set the logs in the global variable again
		$this->users[$user_id]['logs'] = $logs;
	}

	/**
	 * Get the "main" log entry, the first key of the array.
	 *
	 * @param  array	$logs		The logs array
	 * @return string				The main log entry string
	 * @access protected
	 */
	protected function main_log(array $logs)
	{
		reset($logs);
		$action = key($logs);

		return $action;
	}

	/**
	 * Merge a log entry into existing log entries.
	 *
	 * Log entries are language strings (key) with point values (value).
	 * 	array('APS_SOME_ACTION' => 5.00)
	 *
	 * If logs are merged, an array is created which has to be equated.
	 * 	array('APS_SOME_ACTION' => array(5.00, 2.00)
	 *
	 * @param  array	$logs		The existing log entries
	 * @param  array	$array		The log entry to merge in
	 * @return void					Passed by reference
	 * @access protected
	 */
	protected function merge_logs(array &$logs, array $array)
	{
		// Merge the array in to the existing entries
		$logs = array_merge_recursive($logs, $array);

		// Iterate over the logged actions
		foreach ($logs as $key => $value)
		{
			// If the logged action is no longer a single points value, equate it.
			if (is_array($value))
			{
				$logs[$key] = $this->functions->equate_array($value);
			}
		}
	}

	/**
	 * Set up a base array for this user.
	 *
	 * 'current
	 * 	The user's current points
	 *
	 * 'actions'
	 * 	Array that will be filled with arrays added by all the action types.
	 *
	 * 'points'
	 * 	Array that will be filled with points added by all the action types.
	 *
	 * 'approve'
	 * 	Array that will be filled with post identifiers that need to be approved from the logs table.
	 *
	 * 'disapprove'
	 * 	Array that will be filled with post identifiers that need to be disapproved from the logs table.
	 *
	 * 'logs'
	 * 	Array of log entries that are going to be added for this user.
	 *
	 * 'total'
	 * 	The total points gained for this user, summing up all points per action type.
	 *
	 * @param  double	$points		The user's current points
	 * @return array				The user's base array
	 * @access protected
	 */
	protected function user_array($points)
	{
		return [
			'current'		=> (double) $points,
			'actions'		=> [],
			'points'		=> [],
			'approve'		=> [],
			'disapprove'	=> [],
			'logs'			=> [],
			'total'			=> 0.00,
		];
	}
}
