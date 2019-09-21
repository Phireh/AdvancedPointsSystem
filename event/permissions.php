<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * phpBB Studio - Advanced Points System Event listener.
 */
class permissions implements EventSubscriberInterface
{
	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @static
	 * @return array
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.permissions'	=> 'permissions',
		];
	}

	/**
	 * Add Advanced Points System permissions
	 *
	 * @event  core.permissions
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function permissions($event)
	{
		$categories = $event['categories'];
		$permissions = $event['permissions'];

		if (empty($categories['phpbb_studio']))
		{
			$categories['phpbb_studio'] = 'ACL_CAT_PHPBB_STUDIO';

			$event['categories'] = $categories;
		}

		$perms = [
			'a_aps_logs', 'a_aps_points', 'a_aps_reasons', 'a_aps_display', 'a_aps_settings',
			'm_aps_adjust_custom', 'm_aps_adjust_reason',
			'u_aps_view_build', 'u_aps_view_build_other', 'u_aps_view_logs', 'u_aps_view_logs_other', 'u_aps_view_mod',
		];

		foreach ($perms as $permission)
		{
			$permissions[$permission] = ['lang' => 'ACL_' . utf8_strtoupper($permission), 'cat' => 'phpbb_studio'];
		}

		$event['permissions'] = $permissions;
	}
}
