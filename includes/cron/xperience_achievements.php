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

if ($vbulletin->options['xperience_enabled'] || $vbulletin->options['xperience_use_achievements']) { 
		
		
	require_once(DIR . '/includes/functions_xperience.php');

	DoAchievements();


	log_cron_action('Experience achievements calculated', $nextitem);
}


?>