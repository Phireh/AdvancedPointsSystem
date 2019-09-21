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
 * phpBB Studio - Advanced Points System migrations: Permissions update.
 */
class update_permissions extends \phpbb\db\migration\migration
{
	/**
	 * Assign migration file dependencies for this migration.
	 *
	 * @return array		Array of migration files
	 * @access public
	 * @static
	 */
	static public function depends_on()
	{
		return ['\phpbbstudio\aps\migrations\install_permissions'];
	}

	/**
	 * Add additional Advanced Points System permissions to the database.
	 *
	 * @return array		Array of permission
	 * @access public
	 */
	public function update_data()
	{
		$data = [
			['permission.add', ['u_aps_view_build_other']],
			['permission.add', ['u_aps_view_logs_other']],
		];

		if ($this->role_exists('ROLE_USER_STANDARD'))
		{
			$data[] = ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_aps_view_build_other']];
			$data[] = ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_aps_view_logs_other']];
		}

		if ($this->role_exists('ROLE_USER_FULL'))
		{
			$data[] = ['permission.permission_set', ['ROLE_USER_FULL', 'u_aps_view_build_other']];
			$data[] = ['permission.permission_set', ['ROLE_USER_FULL', 'u_aps_view_logs_other']];
		}

		return $data;
	}

	/**
	 * Checks whether the given role does exist or not.
	 *
	 * @param  string	$role	The name of the role
	 * @return bool				True if the role exists, false otherwise
	 */
	private function role_exists($role)
	{
		$sql = 'SELECT role_id
				FROM ' . ACL_ROLES_TABLE . '
				WHERE role_name = "' . $this->db->sql_escape($role) . '"';
		$result = $this->db->sql_query_limit($sql, 1);
		$role_id = $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);

		return (bool) $role_id;
	}
}
