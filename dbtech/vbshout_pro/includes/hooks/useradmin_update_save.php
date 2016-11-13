<?php

if ($vbulletin->GPC['userid'])
{
	// Check whether we should reset shout styles
	$resetvbshout = $vbulletin->input->clean_gpc('p', 'dbtech_vbshout_shoutstyle', TYPE_BOOL);
	
	if ($resetvbshout)
	{
		// Set shout style
		$userdata->set('dbtech_vbshout_shoutstyle', '');
	}
}
?>