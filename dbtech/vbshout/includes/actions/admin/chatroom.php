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

print_cp_header($vbphrase['dbtech_vbshout_chatroom_management']);

// Table header
$headings = array();
$headings[] = $vbphrase['title'];
$headings[] = $vbphrase['usergroups'];
$headings[] = $vbphrase['dbtech_vbshout_instance'];
$headings[] = $vbphrase['active'];

// Hook goes here

$headings[] = $vbphrase['edit'];


if (count($vbshout->cache['chatroom']))
{
	print_form_header('vbshout', 'modifychatroom');	
	print_table_header($vbphrase['dbtech_vbshout_chatroom_management'], count($headings));
	print_description_row($vbphrase['dbtech_vbshout_chatroom_management_descr'], false, count($headings));	
	print_cells_row($headings, 0, 'thead');
	
	foreach ($vbshout->cache['chatroom'] as $chatroomid => $chatroom)
	{
		if (!$chatroom['membergroupids'])
		{
			// This is an on-the-fly chatroom
			continue;
		}
		
		$usergroups = array();
		foreach(explode(',', $chatroom['membergroupids']) as $usergroupid)
		{
			// Usergroup cache
			$usergroups[] = $vbulletin->usergroupcache["$usergroupid"]['title'];
		}
		
		// Table data
		$cell = array();
		$cell[] = $chatroom['title'];
		$cell[] = implode(', ', $usergroups);
		$cell[] = ($chatroom['instanceid'] ? $vbshout->cache['instance']["$chatroom[instanceid]"] : $vbphrase['dbtech_vbshout_all_instances']);
		$cell[] = ($chatroom['active'] ? $vbphrase['yes'] : $vbphrase['no']);
		
		// Hook goes here
		
		$cell[] = construct_link_code($vbphrase['edit'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=modifychatroom&amp;chatroomid=' . $chatroomid);
		
		// Print the data
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
	}
	print_submit_row($vbphrase['dbtech_vbshout_add_new_chatroom'], false, count($headings));	
}
else
{
	print_form_header('vbshout', 'modifychatroom');	
	print_table_header($vbphrase['dbtech_vbshout_chatroom_management'], count($headings));
	print_description_row($vbphrase['dbtech_vbshout_no_chatrooms'], false, count($headings));
	print_submit_row($vbphrase['dbtech_vbshout_add_new_chatroom'], false, count($headings));	
}

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: chatroom.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>