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
 * phpBB Studio - Advanced Points System migrations: MCP Module.
 */
class install_mcp_module extends \phpbb\db\migration\migration
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
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'mcp'
				AND module_langname = 'MCP_APS_POINTS'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
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
	 * Add the Advanced Points System MCP module to the database.
	 *
	 * @return array		Array of module data
	 * @access public
	 */
	public function update_data()
	{
		return [
			['module.add', [
				'mcp',
				0,
				'MCP_APS_POINTS'
			]],
			['module.add', [
				'mcp',
				'MCP_APS_POINTS',
				[
					'module_basename'	=> '\phpbbstudio\aps\mcp\main_module',
					'modes'				=> ['front', 'change', 'logs'],
				],
			]],
		];
	}
}
