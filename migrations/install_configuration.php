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
 * phpBB Studio - Advanced Points System migrations: Configuration.
 */
class install_configuration extends \phpbb\db\migration\migration
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
		return isset($this->config['aps_points_name_en']);
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
		return ['\phpbbstudio\aps\migrations\install_user_schema'];
	}

	/**
	 * Add the Advanced Points System configuration to the database.
	 *
	 * @return array		Array of configuration
	 * @access public
	 */
	public function update_data()
	{
		return [
			['config.add', ['aps_points_name_en', 'Points']],
			['config.add', ['aps_points_safe_mode', false]], // @todo Change to true upon release
			['config.add', ['aps_points_icon', 'fa-money']],
			['config.add', ['aps_points_icon_position', 1]],
			['config.add', ['aps_points_decimals', 2]],
			['config.add', ['aps_points_separator_dec', ',']],
			['config.add', ['aps_points_separator_thou', htmlspecialchars('&#8239;')]],
			['config.add', ['aps_points_display_pm', true]],
			['config.add', ['aps_points_display_post', true]],
			['config.add', ['aps_points_display_profile', true]],
			['config.add', ['aps_points_min', '']],
			['config.add', ['aps_points_max', '']],
			['config.add', ['aps_points_exclude_words', 1]],
			['config.add', ['aps_points_exclude_chars', 1]],

			['config.add', ['aps_birthday_last_run', 0, true]],
			['config.add', ['aps_notification_id', 0]],
			['config.add', ['aps_actions_per_page', 10]],

			['config.add', ['aps_chain_merge_delete', false]],
			['config.add', ['aps_chain_merge_move', false]],
			['config.add', ['aps_chain_warn_pm', false]],

			['config.add', ['aps_display_top_change', true]],
			['config.add', ['aps_display_top_count', 3]],
			['config.add', ['aps_display_adjustments', 5]],
			['config.add', ['aps_display_graph_time', 1500]],
		];
	}
}
