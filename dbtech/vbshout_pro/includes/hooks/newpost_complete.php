<?php

if (
	$vbulletin->userinfo['userid'] AND
	!$vbulletin->userinfo['dbtech_vbshout_banned'] AND	
	($vbulletin->options['dbtech_vbshout_notices'] & 4) AND
	$post['visible'] AND	
	$foruminfo['countposts']
)
{
	if (($vbulletin->userinfo['posts'] + 1) % $vbulletin->options['dbtech_vbshout_postping_interval'] == 0)
	{
		global $vbshout;
		
		foreach ((array)$vbshout->cache['instance'] as $instanceid => $instance)
		{
			$shout = $vbshout->datamanager_init('vBShout', $vbulletin, ERRTYPE_ARRAY);
			$shout->set('message', construct_phrase(
					$vbphrase["dbtech_vbshout_has_reached_x_posts"],
					($vbulletin->userinfo['posts'] + 1)
				))
				->set('instanceid', $instanceid)				
				->set('type', $vbshout->shouttypes['notif']);
			$shout->save();
			unset($shout);
		}
	}
}
?>