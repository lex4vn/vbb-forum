<?php
self::$vbulletin->input->clean_array_gpc(self::$fetchtype, array(
	'tabs' => TYPE_ARRAY_BOOL,
));

if (!is_array(self::$vbulletin->userinfo['dbtech_vbshout_soundsettings']))
{
	self::$vbulletin->userinfo['dbtech_vbshout_soundsettings'] = @unserialize(self::$vbulletin->userinfo['dbtech_vbshout_soundsettings']);
}

$instanceid = self::$instance['instanceid'];
self::$vbulletin->userinfo['dbtech_vbshout_soundsettings']["$instanceid"] = self::$vbulletin->GPC['tabs'];

// Update the user's editor styles
self::$vbulletin->db->query_write("
	UPDATE " . TABLE_PREFIX . "user
	SET dbtech_vbshout_soundsettings = " . self::$vbulletin->db->sql_prepare(trim(serialize(self::$vbulletin->userinfo['dbtech_vbshout_soundsettings']))) . "
	WHERE userid = " . intval(self::$vbulletin->userinfo['userid'])	
);
?>