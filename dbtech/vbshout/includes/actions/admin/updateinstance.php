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
	'instanceid' 	=> TYPE_UINT,
	'active' 		=> TYPE_UINT,
	'name' 			=> TYPE_STR,
	'description' 	=> TYPE_STR,
	//'sticky' 		=> TYPE_STR,
	'autodisplay'	=> TYPE_UINT,
	'deployment'	=> TYPE_STR,
	'shoutsound'	=> TYPE_STR,
	'invitesound'	=> TYPE_STR,
	'pmsound'		=> TYPE_STR,
	'templates'		=> TYPE_STR
));

if ($vbulletin->GPC['instanceid'])
{
	if (!$existing = $vbshout->cache['instance']["{$vbulletin->GPC[instanceid]}"])
	{
		// Editing ID doesn't exist
		print_stop_message('dbtech_vbshout_invalid_x', $vbphrase['dbtech_vbshout_instance'], $vbulletin->GPC['instanceid']);
	}
	
	if ($existing = $db->query_first("
		SELECT `name`
		FROM `" . TABLE_PREFIX . "dbtech_vbshout_instance`
		WHERE `name` = " . $db->sql_prepare($vbulletin->GPC['name']) . "
			AND `instanceid` != " . $db->sql_prepare($vbulletin->GPC['instanceid']) . "
		LIMIT 1
	"))
	{
		// Whoopsie, exists
		print_stop_message('dbtech_vbshout_x_already_exists_y', $vbphrase['dbtech_vbshout_instance'], $existing['name']);
	}
	
	###`sticky` = " . $db->sql_prepare($vbulletin->GPC['sticky']) . ",
	// Update the database
	$db->query_write("
		UPDATE `" . TABLE_PREFIX . "dbtech_vbshout_instance`
		SET
			`name` = " . $db->sql_prepare($vbulletin->GPC['name']) . ",
			`active` = " . $db->sql_prepare($vbulletin->GPC['active']) . ",
			`description` = " . $db->sql_prepare($vbulletin->GPC['description']) . ",
			`autodisplay` = " . $db->sql_prepare($vbulletin->GPC['autodisplay']) . ",
			`deployment` = " . $db->sql_prepare($vbulletin->GPC['deployment']) . ",
			`shoutsound` = " . $db->sql_prepare($vbulletin->GPC['shoutsound']) . ",
			`invitesound` = " . $db->sql_prepare($vbulletin->GPC['invitesound']) . ",
			`pmsound` = " . $db->sql_prepare($vbulletin->GPC['pmsound']) . ",
			`templates` = " . $db->sql_prepare($vbulletin->GPC['templates']) . "
		WHERE `instanceid` = " . $db->sql_prepare($vbulletin->GPC['instanceid'])
	);
	
	// Set redirect phrase
	$phrase = $vbphrase['dbtech_vbshout_edited'];	
}
else
{
	// Add
	if ($existing = $db->query_first("
		SELECT `name`
		FROM `" . TABLE_PREFIX . "dbtech_vbshout_instance`
		WHERE `name` = " . $db->sql_prepare($vbulletin->GPC['name']) . "
		LIMIT 1
	"))
	{
		// Whoopsie, exists
		print_stop_message('dbtech_vbshout_x_already_exists_y', $vbphrase['dbtech_vbshout_instance'], $existing['name']);
	}
	
	// Update the database
	$db->query_write("
		INSERT INTO `" . TABLE_PREFIX . "dbtech_vbshout_instance`
			(`name`, `active`, `description`, `autodisplay`, `deployment`, `shoutsound`, `invitesound`, `pmsound`, `templates`)
		VALUES
			(
				" . $db->sql_prepare($vbulletin->GPC['name']) . ",
				" . $db->sql_prepare($vbulletin->GPC['active']) . ",
				" . $db->sql_prepare($vbulletin->GPC['description']) . ",
				" . $db->sql_prepare($vbulletin->GPC['autodisplay']) . ",
				" . $db->sql_prepare($vbulletin->GPC['deployment']) . ",
				" . $db->sql_prepare($vbulletin->GPC['shoutsound']) . ",
				" . $db->sql_prepare($vbulletin->GPC['invitesound']) . ",
				" . $db->sql_prepare($vbulletin->GPC['pmsound']) . ",
				" . $db->sql_prepare($vbulletin->GPC['templates']) . "
			)
	");
	
	// Set redirect phrase
	$phrase = $vbphrase['dbtech_vbshout_added'];
}
$vbshout->build_cache('dbtech_vbshout_instance');

define('CP_REDIRECT', 'vbshout.php?do=instance');
print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_instance'], $phrase);


/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: instance.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>