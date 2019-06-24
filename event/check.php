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
class check implements EventSubscriberInterface
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
			'core.viewtopic_modify_page_title'			=> 'check_bump',
			'core.handle_post_delete_conditions'		=> 'check_delete',
			'core.modify_posting_auth'					=> 'check_post',
			'core.viewtopic_modify_poll_data'			=> 'check_vote',
		];
	}

	protected $functions;

	protected $lang;

	protected $template;

	protected $user;

	protected $valuator;

	protected $min;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\config\config				$config		Configuration object
	 * @param  \phpbbstudio\aps\core\functions	$functions	APS Core functions
	 * @param  \phpbb\language\language			$lang		Language object
	 * @param  \phpbb\template\template			$template	Template object
	 * @param  \phpbb\user						$user		User object
	 * @param  \phpbbstudio\aps\points\valuator	$valuator	APS Valuator object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbbstudio\aps\core\functions $functions, \phpbb\language\language $lang, \phpbb\template\template $template, \phpbb\user $user, \phpbbstudio\aps\points\valuator $valuator)
	{
		$this->lang			= $lang;
		$this->functions	= $functions;
		$this->template		= $template;
		$this->user			= $user;
		$this->valuator		= $valuator;

		$this->min = $config['aps_points_min'] !== '' ? $config['aps_points_min'] : false;
	}

	public function check_bump($event)
	{
		if ($this->min)
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
		if (!$this->is_negative($value))
		{
			return;
		}

		if ($this->below_min($value))
		{
			$this->template->assign_var('U_BUMP_TOPIC', '');
		}
	}

	public function check_delete($event)
	{
		if (!$this->min)
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
					$this->lang->lang('APS_POINTS_TOO_LOW', $this->functions->get_name()) . '<br>' .
					$this->lang->lang('APS_POINTS_ACTION_COST', $this->functions->display_points($value))
				]);
			}
		}
	}

	public function check_post($event)
	{
		if (!$this->min)
		{
			return;
		}

		switch ($event['mode'])
		{
			default:
				return;
			break;

			case 'post':
			case 'reply':
			case 'quote':
				$field = $event['mode'] === 'post' ? 'aps_topic_base' : 'aps_post_base';
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
				$this->lang->lang('APS_POINTS_TOO_LOW', $this->functions->get_name()) . '<br>' .
				$this->lang->lang('APS_POINTS_ACTION_COST', $this->functions->display_points($value))
			]);
		}
	}

	public function check_vote($event)
	{
		if (!$this->min)
		{
			return;
		}

		if ($this->check_value('aps_vote', $event['forum_id']))
		{
			$event['s_can_vote'] = false;
		}
	}

	protected function check_value($field, $forum_id)
	{
		$value = $this->get_value($field, $forum_id);

		return ($this->is_negative($value) && $this->below_min($value)) ? $value : false;
	}

	protected function get_value($field, $forum_id)
	{
		$fields = [0 => [$field]];

		$values = $this->valuator->get_points($fields, $forum_id, false);

		$value = isset($values[$forum_id][$field]) ? $values[$forum_id][$field] : 0.00;

		return (double) $value;
	}

	protected function is_negative($value)
	{
		return (bool) $value < 0;
	}

	protected function below_min($value)
	{
		return (bool) $this->user->data['user_points'] + $value < $this->min;
	}
}
