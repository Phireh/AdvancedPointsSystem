<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\actions\type;

/**
 * phpBB Studio - Advanced Points System action: Private message
 */
class pm extends base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\textformatter\s9e\utils */
	protected $utils;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config		Config object
	 * @param \phpbb\textformatter\s9e\utils	$utils		s9e Textformatter utilities object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\textformatter\s9e\utils $utils)
	{
		$this->config	= $config;
		$this->utils	= $utils;
	}

	/**
	 * Get action name.
	 *
	 * @return string			The name of the action this type belongs to
	 * @access public
	 */
	public function get_action()
	{
		return 'pm';
	}

	/**
	 * Get global state.
	 *
	 * @return bool				If this type is global or local (per-forum basis)
	 * @access public
	 */
	public function is_global()
	{
		return true;
	}

	/**
	 * Get type category under which it will be listed in the ACP.
	 *
	 * @return string			The name of the category this type belongs to
	 * @access public
	 */
	public function get_category()
	{
		return 'PRIVATE_MESSAGE';
	}

	/**
	 * Get type data.
	 *
	 * @return array			An array of value names and their language string
	 * @access public
	 */
	public function get_data()
	{
		return [
			// Initial points
			'aps_pm_base'			=> 'APS_POINTS_PM',

			// Recipients points
			'aps_pm_per_recipient'	=> 'APS_POINTS_PM_PER_RECIPIENT',

			// Text points
			'aps_pm_per_char'		=> 'APS_POINTS_PER_CHAR',
			'aps_pm_per_word'		=> 'APS_POINTS_PER_WORD',
			'aps_pm_per_quote'		=> 'APS_POINTS_PER_QUOTE',

			// Attachment points
			'aps_pm_has_attach'		=> 'APS_POINTS_ATTACH_HAS',
			'aps_pm_per_attach'		=> 'APS_POINTS_ATTACH_PER',

			// Modification points
			'aps_pm_edit'			=> 'APS_POINTS_EDIT',
		];
	}

	/**
	 * Calculate points for this type.
	 *
	 * @param  array	$data	The data available from the $event that triggered this action
	 * @param  array	$values	The point values available, indexed per forum_id and 0 for global values
	 * @retrun void
	 */
	public function calculate($data, $values)
	{
		// Grab event data
		$mode = $data['mode'];
		$message = $data['data']['message'];
		$attachments = !empty($data['data']['attachment_data']) ? $data['data']['attachment_data'] : [];
		$recipients = $data['pm_data']['recipients'];

		$logs = [];
		$values = $values[0];
		$strings = $this->get_data();

		switch ($mode)
		{
			case 'edit':
				$points = $logs[$strings['aps_pm_' . $mode]] = $values['aps_pm_' . $mode];
			break;

			default:
				// Initial points
				$points = $logs[$strings['aps_pm_base']] = $values['aps_pm_base'];

				// Recipient points
				$points += $logs[$strings['aps_pm_per_recipient']] = $this->equate($values['aps_pm_per_recipient'], count($recipients), '*');

				// Text points
				$quotes = $this->utils->get_outermost_quote_authors($message);
				$message = $this->utils->remove_bbcode($message, 'quote');
				$message = $this->utils->remove_bbcode($message, 'attachment');
				$message = $this->utils->clean_formatting($message);
				$words = array_filter(preg_split('/[\s]+/', $message));
				$chars = implode('', $words);

				if ($min = $this->config['acp_points_exclude_words'])
				{
					$words = array_filter($words, function($word) use ($min) {
						return strlen($word) > $min;
					});

					if ($this->config['acp_points_exclude_words'])
					{
						$chars = implode('', $words);
					}
				}

				$points += $logs[$strings['aps_pm_per_quote']] = $this->equate($values['aps_pm_per_quote'], count($quotes), '*');
				$points += $logs[$strings['aps_pm_per_word']] = $this->equate($values['aps_pm_per_word'], count($words), '*');
				$points += $logs[$strings['aps_pm_per_char']] = $this->equate($values['aps_pm_per_char'], strlen($chars), '*');

				// Attachment points
				if (!empty($attachments))
				{
					$points += $logs[$strings['aps_pm_has_attach']] = $values['aps_pm_has_attach'];
					$points += $logs[$strings['aps_pm_per_attach']] = $this->equate($values['aps_pm_per_attach'], count($attachments), '*');
				}
			break;
		}

		foreach (array_keys($this->users) as $user_id)
		{
			$this->add($user_id, [
				'points'	=> $points,
				'logs'		=> $logs,
			]);
		}
	}
}
