<?php
do
{
	// Initialise saving
	self::$vbulletin->input->clean_array_gpc(self::$fetchtype, array(
		'shoutid' 		=> TYPE_INT,
		'message' 		=> TYPE_NOHTML,
		'type' 			=> TYPE_STR,
		'pmuserid' 		=> TYPE_UINT,
		'chatroomid' 	=> TYPE_UINT,
		'tabid' 		=> TYPE_STR,
	));
	
	if (!self::$tabid)
	{
		// Set tabid
		self::$tabid = (in_array(self::$vbulletin->GPC['tabid'], array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : self::$vbulletin->GPC['tabid']) . self::$instance['instanceid'];
	}
	
	$type = substr(self::$vbulletin->GPC['type'], 0, strpos(self::$vbulletin->GPC['type'], '_'));
	
	// Make sure it's set
	$shouttype = (self::$shouttypes["$type"] ? $type : 'shout');
	
	if (empty(self::$vbulletin->GPC['type']))
	{
		self::$vbulletin->GPC['type'] = 'shouts';
	}
	
	// Init the Shout DM
	$shout = self::datamanager_init('Shout', self::$vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('instance', self::$instance);
	
	if (self::$vbulletin->GPC['shoutid'])
	{
		if (!self::$vbulletin->GPC['shoutinfo'] = self::$vbulletin->db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE shoutid = " . self::$vbulletin->db->sql_prepare(self::$vbulletin->GPC['shoutid'])))
		{
			// Shout didn't exist
			break;
		}
		
		// To avoid references
		$existing = self::$vbulletin->GPC['shoutinfo'];
		
		// Set the existing data
		$shout->set_existing($existing);
		
		// Only thing that's changed
		self::$vbulletin->GPC['shoutinfo']['message'] = self::$vbulletin->GPC['message'];
	}
	else
	{
		// Construct the shout info on the fly
		self::$vbulletin->GPC['shoutinfo'] = array(
			'id' 			=> self::$vbulletin->GPC['pmuserid'],
			'message' 		=> self::$vbulletin->GPC['message'],
			'type'			=> self::$shouttypes["$shouttype"],
			'instanceid' 	=> self::$instance['instanceid'],
			'chatroomid'	=> self::$vbulletin->GPC['chatroomid'],
		);
	}
	
	// Shorthand
	$chatroomid = self::$vbulletin->GPC['shoutinfo']['chatroomid'];
	if ($chatroom = self::$cache['chatroom']["$chatroomid"])
	{
		// Ensure the proper instance id is set
		self::$vbulletin->GPC['shoutinfo']['instanceid'] = $chatroom['instanceid'];
	}
	
	foreach (self::$vbulletin->GPC['shoutinfo'] as $varname => $value)
	{
		// Set everything
		$shout->set($varname, $value);
	}
	
	// Now finally save
	$shout->save();
	
	if (self::$fetched['error'])
	{
		// We haz error
		break;
	}
	
	$markread = true;
	if (substr(self::$tabid, 0, 2) == 'pm')
	{
		self::$tabid = 'shouts' . self::$instance['instanceid'];
		$markread = false;
	}
	
	// Update the AOP
	self::set_aop('shouts', self::$instance['instanceid'], $markread);
	
	if ($shouttype == self::$shouttypes['notif'])
	{
		// Update the AOP
		self::set_aop('shoutnotifs', self::$instance['instanceid']);
	}
	
	if ($shouttype == self::$shouttypes['system'])
	{
		// Update the AOP
		self::set_aop('systemmsgs', self::$instance['instanceid']);
	}
	
	// Fetch the file in question
	require(DIR . '/dbtech/vbshout/actions/ajax/fetch.php');
	
	/*
	// Shout fetching args
	if (self::$vbulletin->GPC['userid'])
	{
		// Fetch only PMs
		$args['types'] 		= self::$shouttypes['pm'];
		$args['onlyuser'] 	= self::$vbulletin->GPC['userid'];
	}
	
	// Fetch only from this chatroom
	$args['chatroomid'] = self::$vbulletin->GPC['chatroomid'];
	
	// We want to fetch shouts
	self::fetch_shouts($args);
	*/
}
while (false);
?>