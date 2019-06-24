<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps;

/**
 * phpBB Studio - Advanced Points System Extension base
 */
class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		$is_enableable = true;

		$user = $this->container->get('user');
		$user->add_lang_ext('phpbbstudio/aps', 'aps_ext');

		$lang = $user->lang;

		if (!(phpbb_version_compare(PHPBB_VERSION, '3.2.7', '>=') && phpbb_version_compare(PHPBB_VERSION, '4.0.0@dev', '<')))
		{
			/**
			 * Despite it seems wrong that's the right approach and not an error in coding
			 * That's done in order to avoid a PHP error like
			 * "Indirect modification of overloaded property phpbb/user::$lang has no effect"
			 * Discussed here: https://www.phpbb.com/community/viewtopic.php?p=14724151#p14724151
			 */
			$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('APS_PHPBB_VERSION', '3.2.7', '4.0.0@dev');

			$is_enableable = false;
		}

		$user->lang = $lang;

		return $is_enableable;
	}

	/**
	 * Enable notifications for the extension
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return mixed Returns false after last step, otherwise temporary state
	 */
	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			$this->container->get('notification_manager')
				->enable_notifications('phpbbstudio.aps.notification.type.adjust');

			return 'notification';
		}

		return parent::enable_step($old_state);
	}

	/**
	 * Disable notifications for the extension
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return mixed Returns false after last step, otherwise temporary state
	 */
	public function disable_step($old_state)
	{
		if ($old_state === false)
		{
			try
			{
				if ($this->container->hasParameter('phpbbstudio.aps.extended'))
				{
					$language = $this->container->get('language');
					$language->add_lang('aps_ext', 'phpbbstudio/aps');

					$message = $language->lang('APS_DISABLE_EXTENDED', $this->container->getParameter('phpbbstudio.aps.extended'));

					// Trigger error for the ACP
					@trigger_error($message, E_USER_WARNING);

					// Throw an exception for the CLI
					throw new \RuntimeException($message);
				}
			}
			catch (\InvalidArgumentException $e)
			{
				// Continue
			}

			$this->container->get('notification_manager')
				->disable_notifications('phpbbstudio.aps.notification.type.adjust');

			return 'notification';
		}

		return parent::disable_step($old_state);
	}

	/**
	 * Purge notifications for the extension
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return mixed Returns false after last step, otherwise temporary state
	 */
	public function purge_step($old_state)
	{
		if ($old_state === false)
		{
			$this->container->get('notification_manager')
				->purge_notifications('phpbbstudio.aps.notification.type.adjust');

			return 'notification';
		}

		return parent::purge_step($old_state);
	}
}
