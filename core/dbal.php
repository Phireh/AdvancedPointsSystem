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
 * phpBB Studio - Advanced Points System DBAL.
 */
class dbal
{
	/** @var string The name of the sql layer */
	protected $layer;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\db\driver\driver_interface	$db		Database object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db)
	{
		$this->layer = $db->get_sql_layer();
	}

	/**
	 * Get the "random"-function for the current SQL layer.
	 *
	 * @return string				The "random"-function
	 * @access public
	 */
	public function random()
	{
		switch ($this->layer)
		{
			case 'postgres':
				return 'RANDOM()';
			break;

			case 'mssql':
			case 'mssql_odbc':
				return 'NEWID()';
			break;

			default:
				return 'RAND()';
			break;
		}
	}

	/**
	 * Get the "month from a UNIX timestamp"-function for the current SQL layer.
	 *
	 * @param  string	$column		The column name holding the UNIX timestamp
	 * @return string				The "month from a UNIX timestamp"-function
	 * @access public
	 */
	public function unix_to_month($column)
	{
		switch ($this->layer)
		{
			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
				return 'DATEADD(m, ' . $column . ', 19700101)';
			break;

			case 'postgres':
				return 'extract(month from to_timestamp(' . $column . '))';
			break;

			case 'sqlite3':
				return "strftime('%m', datetime(" . $column . ", 'unixepoch'))";
			break;

			default:
				return 'MONTH(FROM_UNIXTIME(' . $column . '))';
			break;
		}
	}

	/**
	 * Get the "year from a UNIX timestamp"-function for the current SQL layer.
	 *
	 * @param  string	$column		The column name holding the UNIX timestamp
	 * @return string				The "year from a UNIX timestamp"-function
	 * @access public
	 */
	public function unix_to_year($column)
	{
		switch ($this->layer)
		{
			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
				return 'DATEADD(y, ' . $column . ', 19700101)';
			break;

			case 'postgres':
				return 'extract(year from to_timestamp(' . $column . '))';
			break;

			case 'sqlite3':
				return "strftime('%y', datetime(" . $column . ", 'unixepoch'))";
			break;

			default:
				return 'YEAR(FROM_UNIXTIME(' . $column . '))';
			break;
		}
	}
}
