<?php

if (
	$vbulletin->userinfo['userid'] AND
	!$vbulletin->userinfo['dbtech_vbshout_banned'] AND
	$post['visible'] AND
	(
		($type == 'thread' AND ($vbulletin->options['dbtech_vbshout_notices'] & 1)) OR 
		($type == 'reply' AND ($vbulletin->options['dbtech_vbshout_notices'] & 2))
	)
)
{
	// Ensure we got this
	global $vbshout;
	
	if (!file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml'))
	{
		// Lite-only shit
		$parentlist = explode(',', $foruminfo['parentlist']);
		if ($parentlist[0] == -1)
		{
			// This forum
			$noticeforum = $foruminfo['forumid'];		
		}
		else
		{
			$key = (count($parentlist) - 2);
			$noticeforum = $parentlist["$key"];
		}
	}
	else
	{
		// This forum
		$noticeforum = $foruminfo['forumid'];
	}
	
	foreach ((array)$vbshout->cache['instance'] as $instanceid => $instance)
	{
		// Grab notices
		$instance['notices'] = @unserialize($instance['notices']);
		
		// Initialise BBCode Permissions
		$vbshout->init_bbcode_permissions($instance['bbcodepermissions']);
		
		if (
			($type == 'thread' AND (bool)($instance['notices']["$noticeforum"] & 1)) OR 
			($type == 'reply' AND (bool)($instance['notices']["$noticeforum"] & 2))
		)
		{
			if ($vbshout->bbcodepermissions & 64)
			{
				// We can use BBCode
				switch ($type)
				{
					case 'thread':
						$notif = '[thread=' . $threadinfo['threadid'] . ']' . $threadinfo['title'] . '[/thread]';
						break;
						
					case 'reply':
						$notif = '[post=' . $post['postid'] . ']' . $threadinfo['title'] . '[/post]';
						break;
				}		
			}
			else
			{
				// We can't, so don't even bother
				$notif = $threadinfo['title'];
			}
			
			// Init the Shout DM
			$shout = $vbshout->datamanager_init('vBShout', $vbulletin, ERRTYPE_ARRAY);
			$shout->set('message', construct_phrase($vbphrase["dbtech_vbshout_notif_$type"], ($notif)))
				->set('type', $vbshout->shouttypes['notif'])
				->set('instanceid', $instanceid)
				->set('forumid', $foruminfo['forumid']);
			
			// Get the shout id
			$shoutid = $shout->save();
			unset($shout);
			
			($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_notification')) ? eval($hook) : false;
		}
	}
}
?>