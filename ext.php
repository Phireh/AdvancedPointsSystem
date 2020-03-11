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
		if (!(phpbb_version_compare(PHPBB_VERSION, '3.2.8', '>=') && phpbb_version_compare(PHPBB_VERSION, '4.0.0@dev', '<')))
		{
			if (phpbb_version_compare(PHPBB_VERSION, '3.3.0@dev', '<'))
			{
				$user = $this->container->get('user');
				$user->add_lang_ext('phpbbstudio/aps', 'aps_ext');

				$lang = $user->lang;

				$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('APS_PHPBB_VERSION', '3.2.8', '4.0.0@dev');

				$user->lang = $lang;

				return false;
			}

			if (phpbb_version_compare(PHPBB_VERSION, '3.3.0@dev', '>'))
			{
				$language= $this->container->get('language');
				$language->add_lang('aps_ext', 'phpbbstudio/aps');

				return $language->lang('APS_PHPBB_VERSION', '3.2.8', '4.0.0@dev');
			}
		}

		/**
		 * Now if Ultimate Points is enabled already.
		 */
		$ext_manager = $this->container->get('ext.manager');
		$is_ups_enabled = $ext_manager->is_enabled('dmzx/ultimatepoints');

		if ($is_ups_enabled)
		{
			if (phpbb_version_compare(PHPBB_VERSION, '3.3.0@dev', '<'))
			{
				$user = $this->container->get('user');
				$user->add_lang_ext('phpbbstudio/aps', 'aps_ext');

				$lang = $user->lang;

				$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('APS_UP_INSTALLED');

				$user->lang = $lang;

				return false;
			}

			if (phpbb_version_compare(PHPBB_VERSION, '3.3.0@dev', '>'))
			{
				$language= $this->container->get('language');
				$language->add_lang('aps_ext', 'phpbbstudio/aps');

				return $language->lang('APS_UP_INSTALLED');
			}
		}

		return true;
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
