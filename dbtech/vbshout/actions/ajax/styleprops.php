<?php
self::$vbulletin->input->clean_array_gpc(self::$fetchtype, array(
	'editor' 		=> TYPE_ARRAY,
	'tabid' 		=> TYPE_STR,					
));

if (!self::$tabid)
{
	// Set tabid
	self::$tabid = (in_array(self::$vbulletin->GPC['tabid'], array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : self::$vbulletin->GPC['tabid']) . self::$instance['instanceid'];				
}

// Set shout styles array
$instanceid = self::$instance['instanceid'];
self::$shoutstyle["$instanceid"] = preg_replace('/[^A-Za-z0-9 #(),]/', '', self::$vbulletin->GPC['editor']);

// Update the user's editor styles
self::$vbulletin->db->query_write("
	UPDATE " . TABLE_PREFIX . "user
	SET dbtech_vbshout_shoutstyle = " . self::$vbulletin->db->sql_prepare(serialize(self::$shoutstyle)) . "
	WHERE userid = " . self::$vbulletin->userinfo['userid']
);

// Set the AOP
self::set_aop('shouts', self::$instance['instanceid'], false);				

// Fetch the shouts again¨
self::fetch_shouts($args);
?>