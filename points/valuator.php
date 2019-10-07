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
 * phpBB Studio - Advanced Points System valuator.
 */
class valuator
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\user */
	protected $user;

	/** @var string APS Values table */
	protected $table;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\db\driver\driver_interface	$db			Database object
	 * @param  \phpbbstudio\aps\core\functions		$functions	APS Core functions
	 * @param  \phpbb\user							$user		User object
	 * @param  string								$table		APS Values table
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbbstudio\aps\core\functions $functions,
		\phpbb\user $user,
		$table
	)
	{
		$this->db			= $db;
		$this->functions	= $functions;
		$this->user			= $user;
		$this->table		= $table;
	}

	/**
	 * Get the points for the provided user identifier.
	 *
	 * @param  int		$user_id	The user identifier
	 * @return double				The current user's points
	 * @access public
	 */
	public function user($user_id)
	{
		if ($user_id == $this->user->data['user_id'])
		{
			return (double) $this->user->data['user_points'];
		}

		$sql = 'SELECT user_points
				FROM ' . $this->functions->table('users') . '
				WHERE user_id = ' . (int) $user_id . '
					AND user_type <> ' . USER_IGNORE;
		$result = $this->db->sql_query_limit($sql, 1);
		$user_points = $this->db->sql_fetchfield('user_points');
		$this->db->sql_freeresult($result);

		return (double) $user_points;
	}

	/**
	 * Get the points for the provided user identifiers.
	 *
	 * @param  array|int	$user_ids		The user identifier(s)
	 * @return array|float					Array with the users' point values or Double if an integer was provided
	 * @access public
	 */
	public function users($user_ids)
	{
		// If it's just a single user
		if (!is_array($user_ids))
		{
			return $this->user($user_ids);
		}

		// Make sure the array is full with integers
		$user_ids = array_map('intval', $user_ids);

		// Set up base array
		$user_points = [];

		$sql = 'SELECT user_id, user_points
				FROM ' . $this->functions->table('users') . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids) . '
					AND user_type <> ' . USER_IGNORE;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_points[(int) $row['user_id']] = (double) $row['user_points'];
		}
		$this->db->sql_freeresult($result);

		return $user_points;
	}

	/**
	 * Retrieve point values from the database.
	 *
	 * @param  array		$fields		Array of action type fields
	 * @param  array|int	$forum_ids	The forum identifier(s)
	 * @param  bool			$fill		Whether the point values should be filled
	 * @return array
	 * @access public
	 */
	public function get_points($fields, $forum_ids, $fill = true)
	{
		// Set up base arrays
		$sql_where = $values = [];

		// Iterate over the fields
		foreach ($fields as $scope => $fields_array)
		{
			// If the fields array is not empty, add it to the SQL WHERE clause
			if (!empty($fields_array))
			{
				$sql_where[] = $this->get_sql_where($scope, $fields_array, $forum_ids);
			}
		}

		$sql = 'SELECT *
				FROM ' . $this->table . '
				WHERE ' . implode(' OR ', $sql_where);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$f = (int) $row['forum_id'];
			$n = (string) $row['points_name'];
			$v = (double) $row['points_value'];

			$values[$f][$n] = $v;
		}
		$this->db->sql_freeresult($result);

		// Make sure all values are set
		if ($fill)
		{
			$this->fill_values($values, $fields, $forum_ids);
		}

		return $values;
	}

	/**
	 * Deletes all points from the database that do not belong to any of the action types.
	 *
	 * @param  array	$fields		Array of action types fields
	 * @param  int		$forum_id	The forum identifier
	 * @return void
	 * @access public
	 */
	public function clean_points($fields, $forum_id)
	{
		$sql = 'DELETE FROM ' . $this->table . '
				WHERE forum_id = ' . (int) $forum_id . '
					AND ' . $this->db->sql_in_set('points_name', $fields, true, true);
		$this->db->sql_query($sql);
	}

	/**
	 * Deletes all points from the database that do not belong to any of the action types.
	 *
	 * @param  array	$fields		Array of action types fields
	 * @return void
	 * @access public
	 */
	public function clean_all_points($fields)
	{
		// Set up base arrays
		$sql_where = $forum_ids = [];

		$sql = 'SELECT forum_id FROM ' . $this->functions->table('forums');
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_ids[] = (int) $row['forum_id'];
		}
		$this->db->sql_freeresult($result);

		// Iterate over the fields
		foreach ($fields as $scope => $fields_array)
		{
			// If the fields array is not empty, add it to the SQL WHERE clause
			if (!empty($fields_array))
			{
				$sql_where[] = $this->get_sql_where($scope, $fields_array, $forum_ids, true);
			}
		}

		$sql = 'DELETE FROM ' . $this->table . '
				WHERE points_value = 0
					OR ' . $this->db->sql_in_set('forum_id', ($forum_ids + [0]), true) . '
					OR ' . implode(' OR ', $sql_where);
		$this->db->sql_query($sql);
	}

	/**
	 * Delete the point values in the database for a specific forum.
	 *
	 * @param  int		$forum_id		The forum identifier
	 * @return bool						Whether the values were deleted or not.
	 * @access public
	 */
	public function delete_points($forum_id)
	{
		$sql = 'DELETE FROM ' . $this->table . '
				WHERE forum_id = ' . (int) $forum_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	/**
	 * Set the point values in the database.
	 *
	 * @param  array	$points			Array of point values
	 * @param  int		$forum_id		The forum identifier
	 * @return void
	 * @access public
	 */
	public function set_points($points, $forum_id)
	{
		$existing = [(int) empty($forum_id) => array_keys($points)];
		$existing = $this->get_points($existing, $forum_id, false);
		$existing = $existing ? array_keys($existing[(int) $forum_id]) : [];

		$this->db->sql_transaction('begin');

		foreach ($points as $name => $value)
		{
			// If the value already exists in the database, update it
			if (in_array($name, $existing))
			{
				$sql = 'UPDATE ' . $this->table . '
						SET points_value = ' . (double) $value . '
						WHERE points_name = "' . $this->db->sql_escape($name) . '"
							AND forum_id = ' . (int) $forum_id;
			}
			else
			{
				// Otherwise insert it for the first time
				$row['forum_id'] = (int) $forum_id;

				$sql = 'INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('INSERT', [
					'points_name'	=> (string) $name,
					'points_value'	=> (double) $value,
					'forum_id'		=> (int) $forum_id,
					]);
			}

			$this->db->sql_query($sql);
		}

		$this->db->sql_transaction('commit');
	}

	/**
	 * Copy the point values from one forum to an other.
	 *
	 * @param  int			$from	The "from" forum identifier
	 * @param  array|int	$to		The "to" forum identifier(s)
	 * @return void
	 * @access public
	 */
	public function copy_points($from, $to)
	{
		$this->db->sql_transaction('begin');

		// Select "from" points
		$sql = 'SELECT points_name, points_value
				FROM ' . $this->table . '
				WHERE forum_id = ' . (int) $from;
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$to = array_map('intval', array_unique(array_filter($to)));

		// Delete "to" points
		$sql = 'DELETE FROM ' . $this->table . '
				WHERE ' . $this->db->sql_in_set('forum_id', $to);
		$this->db->sql_query($sql);

		foreach ($to as $forum_id)
		{
			for ($i = 0; $i < count($rowset); $i++)
			{
				$rowset[$i]['forum_id'] = (int) $forum_id;
			}

			$this->db->sql_multi_insert($this->table, $rowset);
		}

		$this->db->sql_transaction('commit');
	}

	/**
	 * Create a SQL WHERE clause based on the scope (local|global) and the provided fields.
	 *
	 * @param  int			$scope			The scope for the fields (local|global)
	 * @param  array		$fields			Array of action types fields
	 * @param  array|int	$forum_ids		The forum identifier(s)
	 * @param  bool			$negate			Whether it should be a SQL IN or SQL NOT IN clause
	 * @return string						The SQL WHERE clause
	 * @access protected
	 */
	protected function get_sql_where($scope, $fields, $forum_ids, $negate = false)
	{
		$sql_where = '(';
		$sql_where .= $this->db->sql_in_set('points_name', $fields, $negate);
		$sql_where .= ' AND ';

		switch ($scope)
		{
			// Local
			case 0:
				$sql_where .= is_array($forum_ids) ? $this->db->sql_in_set('forum_id', array_map('intval', $forum_ids), $negate) : 'forum_id ' . ($negate ? '!= ' : '= ') . (int) $forum_ids;
			break;

			// Global
			case 1:
				$sql_where .= 'forum_id = 0';
			break;
		}

		$sql_where .= ')';

		return $sql_where;
	}

	/**
	 * Fills the values array, meaning that if a point value is not available in the database
	 * the key is still set with a default value of 0.00
	 *
	 * @param  array		$values			Array of point values
	 * @param  array		$fields			Array of action types fields
	 * @param  array|int	$forum_ids		The forum identifier(s)
	 * @return void
	 * @access public
	 */
	protected function fill_values(&$values, $fields, $forum_ids)
	{
		// Make sure all forum ids are set
		$fill = is_array($forum_ids) ? array_map('intval', $forum_ids) : [(int) $forum_ids];
		$fill = array_fill_keys($fill, []);
		$values = $values + $fill;

		// Iterate over the set values
		foreach ($values as $forum_id => $values_array)
		{
			// The scope: 0 - local | 1 - global
			$scope = (int) empty($forum_id);

			// Set up an array with all fields as array and a default value
			$requested = array_fill_keys($fields[$scope], 0.00);

			// Merge the set values with the requested values, where the set values take precedence
			$values[$forum_id] = array_merge($requested, $values_array);
		}
	}
}
