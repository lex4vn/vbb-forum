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

// Grab stuff
$vbulletin->input->clean_array_gpc('p', array(
	'chatroomid' 		=> TYPE_UINT,
	'title' 			=> TYPE_STR,
	'membergroupids' 	=> TYPE_ARRAY_UINT,
	'instanceid'		=> TYPE_UINT,
	'active'			=> TYPE_UINT
));

$vbulletin->GPC['membergroupids'] = implode(',', $vbulletin->GPC['membergroupids']);
if ($vbulletin->GPC['chatroomid'])
{
	if (!$existing = $vbshout->cache['chatroom']["{$vbulletin->GPC[chatroomid]}"])
	{
		// Editing ID doesn't exist
		print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['dbtech_vbshout_chatroom'], $vbulletin->GPC['chatroomid']);
	}
	
	if (!$vbulletin->GPC['membergroupids'])
	{
		// Editing ID doesn't exist
		print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['usergroup'], $vbulletin->GPC['membergroupids']);
	}
	
	if ($existing = $db->query_first("
		SELECT `title`
		FROM `" . TABLE_PREFIX . "dbtech_vbshout_chatroom`
		WHERE `title` = " . $db->sql_prepare($vbulletin->GPC['title']) . "
			AND `chatroomid` != " . $db->sql_prepare($vbulletin->GPC['chatroomid']) . "
		LIMIT 1
	"))
	{
		// Whoopsie, exists
		//print_stop_message('dbtech_vbshout_x_already_exists_y', $vbphrase['dbtech_vbshout_chatroom'], $existing['title']);
	}
	
	// Update the database
	$db->query_write("
		UPDATE `" . TABLE_PREFIX . "dbtech_vbshout_chatroom`
		SET
			`title` = " . $db->sql_prepare($vbulletin->GPC['title']) . ",
			`membergroupids` = " . $db->sql_prepare($vbulletin->GPC['membergroupids']) . ",
			`instanceid` = " . $db->sql_prepare($vbulletin->GPC['instanceid']) . ",
			`active` = " . $db->sql_prepare($vbulletin->GPC['active']) . "
		WHERE `chatroomid` = " . $db->sql_prepare($vbulletin->GPC['chatroomid'])
	);
	
	// Set redirect phrase
	$phrase = $vbphrase['dbtech_vbshout_edited'];	
}
else
{
	if ($existing = $db->query_first("
		SELECT `title`
		FROM `" . TABLE_PREFIX . "dbtech_vbshout_chatroom`
		WHERE `title` = " . $db->sql_prepare($vbulletin->GPC['title']) . "
		LIMIT 1
	"))
	{
		// Whoopsie, exists
		//print_stop_message('dbtech_vbshout_x_already_exists_y', $vbphrase['dbtech_vbshout_chatroom'], $existing['title']);
	}
	
	// Update the database
	$db->query_write("
		INSERT INTO `" . TABLE_PREFIX . "dbtech_vbshout_chatroom`
			(`title`, `membergroupids`, `instanceid`, `active`)
		VALUES
			(
				" . $db->sql_prepare($vbulletin->GPC['title']) . ",
				" . $db->sql_prepare($vbulletin->GPC['membergroupids']) . ",
				" . $db->sql_prepare($vbulletin->GPC['instanceid']) . ",
				" . $db->sql_prepare($vbulletin->GPC['active']) . "
			)
	");
	
	// Set redirect phrase
	$phrase = $vbphrase['dbtech_vbshout_added'];
}

// Rebuild the cache
$vbshout->build_cache('dbtech_vbshout_chatroom');

define('CP_REDIRECT', 'vbshout.php?do=chatroom');
print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_chatroom'], $phrase);


/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: chatroom.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>