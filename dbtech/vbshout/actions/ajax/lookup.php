<?php
do
{
	$username	= self::$vbulletin->input->clean_gpc(self::$fetchtype, 'username',	TYPE_STR);
	
	if (!self::$instance['options']['enablepms'])
	{
		self::$fetched['error'] = $vbphrase['dbtech_vbshout_pms_disabled'];
		break;
	}
	
	if ($username == self::$vbulletin->userinfo['username'])
	{
		self::$fetched['error'] = $vbphrase['dbtech_vbshout_invalid_username'];
		break;
	}
	
	if (!$userid = self::$vbulletin->db->query_first("
		SELECT userid
		FROM " . TABLE_PREFIX . "user
		WHERE username = " . self::$vbulletin->db->sql_prepare($username)
	))
	{
		self::$fetched['error'] = $vbphrase['dbtech_vbshout_invalid_username'];
		break;
	}
	
	// Return the userid
	self::$fetched['pmuserid'] = $userid['userid'];
}
while (false);
?>