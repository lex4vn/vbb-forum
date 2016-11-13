<?php

global $vbulletin, $vbphrase;
if (
	$vbulletin->userinfo['userid'] AND
	!$vbulletin->userinfo['dbtech_vbshout_banned'] AND
	$this->fetch_field('state') == 'visible' AND
	!$this->condition
)
{
	// Ensure we got this
	global $vbshout;
	
	$title = $vbulletin->db->query_first("SELECT title FROM " . TABLE_PREFIX . "blog WHERE blogid = " . intval($this->fetch_field('blogid')));
	foreach ((array)$vbshout->cache['instance'] as $instanceid => $instance)
	{
		// Grab notices
		$instance['notices'] = @unserialize($instance['notices']);
		
		// Initialise BBCode Permissions
		$vbshout->init_bbcode_permissions($instance['bbcodepermissions']);
		
		if ($vbshout->bbcodepermissions & 64)
		{
			$notif = '[URL="' . $vbulletin->options['bburl'] . '/blog.php?blogtextid=' . intval($this->fetch_field('blogtextid')) . '#comment' . intval($this->fetch_field('blogtextid')) . '"]' . $title['title'] . '[/URL]';
		}
		else
		{
			// We can't, so don't even bother
			$notif = $title['title'];
		}
		
		$type = 'blogcomment';
		
		// Init the Shout DM
		$shout = $vbshout->datamanager_init('vBShout', $vbulletin, ERRTYPE_ARRAY);
		$shout->set('message', construct_phrase($vbphrase["dbtech_vbshout_notif_$type"], ($notif)))
			->set('type', $vbshout->shouttypes['notif'])
			->set('instanceid', $instanceid);
		
		// Get the shout id
		$shoutid = $shout->save();
		
		if (!$shoutid)
		{
			/*
			echo "<pre>";
			print_r($shout->errors);
			die();
			*/
		}
		
		unset($shout);
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_notification')) ? eval($hook) : false;
	}
}
?>