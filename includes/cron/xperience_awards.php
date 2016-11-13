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

if ($vbulletin->options['xperience_enabled'] || $vbulletin->options['xperience_use_awards']) { 
		
		
	require_once(DIR . '/includes/class_xperience.php');

	$xPerience =& new xPerience;
	$xPerience->CalculateAwards();


	log_cron_action('Experience awards calculated', $nextitem);
}


?>