<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\core;

/**
 * phpBB Studio - Advanced Points System language functions.
 */
class language
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\extension\manager */
	protected $manager;

	/** @var \phpbb\user */
	protected $user;

	/** @var string php file extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\config\config		$config		Configuration object
	 * @param  \phpbb\language\language	$language	Language object
	 * @param  \phpbb\extension\manager	$manager	Extension manager object
	 * @param  \phpbb\user				$user		User object
	 * @param  string					$php_ext	php file extension
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\extension\manager $manager,
		\phpbb\user $user,
		$php_ext
	)
	{
		$this->config	= $config;
		$this->language	= $language;
		$this->manager	= $manager;
		$this->user		= $user;

		$this->php_ext	= $php_ext;
	}

	/**
	 * Load all language files used for the Advanced Points System.
	 *
	 * @see \p_master::add_mod_info()
	 *
	 * @return void
	 * @access public
	 */
	public function load()
	{
		$finder = $this->manager->get_finder();

		$finder->prefix('phpbbstudio_aps_')
				->suffix('.' . $this->php_ext);

		// We grab the language files from the default, English and user's language.
		// So we can fall back to the other files like we do when using add_lang()
		$default_lang_files = $english_lang_files = $user_lang_files = [];

		// Search for board default language if it's not the user language
		if ($this->config['default_lang'] != $this->user->lang_name)
		{
			$default_lang_files = $finder
				->extension_directory('/language/' . basename($this->config['default_lang']))
				->find();
		}

		// Search for english, if its not the default or user language
		if ($this->config['default_lang'] != 'en' && $this->user->lang_name != 'en')
		{
			$english_lang_files = $finder
				->extension_directory('/language/en')
				->find();
		}

		// Find files in the user's language
		$user_lang_files = $finder
			->extension_directory('/language/' . $this->user->lang_name)
			->find();

		$lang_files = array_merge($english_lang_files, $default_lang_files, $user_lang_files);
		foreach ($lang_files as $lang_file => $ext_name)
		{
			$this->language->add_lang($lang_file, $ext_name);
		}
	}
}
