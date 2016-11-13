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

// Grab the instance id
$instanceid = $vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);

// Shorthand
$instance = $vbshout->cache['instance']["$instanceid"];

if (!$instance)
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_instanceid_specified')));
}

// Init permissions
$vbshout->init_permissions($instance['permissions']);

// Grab the chat room id
$chatroomid = $vbulletin->input->clean_gpc('r', 'chatroomid', TYPE_UINT);

// Shorthand
$chatroom = $vbshout->cache['chatroom']["$chatroomid"];

if (!$chatroom OR $chatroom['membergroupids'])
{
	// Invalid chat room
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_chatroomid_specified')));
}

if (!$vbshout->permissions['canmodchat'])
{
	// Gtfo.
	eval(standard_error(fetch_error('dbtech_vbshout_cannot_access_list')));
}

// Leave the chat room
$vbshout->leave_chatroom($chatroom, $chatroom['creator']);

// Build the cache
$vbshout->build_cache('dbtech_vbshout_chatroom');

// Grab the user id
$ajax = $vbulletin->input->clean_gpc('r', 'ajax', TYPE_UINT);

if (!$ajax)
{
	$vbulletin->url = 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=chatlist&instanceid=' . $instanceid;
	eval(print_standard_redirect('redirect_dbtech_vbshout_chat_room_disbanded'));	
}

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>