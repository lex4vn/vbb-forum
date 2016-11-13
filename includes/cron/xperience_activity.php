<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz. All Rights Reserved.             ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
	
	if ($vbulletin->options['xperience_enabled']) { 
	
	require_once(DIR . '/includes/functions_xperience.php');
	require_once(DIR . '/includes/class_xperience.php');

	$xPerience =& new xPerience;
	$avgppd = $xPerience->GetAVGPPD($vbulletin->options['xperience_ppd_days']);
	$avgppd30 = $xPerience->GetAVGPPD(30);
	$avgppd7 = $xPerience->GetAVGPPD(7);
	
	
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd." WHERE varname='xperience_avgppd'");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd30." WHERE varname='xperience_avgppd30'");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd7." WHERE varname='xperience_avgppd7'");
	
	log_cron_action('xPerience PPD calculated', $nextitem);
}

?>