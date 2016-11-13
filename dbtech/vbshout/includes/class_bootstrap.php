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
// bootstrapper for the framework

if (!class_exists('DBTech_Framework_Core'))
{
	// It's not been included before, fetch it
	require_once(dirname(__FILE__) . '/libs/core.php');
}

/**
* Class to initialise the framework
*
* @package	vBSHout
* @version	$ $Rev$ $
* @date		$ $Date$ $
*/
class vBShout_Bootstrap
{
	/**
	* Static function for initialising the framework
	*
	* @return	DBTech_Framework_Core	Instantiated framework core
	*/
	public static function init()
	{		
		return new DBTech_Framework_Core(dirname(__FILE__));
	}
}

/*======================================================================*\
|| ####################################################################
|| # Created: 23:48, Fri Dec 25th 2009
|| # SVN: $ $Rev$ $ on $ $Date$ $
|| ####################################################################
\*======================================================================*/