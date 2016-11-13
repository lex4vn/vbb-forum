<?php

$hook_query_and .= " AND vbshout.userid NOT IN(
	SELECT userid
	FROM " . TABLE_PREFIX . "user AS user
	WHERE dbtech_vbshout_silenced = 1
		AND userid != " . $this->registry->userinfo['userid'] . "
)";

if ($this->registry->userinfo['userid'])
{
	// All excluded shout types
	$excludetypes = array();
	
	if (!($this->registry->userinfo['dbtech_vbshout_settings'] & 128))
	{
		// We don't want PMs
		$excludetypes[] = $this->shouttypes['pm'];
	}
	
	if (!($this->registry->userinfo['dbtech_vbshout_settings'] & 256))
	{
		// We don't want PMs
		$excludetypes[] = $this->shouttypes['me'];
	}
	
	if (!($this->registry->userinfo['dbtech_vbshout_settings'] & 512) AND !($args['types'] & $this->shouttypes['notif']))
	{
		// We don't want PMs
		$excludetypes[] = $this->shouttypes['notif'];
	}
	
	if (!($this->registry->userinfo['dbtech_vbshout_settings'] & 1024) AND !($args['types'] & $this->shouttypes['system']))
	{
		// We don't want PMs
		$excludetypes[] = $this->shouttypes['system'];
	}
	
	if (count($excludetypes))
	{
		// We have at least one exclude type
		$hook_query_and .= ' AND vbshout.type NOT IN(' . implode(',', $excludetypes) . ')';
	}
}

if (!$this->registry->options['dbtech_vbshout_enable_sysmsg'])
{
	// We're not going to print this after all
	$hook_query_and .= ' AND vbshout.type != ' . $this->shouttypes['system'];
}

$hook_query_select .= ', pmuser.username AS pmusername';
$hook_query_join .= " LEFT JOIN " . TABLE_PREFIX . "user AS pmuser ON(pmuser.userid = vbshout.id)";
?>