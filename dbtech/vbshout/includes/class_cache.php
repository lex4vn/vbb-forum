<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2007-2009 Fillip Hannisdal AKA Revan/NeoRevan/Belazor # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #############################################################################
// cache functionality class

/**
* Class that handles keeping the database cache up to date.
*
* @package	Framework
* @version	$ $Rev$ $
* @date		$ $Date$ $
*/
class VBSHOUT_CACHE
{
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	private static $vbulletin 		= NULL;
	
	/**
	* The prefix for the mod we are working with
	*
	* @public	string
	*/	
	public static $prefix 			= 'dbtech_vbshout_';
	
	/**
	* Array of cache fields
	*
	* @public	array
	*/	
	public static $cachefields 		= array();

	/**
	* Array of items to fetch
	*
	* @protected	array
	*/	
	protected static $queryfields	= array();

	/**
	* Array of items to NOT fetch
	*
	* @protected	array
	*/	
	protected static $exclude		= array();
	
	
	
	/**
	* Initialises the database caching by setting the cache
	* list and begins verification of the data.
	*
	* @param	vB_Registry	Registry object
	* @param	string		Prefix
	* @param	array		(Optional) List of all cached arrays
	* @param	array		(Optional) List of values to not fetch
	*
	* @return	none		Nothing
	*/
	public function init($vbulletin, $cachefields = array(), $exclude = array())
	{
		// Check if the vBulletin Registry is an object
		if (is_object($vbulletin))
		{
			// Yep, all good
			self::$vbulletin =& $vbulletin;
		}
		else
		{
			// Something went wrong here I think
			trigger_error(__CLASS__ . "::Registry object is not an object", E_USER_ERROR);
		}
		
		// Set exclude
		self::$exclude = $exclude;
		
		if (count($cachefields) > 0)
		{
			foreach ($cachefields as $key => $title)
			{
				if (strpos($title, self::$prefix) === false)
				{
					// Get rid of the non-relevant fields
					unset($cachefields["$key"]);
				}
			}
			
			// Set the cleaned cachefields variable
			self::$cachefields = $cachefields;
		}
		
		if (count(self::$cachefields) == 0)
		{
			// We don't need this stuff
			return;
			
			// Something went wrong here I think
			//trigger_error("DBTech_Framework_Cache::Cachefields has no elements.", E_USER_ERROR);
		}
		
		// Check for valid info
		self::check_datastore();
		
		if (count(self::$queryfields) > 0)
		{
			// We need to re-query - prepare the string
			$itemlist = "'" . implode("','", self::$queryfields) . "'";
			
			if ($itemlist != "''")
			{
				// Do fetch from the database
				self::$vbulletin->datastore->do_db_fetch($itemlist);
			}
		}
		
		// Set the cache fields
		self::set_cache();		
	}

	/**
	* Builds the cache in case the datastore has been cleaned out.
	*
	* @param	string	Database table we are working with
	* @param	string	(Optional) Any additional clauses to the query
	*/
	public static function build_cache($type, $clauses = '')
	{
		// Premove the prefix
		$dbtype = self::$prefix . $type;

		// Initialise the some arrays so we can add to them quicker
		$data = array();

		// Prepare the variable for the identifier
		$firstrow = $type . 'id';
		
		self::$vbulletin->db->hide_errors();
		$default_query = self::$vbulletin->db->query_read("SELECT $dbtype.* FROM `" . TABLE_PREFIX . "$dbtype` AS $dbtype $clauses");
		while ($default = self::$vbulletin->db->fetch_array($default_query))
		{
			$data["$default[$firstrow]"]["$firstrow"] = $default["$firstrow"];
			foreach ($default as $key => $value)
			{
				// Loop through the query result and build the array
				$data["$default[$firstrow]"]["$key"] = addslashes($value);
			}
		}
		self::$vbulletin->db->free_result($default_query);
		self::$vbulletin->db->show_errors();
		
		// Finally update the datastore with the new value
		build_datastore($dbtype, serialize($data), 1);
		
		// Premove the prefix
		$field_short = substr($dbtype, strlen(self::$prefix));
		
		// Strip the slashes
		self::$vbulletin->input->stripslashes_deep($data);		
		
		// Set the data
		VBSHOUT::$cache["$field_short"] = $data;
		
		foreach ((array)VBSHOUT::$cache["$field_short"] as $id => $arr)
		{
			foreach ((array)VBSHOUT::$unserialize["$field_short"] as $key)
			{
				// Do unserialize
				VBSHOUT::$cache["$field_short"]["$id"]["$key"] = @unserialize(stripslashes($arr["$key"]));
			}
		}
	}
	
	/**
	* Checks whether or not datastore items are present,
	* and schedules for re-query if needed.
	*/
	private static function check_datastore()
	{
		foreach (self::$cachefields as $title)
		{
			if (strpos($title, self::$prefix) === false)
			{
				// We don't care.
				continue;
			}
			
			// Check if the value is set
			if (!isset(self::$vbulletin->$title))
			{
				if (in_array($title, self::$exclude))
				{
					// Skip this
					self::$vbulletin->$title = self::$exclude["$title"];
				}
				else
				{
					// It wasn't :(
					self::$queryfields[] = $title;
				
					// Build datastore
					self::build_cache(substr($title, strlen(self::$prefix)));
				}
			}
		}
	}
	
	/**
	* Sets the specified cache field after making sure all slashes
	* are stripped again
	*/
	private static function set_cache()
	{
		foreach (self::$cachefields as $field)
		{
			// Premove the prefix
			$field_short = substr($field, strlen(self::$prefix));
			
			// Fetch the data from the vB array
			$data = self::$vbulletin->$field;
			
			if (is_array($data))
			{
				// Strip the slashes
				self::$vbulletin->input->stripslashes_deep($data);

				// Unset from the vbulletin array to save memory
				unset(self::$vbulletin->$field);
			}
			else if (!in_array($field, self::$exclude))
			{
				// Ensure this is an array
				$data = array();
			}
			
			// Set the data
			VBSHOUT::$cache["$field_short"] = $data;
		}
	}	
}