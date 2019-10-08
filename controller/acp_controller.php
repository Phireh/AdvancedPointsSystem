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

/**
 * phpBB Studio - Advanced Points System ACP controller.
 */
class acp_controller
{
	/** @var \phpbbstudio\aps\core\acp */
	protected $acp;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbbstudio\aps\points\blockader */
	protected $blockader;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var array Clone of phpBB config */
	protected $config_new;

	/** @var \phpbbstudio\aps\controller\main_controller */
	protected $controller;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log_phpbb;

	/** @var \phpbbstudio\aps\core\log */
	protected $log;

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

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor.
	 *
	 * @param  \phpbbstudio\aps\core\acp					$acp			APS ACP functions
	 * @param  \phpbb\auth\auth								$auth			Authentication object
	 * @param  \phpbbstudio\aps\points\blockader			$blockader		APS Blockader object
	 * @param  \phpbb\config\config							$config			Configuration object
	 * @param  \phpbbstudio\aps\controller\main_controller	$controller		APS Main controller
	 * @param  \phpbb\db\driver\driver_interface			$db				Database object
	 * @param  \phpbb\event\dispatcher						$dispatcher		Event dispatcher
	 * @param  \phpbbstudio\aps\core\functions				$functions		APS Core functions
	 * @param  \phpbb\language\language						$lang			phpBB Language object
	 * @param  \phpbb\log\log								$log_phpbb		phpBB Log object
	 * @param  \phpbbstudio\aps\core\log					$log			APS Log object
	 * @param  \phpbb\pagination							$pagination		Pagination object
	 * @param  \phpbbstudio\aps\points\reasoner				$reasoner		APS Reasoner object
	 * @param  \phpbb\request\request						$request		Request object
	 * @param  \phpbb\template\template						$template		Template object
	 * @param  \phpbb\user									$user			User object
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbbstudio\aps\core\acp $acp,
		\phpbb\auth\auth $auth,
		\phpbbstudio\aps\points\blockader $blockader,
		\phpbb\config\config $config,
		main_controller $controller,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbbstudio\aps\core\functions $functions,
		\phpbb\language\language $lang,
		\phpbb\log\log $log_phpbb,
		\phpbbstudio\aps\core\log $log,
		\phpbb\pagination $pagination,
		\phpbbstudio\aps\points\reasoner $reasoner,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->acp			= $acp;
		$this->auth			= $auth;
		$this->blockader	= $blockader;
		$this->config		= $config;
		$this->controller	= $controller;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->functions	= $functions;
		$this->lang			= $lang;
		$this->log_phpbb	= $log_phpbb;
		$this->log			= $log;
		$this->pagination	= $pagination;
		$this->reasoner		= $reasoner;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$log->load_lang();
		$lang->add_lang('aps_acp_common', 'phpbbstudio/aps');
	}

	/**
	 * Handle ACP settings.
	 *
	 * @return void
	 * @access public
	 */
	public function settings()
	{
		$errors = [];
		$submit = $this->request->is_set_post('submit');
		$form_key = 'aps_settings';
		add_form_key($form_key);

		if ($action = $this->request->variable('action', '', true))
		{
			switch ($action)
			{
				case 'copy':
					$errors = $this->copy_points();
				break;

				case 'clean':
					$this->clean_points();
				break;
			}
		}

		$settings = [
			'legend1'		=> 'GENERAL_OPTIONS',
			'aps_points_copy'				=> ['lang' => 'ACP_APS_POINTS_COPY_TITLE', 'type' => 'custom', 'method' => 'set_action', 'params' => ['copy']],
			'aps_points_clean'				=> ['lang' => 'ACP_APS_POINTS_CLEAN', 'type' => 'custom', 'method' => 'set_action', 'params' => ['clean']],
			'aps_points_safe_mode'			=> ['lang' => 'ACP_APS_POINTS_SAFE_MODE', 'type' => 'radio:enabled_disabled', 'validate' => 'bool', 'explain' => true],
			'legend2'		=> 'ACP_APS_POINTS_NAMES',
			// Later inserted
			'legend3'		=> 'ACP_APS_FORMATTING',
			'aps_points_icon'				=> ['lang' => 'ACP_APS_POINTS_ICON', 'type' => 'text:0:100', 'append' => '<i class="icon fa-fw" aria-hidden="true"></i>'],
			'aps_points_icon_position'		=> ['lang' => 'ACP_APS_POINTS_ICON_POSITION', 'validate' => 'bool', 'type' => 'custom', 'method' => 'build_position_radio'],
			'aps_points_decimals'			=> ['lang' => 'ACP_APS_POINTS_DECIMALS', 'validate' => 'string', 'type' => 'select', 'method' => 'build_decimal_select'],
			'aps_points_separator_dec'		=> ['lang' => 'ACP_APS_SEPARATOR_DEC', 'validate' => 'string', 'type' => 'select', 'method' => 'build_separator_select', 'params' => ['{CONFIG_VALUE}']],
			'aps_points_separator_thou'		=> ['lang' => 'ACP_APS_SEPARATOR_THOU', 'validate' => 'string', 'type' => 'select', 'method' => 'build_separator_select'],
			'legend4'		=> 'GENERAL_SETTINGS',
			'aps_points_display_profile'	=> ['lang' => 'ACP_APS_POINTS_DISPLAY_PROFILE', 'type' => 'radio:yes_no', 'validate' => 'bool', 'explain' => true],
			'aps_points_display_post'		=> ['lang' => 'ACP_APS_POINTS_DISPLAY_POST', 'type' => 'radio:yes_no', 'validate' => 'bool', 'explain' => true],
			'aps_points_display_pm'			=> ['lang' => 'ACP_APS_POINTS_DISPLAY_PM', 'type' => 'radio:yes_no', 'validate' => 'bool', 'explain' => true],
			'aps_points_min'				=> ['lang' => 'ACP_APS_POINTS_MIN', 'type' => 'number', 'validate' => 'string', 'explain' => true], // Validate as string to make sure it does not default to 0
			'aps_points_max'				=> ['lang' => 'ACP_APS_POINTS_MAX', 'type' => 'number', 'validate' => 'string', 'explain' => true],
			'aps_actions_per_page'			=> ['lang' => 'ACP_APS_POINTS_PER_PAGE', 'type' => 'number:10:100', 'validate', 'validate' => 'number:10:100', 'explain' => true],
			'aps_points_exclude_words'		=> ['lang' => 'ACP_APS_POINTS_EXCLUDE_WORDS', 'type' => 'number:0:10', 'validate' => 'number:0:10', 'explain' => true, 'append' => '&nbsp;' . $this->lang->lang('ACP_APS_CHARACTERS')],
			'aps_points_exclude_chars'		=> ['lang' => 'ACP_APS_POINTS_EXCLUDE_CHARS', 'type' => 'radio:yes_no', 'validate' => 'bool', 'explain' => true],
			'legend5'		=> 'ACP_APS_CHAIN_SETTINGS',
			'aps_chain_merge_delete'		=> ['lang' => 'ACP_APS_CHAIN_MERGE_DELETE', 'type' => 'radio:enabled_disabled', 'validate' => 'bool', 'explain' => true],
			'aps_chain_merge_move'			=> ['lang' => 'ACP_APS_CHAIN_MERGE_MOVE', 'type' => 'radio:enabled_disabled', 'validate' => 'bool', 'explain' => true],
			'aps_chain_warn_pm'				=> ['lang' => 'ACP_APS_CHAIN_WARN_PM', 'type' => 'radio:enabled_disabled', 'validate' => 'bool', 'explain' => true],
		];

		$settings = phpbb_insert_config_array($settings, $this->build_point_names(), ['after' => 'legend2']);

		/**
		 * Event to add additional settings to the APS ACP settings page.
		 *
		 * @event phpbbstudio.aps.acp_settings
		 * @var	array	settings	Available settings
		 * @since 1.0.0
		 */
		$vars = ['settings'];
		extract($this->dispatcher->trigger_event('phpbbstudio.aps.acp_settings', compact($vars)));

		$this->config_new = clone $this->config;
		$settings_array = $submit ? $this->request->variable('config', ['' => '']) : $this->config_new;

		validate_config_vars($settings, $settings_array, $errors);

		if ($submit && !check_form_key($form_key))
		{
			$errors[] = $this->lang->lang('FORM_INVALID');
		}

		if (!empty($errors))
		{
			$submit = false;
		}

		foreach ($settings as $config_name => $data)
		{
			if (!isset($settings_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$this->config_new[$config_name] = $config_value = $settings_array[$config_name];

			if ($submit)
			{
				$this->config->set($config_name, $config_value);
			}
		}

		if ($submit)
		{
			// Log the action
			$this->log_phpbb->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_SETTINGS');

			// Show success message
			trigger_error($this->lang->lang('ACP_APS_SETTINGS_SUCCESS') . adm_back_link($this->u_action));
		}

		foreach ($settings as $config_key => $setting)
		{
			if (!is_array($setting) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$this->template->assign_block_vars('settings', [
					'CLASS'		=> str_replace(['acp_', '_'], ['', '-'], utf8_strtolower($setting)),
					'LEGEND'	=> $setting,
					'S_LEGEND'	=> true,
				]);

				continue;
			}

			$type = explode(':', $setting['type']);
			$content = build_cfg_template($type, $config_key, $this->config_new, $config_key, $setting);

			if (empty($content))
			{
				continue;
			}

			$booleans = ['yes_no', 'no_yes', 'enabled_disabled', 'disabled_enabled'];
			if ($type[0] === 'radio' && !empty($type[1]) && in_array($type[1], $booleans))
			{
				$yes = [$this->lang->lang('YES'), $this->lang->lang('ENABLED')];
				$no = [$this->lang->lang('NO'), $this->lang->lang('DISABLED')];
				$content = preg_replace(
					['/(' . implode('|', $yes) . ')/', '/(' . implode('|', $no) . ')/', '/class="radio"/'],
					['<span class="aps-button-green">$1</span>', '<span class="aps-button-red">$1</span>', 'class="radio aps-bool"'],
					$content
				);
			}

			$this->template->assign_block_vars('settings', [
				'KEY'		=> $config_key,
				'CONTENT'	=> $content,
				'TITLE'		=> $setting['lang'],
				'S_EXPLAIN'	=> isset($setting['explain']) ? $setting['explain'] : false,
			]);
		}

		$this->template->assign_vars([
			'S_ERROR'		=> !empty($errors),
			'ERROR_MSG'		=> !empty($errors) ? implode('<br>', $errors) : '',

			'U_ACTION'		=> $this->u_action,
		]);
	}

	/**
	 * Handle ACP display.
	 *
	 * @return void
	 * @access public
	 */
	public function display()
	{
		$errors = [];

		add_form_key('aps_display');

		$blocks = $this->controller->get_page_blocks();
		$admin_blocks = $this->blockader->row($this->blockader->get_admin_id());
		$insert = empty($admin_blocks);

		if ($insert)
		{
			foreach ($blocks as $slug => $data)
			{
				$admin_blocks[$slug] = array_keys($data['blocks']);
			}
		}

		$admin_pages = array_keys($admin_blocks);

		foreach ($admin_pages as $slug)
		{
			if (empty($blocks[$slug]))
			{
				continue;
			}

			$data = $blocks[$slug];

			$this->template->assign_block_vars('aps_pages', [
				'ID'		=> $slug,
				'TITLE'		=> $data['title'],
				'S_ACTIVE'	=> in_array($slug, $admin_pages),
			]);

			foreach ($data['blocks'] as $block_id => $block)
			{
				$this->template->assign_block_vars('aps_pages.blocks', [
					'ID'		=> $block_id,
					'TITLE'		=> $block['title'],
					'S_ACTIVE'	=> isset($admin_blocks[$slug]) && in_array($block_id, $admin_blocks[$slug]),
				]);
			}
		}

		$submit = $this->request->is_set_post('submit');

		$settings = [
			'aps_display_top_change'	=> $this->request->variable('aps_display_top_change', (int) $this->config['aps_display_top_change']),
			'aps_display_top_count'		=> $this->request->variable('aps_display_top_count', (int) $this->config['aps_display_top_count']),
			'aps_display_adjustments'	=> $this->request->variable('aps_display_adjustments', (int) $this->config['aps_display_adjustments']),
			'aps_display_graph_time'	=> $this->request->variable('aps_display_graph_time', (int) $this->config['aps_display_graph_time']),
		];

		/**
		 * Event to handle additional settings for the APS ACP display page.
		 *
		 * @event phpbbstudio.aps.acp_display
		 * @var	array	settings	Available settings
		 * @var	array	errors		Any errors that may have occurred
		 * @var bool	submit		Whether or not the form was submitted
		 * @since 1.0.2
		 */
		$vars = ['settings', 'errors', 'submit'];
		extract($this->dispatcher->trigger_event('phpbbstudio.aps.acp_display', compact($vars)));

		if ($submit)
		{
			if (!check_form_key('aps_display'))
			{
				$errors[] = $this->lang->lang('FORM_INVALID');
			}

			$display_blocks = $this->request->variable('aps_blocks', ['' => ['']]);

			if (empty($errors))
			{
				// Set the settings
				foreach ($settings as $name => $value)
				{
					if ($this->config[$name] != $value)
					{
						$this->config->set($name, $value);
					}
				}

				foreach ($display_blocks as $key => $array)
				{
					$display_blocks[$key] = array_filter($array);
				}

				// Set the blocks
				$this->blockader->set_blocks($this->blockader->get_admin_id(), $display_blocks, $insert);

				// Log the action
				$this->log_phpbb->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_DISPLAY');

				// Show success message
				trigger_error($this->lang->lang('ACP_APS_DISPLAY_SUCCESS') . adm_back_link($this->u_action));
			}
		}

		foreach ($settings as $name => $value)
		{
			$this->template->assign_var(utf8_strtoupper($name), $value);
		}

		$this->template->assign_vars([
			'S_ERROR'		=> !empty($errors),
			'ERROR_MSG'		=> !empty($errors) ? implode('<br>', $errors) : '',

			'U_ACTION'		=> $this->u_action,
		]);
	}

	/**
	 * Handle ACP points and reasons.
	 *
	 * @return void
	 * @access public
	 */
	public function points()
	{
		$errors = [];
		$action = $this->request->variable('action', '');
		$submit = $this->request->is_set_post('submit');

		$form_name = 'acp_aps_points';
		add_form_key($form_name);

		if (
			(!$this->auth->acl_get('a_aps_points') && !$this->auth->acl_get('a_aps_reasons'))
			|| (!empty($action) && !$this->auth->acl_get('a_aps_reasons'))
		)
		{
			trigger_error('NOT_AUTHORISED', E_USER_WARNING);
		}

		switch ($action)
		{
			case 'add':
			case 'edit':
				$reason_id = (int) $this->request->variable('r', 0);

				$reason = $this->reasoner->row($reason_id);

				$reason = $this->reasoner->fill($reason);

				$reason['reason_title'] = $this->request->variable('title', (string) $reason['reason_title'] , true);
				$reason['reason_desc'] = $this->request->variable('description', (string) $reason['reason_desc'], true);
				$reason['reason_points'] = $this->request->variable('points', (double) $reason['reason_points']);

				if ($submit)
				{
					if (!check_form_key($form_name))
					{
						$errors[] = $this->lang->lang('FORM_INVALID');
					}

					if (empty($reason['reason_title']) || strlen($reason['reason_title']) > 255)
					{
						$errors[] = $this->lang->lang('ACP_APS_REASON_EMPTY_SUBJECT');
					}

					$reason_points_to_check = round($reason['reason_points'], 2);
					if (empty($reason_points_to_check))
					{
						$errors[] = $this->lang->lang('ACP_APS_REASON_EMPTY_POINTS', $this->functions->get_name());
					}

					if (empty($errors))
					{
						if ($action === 'add')
						{
							$this->reasoner->insert($reason);
						}
						else
						{
							$this->reasoner->update($reason, $reason_id);
						}

						$this->log_phpbb->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_REASON_' . utf8_strtoupper($action));

						trigger_error($this->lang->lang('ACP_APS_REASON_SAVED') . adm_back_link($this->u_action));
					}
				}

				$this->template->assign_vars([
					'REASON_TITLE'	=> $reason['reason_title'],
					'REASON_DESC'	=> $reason['reason_desc'],
					'REASON_POINTS'	=> $reason['reason_points'],
				]);
			break;

			case 'delete':
				$reason_id = (int) $this->request->variable('r', 0);

				if (confirm_box(true))
				{
					$this->reasoner->delete($reason_id);

					$json_response = new \phpbb\json_response;
					$json_response->send([
						'SUCCESS'		=> true,
						'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
						'MESSAGE_TEXT'	=> $this->lang->lang('ACP_APS_REASON_DELETE_SUCCESS'),
					]);
				}
				else
				{
					confirm_box(false, 'ACP_APS_REASON_DELETE', build_hidden_fields([
						'submit'	=> $submit,
						'action'	=> $action,
					]));
				}
			break;

			case 'move':
				$reason_id = $this->request->variable('r', 0);
				$dir = $this->request->variable('dir', '');

				$this->reasoner->order($reason_id, $dir);

				$json_response = new \phpbb\json_response;
				$json_response->send([
					'success' => true,
				]);
			break;

			default:
				if ($this->auth->acl_get('a_aps_points'))
				{
					$this->acp->build();

					if ($submit)
					{
						if (!check_form_key($form_name))
						{
							$errors[] = $this->lang->lang('FORM_INVALID');
						}

						if (empty($errors))
						{
							$values = $this->request->variable('aps_values', ['' => 0.00]);

							$this->acp->set_points($values);

							$this->log_phpbb->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_POINTS', time(), [$this->functions->get_name()]);

							trigger_error($this->lang->lang('ACP_APS_POINTS_SUCCESS', $this->functions->get_name()) . adm_back_link($this->u_action));
						}
					}
				}

				if ($this->auth->acl_get('a_aps_points'))
				{
					$rowset = $this->reasoner->rowset();

					foreach ($rowset as $row)
					{
						$this->template->assign_block_vars('aps_reasons', [
							'ID'		=> (int) $row['reason_id'],
							'TITLE'		=> (string) $row['reason_title'],
							'DESC'		=> (string) $row['reason_desc'],
							'POINTS'	=> (double) $row['reason_points'],

							'U_DELETE'		=> $this->u_action . '&action=delete&r=' . (int) $row['reason_id'],
							'U_EDIT'		=> $this->u_action . '&action=edit&r=' . (int) $row['reason_id'],
							'U_MOVE_UP'		=> $this->u_action . '&action=move&dir=up&r=' . (int) $row['reason_id'],
							'U_MOVE_DOWN'	=> $this->u_action . '&action=move&dir=down&r=' . (int) $row['reason_id'],
						]);
					}

					$this->template->assign_vars([
						'U_APS_REASON_ADD'	=> $this->u_action . '&action=add',
					]);
				}
			break;
		}

		$s_errors = (bool) count($errors);

		$this->template->assign_vars([
			'S_ERRORS'			=> $s_errors,
			'ERROR_MSG'			=> $s_errors ? implode('<br />', $errors) : '',

			'APS_TITLE'			=> $action ? $this->lang->lang('ACP_APS_REASON_' . utf8_strtoupper($action)) : $this->functions->get_name(),

			'S_APS_ACTION'		=> $action,
			'S_APS_POINTS'		=> $this->auth->acl_get('a_aps_points'),
			'S_APS_REASONS'		=> $this->auth->acl_get('a_aps_reasons'),

			'U_APS_ACTION'		=> $this->u_action . ($action ? "&amp;action={$action}" : '') . (!empty($reason_id) ? "&amp;r={$reason_id}" : ''),
		]);
	}

	/**
	 * Handle ACP logs.
	 *
	 * @return void
	 * @access public
	 */
	public function logs()
	{
		// Set up general vars
		$start		= $this->request->variable('start', 0);
		$forum_id	= $this->request->variable('f', '');
		$topic_id	= $this->request->variable('t', 0);
		$post_id	= $this->request->variable('p', 0);
		$user_id	= $this->request->variable('u', 0);
		$reportee_id = $this->request->variable('r', 0);

		$delete_mark = $this->request->variable('del_marked', false, false, \phpbb\request\request_interface::POST);
		$delete_all	= $this->request->variable('del_all', false, false, \phpbb\request\request_interface::POST);
		$marked		= $this->request->variable('mark', [0]);

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 't');
		$sort_dir	= $this->request->variable('sd', 'd');

		// Keywords
		$keywords = $this->request->variable('keywords', '', true);
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

		if (($delete_mark || $delete_all))
		{
			if (confirm_box(true))
			{
				$conditions = [];

				if ($delete_mark && count($marked))
				{
					$conditions['log_id'] = ['IN' => $marked];
				}

				if ($delete_all)
				{
					if ($sort_days)
					{
						$conditions['log_time'] = ['>=', time() - ($sort_days * 86400)];
					}

					$conditions['keywords'] = $keywords;
				}

				$this->log->delete($conditions);

				$plural = $delete_all ? 2 : count($marked);
				$log_action = 'LOG_ACP_APS_LOGS_' . $delete_all ? 'CLEARED' : 'DELETED';
				$this->log_phpbb->add('admin', $this->user->data['user_id'], $this->user->ip, $log_action, time(), [$this->functions->get_name()]);

				trigger_error($this->lang->lang('ACP_APS_LOGS_DELETED', $plural) . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'f'				=> $forum_id,
					'start'			=> $start,
					'del_marked'	=> $delete_mark,
					'del_all'		=> $delete_all,
					'mark'			=> $marked,
					'st'			=> $sort_days,
					'sk'			=> $sort_key,
					'sd'			=> $sort_dir,
				]));
			}
		}

		$name = $this->functions->get_name();
		$limit = $this->config['aps_actions_per_page'];

		// Sorting
		$limit_days = [
			0 => $this->lang->lang('ALL_ENTRIES'),
			1 => $this->lang->lang('1_DAY'),
			7 => $this->lang->lang('7_DAYS'),
			14 => $this->lang->lang('2_WEEKS'),
			30 => $this->lang->lang('1_MONTH'),
			90 => $this->lang->lang('3_MONTHS'),
			180 => $this->lang->lang('6_MONTHS'),
			365 => $this->lang->lang('1_YEAR'),
		];
		$sort_by_text = [
			'a'  => $this->lang->lang('SORT_ACTION'),
			'ps' => $name,
			'pn' => $this->lang->lang('APS_POINTS_NEW', $name),
			'po' => $this->lang->lang('APS_POINTS_OLD', $name),
			'uu' => $this->lang->lang('SORT_USERNAME'),
			'ru' => ucfirst($this->lang->lang('FROM')),
			't'  => $this->lang->lang('SORT_DATE'),
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
	 * Set custom form action.
	 *
	 * @param  string			$u_action	Custom form action
	 * @return acp_controller	$this		This controller for chaining calls
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;

		return $this;
	}

	/**
	 * Build a settings array for point names for all installed languages.
	 *
	 * @return array	$names		Array of localised point name settings
	 * @access protected
	 */
	protected function build_point_names()
	{
		$names = [];

		$sql = 'SELECT * FROM ' . LANG_TABLE . ' ORDER BY lang_english_name';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$name['aps_points_name_' . $row['lang_iso']] = ['lang' =>$row['lang_english_name'], 'validate' => 'string:0:100', 'type' => 'text:0:100'];

			$names = $row['lang_iso'] === $this->config['default_lang'] ? $name + $names : array_merge($names, $name);
		}
		$this->db->sql_freeresult($result);

		// Only add the 'expand view' if there are multiple language installed
		if (count($names) > 1)
		{
			// Get the first array key
			$key = array_keys($names)[0];

			// The 'expand view' HTML string
			$expand = '<a class="aps-names-toggle" data-text="' . $this->lang->lang('COLLAPSE_VIEW') . '">' . $this->lang->lang('EXPAND_VIEW') . '</a>';

			$names[$key]['append'] = $expand;
		}

		return $names;
	}

	/**
	 * Handle the copy action from the settings page.
	 *
	 * @return array			Array filled with errors
	 * @access protected
	 */
	protected function copy_points()
	{
		$json_response = new \phpbb\json_response;

		add_form_key('aps_points_copy');

		if ($this->request->is_set_post('submit_copy'))
		{
			$errors = [];

			$from = $this->request->variable('aps_forums_from', 0);
			$to = $this->request->variable('aps_forums_to', [0]);

			if (empty($from) || empty($to))
			{
				$errors[] = $this->lang->lang('ACP_APS_POINTS_COPY_EMPTY');
			}

			if (!check_form_key('aps_points_copy'))
			{
				$errors[] = $this->lang->lang('FORM_INVALID');
			}

			if ($errors)
			{
				if ($this->request->is_ajax())
				{
					$json_response->send([
						'MESSAGE_TITLE' => $this->lang->lang('ERROR'),
						'MESSAGE_TEXT'  => implode('<br />', $errors),
					]);
				}
				else
				{
					return $errors;
				}
			}
			else
			{
				$this->acp->copy_multiple($from, $to);

				$this->log_phpbb->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_COPIED', time(), [$this->functions->get_name()]);

				$json_response->send([
					'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
					'MESSAGE_TEXT'	=> $this->lang->lang('ACP_APS_POINTS_COPY_SUCCESS', $this->functions->get_name()),
				]);

				trigger_error($this->lang->lang('ACP_APS_POINTS_COPY_SUCCESS', $this->functions->get_name()) . adm_back_link($this->u_action));
			}
		}

		$this->template->set_filenames(['copy' => '@phpbbstudio_aps/aps_points_copy.html']);

		$this->template->assign_vars([
			'S_APS_COPY'		=> true,
			'S_APS_FORUMS'		=> make_forum_select(),
			'U_APS_ACTION_COPY'	=> $this->u_action . '&action=copy',
		]);

		if ($this->request->is_ajax())
		{
			$json_response->send([
				'MESSAGE_TITLE'	=> $this->lang->lang('ACP_APS_POINTS_COPY_TITLE', $this->functions->get_name()),
				'MESSAGE_TEXT'	=> $this->template->assign_display('copy'),
			]);
		}

		return [];
	}

	/**
	 * Handles the clean action from the settings page.
	 *
	 * @return void
	 * @access protected
	 */
	protected function clean_points()
	{
		if (confirm_box(true))
		{
			$this->acp->clean_points();

			$this->log_phpbb->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_CLEANED', time(), [$this->functions->get_name()]);

			$json_response = new \phpbb\json_response;
			$json_response->send([
				'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
				'MESSAGE_TEXT'	=> $this->lang->lang('ACP_APS_POINTS_CLEAN_SUCCESS', $this->functions->get_name()),
			]);
		}
		else
		{
			confirm_box(false, $this->lang->lang('ACP_APS_POINTS_CLEAN_CONFIRM', $this->functions->get_name()), build_hidden_fields([
				'action' => 'clean',
			]));
		}
	}
}
