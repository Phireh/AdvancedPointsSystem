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
 * phpBB Studio - Advanced Points System Event listener: Check.
 */
class check implements EventSubscriberInterface
{
	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\aps\points\valuator */
	protected $valuator;

	/** @var double|false The minimum point value */
	protected $min;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\config\config					$config			Configuration object
	 * @param  \phpbbstudio\aps\core\functions		$functions		APS Core functions
	 * @param  \phpbb\language\language				$language		Language object
	 * @param  \phpbb\template\template				$template		Template object
	 * @param  \phpbb\user							$user			User object
	 * @param  \phpbbstudio\aps\points\valuator		$valuator		APS Valuator object
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbbstudio\aps\core\functions $functions,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbbstudio\aps\points\valuator $valuator
	)
	{
		$this->functions	= $functions;
		$this->language		= $language;
		$this->template		= $template;
		$this->user			= $user;
		$this->valuator		= $valuator;

		$this->min = $config['aps_points_min'] !== '' ? (double) $config['aps_points_min'] : false;
	}

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
			'core.viewtopic_modify_page_title'			=> 'check_bump',
			'core.handle_post_delete_conditions'		=> 'check_delete',
			'core.modify_posting_auth'					=> 'check_post',
			'core.viewtopic_modify_poll_data'			=> 'check_vote',
		];
	}

	/**
	 * Check the action: "Bump".
	 *
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function check_bump(\phpbb\event\data $event)
	{
		if ($this->min === false)
		{
			return;
		}

		// If there already is no bump link, return
		if ($this->template->retrieve_var('U_BUMP_TOPIC') === '')
		{
			return;
		}

		// Get the value
		$value = $this->get_value('aps_bump', $event['forum_id']);

		// Check if the value is negative
		if ($value >= 0)
		{
			return;
		}

		if ($this->below_min($value))
		{
			$this->template->assign_var('U_BUMP_TOPIC', '');
		}
	}

	/**
	 * Check the action: "Delete".
	 *
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function check_delete(\phpbb\event\data $event)
	{
		if ($this->min === false)
		{
			return;
		}

		if (confirm_box(true))
		{
			$data = $this->functions->post_data($event['post_id']);

			if ($this->user->data['user_id'] != $data['poster_id'])
			{
				return;
			}

			$field = 'aps_post_delete' . ($event['is_soft'] ? '_soft' : '');

			if ($value = $this->check_value($field, $event['forum_id']))
			{
				$event['error'] = array_merge($event['error'], [
					$this->language->lang('APS_POINTS_TOO_LOW', $this->functions->get_name()) . '<br>' .
					$this->language->lang('APS_POINTS_ACTION_COST', $this->functions->display_points($value))
				]);
			}
		}
	}

	/**
	 * Check the action: "Post" and "Topic".
	 *
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function check_post(\phpbb\event\data $event)
	{
		if ($this->min === false)
		{
			return;
		}

		switch ($event['mode'])
		{
			default:
				return;
			break;

			case 'post':
				$field = 'aps_topic_base';
			break;

			case 'reply':
			case 'quote':
				$field = 'aps_post_base';
			break;

			case 'edit':
				$data = $this->functions->post_data($event['post_id']);

				if ($this->user->data['user_id'] != $data['poster_id'])
				{
					return;
				}

				$type = $data['topic_first_post_id'] == $event['post_id'] ? 'topic' : 'post';
				$field = 'aps_' . $type . '_edit';
			break;
		}

		if ($value = $this->check_value($field, $event['forum_id']))
		{
			$event['error'] = array_merge($event['error'], [
				$this->language->lang('APS_POINTS_TOO_LOW', $this->functions->get_name()) . '<br>' .
				$this->language->lang('APS_POINTS_ACTION_COST', $this->functions->display_points($value))
			]);
		}
	}

	/**
	 * Check the action: "Vote".
	 *
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function check_vote(\phpbb\event\data $event)
	{
		if ($this->min === false)
		{
			return;
		}

		if ($this->check_value('aps_vote', $event['forum_id']))
		{
			$event['s_can_vote'] = false;
		}
	}

	/**
	 * Verify the user has enough points to perform an action.
	 *
	 * @param  int		$field			The points field
	 * @param  int		$forum_id		The forum identifier
	 * @return double|false				The points value
	 * @access protected
	 */
	protected function check_value($field, $forum_id)
	{
		$value = $this->get_value($field, $forum_id);

		$check = $value < 0 && $this->below_min($value) ? $value : false;

		return $check;
	}

	/**
	 * Get the base value for a points action.
	 *
	 * @param  int		$field			The points field
	 * @param  int		$forum_id		The forum identifier
	 * @return double					The points value
	 * @access protected
	 */
	protected function get_value($field, $forum_id)
	{
		$fields = [0 => [$field]];

		$values = $this->valuator->get_points($fields, $forum_id, false);

		$value = isset($values[$forum_id][$field]) ? $values[$forum_id][$field] : 0.00;

		return (double) $value;
	}

	/**
	 * Check whether or not the value is below the points minimum.
	 *
	 * @param  double	$value			The points value
	 * @return bool						Whether or not the value is below the minimum
	 * @access protected
	 */
	protected function below_min($value)
	{
		$points = $this->functions->equate_points($this->user->data['user_points'], $value);

		return (bool) ($points < $this->min);
	}
}
