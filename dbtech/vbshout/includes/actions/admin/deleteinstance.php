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

$vbulletin->input->clean_array_gpc('r', array(
	'instanceid' => TYPE_UINT,
	'kill' 		 => TYPE_BOOL
));

if (!$vbulletin->GPC['kill'])
{
	print_cp_header(construct_phrase($vbphrase['dbtech_vbshout_delete_x'], $vbphrase['dbtech_vbshout_instance']));
	print_delete_confirmation('dbtech_vbshout_instance', $vbulletin->GPC['instanceid'], 'vbshout', 'deleteinstance', 'dbtech_vbshout_instance', array('kill' => true), '', 'name');
	print_cp_footer();
}
else
{
	// ###################### Start Kill #######################
	$db->query_write("
		DELETE FROM `" . TABLE_PREFIX . "dbtech_vbshout_instance`
		WHERE `instanceid` = " . $db->sql_prepare($vbulletin->GPC['instanceid'])
	);
	$db->query_write("
		DELETE FROM `" . TABLE_PREFIX . "dbtech_vbshout_shout`
		WHERE `instanceid` = " . $db->sql_prepare($vbulletin->GPC['instanceid'])
	);
	$vbshout->build_cache('dbtech_vbshout_instance');
	
	// Rebuild shout counters
	$vbshout->build_shouts_counter();
		
	define('CP_REDIRECT', 'vbshout.php?do=instance');
	print_stop_message('dbtech_vbshout_x_y', $vbphrase['dbtech_vbshout_instance'], $vbphrase['dbtech_vbshout_deleted']);
}

/*======================================================================*\
|| #################################################################### ||
|| # Created: 16:52, Thu Sep 18th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>