<?php
/**
 *
 * phpBB Studio - Advanced Points System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\aps\controller;

use phpbbstudio\aps\event\check;

/**
 * phpBB Studio - Advanced Points System MCP controller.
 */
class mcp_controller
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbbstudio\aps\points\distributor */
	protected $distributor;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbbstudio\aps\core\log */
	protected $log;

	/** @var \phpbb\notification\manager */
	protected $notification;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbbstudio\aps\points\reasoner */
	protected $reasoner;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\aps\points\valuator */
	protected $valuator;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP file extension */
	protected $php_ext;

	/** @var string Points name */
	protected $name;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth			Authentication object
	 * @param  \phpbb\config\config					$config			Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db				Database object
	 * @param  \phpbb\event\dispatcher				$dispatcher		Event dispatcher
	 * @param  \phpbbstudio\aps\points\distributor	$distributor	APS Distributor object
	 * @param  \phpbbstudio\aps\core\functions		$functions		APS Core functions
	 * @param  \phpbb\group\helper					$group_helper	Group helper object
	 * @param  \phpbb\language\language				$language		Language object
	 * @param  \phpbbstudio\aps\core\log			$log			APS Log object
	 * @param  \phpbb\notification\manager			$notification	Notification manager object
	 * @param  \phpbb\pagination					$pagination		Pagination object
	 * @param  \phpbbstudio\aps\points\reasoner		$reasoner		APS Reasoner object
	 * @param  \phpbb\request\request				$request		Request object
	 * @param  \phpbb\template\template				$template		Template object
	 * @param  \phpbb\user							$user			User object
	 * @param  \phpbbstudio\aps\points\valuator		$valuator		APS Valuator object
	 * @param  string								$root_path		phpBB root path
	 * @param  string								$php_ext		PHP file extension
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbbstudio\aps\points\distributor $distributor,
		\phpbbstudio\aps\core\functions $functions,
		\phpbb\group\helper $group_helper,
		\phpbb\language\language $language,
		\phpbbstudio\aps\core\log $log,
		\phpbb\notification\manager $notification,
		\phpbb\pagination $pagination,
		\phpbbstudio\aps\points\reasoner $reasoner,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbbstudio\aps\points\valuator $valuator,
		$root_path,
		$php_ext
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->distributor	= $distributor;
		$this->functions	= $functions;
		$this->group_helper	= $group_helper;
		$this->language		= $language;
		$this->log			= $log;
		$this->notification	= $notification;
		$this->pagination	= $pagination;
		$this->reasoner		= $reasoner;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->valuator		= $valuator;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;

		$this->name			= $functions->get_name();
	}

	/**
	 * Handle MCP front page.
	 *
	 * @return void
	 * @access public
	 */
	public function front()
	{
		if ($this->auth->acl_get('u_aps_view_logs'))
		{
			$this->log->load_lang();

			// Latest 5 logs
			$logs = $this->log->get(false, 5);
			foreach ($logs as $row)
			{
				$this->template->assign_block_vars('logs', array_change_key_case($row, CASE_UPPER));
			}

			// Latest 5 adjustments
			$moderated = $this->log->get(false, 5, 0, '', 0, 0, 0, 0, 0, 'l.log_time DESC', 'APS_POINTS_USER_ADJUSTED');
			foreach ($moderated as $row)
			{
				$this->template->assign_block_vars('moderated', array_change_key_case($row, CASE_UPPER));
			}
		}

		// Top 5 users
		$sql = 'SELECT user_id, username, user_colour, user_points
				FROM ' . $this->functions->table('users') . '
				WHERE user_type <> ' . USER_IGNORE . '
				ORDER BY user_points DESC, username_clean ASC';
		$result = $this->db->sql_query_limit($sql, 5);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('aps_users_top', [
				'NAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'POINTS'	=> $row['user_points'],
			]);
		}
		$this->db->sql_freeresult($result);

		// Bottom 5 user
		$sql = 'SELECT user_id, username, user_colour, user_points
				FROM ' . $this->functions->table('users') . '
				WHERE user_type <> ' . USER_IGNORE . '
				ORDER BY user_points ASC, username_clean DESC';
		$result = $this->db->sql_query_limit($sql, 5);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('aps_users_bottom', [
				'NAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'POINTS'	=> $row['user_points'],
			]);
		}
		$this->db->sql_freeresult($result);

		/**
		 * Event to assign additional variables for the APS MCP front page.
		 *
		 * @event phpbbstudio.aps.mcp_front
		 * @since 1.0.0
		 */
		$this->dispatcher->dispatch('phpbbstudio.aps.mcp_front');

		$this->template->assign_vars([
			'S_APS_LOGS'	=> $this->auth->acl_get('u_aps_view_logs'),
		]);
	}

	/**
	 * Handle MCP logs.
	 *
	 * @return void
	 * @access public
	 */
	public function logs()
	{
		$this->log->load_lang();

		// Set up general vars
		$start		= $this->request->variable('start', 0);
		$forum_id	= $this->request->variable('f', '');
		$topic_id	= $this->request->variable('t', 0);
		$post_id	= $this->request->variable('p', 0);
		$user_id	= $this->request->variable('u', 0);
		$reportee_id = $this->request->variable('r', 0);

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 't');
		$sort_dir	= $this->request->variable('sd', 'd');

		// Keywords
		$keywords = $this->request->variable('keywords', '', true);
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

		$name = $this->functions->get_name();
		$limit = $this->config['aps_actions_per_page'];

		// Sorting
		$limit_days = [
			0 => $this->language->lang('ALL_ENTRIES'),
			1 => $this->language->lang('1_DAY'),
			7 => $this->language->lang('7_DAYS'),
			14 => $this->language->lang('2_WEEKS'),
			30 => $this->language->lang('1_MONTH'),
			90 => $this->language->lang('3_MONTHS'),
			180 => $this->language->lang('6_MONTHS'),
			365 => $this->language->lang('1_YEAR'),
		];
		$sort_by_text = [
			'a'  => $this->language->lang('SORT_ACTION'),
			'ps' => $name,
			'pn' => $this->language->lang('APS_POINTS_NEW', $name),
			'po' => $this->language->lang('APS_POINTS_OLD', $name),
			'uu' => $this->language->lang('SORT_USERNAME'),
			'ru' => $this->language->lang('FROM'),
			't'  => $this->language->lang('SORT_DATE'),
		];
		$sort_by_sql = [
			'a'  => 'l.log_action',
			'ps' => 'l.points_sum',
			'pn' => 'l.points_new',
			'po' => 'l.points_old',
			'uu' => 'u.username',
			'ru' => 'r.username',
			't'  => 'l.log_time',
		];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_time = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		$rowset = $this->log->get(true, $limit, $start, $forum_id, $topic_id, $post_id, $user_id, $reportee_id, $sql_time, $sql_sort, $keywords);
		$start = $this->log->get_valid_offset();
		$total = $this->log->get_log_count();

		$base_url = $this->u_action . "&amp;$u_sort_param$keywords_param";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $limit, $start);

		foreach ($rowset as $row)
		{
			$this->template->assign_block_vars('logs', array_change_key_case($row, CASE_UPPER));
		}

		$this->template->assign_vars([
			'U_ACTION'		=> $this->u_action . "&amp;$u_sort_param$keywords_param&amp;start=$start",

			'S_LIMIT_DAYS'	=> $s_limit_days,
			'S_SORT_KEY'	=> $s_sort_key,
			'S_SORT_DIR'	=> $s_sort_dir,
			'S_KEYWORDS'	=> $keywords,
		]);
	}

	/**
	 * Handle MCP user adjustment.
	 *
	 * @return void
	 * @access public
	 */
	public function change()
	{
		$this->log->load_lang();

		$group_id = $this->request->variable('g', 0);
		$user_id = $this->request->variable('u', 0);

		if (empty($group_id) && empty($user_id))
		{
			$this->find_user();

			return;
		}

		$this->language->add_lang('acp/common');

		$action = $this->request->variable('action', '');

		switch ($action)
		{
			case 'add':
			case 'sub':
			case 'set':
				if (!$this->auth->acl_get('m_aps_adjust_custom'))
				{
					trigger_error($this->language->lang('NOT_AUTHORISED'), E_USER_WARNING);
				}
			break;

			case '':
				continue;
			break;

			default:
				if (!$this->auth->acl_get('m_aps_adjust_reason'))
				{
					trigger_error($this->language->lang('NOT_AUTHORISED'), E_USER_WARNING);
				}
			break;
		}

		if (!empty($user_id))
		{
			$sql = 'SELECT user_id, username, user_colour
				FROM ' . $this->functions->table('users') . '
				WHERE user_type <> ' . USER_IGNORE . '
					AND user_id = ' . (int) $user_id;
			$result = $this->db->sql_query_limit($sql, 1);
			$user = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($user === false)
			{
				trigger_error($this->language->lang('NO_USER') . $this->back_link($this->u_action), E_USER_WARNING);
			}

			$user_ids = [$user_id];

			$u_action = '&u=' . (int) $user_id;
		}
		else
		{
			$sql = 'SELECT user_id
				FROM ' . $this->functions->table('user_group') . '
				WHERE group_id = ' . (int) $group_id;
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			if (empty($rowset))
			{
				$this->language->add_lang('acp/groups');

				trigger_error($this->language->lang('GROUPS_NO_MEMBERS') . $this->back_link($this->u_action), E_USER_WARNING);
			}

			$user_ids = array_column($rowset, 'user_id');

			$u_action = '&g=' . (int) $group_id;
		}


		// Actions
		$reasons = $this->reasoner->rowset();
		$actions = [
			'add'	=> $this->language->lang('ADD'),
			'sub'	=> $this->language->lang('REMOVE'),
			'set'	=> $this->language->lang('CHANGE'),
		];

		$users = $this->valuator->users($user_ids);

		$submit = $this->request->is_set_post('submit');
		$points = $this->request->variable('points', 0.00);
		$reason = $this->request->variable('reason', '', true);

		if ($submit)
		{
			if ($action === '')
			{
				trigger_error($this->language->lang('NO_ACTION'), E_USER_WARNING);
			}

			if (confirm_box(true))
			{
				foreach ($users as $uid => $user_points)
				{
					switch ($action)
					{
						case 'add':
							$sum_points = $points;
						break;
						case 'sub':
							$sum_points = $this->functions->equate_points(0, $points, '-');
						break;
						case 'set':
							$sum_points = $this->functions->equate_points($points, $user_points, '-');
						break;

						default:
							if (empty($reasons[$action]))
							{
								trigger_error($this->language->lang('NO_ACTION') . $this->back_link($this->u_action), E_USER_WARNING);
							}

							$sum_points = $reasons[$action]['reason_points'];
							$reason = $reasons[$action]['reason_title'] . '<br />' . $reasons[$action]['reason_desc'];
						break;
					}
					
					$log_entry = [];

					$log_entry[] = [
						'action'		=> 'APS_POINTS_USER_ADJUSTED',
						'actions'		=> !empty($reason) ? [$reason => $sum_points] : ['APS_POINTS_USER_ADJUSTED' => $sum_points],
						'user_id'		=> (int) $uid,
						'reportee_id'	=> (int) $this->user->data['user_id'],
						'reportee_ip'	=> (string) $this->user->ip,
						'points_old'	=> $user_points,
						'points_sum'	=> $sum_points,
					];

					$this->distributor->distribute($uid, $sum_points, $log_entry, $user_points);
				}

				$this->config->increment('aps_notification_id', 1);

				$this->notification->add_notifications('phpbbstudio.aps.notification.type.adjust', [
					'name'				=> $this->functions->get_name(),
					'reason'			=> $reason,
					'user_ids'			=> array_keys($users),
					'moderator'			=> get_username_string('no_profile', $this->user->data['user_id'], $this->user->data['username'], $this->user->data['user_colour']),
					'moderator_id'		=> (int) $this->user->data['user_id'],
					'notification_id'	=> (int) $this->config['aps_notification_id'],
				]);

				trigger_error($this->language->lang('MCP_APS_POINTS_USER_CHANGE_SUCCESS', $this->name) . $this->back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $this->language->lang('MCP_APS_POINTS_USER_CHANGE', $this->name), build_hidden_fields([
					'submit'	=> $submit,
					'action'	=> $action,
					'points'	=> $points,
					'reason'	=> $reason,
				]));

				redirect($this->u_action);
			}
		}

		if (!empty($user_id) && $this->auth->acl_get('u_aps_view_logs'))
		{
			$logs = $this->log->get(false, 5, 0, '', 0, 0, (int) $user_id);

			foreach ($logs as $row)
			{
				$this->template->assign_block_vars('logs', array_change_key_case($row, CASE_UPPER));
			}

			$this->template->assign_var('S_APS_LOGS', true);
		}

		if (!empty($group_id))
		{
			$sql = 'SELECT group_id, group_name, group_colour
					FROM ' . $this->functions->table('groups') . '
					WHERE group_id = ' . (int) $group_id;
			$result = $this->db->sql_query_limit($sql, 1);
			$group = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$group_name = $this->group_helper->get_name_string('full', $group['group_id'], $group['group_name'], $group['group_colour']);
		}

		$this->template->assign_vars([
			'APS_ACTIONS'	=> $actions,
			'APS_REASONS'	=> $reasons,
			'APS_POINTS'	=> $user_id ? $this->functions->display_points($users[$user_id]) : '',
			'APS_USERNAME'	=> !empty($user) ? get_username_string('full', $user['user_id'], $user['username'], $user['user_colour']) : '',
			'APS_GROUP'		=> !empty($group_name) ? $group_name : '',

			'S_APS_CUSTOM'	=> $this->auth->acl_get('m_aps_adjust_custom'),
			'S_APS_REASON'	=> $this->auth->acl_get('m_aps_adjust_reason'),
			'S_APS_POINTS'	=> true,

			'U_APS_ACTION'	=> $this->u_action . $u_action,
		]);
	}

	/**
	 * Find a user for the MCP adjustment page.
	 *
	 * @return void
	 * @access protected
	 */
	protected function find_user()
	{
		$this->language->add_lang('acp/groups');

		$form_name = 'mcp_aps_change';
		add_form_key($form_name);

		$submit_group = $this->request->is_set_post('submit_group');
		$submit_user = $this->request->is_set_post('submit_user');
		$submit = $submit_group || $submit_user;

		$group_id = $this->request->variable('group_id', 0);

		if ($submit && !check_form_key($form_name))
		{
			$error = 'FORM_INVALID';
		}
		else if ($submit)
		{
			if ($submit_group)
			{
				redirect($this->u_action . '&g=' . (int) $group_id);
			}

			if ($submit_user)
			{
				if (!function_exists('user_get_id_name'))
				{
					/** @noinspection PhpIncludeInspection */
					include $this->root_path . 'includes/functions_user.' . $this->php_ext;
				}

				$username[] = $this->request->variable('username', '', true);

				$error = user_get_id_name($user_ids, $username);

				if (empty($error))
				{
					$user_id = $user_ids[0];

					redirect($this->u_action . '&u=' . (int) $user_id);
				}
			}
		}

		if (!function_exists('group_select_options'))
		{
			/** @noinspection PhpIncludeInspection */
			include $this->root_path . 'includes/functions_admin.' . $this->php_ext;
		}

		$this->template->assign_vars([
			'S_ERROR'		=> !empty($error),
			'ERROR_MSG'		=> !empty($error) ? $this->language->lang($error) : '',

			'APS_USERNAME'	=> !empty($username[0]) ? $username[0] : '',
			'APS_GROUPS'	=> group_select_options($group_id),

			'S_APS_SEARCH'	=> true,

			'U_APS_ACTION'	=> $this->u_action,
			'U_APS_SEARCH'	=> append_sid("{$this->root_path}memberlist.{$this->php_ext}", 'mode=searchuser&amp;form=mcp_aps_change&amp;field=username'),
		]);
	}

	/**
	 * Generate a back link for this MCP controller.
	 *
	 * @param  string	$action		The action to return to
	 * @return string				A HTML formatted URL to the action
	 * @access protected
	 */
	protected function back_link($action)
	{
		return '<br /><br /><a href="' . $action . '">&laquo; ' . $this->language->lang('BACK_TO_PREV') . '</a>';
	}

	/**
	 * Set custom form action.
	 *
	 * @param  string			$u_action	Custom form action
	 * @return mcp_controller	$this		This controller for chaining calls
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;

		return $this;
	}
}
