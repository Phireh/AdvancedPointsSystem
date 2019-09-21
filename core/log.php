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
 * phpBB Studio - Advanced Points System log functions.
 */
class log
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbbstudio\aps\core\language */
	protected $lang_aps;

	/** @var \phpbb\user */
	protected $user;

	/** @var string APS Logs table */
	protected $table;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string PHP file extension */
	protected $php_ext;

	/** @var bool Whether called from the ACP or not */
	protected $is_in_admin;

	/** @var int Total log entries for a get query */
	protected $entries_count;

	/** @var int Last page offset for pagination */
	protected $last_page_offset;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth			Authentication object
	 * @param  \phpbb\config\config					$config			Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db				Database object
	 * @param  \phpbbstudio\aps\core\functions		$functions		APS Core functions
	 * @param  \phpbb\language\language				$lang			phpBB Language object
	 * @param  \phpbbstudio\aps\core\language		$lang_aps		APS Language object
	 * @param  \phpbb\user							$user			User object
	 * @param  string								$table			APS Logs table
	 * @param  string								$root_path		phpBB root path
	 * @param  string								$admin_path		phpBB relative admin path
	 * @param  string								$php_ext		PHP File extension
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		functions $functions,
		\phpbb\language\language $lang,
		language $lang_aps,
		\phpbb\user $user,
		$table,
		$root_path,
		$admin_path,
		$php_ext
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->functions	= $functions;
		$this->lang			= $lang;
		$this->lang_aps		= $lang_aps;
		$this->user			= $user;
		$this->table		= $table;

		$this->root_path	= $root_path;
		$this->admin_path	= $root_path . $admin_path;
		$this->php_ext		= $php_ext;

		$this->set_is_admin((defined('ADMIN_START') && ADMIN_START) || (defined('IN_ADMIN') && IN_ADMIN));
	}

	/**
	 * Set is_in_admin in order to return administrative user profile links in get().
	 *
	 * @param  bool		$is_in_admin		Called from within the acp?
	 * @return void
	 * @access public
	 */
	public function set_is_admin($is_in_admin)
	{
		$this->is_in_admin = (bool) $is_in_admin;
	}

	/**
	 * Returns the is_in_admin option.
	 *
	 * @return bool							Called from within the acp?
	 * @access public
	 */
	public function get_is_admin()
	{
		return $this->is_in_admin;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_log_count()
	{
		return ($this->entries_count) ? $this->entries_count : 0;
	}
	/**
	 * {@inheritDoc}
	 */
	public function get_valid_offset()
	{
		return ($this->last_page_offset) ? $this->last_page_offset : 0;
	}

	/**
	 * Loads the language files used by the Advanced Points System.
	 *
	 * @return void
	 * @access public
	 */
	public function load_lang()
	{
		$this->lang_aps->load();
	}

	/**
	 * Log a points action.
	 *
	 * @param  array	$data		The array to log
	 * @param  int		$time		The time to log
	 * @return bool|int				False on error, new log entry identifier otherwise
	 * @access public
	 */
	public function add($data, $time = 0)
	{
		// We need to have at least the log action, points gained/lost and either the old or new user points.
		if ($this->check_row($data))
		{
			return false;
		}

		$row = $this->prepare_row($data, $time);

		$sql = 'INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('UPDATE', $row);
		$this->db->sql_query($sql);

		return $this->db->sql_nextid();
	}

	/**
	 * Log multiple points actions at once.
	 *
	 * @param  array	$data		The arrays to log
	 * @param  int		$time		The time to log
	 * @return bool
	 * @access public
	 */
	public function add_multi($data, $time = 0)
	{
		$logs = [];

		foreach ($data as $row)
		{
			// We need to have at least the log action, points gained/lost and either the old or new user points.
			if ($this->check_row($row))
			{
				continue;
			}

			$logs[] = $this->prepare_row($row, $time);
		}

		$this->db->sql_multi_insert($this->table, $logs);

		return (bool) !empty($logs);
	}

	/**
	 * Check whether a log row has the minimal required information.
	 *
	 * @param  array	$row		The log row the check
	 * @return bool					Whether this log row is eligible or not
	 * @access public
	 */
	public function check_row($row)
	{
		return (bool) (empty($row['action']) || in_array($row['points_sum'], [0, 0.0, 0.00]) || (!isset($row['points_old']) && !isset($row['points_new'])));
	}

	/**
	 * Prepare a log row for inserting in the database table.
	 *
	 * @param  array	$row		The log row to prepare
	 * @param  int		$time		The time to log
	 * @return array				The prepared log row
	 * @access public
	 */
	public function prepare_row($row, $time)
	{
		return [
			'log_action'	=> $row['action'],
			'log_actions'	=> !empty($row['actions']) ? serialize($row['actions']) : '',
			'log_time'		=> $time ? $time : time(),
			'log_approved'	=> isset($row['approved']) ? (bool) $row['approved'] : true,
			'forum_id'		=> !empty($row['forum_id']) ? (int) $row['forum_id'] : 0,
			'topic_id'		=> !empty($row['topic_id']) ? (int) $row['topic_id'] : 0,
			'post_id'		=> !empty($row['post_id']) ? (int) $row['post_id'] : 0,
			'user_id'		=> !empty($row['user_id']) ? (int) $row['user_id'] : (int) $this->user->data['user_id'],
			'reportee_id'	=> !empty($row['reportee_id']) ? (int) $row['reportee_id'] : (int) $this->user->data['user_id'],
			'reportee_ip'	=> !empty($row['reportee_ip']) ? (string) $row['reportee_ip'] : (string) $this->user->ip,
			'points_old'	=> isset($row['points_old']) ? (double) $row['points_old'] : $this->functions->equate_points($row['points_new'], $row['points_sum'], '-'),
			'points_sum'	=> (double) $row['points_sum'],
			'points_new'	=> isset($row['points_new']) ? (double) $this->functions->boundaries($row['points_new']) : $this->functions->boundaries($this->functions->equate_points($row['points_old'], $row['points_sum'], '+')),
		];
	}

	/**
	 * Delete a points action from the logs depending on the conditions.
	 *
	 * @param  array	$conditions		The delete conditions
	 * @return void
	 * @access public
	 */
	public function delete($conditions)
	{
		// Need an "empty" sql where to begin with
		$sql_where = '';

		if (isset($conditions['keywords']))
		{
			$sql_where .= $this->generate_sql_keyword($conditions['keywords'], '');
			unset($conditions['keywords']);
		}

		foreach ($conditions as $field => $field_value)
		{
			$sql_where .= ' AND ';

			if (is_array($field_value) && count($field_value) == 2 && !is_array($field_value[1]))
			{
				$sql_where .= $field . ' ' . $field_value[0] . ' ' . $field_value[1];
			}
			else if (is_array($field_value) && isset($field_value['IN']) && is_array($field_value['IN']))
			{
				$sql_where .= $this->db->sql_in_set($field, $field_value['IN']);
			}
			else
			{
				$sql_where .= $field . ' = ' . $field_value;
			}
		}

		$sql = 'DELETE FROM ' . $this->table . ' WHERE log_id <> 0 ' . $sql_where;
		$this->db->sql_query($sql);
	}

	/**
	 * Gets the logged point values for a given user id and post ids combination.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  array	$post_ids		The post identifiers
	 * @param  bool		$approved		Whether the logged entries are set to approved or not
	 * @return array					The array of point values indexed per post identifier
	 * @access public
	 */
	public function get_values($user_id, $post_ids, $approved = true)
	{
		$points = [];

		$sql = 'SELECT points_sum, post_id
				FROM ' . $this->table . '
				WHERE user_id = ' . (int) $user_id . '
					AND log_approved = ' . (int) $approved . '
					AND ' . $this->db->sql_in_set('post_id', $post_ids, false, true);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$points[(int) $row['post_id']] = $row['points_sum'];
		}
		$this->db->sql_freeresult($result);

		return $points;
	}

	/**
	 * Sets logged entries to approved for a given user id and post ids combination.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  array	$post_ids		The post identifiers
	 * @return void
	 * @access public
	 */
	public function approve($user_id, $post_ids)
	{
		$sql = 'UPDATE ' . $this->table . '
				SET log_approved = 1
				WHERE log_approved = 0
					AND user_id = ' . (int) $user_id . '
					AND ' . $this->db->sql_in_set('post_id', $post_ids, false, true);
		$this->db->sql_query($sql);
	}

	/**
	 * Get logged point actions for a certain combination.
	 *
	 * @param  bool			$count			Whether we should count the total amount of logged entries for this combination.
	 * @param  int			$limit			The amount of rows to return
	 * @param  int			$offset			The amount of rows from the start to return
	 * @param  int|string	$forum_id		The forum identifier (set to '' as 0 is a valid choice)
	 * @param  int|array	$topic_id		The topic identifier
	 * @param  int			$post_id		The post identifier
	 * @param  int			$user_id		The user identifier
	 * @param  int			$reportee_id	The reportee identifier (from user)
	 * @param  int			$time			The logged time
	 * @param  string		$sort_by		The ORDER BY clause
	 * @param  string		$keywords		The keywords to search for
	 * @return array						The found logged point actions for this combination
	 * @access public
	 */
	public function get($count = true, $limit = 0, $offset = 0, $forum_id = '', $topic_id = 0, $post_id = 0, $user_id = 0, $reportee_id = 0, $time = 0, $sort_by = 'l.log_time DESC', $keywords = '')
	{
		$this->entries_count = 0;
		$this->last_page_offset = $offset;

		$limit = !empty($limit) ? $limit : $this->config['aps_actions_per_page'];

		$profile_url = ($this->get_is_admin() && $this->admin_path) ? append_sid("{$this->admin_path}index.{$this->php_ext}", 'i=users&amp;mode=overview') : append_sid("{$this->root_path}memberlist.{$this->php_ext}", 'mode=viewprofile');

		$sql_where = 'l.user_id = u.user_id';
		$sql_where .= $time ? ' AND l.log_time >= ' . (int) $time : '';
		$sql_where .= $forum_id !== '' ? ' AND l.forum_id = ' . (int) $forum_id : '';
		$sql_where .= $topic_id ? (is_array($topic_id) ? ' AND ' . $this->db->sql_in_set('l.topic_id', $topic_id) : ' AND l.topic_id = ' . (int) $topic_id) : '';
		$sql_where .= $post_id ? ' AND l.post_id = ' . (int) $post_id : '';
		$sql_where .= $user_id ? ' AND l.user_id = ' . (int) $user_id : '';
		$sql_where .= $reportee_id ? ' AND l.reportee_id = ' . (int) $reportee_id : '';
		$sql_where .= $this->get_is_admin() ? '' : ' AND l.log_approved = 1';

		$sql_keywords = '';
		if (!empty($keywords))
		{
			// Get the SQL condition for our keywords
			$sql_keywords = $this->generate_sql_keyword($keywords);
		}

		$sql_ary = [
			'SELECT'	=> 'l.*, 
							u.user_id, u.username, u.user_colour,
							r.user_id as reportee_id, r.username as reportee_name, r.user_colour as reportee_colour,
							f.forum_name, t.topic_title, p.post_subject',
			'FROM'		=> [
				$this->table => 'l',
				USERS_TABLE => 'u',
			],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [USERS_TABLE => 'r'],
					'ON'	=> 'l.reportee_id = r.user_id',
				],
				[
					'FROM'	=> [FORUMS_TABLE => 'f'],
					'ON'	=> 'l.forum_id = f.forum_id',
				],
				[
					'FROM'	=> [TOPICS_TABLE => 't'],
					'ON'	=> 'l.topic_id = t.topic_id',
				],
				[
					'FROM'	=> [POSTS_TABLE => 'p'],
					'ON'	=> 'l.post_id = p.post_id AND t.topic_first_post_id != p.post_id',
				],
			],
			'WHERE'		=> $sql_where . $sql_keywords,
			'ORDER_BY'	=> $sort_by,
		];

		// Provide moderator anonymity, exclude any "_MOD_" actions
		if (!$this->auth->acl_get('u_aps_view_mod'))
		{
			$sql_ary['WHERE'] .= ' AND log_action ' . $this->db->sql_not_like_expression($this->db->get_any_char() . '_MOD_' . $this->db->get_any_char());
		}

		if ($count)
		{
			$count_array = $sql_ary;

			$count_array['SELECT'] = 'COUNT(log_id) as count';
			unset($count_array['LEFT_JOIN'], $count_array['ORDER_BY']);

			$sql = $this->db->sql_build_query('SELECT', $count_array);
			$result = $this->db->sql_query($sql);
			$this->entries_count = (int) $this->db->sql_fetchfield('count');
			$this->db->sql_freeresult($result);

			if ($this->entries_count === 0)
			{
				$this->last_page_offset = 0;
				return [];
			}

			while ($this->last_page_offset >= $this->entries_count)
			{
				$this->last_page_offset = max(0, $this->last_page_offset - $limit);
			}
		}

		$logs = [];

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query_limit($sql, $limit, $this->last_page_offset);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_authed = (bool) ($row['forum_id'] && $this->auth->acl_get('f_read', (int) $row['forum_id']));

			// append_sid() will ignore params with a NULL value
			$forum_params = ['f' => ($row['forum_id'] ? (int) $row['forum_id'] : null)];
			$topic_params = ['t' => ($row['topic_id'] ? (int) $row['topic_id'] : null)];

			$s_points = ($this->auth->acl_get('a_forum') && $this->auth->acl_get('a_aps_points'));
			$points_forum = append_sid("{$this->admin_path}index.{$this->php_ext}", ['i' => 'acp_forums', 'mode' => 'manage', 'action' => 'edit', 'f' => (int) $row['forum_id'], '#' => 'aps_points']);
			$points_global = append_sid("{$this->admin_path}index.{$this->php_ext}", ['i' => '-phpbbstudio-aps-acp-main_module', 'mode' => 'points']);

			$logs[] = [
				'id'			=> (int) $row['log_id'],

				'user'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, $profile_url),
				'user_id'		=> (int) $row['user_id'],
				'reportee'		=> $row['reportee_id'] != ANONYMOUS ? get_username_string('full', $row['reportee_id'], $row['reportee_name'], $row['reportee_colour'], false, $profile_url) : '',
				'reportee_id'	=> (int) $row['reportee_id'],
				's_self'		=> (bool) ((int) $row['user_id'] === (int) $row['reportee_id']),

				'ip'			=> (string) $row['reportee_ip'],
				'time'			=> (int) $row['log_time'],
				'action'		=> (string) $row['log_action'],
				'actions'		=> unserialize($row['log_actions']),

				'approved'		=> (bool) $row['log_approved'],

				'forum_id'		=> (int) $row['forum_id'],
				'forum_name'	=> (string) $row['forum_name'],
				'u_forum'		=> ($row['forum_id'] && $s_authed) ? append_sid("{$this->root_path}viewforum.{$this->php_ext}", $forum_params) : '',

				'topic_id'		=> (int) $row['topic_id'],
				'topic_title'	=> (string) $row['topic_title'],
				'u_topic'		=> ($row['topic_id'] && $s_authed) ? append_sid("{$this->root_path}viewtopic.{$this->php_ext}", array_merge($forum_params, $topic_params)) : '',

				'post_id'		=> (int) $row['post_id'],
				'post_subject'	=> (string) $row['post_subject'],
				'u_post'		=> ($row['post_id'] && $s_authed) ? append_sid("{$this->root_path}viewtopic.{$this->php_ext}", array_merge($forum_params, $topic_params, ['p' => (int) $row['post_id'], '#' => 'p' . (int) $row['post_id']])) : '',

				'points_old'	=> $row['points_old'] !== '0.00' ? (double) $row['points_old'] : $this->functions->equate_points((double) $row['points_new'], $row['points_sum'], '-'),
				'points_sum'	=> (double) $row['points_sum'],
				'points_new'	=> $row['points_new'] !== '0.00' ? (double) $row['points_new'] : $this->functions->equate_points((double) $row['points_old'], $row['points_sum'], '+'),
				'u_points'		=> $s_points ? ($row['forum_id'] ? $points_forum : $points_global) : '',
			];
		}
		$this->db->sql_freeresult($result);

		return $logs;
	}

	/**
	 * Generates a sql condition for the specified keywords
	 *
	 * @param	string	$keywords			The keywords the user specified to search for
	 * @param	string	$table_alias		The alias of the logs' table ('l.' by default)
	 * @param	string	$statement_operator	The operator used to prefix the statement ('AND' by default)
	 *
	 * @return	string						Returns the SQL condition searching for the keywords
	 */
	protected function generate_sql_keyword($keywords, $table_alias = 'l.', $statement_operator = 'AND')
	{
		// Use no preg_quote for $keywords because this would lead to sole
		// backslashes being added. We also use an OR connection here for
		// spaces and the | string. Currently, regex is not supported for
		// searching (but may come later).
		$keywords = preg_split('#[\s|]+#u', utf8_strtolower($keywords), 0, PREG_SPLIT_NO_EMPTY);

		$sql_keywords = '';

		if (!empty($keywords))
		{
			$keywords_pattern = [];

			// Build pattern and keywords...
			for ($i = 0, $num_keywords = count($keywords); $i < $num_keywords; $i++)
			{
				$keywords_pattern[] = preg_quote($keywords[$i], '#');
			}

			$keywords_pattern = '#' . implode('|', $keywords_pattern) . '#ui';

			$operations = [];

			foreach ($this->lang->get_lang_array() as $key => $value)
			{
				if (substr($key, 0, 4) == 'APS_')
				{
					if (is_array($value))
					{
						foreach ($value as $plural_value)
						{
							if (preg_match($keywords_pattern, $plural_value))
							{
								$operations[] = $key;
								break;
							}
						}
					}
					else if (preg_match($keywords_pattern, $value))
					{
						$operations[] = $key;
					}
					else if (preg_match($keywords_pattern, $key))
					{
						$operations[] = $key;
					}
				}
			}

			if (!empty($operations))
			{
				$sql_keywords = ' ' . $statement_operator . ' (';
				$sql_keywords .= $this->db->sql_in_set($table_alias . 'log_action', $operations);
				$sql_keywords .= ')';
			}
		}

		return $sql_keywords;
	}
}
