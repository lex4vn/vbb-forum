<?php
do
{
	self::$vbulletin->input->clean_array_gpc(self::$fetchtype, array(
		'shoutid' 		=> TYPE_INT,
		'type' 			=> TYPE_STR,
		'userid' 		=> TYPE_UINT,					
		'tabid' 		=> TYPE_STR,					
		'chatroomid' 	=> TYPE_UINT,					
	));
	
	if (!self::$tabid)
	{
		// Set tabid
		self::$tabid = (in_array(self::$vbulletin->GPC['tabid'], array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : self::$vbulletin->GPC['tabid']) . self::$instance['instanceid'];
	}
	
	$type = self::$vbulletin->GPC['type'];
	
	// Make sure it's set
	$shouttype = (self::$shouttypes["$type"] ? $type : 'shout');
	
	if (empty(self::$vbulletin->GPC['type']))
	{
		self::$vbulletin->GPC['type'] = 'shouts';
	}

	// Init the Shout DM
	$shout = self::datamanager_init('Shout', self::$vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('instance', self::$instance);
	
	if (!self::$vbulletin->GPC['shoutid'])
	{
		// Invalid Shout ID
		break;
	}
	
	if (!self::$vbulletin->GPC['shoutinfo'] = self::$vbulletin->db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE shoutid = " . self::$vbulletin->db->sql_prepare(self::$vbulletin->GPC['shoutid'])))
	{
		// Shout didn't exist
		break;
	}
	
	// Set the existing data
	$shout->set_existing(self::$vbulletin->GPC['shoutinfo']);
	
	// Delete
	$shout->delete();
	
	// Fetch the file in question
	require(DIR . '/dbtech/vbshout/actions/ajax/fetch.php');
	
	/*
	// Shout fetching args
	$args = array();					
	if (self::$vbulletin->GPC['userid'])
	{
		// Fetch only PMs
		$args['types'] 		= self::$shouttypes['pm'];
		$args['onlyuser'] 	= $shout->fetch_field('id');
	}
	
	// Fetch only from this chatroom
	$args['chatroomid'] = self::$vbulletin->GPC['chatroomid'];
	
	// We want to fetch shouts
	self::fetch_shouts($args);
	*/
}
while (false);
?>