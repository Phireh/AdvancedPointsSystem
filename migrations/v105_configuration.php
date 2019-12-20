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
class v105_configuration extends \phpbb\db\migration\migration
{
	/**
	 * Allows you to check if the migration is effectively installed (entirely optional)
	 *
	 * @return bool		True if this migration is installed, False if this migration is not installed (checked on install)
	 * @access public
	 */
	public function effectively_installed()
	{
		return $this->config->offsetExists('aps_ignore_criteria');
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
		return ['\phpbbstudio\aps\migrations\update_configuration'];
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
			['config.add', ['aps_link_locations', 32]],
			['config.add', ['aps_ignore_criteria', 0]],
			['config.add', ['aps_ignore_min_chars', 0]],
			['config.add', ['aps_ignore_min_words', 0]],
			['config.add', ['aps_ignore_excluded_chars', 0]],
			['config.add', ['aps_ignore_excluded_words', 0]],
		];
	}
}
