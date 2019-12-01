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
 * phpBB Studio - Advanced Points System blockader.
 */
class blockader
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string APS Display table */
	protected $blocks_table;

	/** @var int User identifier used for admin desired blocks */
	protected $admin_id = 0;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\db\driver\driver_interface	$db				Database object
	 * @param  string								$blocks_table	APS Display table
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, $blocks_table)
	{
		$this->db			= $db;
		$this->blocks_table	= $blocks_table;
	}

	/**
	 * User identifier user for admin desired blocks.
	 *
	 * @return int					The admin identifier
	 * @access public
	 */
	public function get_admin_id()
	{
		return $this->admin_id;
	}

	/**
	 * Fetch a row from the database for the provided user identifier.
	 *
	 * @param  int		$user_id	The user identifier
	 * @return array				The json decoded database row
	 * @access public
	 */
	public function row($user_id)
	{
		$sql = 'SELECT aps_display FROM ' . $this->blocks_table . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$display = $this->db->sql_fetchfield('aps_display');
		$this->db->sql_freeresult($result);

		return $display ? (array) json_decode($display, true) : [];
	}

	/**
	 * Fetch a rowset from the database for the provided user identifier and admin identifier.
	 *
	 * @param  int		$user_id	The user identifier
	 * @return array				The json decoded database rowset
	 * @access public
	 */
	public function rowset($user_id)
	{
		$rowset = [];

		$sql = 'SELECT user_id, aps_display
				FROM ' . $this->blocks_table . '
				WHERE ' . $this->db->sql_in_set('user_id', [$this->admin_id, (int) $user_id]);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['user_id'] != ANONYMOUS)
			{
				$rowset[(int) $row['user_id']] = (array) json_decode($row['aps_display'], true);
			}
		}
		$this->db->sql_freeresult($result);

		return (array) $rowset;
	}

	/**
	 * Set the user desired blocks in the database.
	 *
	 * @param  int		$user_id	The user identifier
	 * @param  array	$blocks		The user desired blocks
	 * @param  bool		$insert		Whether to insert or update
	 * @return bool|int				Bool on update, integer on insert
	 * @access public
	 */
	public function set_blocks($user_id, array $blocks, $insert)
	{
		if ($user_id == ANONYMOUS)
		{
			return false;
		}

		if ($insert)
		{
			return $this->insert($user_id, $blocks);
		}
		else
		{
			return $this->update($user_id, $blocks);
		}
	}

	/**
	 * Insert a user desired blocks into the database.
	 *
	 * @param  int		$user_id	The user identifier
	 * @param  array	$blocks		The user desired blocks
	 * @return int
	 * @access public
	 */
	public function insert($user_id, array $blocks)
	{
		$sql = 'INSERT INTO ' . $this->blocks_table . ' ' . $this->db->sql_build_array('INSERT', [
			'user_id'		=> (int) $user_id,
			'aps_display'	=> json_encode($blocks),
		]);
		$this->db->sql_query($sql);

		return (int) $this->db->sql_nextid();
	}

	/**
	 * Update a user desired blocks in the database.
	 *
	 * @param  int		$user_id	The user identifier
	 * @param  array	$blocks		The user desired blocks
	 * @return bool
	 * @access public
	 */
	public function update($user_id, array $blocks)
	{
		$sql = 'UPDATE ' . $this->blocks_table . ' SET ' . $this->db->sql_build_array('UPDATE', [
			'aps_display'	=> json_encode($blocks),
		]) . ' WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}
}
