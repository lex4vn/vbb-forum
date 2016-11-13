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
class DBTech_Framework_Cache
{
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	private $registry 		= NULL;
	
	/**
	* The prefix for the mod we are working with
	*
	* @public	string
	*/	
	public $prefix 			= 'dbtech_';
	
	/**
	* Array of cache fields
	*
	* @public	array
	*/	
	public $cachefields 	= array();

	/**
	* Array of items to fetch
	*
	* @protected	array
	*/	
	protected $queryfields	= array();

	/**
	* Array of items to NOT fetch
	*
	* @protected	array
	*/	
	protected $exclude	= array();
	
	/**
	* Array of cached items
	*
	* @public	array
	*/		
	public $cache			= array();
	
	
	
	/**
	* Constructor. Captures the argument from the constructing function
	* and passes it along to the initialiser.
	*/
	public function __construct()
	{
		// Tarp the argument array
		$args = func_get_arg(0);
		
		// Get rid of the class name, we don't need their kind around here
		unset($args[0]);
		
		// Now initialise everything
		call_user_func_array(array($this, 'init'), $args);
	}
	
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
	private function init($registry, $prefix, $cachefields = array(), $exclude = array())
	{
		// Check if the vBulletin Registry is an object
		if (is_object($registry))
		{
			// Yep, all good
			$this->registry =& $registry;
		}
		else
		{
			// Something went wrong here I think
			trigger_error("DBTech_Framework_Cache::Registry object is not an object", E_USER_ERROR);
		}
		
		// Set exclude
		$this->exclude = $exclude;
		
		// Update the prefix
		$this->prefix .= $prefix . '_';
		
		if (count($cachefields) > 0)
		{
			foreach ($cachefields as $key => $title)
			{
				if (strpos($title, $this->prefix) === false)
				{
					// Get rid of the non-relevant fields
					unset($cachefields["$key"]);
				}
			}
			
			// Set the cleaned cachefields variable
			$this->cachefields = $cachefields;
		}
		
		if (count($this->cachefields) == 0)
		{
			// We don't need this stuff
			return;
			
			// Something went wrong here I think
			//trigger_error("DBTech_Framework_Cache::Cachefields has no elements.", E_USER_ERROR);
		}
		
		// Check for valid info
		$this->check_datastore();
		
		if (count($this->queryfields) > 0)
		{
			// We need to re-query - prepare the string
			$itemlist = "'" . implode("','", $this->queryfields) . "'";
			
			if ($itemlist != "''")
			{
				// Do fetch from the database
				$this->registry->datastore->do_db_fetch($itemlist);
			}
		}
		
		// Set the cache fields
		$this->set_cache();		
	}
	
	/**
	* Checks whether or not datastore items are present,
	* and schedules for re-query if needed.
	*/
	private function check_datastore()
	{
		foreach ($this->cachefields as $title)
		{
			if (strpos($title, $this->prefix) === false)
			{
				//die(print_r($this->registry->$title));
				// We don't care.
				continue;
			}
			
			// Check if the value is set
			if (!isset($this->registry->$title))
			{
				if (in_array($title, $this->exclude))
				{
					// Skip this
					$this->registry->$title = $this->exclude["$title"];
				}
				else
				{
					// It wasn't :(
					$this->queryfields[] = $title;
				
					// Build datastore
					$this->build_cache($title);
				}
			}
		}
	}

	/**
	* Builds the cache in case the datastore has been cleaned out.
	*
	* @param	string	Database table we are working with
	* @param	string	(Optional) Any additional clauses to the query
	*/
	public function build_cache($dbtype, $clauses = '')
	{
		// Premove the prefix
		$type = substr($dbtype, strlen($this->prefix));

		// Tamper with the names to make them fit the table names in the database
		switch ($dbtype)
		{
			default:
				// Do nothing
				break;
		}
		
		// Initialise the some arrays so we can add to them quicker
		$data = array();

		// Prepare the variable for the identifier
		$firstrow = $type . 'id';
		
		$this->registry->db->hide_errors();
		$default_query = $this->registry->db->query_read("SELECT $dbtype.* FROM `" . TABLE_PREFIX . "$dbtype` AS $dbtype $clauses");
		while ($default = $this->registry->db->fetch_array($default_query))
		{
			$data["$default[$firstrow]"]["$firstrow"] = $default["$firstrow"];
			foreach ($default as $key => $value)
			{
				// Loop through the query result and build the array
				$data["$default[$firstrow]"]["$key"] = addslashes($value);
			}
		}
		$this->registry->db->free_result($default_query);
		$this->registry->db->show_errors();
		
		// Finally update the datastore with the new value
		build_datastore($dbtype, serialize($data), 1);
	}
	
	/**
	* Sets the specified cache field after making sure all slashes
	* are stripped again
	*/
	private function set_cache()
	{
		foreach ($this->cachefields as $field)
		{
			// Premove the prefix
			$field_short = substr($field, strlen($this->prefix));
			
			// Fetch the data from the vB array
			$data = $this->registry->$field;
			
			if (is_array($data))
			{
				// Strip the slashes from the array
				$this->registry->input->stripslashes_deep($data);
				
				// Unset from the registry array to save memory
				unset($this->registry->$field);
			}
			
			// Set the data
			$this->cache["$field_short"] = $data;
		}
	}	
}