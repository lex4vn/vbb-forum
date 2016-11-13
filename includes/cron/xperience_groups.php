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
if ($vbulletin->options['xperience_enabled'] AND $vbulletin->options['xperience_use_groups'])
{

	require_once(DIR . '/includes/class_xperience.php');
	
	$groups = $vbulletin->db->query_read_slave("SELECT 
		groupid
		FROM " . TABLE_PREFIX . "socialgroup as s
		WHERE type='public' OR type='moderated' ");
	
	$xPerience =& new xPerience;
	while ($group = $vbulletin->db->fetch_array($groups))
	{
		$xPerience->CalculateGroupXP($group, 0);
	}
	

log_cron_action('Experience Social Group Ranking calculated', $nextitem);

}

?>