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
 * phpBB Studio - Advanced Points System block functions.
 */
class blocks
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbbstudio\aps\core\dbal */
	protected $dbal;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbbstudio\aps\core\log */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP File extension */
	protected $php_ext;

	/** @var string APS Logs table */
	protected $table;

	/** @var string Localised points name */
	protected $name;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth			Authentication object
	 * @param  \phpbb\config\config					$config			Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db				Database object
	 * @param  \phpbbstudio\aps\core\dbal			$dbal			APS DBAL functions
	 * @param  \phpbbstudio\aps\core\functions		$functions		APS Core functions
	 * @param  \phpbb\group\helper					$group_helper	Group helper object
	 * @param  \phpbb\controller\helper				$helper			Controller helper object
	 * @param  \phpbb\language\language				$lang			Language object
	 * @param  \phpbbstudio\aps\core\log			$log			APS Log object
	 * @param  \phpbb\pagination					$pagination		Pagination object
	 * @param  \phpbb\request\request				$request		Request object
	 * @param  \phpbb\template\template				$template		Template object
	 * @param  \phpbb\user							$user			User object
	 * @param  string								$root_path		phpBB root path
	 * @param  string								$php_ext		PHP File extension
	 * @param  string								$table			APS Logs table
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		dbal $dbal,
		functions $functions,
		\phpbb\group\helper $group_helper,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$table
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->dbal			= $dbal;
		$this->functions	= $functions;
		$this->group_helper	= $group_helper;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->pagination	= $pagination;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->table		= $table;

		$this->name			= $functions->get_name();

		$log->load_lang();
	}

	/**
	 * Display the "Top users" and "Find a Member" blocks.
	 *
	 * @param  string	$block_id		The block identifier
	 * @return void
	 * @access public
	 */
	public function user_top_search($block_id)
	{
		$submit = $this->request->is_set_post('submit');
		$action = $this->request->variable('action', '', true);

		$count = $this->request->variable('aps_user_top_count', (int) $this->config['aps_display_top_count']);
		$top_username = '';

		$sql = 'SELECT user_id, username, username_clean, user_colour, user_points,
						user_avatar, user_avatar_type, user_avatar_width, user_avatar_height
				FROM ' . $this->functions->table('users') . '
				WHERE user_type <> ' . USER_IGNORE . '
				ORDER BY user_points DESC, username_clean ASC';
		$result = $this->db->sql_query_limit($sql, $count);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$top_username = empty($top_username) ? $row['username_clean'] : $top_username;

			$this->template->assign_block_vars('top_users', [
				'NAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'AVATAR'	=> phpbb_get_user_avatar($row),
				'POINTS'	=> $row['user_points'],
				'U_ADJUST'	=> append_sid("{$this->root_path}mcp.{$this->php_ext}", 'i=-phpbbstudio-aps-mcp-main_module&amp;mode=change&amp;u=' . (int) $row['user_id'], true, $this->user->session_id)
			]);
		}
		$this->db->sql_freeresult($result);

		// Set up the default user to display, either this user or the top user if this user is a guest
		$default = $this->user->data['user_id'] != ANONYMOUS ? $this->user->data['username'] : $top_username;
		$username = $this->request->variable('aps_user_search', $default, true);

		// Find the user for the provided username
		$user = $this->find_user($username);

		// If a user was found
		if ($user !== false)
		{
			// Count the amount of users with more points than this user.
			$sql = 'SELECT COUNT(user_points) as rank
					FROM ' . $this->functions->table('users') . '
					WHERE user_points > ' . $user['user_points'];
			$result = $this->db->sql_query_limit($sql, 1);
			$user_rank = (int) $this->db->sql_fetchfield('rank');
			$this->db->sql_freeresult($result);

			// Increment by one, as the rank is the amount of users above this user
			$user_rank++;
		}

		// Output the template variables for display
		$this->template->assign_vars([
			// Set up a default no avatar
			'APS_NO_AVATAR'		=> $this->functions->get_no_avatar(),

			// The searched user data
			'APS_SEARCH_USERNAME'		=> $username,
			'APS_SEARCH_USER_AVATAR'	=> !empty($user) ? phpbb_get_user_avatar($user) : '',
			'APS_SEARCH_USER_FULL'		=> !empty($user) ? get_username_string('full', $user['user_id'], $user['username'], $user['user_colour']) : $this->lang->lang('NO_USER'),
			'APS_SEARCH_USER_POINTS'	=> !empty($user) ? $user['user_points'] : 0.00,
			'APS_SEARCH_USER_RANK'		=> !empty($user_rank) ? $user_rank : $this->lang->lang('NA'),
			'U_APS_SEARCH_USER_ADJUST'	=> !empty($user) ? append_sid("{$this->root_path}mcp.{$this->php_ext}", 'i=-phpbbstudio-aps-mcp-main_module&amp;mode=change&amp;u=' . (int) $user['user_id'], true, $this->user->session_id) : '',

			// Amount of top users to display
			'APS_TOP_USERS_COUNT'	=> $count,

			// APS Moderator
			'S_APS_USER_ADJUST'		=> $this->auth->acl_get('m_aps_adjust_custom') || $this->auth->acl_get('m_aps_'),

			// Block actions
			'U_APS_ACTION_SEARCH'	=> $this->helper->route('phpbbstudio_aps_display', ['page' => 'overview', 'action' => 'search']),
			'U_APS_ACTION_TOP'		=> $this->helper->route('phpbbstudio_aps_display', ['page' => 'overview', 'action' => 'top']),
		]);

		// Handle any AJAX actions regarding these blocks
		if ($submit && $this->request->is_ajax() && in_array($action, ['search', 'top']))
		{
			$this->template->set_filenames(['aps_body' => '@phpbbstudio_aps/blocks/base.html']);
			$this->template->assign_vars([
				'block'	=> [
					'ID'			=> $block_id,
					'TITLE'			=> $action === 'top' ? $this->lang->lang('APS_TOP_USERS') : $this->lang->lang('FIND_USERNAME'),
					'TEMPLATE'		=> '@phpbbstudio_aps/blocks/points_' . $action . '.html',
				],
			]);

			$json_response = new \phpbb\json_response;
			$json_response->send([
				'body'	=> $this->template->assign_display('aps_body'),
			]);
		}
	}

	/**
	 * Display the "Random member" block.
	 *
	 * @return void
	 * @access public
	 */
	public function user_random()
	{
		$sql = 'SELECT user_id, username, user_colour, user_points,
						user_avatar, user_avatar_type, user_avatar_width, user_avatar_height
				FROM ' . $this->functions->table('users') . '
				WHERE user_type <> ' . USER_IGNORE . '
				AND user_type <> ' . USER_INACTIVE . '
				ORDER BY ' . $this->dbal->random();

		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'APS_RANDOM_NO_AVATAR'		=> $this->functions->get_no_avatar(),

			'APS_RANDOM_USER_AVATAR'	=> phpbb_get_user_avatar($row),
			'APS_RANDOM_USER_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			'APS_RANDOM_USER_POINTS'	=> $row['user_points'],
		]);
	}

	/**
	 * Display the "Points actions" block.
	 *
	 * @param  int		$pagination		The pagination's page number
	 * @param  string	$block_id		The block identifier
	 * @return void
	 * @access public
	 */
	public function display_actions($pagination, $block_id)
	{
		$params = ['page' => 'actions'];
		$limit = $this->config['aps_actions_per_page'];

		// Set up general vars
		$s_reportee = $this->auth->acl_get('u_aps_view_mod');
		$s_username = $this->auth->acl_get('u_aps_view_logs_other');

		$forum_id	= $this->request->variable('f', '');
		$topic_title = $this->request->variable('t', '', true);
		$username	= $this->request->variable('u', '', true);
		$reportee	= $this->request->variable('r', '', true);

		$username	= $s_username ? $username : '';
		$reportee	= $s_reportee ? $reportee : '';

		$topic_ids	= $this->find_topic($topic_title);
		$user_id	= $this->find_user($username, false);
		$reportee_id = $this->find_user($reportee, false);

		$post_id	= 0;
		$user_id	= $s_username ? $user_id : (int) $this->user->data['user_id'];

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 't');
		$sort_dir	= $this->request->variable('sd', 'd');

		// Keywords
		$keywords = $this->request->variable('keywords', '', true);
		if (!empty($keywords))
		{
			$params['keywords'] = urlencode(htmlspecialchars_decode($keywords));
		}

		// Calculate the start (SQL offset) from the page number
		$start = ($pagination - 1) * $limit;

		// Sorting
		$limit_days = [
			0 => $this->lang->lang('APS_POINTS_ACTIONS_ALL', $this->name),
			1 => $this->lang->lang('1_DAY'),
			7 => $this->lang->lang('7_DAYS'),
			14 => $this->lang->lang('2_WEEKS'),
			30 => $this->lang->lang('1_MONTH'),
			90 => $this->lang->lang('3_MONTHS'),
			180 => $this->lang->lang('6_MONTHS'),
			365 => $this->lang->lang('1_YEAR'),
		];
		$sort_by_text = [
			'a'  => $this->lang->lang('APS_POINTS_ACTION', $this->name),
			'ps' => $this->name,
			'pn' => $this->lang->lang('APS_POINTS_NEW', $this->name),
			'po' => $this->lang->lang('APS_POINTS_OLD', $this->name),
			'uu' => $this->lang->lang('SORT_USERNAME'),
			'ru' => ucfirst($this->lang->lang('FROM')),
			't'  => $this->lang->lang('APS_POINTS_ACTION_TIME', $this->name),
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

		if (!empty($u_sort_param))
		{
			$sort_params = explode('&amp;', $u_sort_param);

			foreach ($sort_params as $param)
			{
				list($key, $value) = explode('=', $param);

				$params[$key] = $value;
			}
		}

		// Define where and sort sql for use in displaying logs
		$sql_time = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		$rowset = $this->log->get(true, $limit, $start, $forum_id, $topic_ids, $post_id, $user_id, $reportee_id, $sql_time, $sql_sort, $keywords);
		$start = $this->log->get_valid_offset();
		$total = $this->log->get_log_count();

		$user_ids = [];

		foreach ($rowset as $row)
		{
			$user_ids[] = $row['user_id'];
			$this->template->assign_block_vars('aps_actions', array_merge(array_change_key_case($row, CASE_UPPER), [
				'S_AUTH_BUILD'			=> (bool) $this->auth->acl_get('u_aps_view_build'),
				'S_AUTH_BUILD_OTHER'	=> (bool) ($this->auth->acl_get('u_aps_view_build_other') || ((int) $this->user->data['user_id'] === $row['user_id'])),
				'S_AUTH_MOD'			=> (bool) $this->auth->acl_get('u_aps_view_mod'),
				'S_MOD'					=> (bool) strpos($row['action'],'_USER_') !== false,
			]));
		}

		$avatars = $this->functions->get_avatars($user_ids);

		if (!function_exists('make_forum_select'))
		{
			/** @noinspection PhpIncludeInspection */
			include $this->root_path . 'includes/functions_admin.' . $this->php_ext;
		}

		/**
		 * phpBB's DocBlock expects a string but allows arrays aswell..
		 * @noinspection PhpParamsInspection
		 */
		$this->pagination->generate_template_pagination(
			[
				'routes' => [
					'phpbbstudio_aps_display',
					'phpbbstudio_aps_display_pagination',
				],
				'params' => $params,
			], 'pagination', 'pagination', $total, $limit, $start);

		$this->template->assign_vars([
			'PAGE_NUMBER'	=> $this->pagination->on_page($total, $limit, $start),
			'TOTAL_LOGS'	=> $this->lang->lang('APS_POINTS_ACTIONS_TOTAL', $this->name, $total),

			'APS_ACTIONS_AVATARS'	=> $avatars,
			'APS_ACTIONS_NO_AVATAR'	=> $this->functions->get_no_avatar(),

			'S_AUTH_FROM'			=> $s_reportee,
			'S_AUTH_USER'			=> $s_username,

			'S_SEARCH_TOPIC'		=> $topic_title,
			'S_SEARCH_FROM'			=> $reportee,
			'S_SEARCH_USER'			=> $username,
			'S_SELECT_FORUM'		=> make_forum_select((int) $forum_id),

			'S_SELECT_SORT_DAYS'	=> $s_limit_days,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_KEYWORDS'			=> $keywords,

			'U_APS_ACTION_LOGS'		=> $this->helper->route('phpbbstudio_aps_display', ['page' => 'actions', 'action' => 'search']),
		]);

		$submit = $this->request->is_set_post('submit');
		$action = $this->request->variable('action', '', true);

		// Handle any AJAX action regarding this block
		if ($submit && $this->request->is_ajax() && $action === 'search')
		{
			$this->template->set_filenames(['aps_body' => '@phpbbstudio_aps/blocks/base.html']);
			$this->template->assign_vars([
				'block'	=> [
					'ID'			=> $block_id,
					'TITLE'			=> $this->lang->lang('APS_POINTS_ACTIONS', $this->name),
					'TEMPLATE'		=> '@phpbbstudio_aps/blocks/points_actions.html',
				],
			]);

			$json_response = new \phpbb\json_response;
			$json_response->send([
				'body'	=> $this->template->assign_display('aps_body'),
			]);
		}
	}

	/**
	 * Display the "Recent adjustments" block.
	 *
	 * @return void
	 * @access public
	 */
	public function recent_adjustments()
	{
		$user_id = !$this->auth->acl_get('u_aps_view_logs_other') ? (int) $this->user->data['user_id'] : 0;

		$limit = (int) $this->config['aps_display_adjustments'];
		$rowset = $this->log->get(true, $limit, 0, 0, 0, 0, $user_id, 0, 0, 'l.log_time DESC', 'APS_POINTS_USER_ADJUSTED');

		$user_ids = [];

		foreach ($rowset as $row)
		{
			$user_ids[] = $row['user_id'];
			$this->template->assign_block_vars('aps_adjustments', array_merge(array_change_key_case($row, CASE_UPPER), [
				'S_AUTH_BUILD'			=> (bool) $this->auth->acl_get('u_aps_view_build'),
				'S_AUTH_BUILD_OTHER'	=> (bool) ($this->auth->acl_get('u_aps_view_build_other') || ((int) $this->user->data['user_id'] === $row['user_id'])),
				'S_AUTH_MOD'			=> (bool) $this->auth->acl_get('u_aps_view_mod'),
				'S_MOD'					=> (bool) strpos($row['action'],'_USER_') !== false,
			]));
		}

		$avatars = $this->functions->get_avatars($user_ids);

		$this->template->assign_vars([
			'APS_ADJUSTMENTS_AVATARS'	=> $avatars,
			'APS_ADJUSTMENTS_NO_AVATAR'	=> $this->functions->get_no_avatar(),
		]);
	}

	/**
	 * Display the "Points per forum" block.
	 *
	 * @return void
	 * @access public
	 */
	public function charts_forum()
	{
		$rowset = [];

		$sql = 'SELECT forum_id, SUM(points_sum) as points
				FROM ' . $this->table . '
				WHERE log_approved = 1
				GROUP BY forum_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row['points']))
			{
				continue;
			}

			$rowset[(int) $row['forum_id']]['POINTS'] = $row['points'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT forum_name, forum_id
				FROM ' . $this->functions->table('forums') . '
				WHERE ' . $this->db->sql_in_set('forum_id', array_keys($rowset), false, true);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[(int) $row['forum_id']]['NAME'] = utf8_decode_ncr($row['forum_name']);
		}
		$this->db->sql_freeresult($result);

		if (isset($rowset[0]))
		{
			$rowset[0]['NAME'] = $this->lang->lang('APS_POINTS_GLOBAL');
		}

		$this->template->assign_block_vars_array('aps_forums', $rowset);
	}

	/**
	 * Display the "Points per group" block.
	 *
	 * @return void
	 * @access public
	 */
	public function charts_group()
	{
		$rowset = [];

		$sql = 'SELECT u.group_id, SUM(p.points_sum) as points
				FROM ' . $this->table . ' p,
					' . $this->functions->table('users') . ' u
				WHERE u.user_id = p.user_id
					AND p.log_approved = 1
				GROUP BY u.group_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[(int) $row['group_id']] = $row['points'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT group_name, group_colour, group_id
				FROM ' . $this->functions->table('groups') . '
				WHERE group_name <> "BOTS"
					AND group_type <> ' . GROUP_HIDDEN . '
					AND ' . $this->db->sql_in_set('group_id', array_keys($rowset), false, true);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('aps_groups', [
				'COLOUR'	=> $row['group_colour'],
				'NAME'		=> $this->group_helper->get_name($row['group_name']),
				'POINTS'	=> $rowset[(int) $row['group_id']],
			]);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Display the "Points trade off" and "Points growth" blocks.
	 *
	 * @return void
	 * @access public
	 */
	public function charts_period()
	{
		$sql = 'SELECT ' . $this->dbal->unix_to_month('log_time') . ' as month,
					' . $this->dbal->unix_to_year('log_time') . ' as year, 
					SUM(' . $this->db->sql_case('points_sum < 0', 'points_sum', 0) . ') AS negative,
					SUM(' . $this->db->sql_case('points_sum > 0', 'points_sum', 0) . ') AS positive
				FROM ' . $this->table . '
				WHERE log_time > ' .  strtotime('-1 year') . '
				GROUP BY ' . $this->dbal->unix_to_month('log_time') . ',
					' . $this->dbal->unix_to_year('log_time') . '
				ORDER BY ' . $this->dbal->unix_to_year('log_time') . ' ASC,
					' . $this->dbal->unix_to_month('log_time') . ' ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$timestamp = $this->user->get_timestamp_from_format('m Y', $row['month'] . ' ' . $row['year']);
			$formatted = $this->user->format_date($timestamp, 'F Y');

			$this->template->assign_block_vars('aps_period', [
				'DATE'		=> $formatted,
				'NEGATIVE'	=> -$row['negative'], // Make it positive
				'POSITIVE'	=> $row['positive'],
				'TOTAL'		=> $this->functions->equate_points($row['positive'], $row['negative']),
			]);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Finds a user row for the provided username.
	 *
	 * @param  string	$username	The username
	 * @param  bool		$full		Whether we want just the identifier or everything
	 * @return mixed				If $full is true: a user row or false if no user was found
	 *                 				If $full is false: the user identifier
	 * @access protected
	 */
	protected function find_user($username, $full = true)
	{
		if (empty($username) && !$full)
		{
			return 0;
		}

		$select = !$full ? 'user_id' : 'user_id, username, username_clean, user_colour, user_points, user_avatar, user_avatar_type, user_avatar_width, user_avatar_height';

		$sql = 'SELECT ' . $select . '
				FROM ' . $this->functions->table('users') . '
				WHERE user_type <> ' . USER_IGNORE . '
					AND (username = "' . $this->db->sql_escape($username) . '"
						OR username_clean = "' . $this->db->sql_escape(utf8_clean_string($username)) . '"
					)';
		$result = $this->db->sql_query_limit($sql, 1);
		$user = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $full ? $user : (int) $user['user_id'];
	}

	/**
	 * Find a topic identifier for a provided topic title.
	 *
	 * @param  string	$title	The topic title
	 * @return array			The topic identifier or 0 if no topic was found or unauthorised
	 * @access protected
	 */
	protected function find_topic($title)
	{
		$topic_ids = [];

		$sql = 'SELECT forum_id, topic_id
				FROM ' . $this->functions->table('topics') . '
				WHERE topic_title = "' . $this->db->sql_escape($title) . '"';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($this->auth->acl_get('f_read', (int) $row['topic_id']))
			{
				$topic_ids[] = (int) $row['topic_id'];
			}
		}

		return $topic_ids;
	}
}
