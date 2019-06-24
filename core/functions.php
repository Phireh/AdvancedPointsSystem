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
 * phpBB Studio - Advanced Points System core functions.
 */
class functions
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\user */
	protected $user;

	/** @var string Table prefix */
	protected $table_prefix;

	/** @var bool Whether Default Avatar Extended (DAE) is enabled */
	protected $is_dae_enabled;

	/** @var string The localised points name */
	protected $name;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth			Authentication object
	 * @param  \phpbb\config\config					$config			Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db				Database object
	 * @param  \phpbb\extension\manager				$ext_manager	Extension manager object
	 * @param  \phpbb\language\language				$lang			Language object
	 * @param  \phpbb\path_helper					$path_helper	Path helper object
	 * @param  \phpbb\user							$user			User object
	 * @param  string								$table_prefix	Table prefix
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\extension\manager $ext_manager, \phpbb\language\language $lang, \phpbb\path_helper $path_helper, \phpbb\user $user, $table_prefix)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->lang			= $lang;
		$this->path_helper	= $path_helper;
		$this->user			= $user;
		$this->table_prefix = $table_prefix;

		$this->is_dae_enabled = $ext_manager->is_enabled('threedi/dae') && $config['threedi_default_avatar_extended'];
	}

	/**
	 * Prefix a table name.
	 *
	 * This is to not rely on constants.
	 *
	 * @param  string	$name	The table name to prefix
	 * @return string			The prefixed table name
	 * @access public
	 */
	public function table($name)
	{
		return $this->table_prefix . $name;
	}

	/**
	 * Select a forum name for a specific forum identifier.
	 *
	 * @param  int		$forum_id		The forum identifier
	 * @return string					The forum name
	 * @access public
	 */
	public function forum_name($forum_id)
	{
		$sql = 'SELECT forum_name FROM ' . $this->table('forums') . ' WHERE forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$forum_name = $this->db->sql_fetchfield('forum_name');
		$this->db->sql_freeresult($result);

		return $forum_name;
	}

	public function post_data($post_id)
	{
		$sql = 'SELECT t.topic_first_post_id, p.poster_id
				FROM ' . $this->table('posts') . ' p,
				' . $this->table('topics') . ' t
				WHERE p.topic_id = t.topic_id
					AND p.post_id = ' . (int) $post_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$post_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $post_data;
	}

	/**
	 * Get topic and post locked status.
	 *
	 * Called when a moderator edits a post.
	 *
	 * @param  int		$post_id		The post identifier
	 * @return array					The database row
	 * @access public
	 */
	public function topic_post_locked($post_id)
	{
		$sql = 'SELECT t.topic_poster, t.topic_status, p.post_edit_locked
				FROM ' . $this->table('posts') . ' p,
				' . $this->table('topics') . ' t
				WHERE p.topic_id = t.topic_id
					AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * Get avatars for provided user identifiers.
	 *
	 * @param  array	$user_ids		The user identifiers.
	 * @return array					Array of the users' avatars indexed per user identifier
	 * @access public
	 */
	public function get_avatars($user_ids)
	{
		$avatars = [];

		$sql = 'SELECT user_id, user_avatar, user_avatar_type, user_avatar_width, user_avatar_height
				FROM ' . $this->table('users') . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids, false, true) . '
					AND user_type <> ' . USER_IGNORE;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$avatars[(int) $row['user_id']] = phpbb_get_user_avatar($row);
		}
		$this->db->sql_freeresult($result);

		return $avatars;
	}

	/**
	 * Checks whether Advanced Points System is ran in Safe Mode,
	 * meaning that exceptions will be caught and logged instead of thrown.
	 * Safe mode should be turned "off" when testing and developing.
	 *
	 * @return bool					Whether APS is ran in Safe Mode or not.
	 * @access public
	 */
	public function safe_mode()
	{
		return (bool) $this->config['aps_points_safe_mode'];
	}

	/**
	 * Get a formatted points string according to the settings.
	 *
	 * @param  double	$points		The points to display
	 * @param  bool		$icon		Whether or not to also display the points icon
	 * @return string				The formatted points for display
	 * @access public
	 */
	public function display_points($points, $icon = true)
	{
		$separator_dec = htmlspecialchars_decode($this->config['aps_points_separator_dec']);
		$separator_thou = htmlspecialchars_decode($this->config['aps_points_separator_thou']);

		$points = number_format((double) $points, (int) $this->config['aps_points_decimals'], (string) $separator_dec, (string) $separator_thou);

		// If we do not want the icon, return now
		if (!$icon)
		{
			return $points;
		}

		// Get the icon side
		$right = (bool) $this->config['aps_points_icon_position'];

		return $right ? ($points . '&nbsp;' . $this->get_icon()) : ($this->get_icon() . '&nbsp;' . $points);
	}

	/**
	 * Format points for usage in input fields
	 *
	 * @param  double	$points		The points to format
	 * @return double
	 * @access public
	 */
	public function format_points($points)
	{
		return (double) round($points, (int) $this->config['aps_points_decimals']);
	}

	/**
	 * Get the points icon for display.
	 *
	 * @return string				The HTML formatted points icon
	 * @access public
	 */
	public function get_icon()
	{
		return '<i class="icon ' . (string) $this->config['aps_points_icon'] . ' fa-fw" title="' . $this->get_name() . '" aria-hidden="true"></i>';
	}

	/**
	 * Get the localised points name.
	 *
	 * @return string				The localised points name
	 * @access public
	 */
	public function get_name()
	{
		if (empty($this->name))
		{
			$key = 'aps_points_name_';

			$name = !empty($this->config[$key . $this->user->lang_name]) ? $this->config[$key . $this->user->lang_name] : $this->config[$key . $this->config['default_lang']];

			// Fallback
			$name = !empty($name) ? $name : 'Points';

			$this->name = $name;
		}

		return $this->name;
	}

	public function get_auth($name, $forum_id)
	{
		// Fix for template functions
		$forum_id = $forum_id === true ? 0 : $forum_id;

		return $this->auth->acl_get($name, $forum_id);
	}

	/**
	 * Get an config value for given config name.
	 *
	 * @param  string	$name		The APS config name
	 * @return string				The APS config value
	 * @access public
	 */
	public function get_config($name)
	{
		return $this->config->offsetGet($name);
	}

	/**
	 * Get the step amount for a numeric input field.
	 *
	 * @return double
	 * @access public
	 */
	public function get_step()
	{
		return round(substr_replace('001', '.', (3 - (int) $this->config['aps_points_decimals']), 0), $this->config['aps_points_decimals']);
	}

	/**
	 * Equates an array of points to a single points value.
	 *
	 * @param  array	$array		The array to equate
	 * @param  string	$operator	The equation operator
	 * @return double				The equated points value
	 * @access public
	 */
	public function equate_array(array $array, $operator = '+')
	{
		$result = array_reduce(
			$array,
			function($a, $b) use ($operator)
			{
				return $this->equate_points($a, $b, $operator);
			},
			0.00);

		return $result;
	}

	/**
	 * Equate two points by reference.
	 *
	 * @param  double	$a			The referenced points value
	 * @param  double	$b			The points value to equate
	 * @param  string	$operator	The equation operator
	 * @return void					Passed by reference
	 * @access public
	 */
	public function equate_reference(&$a, $b, $operator = '+')
	{
		$a = $this->equate_points($a, $b, $operator);
	}

	/**
	 * Equate two points.
	 *
	 * @param  double	$a			The points value to equate
	 * @param  double	$b			The points value to equate
	 * @param  string	$operator	The equation operator
	 * @return double				The equated points value
	 * @access public
	 */
	public function equate_points($a, $b, $operator = '+')
	{
		$b = $this->is_points($b) ? $b : 0;

		switch ($operator)
		{
			# Multiply
			case 'x':
			case '*';
				$a *= $b;
			break;

			# Divide
			case '÷':
			case '/':
				$a = $b ? $a / $b : 0;
			break;

			# Subtract
			case '-':
				$a -= $b;
			break;

			# Add
			case '+':
			default:
				$a += $b;
			break;
		}

		return (double) $a;
	}

	/**
	 * Check if a points value is numeric.
	 *
	 * @param  mixed	$points		The points value
	 * @return bool					Whether the value is numeric or not
	 * @access public
	 */
	public function is_points($points)
	{
		return is_numeric($points);
	}

	/**
	 * Checks whether a user's points are within the Min. and Max. allowed points.
	 *
	 * @param  double	$points			The new total
	 * @return double					The new total that is within the boundaries
	 * @access public
	 */
	public function boundaries($points)
	{
		// Check if the new total is lower than the minimum value, has to be '' as 0 is a valid minimum value.
		if (($min = $this->config['aps_points_min']) !== '')
		{
			$min = (double) $min;
			$points = $points < $min ? $min : $points;
		}

		// Check if the new total is higher than the maximum value, has to be '' as 0 is a valid maximum value.
		if (($max = $this->config['aps_points_max']) !== '')
		{
			$max = (double) $max;
			$points = $points > $max ? $max : $points;
		}

		return $points;
	}

	/**
	 * Get a default no_avatar HTML string.
	 *
	 * @return string					HTML formatted no_avatar string
	 * @access public
	 */
	public function get_no_avatar()
	{
		// If DAE is enabled we do not have to set up a default avatar
		if ($this->is_dae_enabled())
		{
			return '';
		}

		$board_url = generate_board_url() . '/';
		$corrected_path = $this->path_helper->get_web_root_path();
		$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $corrected_path;
		$theme_path = "{$web_path}styles/" . rawurlencode($this->user->style['style_path']) . '/theme';

		$no_avatar = '<img class="avatar" src="' . $theme_path . '/images/no_avatar.gif" alt="' . $this->lang->lang('USER_AVATAR') . '" />';

		return $no_avatar;
	}

	/**
	 * Checks whether Default Avatar Extended (DAE) is enabled or not.
	 *
	 * @return bool						Whether DAE is enabled or not.
	 * @access public
	 */
	public function is_dae_enabled()
	{
		return (bool) ($this->is_dae_enabled && $this->config['threedi_default_avatar_extended']);
	}
}
