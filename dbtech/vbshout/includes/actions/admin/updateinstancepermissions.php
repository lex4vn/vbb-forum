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

// Grab stuff
$vbulletin->input->clean_array_gpc('p', array(
	'permissions' 		=> TYPE_ARRAY,
	'bbcodepermissions' => TYPE_ARRAY,
	'usergrouplist' 	=> TYPE_ARRAY
));

// Ensure we can fetch bitfields
require_once(DIR . '/includes/adminfunctions_options.php');
$bitfields = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
$bitfields2 = fetch_bitfield_definitions('nocache|allowedbbcodesfull');

foreach ($vbulletin->GPC['permissions'] as $instanceid => $permissions)
{
	if (!$existing = $vbshout->cache['instance']["$instanceid"])
	{
		// Editing ID doesn't exist
		continue;
	}
	
	// Begin array of permissions
	$permarray = @unserialize($existing['permissions']);
	$permarray = (is_array($permarray) ? $permarray : array());
	
	if (empty($vbulletin->GPC['usergrouplist']))
	{
		foreach ($permissions as $usergroupid => $permvalues)
		{
			$permarray["$usergroupid"] = 0;
			foreach ($permvalues as $bitfield => $bit)
			{
				// Update the permissions array
				$permarray["$usergroupid"] += ($bit ? $bitfields["$bitfield"] : 0);
			}
		}
	}
	else
	{
		$permvalue = 0;
		foreach ($permissions as $bitfield => $bit)
		{
			// Update the permissions array
			$permvalue += ($bit ? $bitfields["$bitfield"] : 0);
		}
		
		foreach ($vbulletin->GPC['usergrouplist'] as $usergroupid => $onoff)
		{
			// Now store the permissions array
			$permarray["$usergroupid"] = $permvalue;
		}
	}
	
	if (file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml'))
	{
		$SQL = "WHERE `instanceid` = " . $db->sql_prepare($instanceid);
	}
	
	// Update the database
	$db->query_write("
		UPDATE `" . TABLE_PREFIX . "dbtech_vbshout_instance`
		SET
			`permissions` = '" . $db->escape_string(trim(serialize($permarray))) . "'
		$SQL
	");
}

foreach ($vbulletin->GPC['bbcodepermissions'] as $instanceid => $permissions)
{
	if (!$existing = $vbshout->cache['instance']["$instanceid"])
	{
		// Editing ID doesn't exist
		continue;
	}
	
	// Begin array of permissions
	$permarray = @unserialize($existing['bbcodepermissions']);
	$permarray = (is_array($permarray) ? $permarray : array());
	
	if (empty($vbulletin->GPC['usergrouplist']))
	{
		foreach ($permissions as $usergroupid => $permvalues)
		{
			$permarray["$usergroupid"] = 0;
			foreach ($permvalues as $bitfield => $bit)
			{
				// Update the permissions array
				$permarray["$usergroupid"] += ($bit ? $bitfields2["$bitfield"] : 0);
			}
			//print_r($permarray);
		}
	}
	else
	{
		$permvalue = 0;
		foreach ($permissions as $bitfield => $bit)
		{
			// Update the permissions array
			$permvalue += ($bit ? $bitfields2["$bitfield"] : 0);
		}
		
		foreach ($vbulletin->GPC['usergrouplist'] as $usergroupid => $onoff)
		{
			// Now store the permissions array
			$permarray["$usergroupid"] = $permvalue;
		}
	}
	//print_r($permarray);
	//die();
	
	if (file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml'))
	{
		$SQL = "WHERE `instanceid` = " . $db->sql_prepare($instanceid);
	}
	
	// Update the database
	$db->query_write("
		UPDATE `" . TABLE_PREFIX . "dbtech_vbshout_instance`
		SET
			`bbcodepermissions` = '" . $db->escape_string(trim(serialize($permarray))) . "'
		$SQL
	");
}
$vbshout->build_cache('dbtech_vbshout_instance');

define('CP_REDIRECT', 'vbshout.php?do=instance');
print_stop_message('dbtech_vbshout_x_y', $vbphrase['permissions'], $vbphrase['dbtech_vbshout_edited']);

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: instance.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>