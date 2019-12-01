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
 * phpBB Studio - Advanced Points System reasoner.
 */
class reasoner
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string APS Reasons table */
	protected $reasons_table;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\db\driver\driver_interface	$db					Database object
	 * @param  string								$reasons_table		APS Reasons table
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, $reasons_table)
	{
		$this->db				= $db;
		$this->reasons_table	= $reasons_table;
	}

	/**
	 * Insert a reason in the database for the first time.
	 *
	 * @param  array	$reason		The array to insert
	 * @return int					The new reason identifier
	 * @access public
	 */
	public function insert(array $reason)
	{
		unset($reason['reason_id']);

		$sql = 'SELECT MAX(reason_order) as reason_order FROM ' . $this->reasons_table;
		$result = $this->db->sql_query($sql);
		$order = $this->db->sql_fetchfield('reason_order');
		$this->db->sql_freeresult($result);

		$reason['reason_order'] = ++$order;

		$sql = 'INSERT INTO ' . $this->reasons_table . ' ' . $this->db->sql_build_array('INSERT', $reason);
		$this->db->sql_query($sql);

		return (int) $this->db->sql_nextid();
	}

	/**
	 * Updates an already existing reason in the database.
	 *
	 * @param  array	$reason		The array to update
	 * @param  int		$reason_id	The reason identifier
	 * @return bool					Whether the reason's row in the database was updated or not
	 * @access public
	 */
	public function update(array $reason, $reason_id)
	{
		unset($reason['reason_id']);

		$sql = 'UPDATE ' . $this->reasons_table . ' SET ' . $this->db->sql_build_array('UPDATE', $reason) . ' WHERE reason_id = ' . (int) $reason_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	/**
	 * Deletes a reason row from the database.
	 *
	 * @param  int		$reason_id	The reason identifier
	 * @return bool					Whether or not the reason's row was deleted from the database.
	 * @access public
	 */
	public function delete($reason_id)
	{
		$sql = 'DELETE FROM ' . $this->reasons_table . ' WHERE reason_id = ' . (int) $reason_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	/**
	 * Retrieves a reason row from the database.
	 *
	 * @param  int		$reason_id	The reason identifier
	 * @return mixed				The reason row or false if the row does not exists.
	 * @access public
	 */
	public function row($reason_id)
	{
		$sql = 'SELECT * FROM ' . $this->reasons_table . ' WHERE reason_id = ' . (int) $reason_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * Retrieves all the reason rows from the database.
	 *
	 * @return array				The reasons rowset
	 * @access public
	 */
	public function rowset()
	{
		$rowset = [];

		$sql = 'SELECT * FROM ' . $this->reasons_table . ' ORDER BY reason_order ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[$row['reason_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * Fills a reason row to make sure all values are set.
	 *
	 * @param  array	$reason		The reason array to fill
	 * @return array				The filled reason array
	 * @access public
	 */
	public function fill(array $reason)
	{
		$reason = !empty($reason) ? $reason : [];

		$fields = [
			'title'		=> '',
			'desc'		=> '',
			'points'	=> 0.00,
			'order'		=> 0,
		];

		foreach ($fields as $field => $default)
		{
			$reason['reason_' . $field] = !empty($reason['reason_' . $field]) ? $reason['reason_' . $field] : $default;
		}

		return $reason;
	}

	/**
	 * Re-orders a reason row.
	 *
	 * @param  int		$reason_id		The reason identifier
	 * @param  string	$direction		The direction to move it (up|down).
	 * @return void
	 * @access public
	 */
	public function order($reason_id, $direction)
	{
		// Select the current order
		$sql = 'SELECT reason_order FROM ' . $this->reasons_table . ' WHERE reason_id = ' . (int) $reason_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$order = (int) $this->db->sql_fetchfield('reason_order');
		$this->db->sql_freeresult($result);

		// Set the new (other) order
		$other_order = $direction === 'up' ? $order - 1 : $order + 1;

		// Select the other reason identifier (with which it is being swapped)
		$sql = 'SELECT reason_id FROM ' . $this->reasons_table . ' WHERE reason_order = ' . (int) $other_order;
		$result = $this->db->sql_query_limit($sql, 1);
		$other_id = (int) $this->db->sql_fetchfield('reason_id');
		$this->db->sql_freeresult($result);

		// Update both the reason rows
		$this->update(['reason_order' => $other_order], $reason_id);
		$this->update(['reason_order' => $order], $other_id);
	}
}
