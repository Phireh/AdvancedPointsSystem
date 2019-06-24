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
 * phpBB Studio - Advanced Points System main controller.
 */
class main_controller
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbbstudio\aps\points\blockader */
	protected $blockader;

	/** @var \phpbbstudio\aps\core\blocks */
	protected $blocks;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

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

	/** @var string APS Points name */
	protected $name;

	/** @var string Current page */
	protected $page;

	/** @var array Available page blocks */
	protected $page_blocks;

	/** @var array User desired page blocks */
	protected $user_blocks;

	/** @var array Admin desired page blocks */
	protected $admin_blocks;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth			Authentication object
	 * @param  \phpbbstudio\aps\points\blockader	$blockader		APS Blockader
	 * @param  \phpbbstudio\aps\core\blocks			$blocks			APS Blocks functions
	 * @param  \phpbb\event\dispatcher				$dispatcher		Event dispatcher
	 * @param  \phpbbstudio\aps\core\functions		$functions		APS Core functions
	 * @param  \phpbb\controller\helper				$helper			Controller helper object
	 * @param  \phpbb\language\language				$lang			Language object
	 * @param  \phpbb\request\request				$request		Request object
	 * @param  \phpbb\template\template				$template		Template object
	 * @param  \phpbb\user							$user			User object
	 * @param  string								$root_path		phpBB root path
	 * @param  string								$php_ext		PHP File extension
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbbstudio\aps\points\blockader $blockader,
		\phpbbstudio\aps\core\blocks $blocks,
		\phpbb\event\dispatcher $dispatcher,
		\phpbbstudio\aps\core\functions $functions,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext
	)
	{
		$this->auth			= $auth;
		$this->blockader	= $blockader;
		$this->blocks		= $blocks;
		$this->dispatcher	= $dispatcher;
		$this->functions	= $functions;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;

		$this->name			= $functions->get_name();

		$lang->add_lang('aps_display', 'phpbbstudio/aps');
	}

	/**
	 * Display the points page.
	 *
	 * @param  string	$page		The page slug
	 * @param  int		$pagination	The page number for pagination
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @access public
	 */
	public function display($page, $pagination)
	{
		$this->page = $page;

		// Load page blocks
		$this->get_page_blocks($pagination);

		// Load admin and user blocks
		$this->get_admin_user_blocks();

		// Check existance
		if ($msg = $this->check_existance())
		{
			return $msg;
		}

		// Check authorisation
		if ($msg = $this->check_auth())
		{
			return $msg;
		}

		// Handle any action
		$this->handle_action();

		foreach (array_keys($this->admin_blocks) as $slug)
		{
			$data = $this->page_blocks[$slug];

			// Only list pages that the user is authorised to see
			if (!isset($data['auth']) || $data['auth'])
			{
				$this->template->assign_block_vars('aps_navbar', [
					'TITLE'		=> $data['title'],
					'S_ACTIVE'	=> $this->page === $slug,
					'U_VIEW'	=> $this->helper->route('phpbbstudio_aps_display', ['page' => $slug]),
				]);
			}
		}

		$called_functions = [];

		$blocks = array_keys($this->page_blocks[$this->page]['blocks']);
		$blocks = empty($this->user_blocks[$this->page]) ? $blocks : array_unique(array_merge($this->user_blocks[$this->page], $blocks));

		foreach ($blocks as $block_id)
		{
			// If the block is no longer available, remove it from the user blocks
			if (empty($this->page_blocks[$this->page]['blocks'][$block_id]))
			{
				$this->delete_block($block_id);

				continue;
			}

			// If it's disabled by the admin, do not display it
			if (!in_array($block_id, $this->admin_blocks[$this->page]))
			{
				continue;
			}

			$block = $this->page_blocks[$this->page]['blocks'][$block_id];

			// Only show blocks that the user is authorised to see
			if (!isset($block['auth']) || $block['auth'])
			{
				if (empty($this->user_blocks[$this->page]) || in_array($block_id, $this->user_blocks[$this->page]))
				{
					if (!empty($block['function']) && !in_array($block['function'], $called_functions))
					{
						$called_functions[] = $this->call_function($block_id, $block);
					}

					$this->template->assign_block_vars('aps_blocks', $this->build_display($block_id, $block));
				}
				else
				{
					$this->template->assign_block_vars('aps_blocks_add', ['item' => $this->build_list($block_id, $block)]);
				}
			}
		}

		// Purge temporary variable
		unset($called_functions);

		// Output template variables for display
		$this->template->assign_vars([
			'S_APS_DAE_ENABLED'	=> $this->functions->is_dae_enabled(),
			'S_APS_OVERVIEW'	=> true,
			'U_APS_ACTION_SORT'	=> $this->helper->route('phpbbstudio_aps_display', ['page' => $page, 'aps_action' => 'move']),
			'U_MCP'				=> ($this->auth->acl_get('m_') || $this->auth->acl_getf_global('m_')) ? append_sid("{$this->root_path}mcp.{$this->php_ext}", 'i=-phpbbstudio-aps-mcp-main_module&amp;mode=front', true, $this->user->session_id) : '',
		]);

		// Breadcrumbs
		$this->template->assign_block_vars_array('navlinks', [
			[
				'FORUM_NAME'	=> $this->name,
				'U_VIEW_FORUM'	=> $this->helper->route('phpbbstudio_aps_display'),
			],
			[
				'FORUM_NAME'	=> $this->lang->lang('APS_OVERVIEW'),
				'U_VIEW_FORUM'	=> $this->helper->route('phpbbstudio_aps_display'),
			],
		]);

		// Output the page!
		return $this->helper->render('@phpbbstudio_aps/aps_display.html', $this->page_blocks[$this->page]['title']);
	}

	/**
	 * Build template variables array for a given block.
	 *
	 * @param  string	$block_id	The block identifier
	 * @param  array	$block		The block data
	 * @return array				The block template variables
	 * @access protected
	 */
	protected function build_display($block_id, $block)
	{
		return [
			'ID'			=> $block_id,
			'TITLE'			=> $block['title'],
			'TEMPLATE'		=> $block['template'],
			'S_REQUIRED'	=> !empty($block['required']),
			'U_DELETE'		=> $this->helper->route('phpbbstudio_aps_display', ['page' => $this->page, 'aps_action' => 'delete', 'id' => $block_id]),
		];
	}

	/**
	 * Build template list item for a given block.
	 *
	 * @param  string	$block_id	The block identifier
	 * @param  array	$block		The block data
	 * @return string				The block template list item
	 * @access protected
	 */
	protected function build_list($block_id, $block)
	{
		$u_add = $this->helper->route('phpbbstudio_aps_display', ['page' => $this->page, 'aps_action' => 'add', 'id' => $block_id]);

		return '<li><a class="aps-button-green" href="' . $u_add . '" data-ajax="aps_add">' . $block['title'] . '</a></li>';
	}

	/**
	 * Call a function defined in the block data.
	 *
	 * @param  string	$block_id	The block identifier
	 * @param  array	$block		The block data
	 * @return mixed				The block function declaration
	 * @access protected
	 */
	protected function call_function($block_id, $block)
	{
		// Set up function parameters and append the block id
		$params = !empty($block['params']) ? $block['params'] : [];
		$params = array_merge($params, [
			'block_id'	=> $block_id
		]);

		// Call the function
		call_user_func_array($block['function'], $params);

		return $block['function'];
	}

	/**
	 * Check if the current user is authorised to see this display page.
	 *
	 * @return string|\Symfony\Component\HttpFoundation\Response
	 * @access protected
	 */
	protected function check_auth()
	{
		if (isset($this->page_blocks[$this->page]['auth']) && !$this->page_blocks[$this->page]['auth'])
		{
			$message = $this->lang->lang('NOT_AUTHORISED');
			$back_link = '<a href="' . $this->helper->route('phpbbstudio_aps_display', ['page' => 'overview']) . '">' . $this->lang->lang('APS_OVERVIEW') . '</a>';
			$back_msg = $this->lang->lang('RETURN_TO', $back_link);

			return $this->helper->message($message . '<br /><br />' . $back_msg, [], 'INFORMATION', 401);
		}

		return '';
	}

	/**
	 * Check if the current page is available.
	 *
	 * @return string|\Symfony\Component\HttpFoundation\Response
	 * @access protected
	 */
	protected function check_existance()
	{
		if (empty($this->page_blocks[$this->page]))
		{
			$message = $this->lang->lang('PAGE_NOT_FOUND');
			$back_link = '<a href="' . $this->helper->route('phpbbstudio_aps_display', ['page' => 'overview']) . '">' . $this->lang->lang('APS_OVERVIEW') . '</a>';
			$back_msg = $this->lang->lang('RETURN_TO', $back_link);;

			return $this->helper->message($message . '<br /><br />' . $back_msg, [], 'INFORMATION', 404);
		}

		return '';
	}

	/**
	 * Handle actions for the display blocks.
	 *
	 * @return void
	 * @access protected
	 */
	protected function handle_action()
	{
		// Request the action
		$action = $this->request->variable('aps_action', '', true);

		// Only these actions are available
		if (!in_array($action, ['add', 'delete', 'move']))
		{
			return;
		}

		// Request the block identifier
		$block_id = $this->request->variable('id', '', true);

		// Call the action's function
		$response = $this->{$action . '_block'}($block_id);

		// If the request is AJAX, send a response
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send([
				'success'	=> $response,
				'APS_TITLE'	=> $this->lang->lang('APS_SUCCESS'),
				'APS_TEXT'	=> $this->lang->lang('APS_POINTS_BLOCK_' . utf8_strtoupper($action), $this->name),
			]);
		}

		// Otherwise assign a meta refresh
		$this->helper->assign_meta_refresh_var(0, $this->helper->route('phpbbstudio_aps_display', ['page' => $this->page]));
	}

	/**
	 * Get the admin and user desired page blocks.
	 *
	 * @return void
	 * @access protected
	 */
	protected function get_admin_user_blocks()
	{
		$rowset = $this->blockader->rowset($this->user->data['user_id']);

		foreach ($rowset as $user_id => $blocks)
		{
			if ($user_id == $this->blockader->get_admin_id())
			{
				$this->admin_blocks = $blocks;
			}

			if ($user_id == $this->user->data['user_id'])
			{
				$this->user_blocks = $blocks;
			}
		}

		if (empty($this->admin_blocks))
		{
			foreach ($this->page_blocks as $page => $data)
			{
				$this->admin_blocks[$page] = array_keys($data['blocks']);
			}
		}
	}

	/**
	 * Get all available display blocks.
	 *
	 * @param  int		$pagination		Pagination page number
	 * @return array					The display blocks
	 * @access public
	 */
	public function get_page_blocks($pagination = 1)
	{
		$page_blocks = [
			'overview'	=> [
				'title'		=> $this->lang->lang('APS_OVERVIEW'),
				'blocks'	=> [
					'points_top'      => [
						'title'		=> $this->lang->lang('APS_TOP_USERS'),
						'function'	=> [$this->blocks, 'user_top_search'],
						'template'	=> '@phpbbstudio_aps/blocks/points_top.html',
					],
					'points_search'   => [
						'title'		=> $this->lang->lang('FIND_USERNAME'),
						'function'	=> [$this->blocks, 'user_top_search'],
						'template'	=> '@phpbbstudio_aps/blocks/points_search.html',
					],
					'points_settings' => [
						'title'		=> $this->lang->lang('SETTINGS'),
						'template'	=> '@phpbbstudio_aps/blocks/points_settings.html',
					],
					'points_random'		=> [
						'title'		=> $this->lang->lang('APS_RANDOM_USER'),
						'function'	=> [$this->blocks, 'user_random'],
						'template'	=> '@phpbbstudio_aps/blocks/points_random.html',
					],
					'points_forums'		=> [
						'title'		=> $this->lang->lang('APS_POINTS_PER_FORUM', $this->name),
						'function'	=> [$this->blocks, 'charts_forum'],
						'template'	=> '@phpbbstudio_aps/blocks/points_forums.html',
					],
					'points_groups'		=> [
						'title'		=> $this->lang->lang('APS_POINTS_PER_GROUP', $this->name),
						'function'	=> [$this->blocks, 'charts_group'],
						'template'	=> '@phpbbstudio_aps/blocks/points_groups.html',
					],
					'points_growth'		=> [
						'title'		=> $this->lang->lang('APS_POINTS_GROWTH', $this->name),
						'function'	=> [$this->blocks, 'charts_period'],
						'template'	=> '@phpbbstudio_aps/blocks/points_growth.html',
					],
					'points_trade_off'	=> [
						'title'		=> $this->lang->lang('APS_POINTS_TRADE_OFF', $this->name),
						'function'	=> [$this->blocks, 'charts_period'],
						'template'	=> '@phpbbstudio_aps/blocks/points_trade_off.html',
					],
				],
			],
			'actions'	=> [
				'title'		=> $this->lang->lang('APS_POINTS_ACTIONS', $this->name),
				'auth'		=> $this->auth->acl_get('u_aps_view_logs'),
				'blocks'	=> [
					'points_actions'	=> [
						'auth'		=> $this->auth->acl_get('u_aps_view_logs'),
						'title'		=> $this->lang->lang('APS_POINTS_ACTIONS', $this->name),
						'required'	=> true,
						'function'	=> [$this->blocks, 'display_actions'],
						'params'	=> ['pagination' => $pagination],
						'template'	=> '@phpbbstudio_aps/blocks/points_actions.html',
					],
					'points_registration'	=> [
						'auth'		=> $this->auth->acl_get('u_aps_view_logs'),
						'title'		=> $this->lang->lang('APS_RECENT_ADJUSTMENTS'),
						'function'	=> [$this->blocks, 'recent_adjustments'],
						'template'	=> '@phpbbstudio_aps/blocks/points_adjustments.html',
					],
				],
			],
		];

		/**
		 * Event to add additional page blocks to the APS display page.
		 *
		 * @event phpbbstudio.aps.display_blocks
		 * @var   array	page_blocks		Available page blocks
		 * @var   int	pagination		Pagination's page number
		 * @since 1.0.0
		 */
		$vars = ['page_blocks', 'pagination'];
		extract($this->dispatcher->trigger_event('phpbbstudio.aps.display_blocks', compact($vars)));

		$this->page_blocks = $page_blocks;

		return $this->page_blocks;
	}

	/**
	 * Add a block to the user desired blocks.
	 *
	 * @param  string	$block_id			The block identifier
	 * @return \phpbb\template\template		The rendered block for display
	 * @access protected
	 */
	protected function add_block($block_id)
	{
		$insert = empty($this->user_blocks);

		$this->user_blocks[$this->page] = !$insert ? array_merge($this->user_blocks[$this->page], [$block_id]) : [$block_id];

		$this->blockader->set_blocks($this->user->data['user_id'], $this->user_blocks, $insert);

		$this->template->set_filenames(['block' => '@phpbbstudio_aps/blocks/base.html']);

		if (!empty($this->page_blocks[$this->page]['blocks'][$block_id]['function']))
		{
			$this->call_function($block_id, $this->page_blocks[$this->page]['blocks'][$block_id]);
		}

		$this->template->assign_vars([
			'block'				=> $this->build_display($block_id, $this->page_blocks[$this->page]['blocks'][$block_id]),
			'S_USER_LOGGED_IN'	=> $this->user->data['user_id'] != ANONYMOUS,
			'S_IS_BOT'			=> $this->user->data['is_bot'],
		]);

		return $this->template->assign_display('block');
	}

	/**
	 * Delete a block from the user desired blocks.
	 *
	 * @param  string	$block_id	The block identifier
	 * @return string				HTML list item
	 * @access protected
	 */
	protected function delete_block($block_id)
	{
		$insert = empty($this->user_blocks);

		if ($insert)
		{
			foreach ($this->page_blocks as $page => $data)
			{
				$this->user_blocks[$page] = array_keys($data['blocks']);
			}
		}

		if (($key = array_search($block_id, $this->user_blocks[$this->page])) !== false) {
			unset($this->user_blocks[$this->page][$key]);
		}

		$this->blockader->set_blocks($this->user->data['user_id'], $this->user_blocks, $insert);

		return $this->build_list($block_id, $this->page_blocks[$this->page]['blocks'][$block_id]);
	}

	/**
	 * Move (order) the user desired blocks.
	 *
	 * @return bool|int			Boolean on update or integer on insert.
	 * @access public
	 */
	protected function move_block()
	{
		$insert = empty($this->user_blocks);

		$order = $this->request->variable('order', ['']);

		// Filter out empty block identifiers
		$this->user_blocks[$this->page] = array_filter($order);

		return $this->blockader->set_blocks($this->user->data['user_id'], $this->user_blocks, $insert);
	}
}
