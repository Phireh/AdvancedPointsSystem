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
 * phpBB Studio - Advanced Points System action: Post
 */
class post extends base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\textformatter\s9e\utils */
	protected $utils;

	/** @var array Ignore criteria constants */
	protected $ignore;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config		Config object
	 * @param \phpbb\textformatter\s9e\utils	$utils		s9e Textformatter utilities object
	 * @param array								$constants	APS Constants
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\textformatter\s9e\utils $utils, array $constants)
	{
		$this->config	= $config;
		$this->utils	= $utils;
		$this->ignore	= $constants['ignore'];
	}

	/**
	 * Get action name.
	 *
	 * @return string			The name of the action this type belongs to
	 * @access public
	 */
	public function get_action()
	{
		return 'post';
	}

	/**
	 * Get global state.
	 *
	 * @return bool				If this type is global or local (per-forum basis)
	 * @access public
	 */
	public function is_global()
	{
		return false;
	}

	/**
	 * Get type category under which it will be listed in the ACP.
	 *
	 * @return string			The name of the category this type belongs to
	 * @access public
	 */
	public function get_category()
	{
		return 'POST';
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
			'aps_post_base'				=> 'APS_POINTS_POST',

			// Text points
			'aps_post_per_char'			=> 'APS_POINTS_PER_CHAR',
			'aps_post_per_word'			=> 'APS_POINTS_PER_WORD',
			'aps_post_per_quote'		=> 'APS_POINTS_PER_QUOTE',

			// Attachment points
			'aps_post_has_attach'		=> 'APS_POINTS_ATTACH_HAS',
			'aps_post_per_attach'		=> 'APS_POINTS_ATTACH_PER',

			// Modification points
			'aps_post_edit'				=> 'APS_POINTS_EDIT',
			'aps_post_delete'			=> 'APS_POINTS_DELETE',
			'aps_post_delete_soft'		=> 'APS_POINTS_DELETE_SOFT',
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
		$s_delete = in_array($mode, ['delete', 'soft_delete']);
		$forum_id = $s_delete ? $data['forum_id'] : $data['data']['forum_id'];
		$topic_id = $s_delete ? $data['topic_id'] : $data['data']['topic_id'];
		$post_id = $s_delete ? $data['post_id'] : $data['data']['post_id'];
		$message = $s_delete ? '' : $data['data']['message'];
		$s_approved = !$s_delete ? $data['post_visibility'] == ITEM_APPROVED : true;
		$attachments = $s_delete ? [] : $data['data']['attachment_data'];

		$logs = [];
		$values = $values[$forum_id];
		$strings = $this->get_data();

		switch ($mode)
		{
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'soft_delete':
				$mode = 'delete_soft';
			// no break;
			case 'edit':
			case 'delete':
				$points = $logs[$strings['aps_post_' . $mode]] = $values['aps_post_' . $mode];
			break;

			default:
				// Initial points
				$points = $logs[$strings['aps_post_base']] = $values['aps_post_base'];

				// Text points
				$quotes = $this->utils->get_outermost_quote_authors($message);
				$message = $this->utils->remove_bbcode($message, 'quote');
				$message = $this->utils->remove_bbcode($message, 'attachment');
				$message = $this->utils->clean_formatting($message);
				$words = $exclude_words = array_filter(preg_split('/[\s]+/', $message));
				$chars = $exclude_chars = implode('', $words);

				if ($min = $this->config['aps_points_exclude_words'])
				{
					$exclude_words = array_filter($words, function($word) use ($min)
					{
						return strlen($word) > $min;
					});

					if ($this->config['aps_points_exclude_chars'])
					{
						$exclude_chars = implode('', $exclude_words);
					}
				}

				// Check ignore criteria
				if ($this->config['aps_ignore_criteria'])
				{
					$ignore_words = $this->config['aps_ignore_excluded_words'] ? $exclude_words : $words;
					$ignore_chars = $this->config['aps_ignore_excluded_chars'] ? $exclude_chars : $chars;

					$ignore_words = count($ignore_words) < $this->config['aps_ignore_min_words'];
					$ignore_chars = strlen($ignore_chars) < $this->config['aps_ignore_min_chars'];

					if (($this->config['aps_ignore_criteria'] == $this->ignore['both'] && $ignore_words && $ignore_chars)
						|| ($this->config['aps_ignore_criteria'] == $this->ignore['words'] && $ignore_words)
						|| ($this->config['aps_ignore_criteria'] == $this->ignore['chars'] && $ignore_chars))
					{
						$points = 0;

						// Break out of calculation
						break;
					}
				}

				$words = $exclude_words;
				$chars = $exclude_chars;

				$points += $logs[$strings['aps_post_per_quote']] = $this->equate($values['aps_post_per_quote'], count($quotes), '*');
				$points += $logs[$strings['aps_post_per_word']] = $this->equate($values['aps_post_per_word'], count($words), '*');
				$points += $logs[$strings['aps_post_per_char']] = $this->equate($values['aps_post_per_char'], strlen($chars), '*');

				// Attachment points
				if (!empty($attachments))
				{
					$points += $logs[$strings['aps_post_has_attach']] = $values['aps_post_has_attach'];
					$points += $logs[$strings['aps_post_per_attach']] = $this->equate($values['aps_post_per_attach'], count($attachments), '*');
				}
			break;
		}

		foreach (array_keys($this->users) as $user_id)
		{
			$this->add($user_id, [
				'approved'	=> $s_approved,
				'forum_id'	=> $forum_id,
				'topic_id'	=> $topic_id,
				'post_id'	=> $post_id,
				'points'	=> $points,
				'logs'		=> $logs,
			]);
		}
	}
}
