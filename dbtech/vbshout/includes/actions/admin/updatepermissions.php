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
	'forum' 	=> TYPE_ARRAY,
));

foreach ($vbulletin->GPC['forum'] as $instanceid => $forum)
{
	$SQL = array();	
	foreach ($forum as $forumid => $config)
	{
		foreach ($config as $val)
		{
			// Notice flag
			$SQL["$forumid"] += $val;
		}
	}
	
	if (!empty($SQL))
	{
		// Will never return false but meh
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "dbtech_vbshout_instance
			SET notices = '" . $db->escape_string(trim(serialize($SQL))) . "'
			WHERE instanceid = " . $db->sql_prepare($instanceid)
		);
	}
}

// Build instance cache
$vbshout->build_cache('dbtech_vbshout_instance');

define('CP_REDIRECT', 'vbshout.php?do=permissions');
print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_permissions'], $vbphrase['dbtech_vbshout_edited']);

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: instance.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>