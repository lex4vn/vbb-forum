<?php

if (!$vbulletin->userinfo['dbtech_vbshout_banned'] AND $vbulletin->options['dbtech_vbshout_active'])
{
	// We're not banned and shoutbox is active
	$show['dbtech_vbshout_menu'] = true;
}

// Create our nav template
$dbtech_vbshout_nav = vB_Template::create('dbtech_vbshout_usercp_nav_link');

if (intval($vbulletin->versionnumber) == 3)
{
	$cells[] = 'dbtech_vbshout_options';
	$cells[] = 'dbtech_vbshout_ignorelist';
	$cells[] = 'dbtech_vbshout_customcommands';
}
?>