<?php
do
{
	$action = self::$vbulletin->input->clean_gpc(self::$fetchtype, 	'manageaction', TYPE_STR);
	$userid = self::$vbulletin->input->clean_gpc(self::$fetchtype, 	'userid', 		TYPE_UINT);
	$type 	= self::$vbulletin->input->clean_gpc(self::$fetchtype, 	'type', 		TYPE_STR);
	
	// Grab the username
	$exists = self::$vbulletin->db->query_first("SELECT username, dbtech_vbshout_banned, dbtech_vbshout_silenced FROM " . TABLE_PREFIX . "user WHERE userid = " . self::$vbulletin->db->sql_prepare($userid));
	
	if (!$exists)
	{
		break;
	}
	
	// Init the Shout DM
	$shout = self::datamanager_init('Shout', self::$vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('instance', self::$instance);	
		$shout->set('instanceid', self::$instance['instanceid']);
		$shout->set('chatroomid', self::$chatroom['chatroomid']);
	
	$skip = false;
	switch ($action)
	{
		case 'ignoreunignore':
			$isignored = self::$vbulletin->db->query_first_slave("
				SELECT userid
				FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist
				WHERE userid = " . intval(self::$vbulletin->userinfo['userid']) . "
					AND ignoreuserid = " . self::$vbulletin->db->sql_prepare($userid)
			);
			$shout->set('message', ($isignored ? '/unignore ' : '/ignore ') . $exists['username']);
			break;
			
		case 'chatremove':
			// Remove an user from chat
			
			// Leave the chat room
			self::leave_chatroom(self::$chatroom, $userid);
			
			$shout->set('message', construct_phrase($vbphrase['dbtech_vbshout_x_removed_successfully'], $exists['username']));
			$shout->set('userid', -1);
			$shout->set('type', self::$shouttypes['system']);
			break;
			
		default:
			$skip = true;
			break;
	}
	
	if (!$skip)
	{
		// Now save it
		$shout->save();
		
		if (self::$fetched['error'])
		{
			// We haz error
			break;
		}
		
		// Shout fetching args
		$args = array();					
		if ($type == 'pm')
		{
			// Fetch only PMs
			$args['types'] 		= self::$shouttypes['pm'];
			$args['onlyuser'] 	= $userid;
		}
		
		// Fetch only from this chatroom
		$args['chatroomid'] = self::$chatroom['chatroomid'];
		
		// We want to fetch shouts
		self::fetch_shouts($args);
	}
	unset($shout);
}
while (false);
?>