<?php
$hook_query_and .= " AND vbshout.userid NOT IN(
	SELECT userid
	FROM " . TABLE_PREFIX . "user AS user
	WHERE dbtech_vbshout_silenced = 1
		AND userid != " . self::$vbulletin->userinfo['userid'] . "
)";

if (self::$vbulletin->userinfo['userid'])
{
	// All excluded shout types
	$excludetypes = array();
	
	if (!(self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 128))
	{
		// We don't want PMs
		$excludetypes[] = self::$shouttypes['pm'];
	}
	
	if (!(self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 256))
	{
		// We don't want PMs
		$excludetypes[] = self::$shouttypes['me'];
	}
	
	if (!(self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 512) AND !($args['types'] & self::$shouttypes['notif']))
	{
		// We don't want PMs
		$excludetypes[] = self::$shouttypes['notif'];
	}
	
	if (!(self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 1024) AND !($args['types'] & self::$shouttypes['system']))
	{
		// We don't want PMs
		$excludetypes[] = self::$shouttypes['system'];
	}
	
	if (count($excludetypes))
	{
		// We have at least one exclude type
		$hook_query_and .= ' AND vbshout.type NOT IN(' . implode(',', $excludetypes) . ')';
	}
}

if (!VBSHOUT::$instance['options']['enable_sysmsg'])
{
	// We're not going to print this after all
	$hook_query_and .= ' AND vbshout.type != ' . self::$shouttypes['system'];
}
?>