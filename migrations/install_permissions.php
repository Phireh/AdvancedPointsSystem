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
 * phpBB Studio - Advanced Points System migrations: Permissions.
 */
class install_permissions extends \phpbb\db\migration\migration
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
		return ['\phpbbstudio\aps\migrations\install_user_schema'];
	}

	/**
	 * Add the Advanced Points System permissions to the database.
	 *
	 * @return array		Array of permission
	 * @access public
	 */
	public function update_data()
	{
		$data = [
			['permission.add', ['a_aps_logs']],
			['permission.add', ['a_aps_points']],
			['permission.add', ['a_aps_reasons']],
			['permission.add', ['a_aps_display']],
			['permission.add', ['a_aps_settings']],

			['permission.add', ['m_aps_adjust_custom']],
			['permission.add', ['m_aps_adjust_reason']],

			['permission.add', ['u_aps_view_build']],
			['permission.add', ['u_aps_view_logs']],
			['permission.add', ['u_aps_view_mod']],
		];

		if ($this->role_exists('ROLE_USER_STANDARD'))
		{
			$data[] = ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_aps_view_build']];
			$data[] = ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_aps_view_logs']];
			// Can NOT view the moderator's name
		}

		if ($this->role_exists('ROLE_USER_FULL'))
		{
			$data[] = ['permission.permission_set', ['ROLE_USER_FULL', 'u_aps_view_build']];
			$data[] = ['permission.permission_set', ['ROLE_USER_FULL', 'u_aps_view_logs']];
			$data[] = ['permission.permission_set', ['ROLE_USER_FULL', 'u_aps_view_mod']];
		}

		if ($this->role_exists('ROLE_MOD_STANDARD'))
		{
			$data[] = ['permission.permission_set', ['ROLE_MOD_STANDARD', 'm_aps_adjust_reason']];
			// Can NOT adjust a user's points with a custom action, only admin defined ones
		}

		if ($this->role_exists('ROLE_MOD_FULL'))
		{
			$data[] = ['permission.permission_set', ['ROLE_MOD_FULL', 'm_aps_adjust_custom']];
			$data[] = ['permission.permission_set', ['ROLE_MOD_FULL', 'm_aps_adjust_reason']];
		}

		if ($this->role_exists('ROLE_ADMIN_STANDARD'))
		{
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_STANDARD', 'a_aps_logs']];
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_STANDARD', 'a_aps_points']];
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_STANDARD', 'a_aps_reasons']];
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_STANDARD', 'a_aps_display']];
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_STANDARD', 'a_aps_settings']];
		}

		if ($this->role_exists('ROLE_ADMIN_FULL'))
		{
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_aps_logs']];
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_aps_points']];
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_aps_reasons']];
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_aps_display']];
			$data[] = ['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_aps_settings']];
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
				WHERE role_name = \'' . $this->db->sql_escape($role) . '\'';
		$result = $this->db->sql_query_limit($sql, 1);
		$role_id = $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);

		return (bool) $role_id;
	}
}
