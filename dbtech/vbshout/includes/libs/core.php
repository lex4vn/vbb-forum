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
// core functionality class

/**
* Class to act as a bridge to other classes.
*
* @package	Framework
* @version	$ $Rev$ $
* @date		$ $Date$ $
*/
class DBTech_Framework_Core
{
	/**
	* Array of loaded classes
	*
	* @public	array
	*/	
	public $loaded_classes 	= array();
	
	/**
	* Constructor. Tells the class where we're at.
	*
	* @param	string	The path we're in atm
	*/
	public function __construct($cwd)
	{
		// Set the CWD
		$this->cwd = $cwd;
	}
	
	/**
	* Loads a parameter-less class from our libraries
	*
	* @param	string	The name of the class to load
	*/
	public function load_class($classname)
	{
		// Set error handler
		set_error_handler('vb_error_handler');
		
		// Get the full name of the class
		$fullname = 'DBTech_Framework_' . ucfirst($classname);
		
		if (!class_exists($fullname))
		{
			// It's not been included before, fetch it
			require_once($this->cwd . '/libs/' . $classname . '.php');
		}
		
		// Instantiate the new class
		$this->$classname = new $fullname(func_get_args());
		
		// Store the loaded classes
		$this->loaded_classes[] = $classname;
	}
	
	/**
	* Wrapper for load_class that also loads all the final classes
	*
	* @param	string		The name of the class to load
	* @param	vB_Registry	Registry object
	*/
	public function load_final_class($classname, $registry)
	{
		// Now load the actual class
		$this->load_class($classname, $registry, $this->get_loaded_classes());
	}
	
	/**
	* Fetches the objects of all our loaded classes
	*
	* @return	array	All our loaded classes and their objects
	*/
	private function get_loaded_classes()
	{
		// Init the array
		$classobjs = array();
		
		foreach ($this->loaded_classes as $classname)
		{
			// Store the objects
			$classobjs["$classname"] = $this->$classname;
		}
		
		// Finally return all the objects
		return $classobjs;
	}

	/**
	* Same as array_search() only works with recursive arrays.
	*
	* @param	mixed	The element to search for
	* @param	array	The array in which to search for it
	*
	* @return	mixed	False if a matching key was not found, key otherwise
	*/
	public function array_search_multi($needle, $haystack)
	{
		if (is_array($haystack))
		{
			foreach ($haystack as $key => $value)
			{
				$result = $this->array_search_multi($needle, $value);
				if ($result == true)
				{
					return $key;
				}
			}
			return false;
		}
		else
		{
			if (trim($needle) != trim($haystack))
			{
				return false;
			}
			return true;
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Created: 23:48, Fri Dec 25th 2009
|| # SVN: $ $Rev$ $ on $ $Date$ $
|| ####################################################################
\*======================================================================*/