<?php
$type = self::$vbulletin->input->clean_gpc(self::$fetchtype, 	'type', 	TYPE_NOHTML);
$title = self::$vbulletin->input->clean_gpc(self::$fetchtype, 'title', TYPE_NOHTML);

// Init the Shout DM
$shout = self::datamanager_init('Shout', self::$vbulletin, ERRTYPE_ARRAY);
	$shout->set_info('instance', self::$instance);
	$shout->set('instanceid', self::$instance['instanceid']);
	$shout->set('chatroomid', self::$chatroom['chatroomid']);
	$shout->set('message', '/createchat ' . $title);
$shout->save();
?>