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

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$instances = array();
$instances[0] = $vbphrase['dbtech_vbshout_all_instances'];
if (file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml'))
{
	foreach ($vbshout->cache['instance'] as $instanceid => $instance)
	{
		// Store the instance
		$instances["$instanceid"] = $instance['name'];
	}	
}
asort($instances);

$chatroomid = $vbulletin->input->clean_gpc('r', 'chatroomid', TYPE_UINT);
$chatroom = ($chatroomid ? $vbshout->cache['chatroom']["$chatroomid"] : false);

if (!is_array($chatroom))
{
	// Non-existing chatroom
	$chatroomid = 0;
}

if ($chatroomid)
{
	// Edit
	print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['dbtech_vbshout_chatroom'], $chatroom['title'])));
	print_form_header('vbshout', 'updatechatroom');
	construct_hidden_code('chatroomid', $chatroomid);
	print_table_header(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['dbtech_vbshout_chatroom'], $chatroom['title']));
}
else
{
	// Add
	print_cp_header($vbphrase['dbtech_vbshout_add_new_chatroom']);
	print_form_header('vbshout', 'updatechatroom');	
	print_table_header($vbphrase['dbtech_vbshout_add_new_chatroom']);
}

print_input_row($vbphrase['title'], 					'title', 					$chatroom['title']);
print_membergroup_row($vbphrase['usergroups'], 			'membergroupids', 2, 		$chatroom);
print_select_row($vbphrase['dbtech_vbshout_instance'], 	'instanceid', $instances,	$chatroom['displayorder']);
print_yes_no_row($vbphrase['active'],					'active',					$chatroom['active']);

print_submit_row(($chatroomid ? $vbphrase['save'] : $vbphrase['dbtech_vbshout_add_new_chatroom']));
print_cp_footer();


/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: chatroom.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>