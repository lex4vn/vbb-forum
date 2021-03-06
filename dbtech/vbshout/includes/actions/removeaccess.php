<?php

/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2007-2009 Fillip Hannisdal AKA Revan/NeoRevan/Belazor # ||
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

if (!$chatroom)
{
	// Invalid chat room
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_chatroomid_specified')));
}

if ($chatroom['creator'] != $vbulletin->userinfo['userid'])
{
	// No perms
	eval(standard_error(fetch_error('dbtech_vbshout_cannot_remove_user')));
}

// Grab the user id
$userid = $vbulletin->input->clean_gpc('r', 'userid', TYPE_UINT);

if ($userid == $vbulletin->userinfo['userid'])
{
	// No perms
	eval(standard_error(fetch_error('dbtech_vbshout_cannot_remove_self')));
}

// Leave the chat room
$vbshout->leave_chatroom($vbshout->cache['chatroom']["$chatroomid"], $vbulletin->GPC['userid']);

// Grab the user id
$ajax = $vbulletin->input->clean_gpc('r', 'ajax', TYPE_UINT);

if (!$ajax)
{
	$vbulletin->url = 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=chataccess&instanceid=' . $instanceid . '&chatroomid=' . $chatroomid;
	eval(print_standard_redirect('redirect_dbtech_vbshout_access_removed'));	
}

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>