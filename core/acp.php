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
 * phpBB Studio - Advanced Points System ACP functions.
 */
class acp
{
	/** @var \phpbbstudio\aps\core\functions */
	protected $functions;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var array Array of action types from the service collection */
	protected $types = [];

	/** @var \phpbbstudio\aps\points\valuator */
	protected $valuator;

	/** @var array Array of template blocks for the action types */
	protected $blocks = [];

	/** @var array Array of type fields defined by their scope. 0: Local, 1: Global */
	protected $fields = [0 => [], 1 => []];

	/**
	 * Constructor.
	 *
	 * @param  \phpbbstudio\aps\core\functions			$functions	APS Core functions
	 * @param  \phpbb\template\template					$template	Template object
	 * @param  \phpbbstudio\aps\actions\type\action[]	$types		Array of action types from the service collection
	 * @param  \phpbbstudio\aps\points\valuator			$valuator	APS Valuator object
	 * @return void
	 * @access public
	 */
	public function __construct(
		functions $functions,
		\phpbb\template\template $template,
		$types,
		\phpbbstudio\aps\points\valuator $valuator
	)
	{
		$this->functions	= $functions;
		$this->template		= $template;
		$this->types		= $types;
		$this->valuator		= $valuator;
	}

	/**
	 * Returns the list of fields for the points list.
	 *
	 * @return array						Array of action types from the service collection
	 * @access public
	 */
	public function get_fields()
	{
		return $this->fields;
	}

	/**
	 * Initiate a build for a points list for the in the ACP.
	 *
	 * @param  int|null		$forum_id		Forum identifier
	 * @param  string		$block_name		The name for the template block
	 * @return void
	 * @access public
	 */
	public function build($forum_id = null, $block_name = 'aps_categories')
	{
		$this->build_list(is_null($forum_id));

		$this->assign_blocks($block_name);

		$this->assign_values($forum_id);
	}

	/**
	 * Build a local|global points list for in the ACP.
	 *
	 * @param  bool		$global		Whether we are building a global or local list
	 * @return void
	 * @access public
	 */
	public function build_list($global)
	{
		/** @var \phpbbstudio\aps\actions\type\action $type */
		foreach ($this->types as $type)
		{
			if ($type->is_global() === $global)
			{
				$this->fields[(int) $type->is_global()] = array_merge($this->fields[(int) $type->is_global()], $type->get_fields());

				if (empty($this->blocks[$type->get_category()]))
				{
					$this->blocks[$type->get_category()] = [
						$type->get_action() => $type->get_data(),
					];
				}
				else
				{
					if (empty($this->blocks[$type->get_category()][$type->get_action()]))
					{
						$this->blocks[$type->get_category()][$type->get_action()] = $type->get_data();
					}
					else
					{
						$this->blocks[$type->get_category()][$type->get_action()] += $type->get_data();
					}
				}
			}
		}
	}

	/**
	 * Assign the points list to the template.
	 *
	 * @param  string	$block_name		The name for the template block
	 * @return void
	 * @access public
	 */
	public function assign_blocks($block_name = 'aps_categories')
	{
		foreach ($this->blocks as $category => $blocks)
		{
			$this->template->assign_block_vars($block_name, [
				'title'		=> $category,
				'blocks'	=> $blocks,
			]);
		}
	}

	/**
	 * Assign the point values to the template.
	 *
	 * @param  int		$forum_id		The forum identifier
	 * @return array					The point values
	 * @access public
	 */
	public function assign_values($forum_id)
	{
		$values = $this->valuator->get_points($this->fields, (int) $forum_id);
		$values = $values[(int) $forum_id];

		// Clean upon assignment, as this possible runs more often than submission
		$this->valuator->clean_points(array_keys($values), (int) $forum_id);

		$this->template->assign_vars([
			'APS_VALUES'	=> $values,
		]);

		return $values;
	}

	/**
	 * Sets the point values for a given forum identifier.
	 *
	 * @param  array	$points			The points to set
	 * @param  int		$forum_id		The forum identifier
	 * @return void
	 * @access public
	 */
	public function set_points(array $points, $forum_id = 0)
	{
		$this->valuator->set_points($points, (int) $forum_id);
	}

	/**
	 * Delete the point values in the database for a specific forum.
	 *
	 * @param  int		$forum_id		The forum identifier
	 * @return void
	 * @access public
	 */
	public function delete_points($forum_id)
	{
		$this->valuator->delete_points($forum_id);
	}

	/**
	 * Copy the point values from one forum to an other.
	 *
	 * @param  int		$from		The from forum identifier
	 * @param  int		$to			The to forum identifier
	 * @param  array	$points		The point values to copy
	 * @return void
	 * @access public
	 */
	public function copy_points($from, $to, array $points)
	{
		$points = [0 => array_keys($points)];
		$points = $this->valuator->get_points($points, (int) $from);
		$points = $points[(int) $from];

		$this->valuator->set_points($points, $to);
	}

	/**
	 * Copy the point values from one forum to multiple others.
	 *
	 * @param  int		$from		The from forum identifier
	 * @param  int		$to			The to forum identifier
	 * @return void
	 * @access public
	 */
	public function copy_multiple($from, $to)
	{
		$this->valuator->copy_points($from, $to);
	}

	/**
	 * Clean the points table.
	 *
	 * @return void
	 * @access public
	 */
	public function clean_points()
	{
		/** @var \phpbbstudio\aps\actions\type\action $type */
		foreach ($this->types as $type)
		{
			$this->fields[(int) $type->is_global()] = array_merge($this->fields[(int) $type->is_global()], $type->get_fields());
		}

		$this->valuator->clean_all_points($this->fields);
	}
}
