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
if ($vbulletin->options['xperience_enabled'])
{

	require_once(DIR . '/includes/class_xperience.php');
	require_once(DIR . '/includes/functions_xperience.php');

	$xPerience =& new xPerience;
	$users = $vbulletin->db->query_read_slave("SELECT 
		u.lastactivity, 
		u.username, 
		u.userid, 
		u.joindate, 
		u.lastactivity,
		u.reputation,
		u.usergroupid,
		u.membergroupids,
		u.ipoints,
		u.posts,
		u.xperience_ppd
		FROM " . TABLE_PREFIX . "user as u
		WHERE xperience_done=0");
				
	while ($user = $vbulletin->db->fetch_array($users))
	{
		$xPerience->CalculateXP($user, 0);
	}
		
	ValidateActivity();
	
	if ($vbulletin->options['xperience_use_awards'])
	{	
		$xPerience->CalculateAwards();
	}
		
	if ($vbulletin->options['xperience_use_groups'])
	{
		$groups = $vbulletin->db->query_read_slave("SELECT 
			groupid
			FROM " . TABLE_PREFIX . "socialgroup as s
			WHERE type='public' OR type='moderated' ");
		
		while ($group = $vbulletin->db->fetch_array($groups))
		{
			$xPerience->CalculateGroupXP($group, 0);
		}
	}

log_cron_action('Experience calculated', $nextitem);

}

?>