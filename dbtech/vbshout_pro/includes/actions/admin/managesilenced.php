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

// Fetch the list of users
$users = $vbulletin->input->clean_gpc('p', 'users', TYPE_ARRAY_UINT);

if (!count($users))
{
	// We weren't unbanning anything
	print_stop_message('nothing_to_do');
}

// Grab all the userids
$users = array_keys($users);

// Unban all the users
$db->query_write("
	UPDATE " . TABLE_PREFIX . "user
	SET dbtech_vbshout_silenced = 0
	WHERE userid IN(" . implode(',', $users) . ")
");

foreach ($users as $userid)
{
	// Log the unbanning
	$vbshout->log_command('unsilence', $userid);
}

foreach ((array)$vbshout->cache['instance'] as $instanceid => $instance)
{
	// We've changed shit
	$vbshout->set_aop('shouts', $instanceid, false);
	$vbshout->set_aop('shoutnotifs', $instanceid, false);
}

define('CP_REDIRECT', 'vbshout.php?do=viewsilenced');
print_stop_message('dbtech_vbshout_users_unsilenced');

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>