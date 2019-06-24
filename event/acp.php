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
class acp implements EventSubscriberInterface
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
			'core.acp_language_after_delete'			=> 'delete_name',

			'core.acp_users_display_overview'			=> 'display_user',

			'core.acp_manage_forums_display_form'		=> 'display_data',
			'core.acp_manage_forums_update_data_after'	=> 'request_data',
		];
	}

	/** @var \phpbbstudio\aps\core\acp */
	protected $acp;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbbstudio\aps\core\log */
	protected $log_aps;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param  \phpbbstudio\aps\core\acp		$acp		APS ACP functions
	 * @param  \phpbb\auth\auth					$auth		Authentication object
	 * @param  \phpbb\config\config				$config		Configuration object
	 * @param  \phpbbstudio\aps\core\functions	$functions	APS Core functions
	 * @param  \phpbb\controller\helper			$helper		Controller helper object
	 * @param  \phpbb\language\language			$lang		Language object
	 * @param  \phpbb\log\log					$log		phpBB Log object
	 * @param  \phpbbstudio\aps\core\log		$log_aps	APS Log object
	 * @param  \phpbb\request\request			$request	Request object
	 * @param  \phpbb\template\template			$template	Template object
	 * @param  \phpbb\user						$user		User object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbbstudio\aps\core\acp $acp, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbbstudio\aps\core\functions $functions, \phpbb\controller\helper $helper, \phpbb\language\language $lang, \phpbb\log\log $log, \phpbbstudio\aps\core\log $log_aps, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->acp			= $acp;
		$this->auth			= $auth;
		$this->config		= $config;
		$this->functions	= $functions;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->log_aps		= $log_aps;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$log_aps->load_lang();
	}

	/**
	 * Delete a localised points name upon language deletion.
	 *
	 * @event  core.acp_language_after_delete
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function delete_name($event)
	{
		$this->config->delete('aps_points_name_' . $event['lang_iso'], true);
	}

	/**
	 * Display a user's points when managing a specific user.
	 *
	 * @event  core.acp_users_display_overview
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function display_user($event)
	{
		$this->template->assign_var('APS_POINTS', $event['user_row']['user_points']);
	}

	/**
	 * Display a points list when adding/creating a forum.
	 *
	 * @event  core.acp_manage_forums_display_form
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function display_data($event)
	{
		// Only display a points list if the administrator is authorised to edit the points
		if ($s_auth = $this->auth->acl_get('a_aps_points'))
		{
			// Build a points list for this forum
			$this->acp->build((int) $event['forum_id']);

			// Request any action (ajax)
			$action = $this->request->variable('aps_action', '');

			// Ajaxify the copy points action
			if (!empty($action) && $this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;

				$forum_id = $this->request->variable('f', 0);

				switch ($action)
				{
					case 'copy':
						$copy = $this->request->variable('aps_points_copy', 0);

						if (empty($copy))
						{
							$json_response->send([
								'MESSAGE_TITLE'	=> $this->lang->lang('ERROR'),
								'MESSAGE_TEXT'	=> $this->lang->lang('ACP_APS_POINTS_COPY_EMPTY_FROM'),
							]);
						}

						$fields = $this->acp->get_fields();
						$fields = array_flip($fields[0]);

						$this->acp->copy_points($copy, $forum_id, $fields);

						$json_response->send([
							'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
							'MESSAGE_TEXT'	=> $this->lang->lang('ACP_APS_POINTS_COPY_SUCCESS', $this->functions->get_name()),
							'APS_VALUES'	=> $this->acp->assign_values($forum_id),
						]);
					break;

					case 'reset':
						if (confirm_box(true))
						{
							$this->acp->delete_points($forum_id);

							$json_response->send([
								'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
								'MESSAGE_TEXT'	=> $this->lang->lang('ACP_APS_POINTS_RESET_SUCCESS', $this->functions->get_name()),
							]);
						}
						else
						{
							confirm_box(false, $this->lang->lang('ACP_APS_POINTS_RESET_CONFIRM', $this->functions->get_name()), build_hidden_fields([
								'aps_action'	=> $action,
								'forum_id'		=> $forum_id,
							]));
						}
					break;
				}
			}
		}

		$this->template->assign_vars([
			'S_APS_POINTS'	=> (bool) $s_auth,
			'U_APS_RESET'	=> $this->helper->get_current_url() . '&amp;aps_action=reset',
		]);
	}

	/**
	 * Request and set the points when adding/editing a forum.
	 *
	 * @event  core.acp_manage_forums_update_data_after
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function request_data($event)
	{
		// Only set the points when the administrator is authorised to edit the points
		if (!$this->auth->acl_get('a_aps_points'))
		{
			return;
		}

		$forum_id = !empty($event['forum_data']['forum_id']) ? (int) $event['forum_data']['forum_id'] : 0;

		$copy = $this->request->variable('aps_points_copy', 0);
		$reset = $this->request->variable('aps_points_reset', 0);
		$values = $this->request->variable('aps_values', ['' => 0.00]);

		if (!empty($reset) && !empty($forum_id))
		{
			$this->acp->delete_points($forum_id);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_POINTS_RESET', time(), [$event['forum_data']['forum_name'], $this->functions->get_name()]);
		}
		else if (!empty($copy) && $copy != $forum_id)
		{
			$this->acp->copy_points($copy, $forum_id, $values);

			$forum_name = $this->functions->forum_name($copy);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_APS_POINTS_COPIED', time(), [$forum_name, $event['forum_data']['forum_name'], $this->functions->get_name()]);
		}
		else
		{
			$this->acp->set_points($values, $forum_id);
		}
	}
}
