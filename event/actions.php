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
 * phpBB Studio - Advanced Points System Event listener: Actions.
 */
class actions implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbbstudio\aps\actions\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP file extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth					$auth		Authentication object
	 * @param  \phpbb\config\config				$config		Configuration object
	 * @param  \phpbbstudio\aps\core\functions	$functions	APS Core functions
	 * @param  \phpbbstudio\aps\actions\manager	$manager	APS Manager object
	 * @param  \phpbb\request\request			$request	Request object
	 * @param  \phpbb\user						$user		User object
	 * @param  string							$root_path	phpBB root path
	 * @param  string							$php_ext	php File extension
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbbstudio\aps\core\functions $functions,
		\phpbbstudio\aps\actions\manager $manager,
		\phpbb\request\request $request,
		\phpbb\user $user,
		$root_path,
		$php_ext
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->functions	= $functions;
		$this->manager		= $manager;
		$this->request		= $request;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
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
			/* User actions */
			'core.modify_posting_auth'					=> 'bump',
			'core.submit_post_end'						=> 'post',
			'core.delete_post_after'					=> 'post_delete',
			'core.viewtopic_modify_poll_ajax_data'		=> 'vote',

			/* Moderator actions */
			'core.mcp_main_modify_fork_sql'				=> 'copy',
			'core.mcp_change_poster_after'				=> 'change',
			'core.delete_topics_before_query'			=> 'delete',
			'core.posting_modify_submit_post_before'	=> 'lock_and_type',
			'core.mcp_lock_unlock_after'				=> 'lock',
			'core.move_posts_before'					=> 'move_posts',
			'core.move_topics_before_query'				=> 'move_topics',
			'core.approve_posts_after'					=> 'queue',
			'core.approve_topics_after'					=> 'queue',
			'core.disapprove_posts_after'				=> 'queue',
			'core.mcp_forum_merge_topics_after'			=> 'merge',

			/* Global actions */
			'core.ucp_register_register_after'			=> 'register',
			'core.mcp_warn_post_after'					=> 'warn',
			'core.mcp_warn_user_after'					=> 'warn',
			'core.submit_pm_after'						=> 'pm',
		];
	}

	/**
	 * Trigger Advanced Points System action: “bump”!
	 *
	 * @event  core.modify_posting_auth
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function bump(\phpbb\event\data $event)
	{
		if (
			($event['mode'] !== 'bump')
			||
			(!$event['is_authed'] || !empty($event['error']) || $event['post_data']['forum_type'] != FORUM_POST)
			||
			(($event['post_data']['forum_status'] == ITEM_LOCKED || (isset($event['post_data']['topic_status']) && $event['post_data']['topic_status'] == ITEM_LOCKED)) && !$this->auth->acl_get('m_edit', $event['forum_id']))
		)
		{
			return;
		}

		if ($bump_time = bump_topic_allowed($event['forum_id'], $event['post_data']['topic_bumped'], $event['post_data']['topic_last_post_time'], $event['post_data']['topic_poster'], $event['post_data']['topic_last_poster_id'])
			&& check_link_hash($this->request->variable('hash', ''), "topic_{$event['post_data']['topic_id']}"))
		{
			$this->manager->trigger('topic', $this->user->data['user_id'], $event, $event['forum_id']);
		}
	}

	/**
	 * Trigger Advanced Points System action: “post” or “topic”!
	 *
	 * @event  core.submit_post_end
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function post(\phpbb\event\data $event)
	{
		if ($event['mode'] === 'edit' && $event['data']['poster_id'] != $this->user->data['user_id'])
		{
			$this->manager->trigger('edit', $event['data']['poster_id'], $event, $event['data']['forum_id']);

			return;
		}

		switch ($event['mode'])
		{
			case 'edit':
				$action = $event['data']['topic_first_post_id'] == $event['data']['post_id'] ? 'topic' : 'post';
			break;

			case 'post':
				$action = 'topic';
			break;

			case 'reply':
			case 'quote':
				$action = 'post';
			break;

			default:
				return;
			break;
		}

		$this->manager->trigger($action, $this->user->data['user_id'], $event, $event['data']['forum_id']);
	}

	/**
	 * Trigger Advanced Points System action: “delete” or “post”!
	 *
	 * @event  core.delete_post_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function post_delete(\phpbb\event\data $event)
	{
		if ($this->user->data['user_id'] == $event['data']['poster_id'])
		{
			$data = array_merge($this->manager->clean_event($event), [
				'mode'		=> ($event['is_soft'] ? 'soft_' : '') . 'delete',
				'post_data'	=> ['topic_type' => POST_NORMAL],
			]);

			$this->manager->trigger('post', $event['data']['poster_id'], $data, $event['forum_id']);
		}
		else
		{
			$data = [
				'action'	=> 'post',
				'is_soft'	=> $event['is_soft'],
				'posts'		=> [
					0 => [
						'forum_id'	=> $event['forum_id'],
						'topic_id'	=> $event['topic_id'],
						'post_id'	=> $event['post_id'],
						'poster_id'	=> $event['data']['poster_id'],
					],
				],
			];

			$this->manager->trigger('delete', $event['data']['poster_id'], $data, $event['forum_id']);
		}
	}

	/**
	 * Trigger Advanced Points System action: “vote”!
	 *
	 * @event  core.viewtopic_modify_poll_ajax_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function vote(\phpbb\event\data $event)
	{
		$this->manager->trigger('vote', $this->user->data['user_id'], $event, $event['forum_id']);
	}

	/**
	 * Trigger Advanced Points System action: “copy”!
	 *
	 * @event  core.mcp_main_modify_fork_sql
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function copy(\phpbb\event\data $event)
	{
		$this->manager->trigger('copy', $event['topic_row']['topic_poster'], $event, [(int) $event['topic_row']['forum_id'], (int) $event['sql_ary']['forum_id']]);
	}

	/**
	 * Trigger Advanced Points System action: “change”!
	 *
	 * @event  core.mcp_change_poster_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function change(\phpbb\event\data $event)
	{
		$this->manager->trigger('change', [$event['userdata']['user_id'], $event['post_info']['poster_id']], $event, $event['post_info']['forum_id']);
	}

	/**
	 * Trigger Advanced Points System action: “delete”!
	 *
	 * @event  core.delete_topics_before_query
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function delete(\phpbb\event\data $event)
	{
		// Check for chain triggering events
		if (!$this->config['aps_chain_merge_delete'] && $this->request->variable('action', '', true) === 'merge_topics')
		{
			return;
		}

		if (!function_exists('phpbb_get_topic_data'))
		{
			/** @noinspection PhpIncludeInspection */
			include $this->root_path . 'includes/functions_mcp.' . $this->php_ext;
		}

		$topics = phpbb_get_topic_data($event['topic_ids']);

		$forum_ids = $this->manager->get_identifiers($topics, 'forum_id');
		$user_ids = $this->manager->get_identifiers($topics, 'topic_poster');

		$data = array_merge($this->manager->clean_event($event), [
			'action'	=> 'topic',
			'topics'	=> $topics,
		]);

		$this->manager->trigger('delete', $user_ids, $data, $forum_ids);
	}

	/**
	 * Trigger Advanced Points System action: “lock” and/or “type”!
	 *
	 * @event  core.posting_modify_submit_post_before
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function lock_and_type(\phpbb\event\data $event)
	{
		if ($event['mode'] === 'edit')
		{
			if ($this->user->data['user_id'] != $event['data']['poster_id'])
			{
				$row = $this->functions->topic_post_locked($event['data']['post_id']);

				if ($row['post_edit_locked'] != $event['data']['post_edit_locked'])
				{
					$data = array_merge($this->manager->clean_event($event), [
						'action'	=> $event['data']['post_edit_locked'] ? 'lock_post' : 'unlock_post',
						'data'		=> [$event['data']],
					]);

					$this->manager->trigger('lock', $event['data']['poster_id'], $data, $event['data']['forum_id']);
				}

				if ($row['topic_status'] != $event['data']['topic_status'])
				{
					$data = array_merge($this->manager->clean_event($event), [
						'action'	=> $event['data']['topic_status'] ? 'unlock' : 'lock',
						'data'		=> [$event['data'] + ['topic_poster' => (int) $row['topic_poster']]],
					]);

					$this->manager->trigger('lock', $row['topic_poster'], $data, $event['data']['forum_id']);
				}
			}

			if ($event['post_data']['orig_topic_type'] != $event['post_data']['topic_type'])
			{
				$data = array_merge($this->manager->clean_event($event), [
					'type_from'	=> $event['post_data']['orig_topic_type'],
					'type_to'	=> $event['post_data']['topic_type'],
				]);

				$this->manager->trigger('topic_type', $event['post_data']['topic_poster'], $data, $event['data']['forum_id']);
			}
		}
	}

	/**
	 * Trigger Advanced Points System action: “lock”!
	 *
	 * @event  core.mcp_lock_unlock_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function lock(\phpbb\event\data $event)
	{
		$s_user = in_array($event['action'], ['lock', 'unlock']) ? 'topic_poster' : 'poster_id';

		$forum_ids = $this->manager->get_identifiers($event['data'], 'forum_id');
		$user_ids = $this->manager->get_identifiers($event['data'] , $s_user);

		$this->manager->trigger('lock', $user_ids, $event, $forum_ids);
	}

	/**
	 * Trigger Advanced Points System action: “move”!
	 *
	 * @event  core.move_posts_before
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function move_posts(\phpbb\event\data $event)
	{
		// Check for chain triggering events
		if (!$this->config['aps_chain_merge_move'] && $this->request->variable('action', '', true) === 'merge_topics')
		{
			return;
		}

		if (!function_exists('phpbb_get_topic_data'))
		{
			/** @noinspection PhpIncludeInspection */
			include $this->root_path . 'includes/functions_mcp.' . $this->php_ext;
		}

		$posts = phpbb_get_post_data($event['post_ids']);

		$forum_ids = $this->manager->get_identifiers($posts, 'forum_id');
		$user_ids = $this->manager->get_identifiers($posts, 'poster_id');

		$data = array_merge($this->manager->clean_event($event), [
			'action'	=> 'post',
			'posts'		=> $posts,
		]);

		$this->manager->trigger('move', $user_ids, $data, $forum_ids);
	}

	/**
	 * Trigger Advanced Points System action: “move”!
	 *
	 * @event  core.move_topics_before_query
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function move_topics(\phpbb\event\data $event)
	{
		if (!function_exists('phpbb_get_topic_data'))
		{
			/** @noinspection PhpIncludeInspection */
			include $this->root_path . 'includes/functions_mcp.' . $this->php_ext;
		}

		$topics = phpbb_get_topic_data($event['topic_ids']);

		$forum_ids = $this->manager->get_identifiers($topics, 'forum_id');
		$user_ids = $this->manager->get_identifiers($topics, 'topic_poster');

		$data = array_merge($this->manager->clean_event($event), [
			'action'	=> 'topic',
			'topics'	=> $topics,
		]);

		$this->manager->trigger('move', $user_ids, $data, $forum_ids);
	}

	/**
	 * Trigger Advanced Points System action: “queue”!
	 *
	 * @event  core.approve_posts_after
	 * @event  core.approve_topics_after
	 * @event  core.disapprove_posts_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function queue(\phpbb\event\data $event)
	{
		$data = array_merge($this->manager->clean_event($event), [
			'mode'	=> isset($event['action']) ? $event['action'] : 'disapprove',
		]);

		$posts = isset($event['post_info']) ? $event['post_info'] : $event['topic_info'];

		$forum_ids = $this->manager->get_identifiers($posts, 'forum_id');
		$user_ids = $this->manager->get_identifiers($posts, 'poster_id');

		$this->manager->trigger('queue', $user_ids, $data, $forum_ids);
	}

	/**
	 * Trigger Advanced Points System action: “merge”!
	 *
	 * @event  core.mcp_forum_merge_topics_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function merge(\phpbb\event\data $event)
	{
		$user_ids = $this->manager->get_identifiers($event['all_topic_data'], 'topic_poster');

		$this->manager->trigger('merge', $user_ids, $event, $event['all_topic_data'][$event['to_topic_id']]['forum_id']);
	}

	/**
	 * Trigger Advanced Points System action: “register”!
	 *
	 * @event  core.ucp_register_register_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function register(\phpbb\event\data $event)
	{
		$this->manager->trigger('register', $event['user_id'], $event);
	}

	/**
	 * Trigger Advanced Points System action: “warn”!
	 *
	 * @event  core.mcp_warn_post_after
	 * @event  core.mcp_warn_user_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function warn(\phpbb\event\data $event)
	{
		$this->manager->trigger('warn', $event['user_row']['user_id'], $event, 0);
	}

	/**
	 * Trigger Advanced Points System action: “pm”!
	 *
	 * @event  core.submit_pm_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @since  1.0.0
	 * @return void
	 * @access public
	 */
	public function pm(\phpbb\event\data $event)
	{
		// Check for chain triggering events
		if (!$this->config['aps_chain_warn_pm'] && $this->request->variable('mode', '', true) === 'warn_user')
		{
			return;
		}

		$this->manager->trigger('pm', [], $event, 0);
	}
}
