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
 * phpBB Studio - Advanced Points System action: Topic
 */
class topic extends base
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
		return 'topic';
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
		return 'TOPIC';
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
			// Type points
			'aps_topic_base'			=> 'APS_POINTS_TOPIC',
			'aps_topic_sticky'			=> 'APS_POINTS_STICKY',
			'aps_topic_announce'		=> 'APS_POINTS_ANNOUNCE',
			'aps_topic_global'			=> 'APS_POINTS_GLOBAL',

			// Text points
			'aps_topic_per_char'		=> 'APS_POINTS_PER_CHAR',
			'aps_topic_per_word'		=> 'APS_POINTS_PER_WORD',
			'aps_topic_per_quote'		=> 'APS_POINTS_PER_QUOTE',

			// Attachment points
			'aps_topic_has_attach'		=> 'APS_POINTS_ATTACH_HAS',
			'aps_topic_per_attach'		=> 'APS_POINTS_ATTACH_PER',

			// Poll points
			'aps_topic_has_poll'		=> 'APS_POINTS_POLL_HAS',
			'aps_topic_per_option'		=> 'APS_POINTS_POLL_OPTION',

			// Miscellaneous
			'aps_topic_bump'			=> 'APS_POINTS_BUMP',
			'aps_topic_edit'			=> 'APS_POINTS_EDIT',
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
		$mode = $data['mode'];
		$s_bump = $mode === 'bump';

		$type = !$s_bump ? $data['topic_type'] : '';
		$post_data = !$s_bump ? $data['data'] : $data['post_data'];

		$s_approved = isset($data['post_visibility']) ? $data['post_visibility'] == ITEM_APPROVED : true;
		$poll = isset($data['poll']['poll_options']) ? $data['poll']['poll_options'] : [];

		$forum_id = $post_data['forum_id'];
		$topic_id = $post_data['topic_id'];
		$post_id = !$s_bump ? $post_data['post_id'] : 0;
		$message = !$s_bump ? $post_data['message'] : '';
		$attachments = !$s_bump ? $post_data['attachment_data'] : '';

		$logs = [];
		$values = $values[$forum_id];
		$strings = $this->get_data();

		switch ($mode)
		{
			case 'bump':
			case 'edit':
				$points = $logs[$strings['aps_topic_' . $mode]] = $values['aps_topic_' . $mode];
			break;

			default:
				// Initial type points
				switch ($type)
				{
					default:
					case POST_NORMAL:
						$points = $logs[$strings['aps_topic_base']] = $values['aps_topic_base'];
					break;

					case POST_STICKY:
						$points = $logs[$strings['aps_topic_sticky']] = $values['aps_topic_sticky'];
					break;

					case POST_ANNOUNCE:
						$points = $logs[$strings['aps_topic_announce']] = $values['aps_topic_announce'];
					break;

					case POST_GLOBAL:
						$points = $logs[$strings['aps_topic_global']] = $values['aps_topic_global'];
					break;
				}

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

				$points += $logs[$strings['aps_topic_per_quote']] = $this->equate($values['aps_topic_per_quote'], count($quotes), '*');
				$points += $logs[$strings['aps_topic_per_word']] = $this->equate($values['aps_topic_per_word'], count($words), '*');
				$points += $logs[$strings['aps_topic_per_char']] = $this->equate($values['aps_topic_per_char'], strlen($chars), '*');

				// Attachment points
				if (!empty($attachments))
				{
					$points += $logs[$strings['aps_topic_has_attach']] = $values['aps_topic_has_attach'];
					$points += $logs[$strings['aps_topic_per_attach']] = $this->equate($values['aps_topic_per_attach'], count($attachments), '*');
				}

				// Poll points
				if ($poll)
				{
					$points += $logs[$strings['aps_topic_has_poll']] = $values['aps_topic_has_poll'];
					$points += $logs[$strings['aps_topic_per_option']] = $this->equate($values['aps_topic_per_option'], count($poll), '*');
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
