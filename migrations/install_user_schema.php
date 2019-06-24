<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\migrations;

/**
 * phpBB Studio - Advanced Points System migrations: Database changes.
 */
class install_user_schema extends \phpbb\db\migration\migration
{
	/**
	 * Allows you to check if the migration is effectively installed (entirely optional)
	 *
	 * This is checked when a migration is installed. If true is returned, the migration will be set as
	 * installed without performing the database changes.
	 * This function is intended to help moving to migrations from a previous database updater, where some
	 * migrations may have been installed already even though they are not yet listed in the migrations table.
	 *
	 * @return bool		True if this migration is installed, False if this migration is not installed (checked on install)
	 * @access public
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_points');
	}

	/**
	 * Assign migration file dependencies for this migration.
	 *
	 * @return array		Array of migration files
	 * @access public
	 * @static
	 */
	static public function depends_on()
	{
		return ['\phpbbstudio\aps\migrations\install_acp_module'];
	}

	/**
	 * Add the Advanced Points System tables and columns to the database.
	 *
	 * @return array		Array of tables and columns data
	 * @access public
	 */
	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'users'			=> [
					'user_points'			=> ['DECIMAL:14', 0.00],
				],
			],
			'add_tables'		=> [
				$this->table_prefix . 'aps_display'	=> [
					'COLUMNS'		=> [
						'user_id'			=> ['ULINT', 0],
						'aps_display'		=> ['MTEXT_UNI', ''],
					],
					'PRIMARY_KEY'	=> 'user_id',
				],
				$this->table_prefix . 'aps_logs'	=> [
					'COLUMNS'		=> [
						'log_id'			=> ['ULINT', null, 'auto_increment'],
						'log_action'		=> ['TEXT_UNI', ''],
						'log_actions'		=> ['MTEXT_UNI', ''],
						'log_time'			=> ['TIMESTAMP', 0],
						'log_approved'		=> ['BOOL', 1],
						'forum_id'			=> ['ULINT', 0],
						'topic_id'			=> ['ULINT', 0],
						'post_id'			=> ['ULINT', 0],
						'user_id'			=> ['ULINT', 0],
						'reportee_id'		=> ['ULINT', 0],
						'reportee_ip'		=> ['VCHAR:40', ''],
						'points_old'		=> ['DECIMAL:14', 0.00],
						'points_sum'		=> ['DECIMAL:14', 0.00],
						'points_new'		=> ['DECIMAL:14', 0.00],
					],
					'PRIMARY_KEY'	=> 'log_id',
					'KEYS'			=> [
						'forum_id'		=> ['INDEX', 'forum_id'],
						'topic_id'		=> ['INDEX', 'topic_id'],
						'post_id'		=> ['INDEX', 'post_id'],
						'user_id'		=> ['INDEX', 'user_id'],
						'reportee_id'	=> ['INDEX', 'reportee_id'],
					],
				],
				$this->table_prefix . 'aps_points'	=> [
					'COLUMNS'		=> [
						'points_name'		=> ['VCHAR_UNI', ''],
						'points_value'		=> ['DECIMAL:6', 0.00],
						'forum_id'			=> ['ULINT', 0],
					],
					'PRIMARY_KEY'	=> ['points_name', 'forum_id'],
					'KEYS'			=> [
						'forum_id'		=> ['INDEX', 'forum_id'],
					],
				],
				$this->table_prefix . 'aps_reasons'	=> [
					'COLUMNS'		=> [
						'reason_id'			=> ['ULINT', null, 'auto_increment'],
						'reason_title'		=> ['VCHAR_UNI', ''],
						'reason_desc'		=> ['TEXT_UNI', ''],
						'reason_points'		=> ['DECIMAL:14', 0.00],
						'reason_order'		=> ['UINT', 0],
					],
					'PRIMARY_KEY'	=> 'reason_id',
				],
			],
		];
	}

	/**
	 * Reverts the database schema by providing a set of change instructions
	 *
	 * @return array    Array of schema changes
	 * 					(compatible with db_tools->perform_schema_changes())
	 * @access public
	 */
	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'users'			=> [
					'user_points',
				],
			],
			'drop_tables'		=> [
				$this->table_prefix . 'aps_display',
				$this->table_prefix . 'aps_logs',
				$this->table_prefix . 'aps_points',
				$this->table_prefix . 'aps_reasons',
			],
		];
	}
}
