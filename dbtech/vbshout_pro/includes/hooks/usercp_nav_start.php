<?php

if (!$vbulletin->userinfo['dbtech_vbshout_banned'] AND $vbulletin->options['dbtech_vbshout_active'])
{
	// We're not banned and shoutbox is active
	$cells[] = 'dbtech_vbshout_options';
	$cells[] = 'dbtech_vbshout_ignorelist';
	$cells[] = 'dbtech_vbshout_customcommands';
}
?>