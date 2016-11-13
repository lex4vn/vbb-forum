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

// Grab the report id
$reportid = $vbulletin->input->clean_gpc('p', 'reportid', TYPE_UINT);

if (!$reportinfo = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dbtech_vbshout_report WHERE reportid = " . $db->sql_prepare($reportid)))
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_shoutid_specified')));
}

// Shorthand
$instance = VBSHOUT::$cache['instance']["$reportinfo[instanceid]"];

if (!$instance)
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_instanceid_specified')));
}

// Load our permissions
VBSHOUT::load_instance_permissions($instance);
VBSHOUT::load_instance_bbcodepermissions($instance);

if (!$instance['permissions_parsed']['canmodchat'])
{
	// Invalid chat room
	print_no_permission();
}

// Grab other things
$handled = $vbulletin->input->clean_gpc('p', 'handled', TYPE_BOOL);
$modnotes = $vbulletin->input->clean_gpc('p', 'modnotes', TYPE_STR);

($hook = vBulletinHook::fetch_hook('dbtech_vbshout_updatereport')) ? eval($hook) : false;

$db->query_write("
	UPDATE " . TABLE_PREFIX . "dbtech_vbshout_report
	SET
		handled = " . $db->sql_prepare($handled) . ",
		modnotes = " . $db->sql_prepare($modnotes) . "
		$hook_query_set
	WHERE reportid = " . $db->sql_prepare($reportid)
);

$vbulletin->url = 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=reportlist&instanceid=' . $reportinfo['instanceid'];
eval(print_standard_redirect('redirect_dbtech_vbshout_report_updated'));


/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>