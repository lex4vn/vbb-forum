<?php

print_modification_message('<p>Updating version number to: ' . $code['version'] . '</p>');

$shortname = 'dbtech_vbshout';

if (!isset($vbulletin->options["{$shortname}_versionnumber"]))
{
	// Fresh install
	$settinggroups = $arr['options']['settinggroup'];
	if (!isset($settinggroups[0]))
	{
		$settinggroups = array($settinggroups);
	}
	
	foreach ($settinggroups AS $key => $group)
	{
		if (empty($group['setting']))
		{
			continue;
		}
		
		$settings = $group['setting'];
		if (!isset($settings[0]))
		{
			$settings = array($settings);
		}
	
		foreach ($settings AS $key2 => $setting)
		{
			if ($setting['varname'] == "{$shortname}_versionnumber")
			{
				// Short version
				$arr['options']['settinggroup']["$key"]['setting']["$key2"]['defaultvalue'] = $shortversion;
			}
			
			if ($setting['varname'] == "{$shortname}_versionnumber_text")
			{
				// Short version
				$arr['options']['settinggroup']["$key"]['setting']["$key2"]['defaultvalue'] = $code['version'];
			}
		}
	}
	unset($settings, $settinggroups);	
}
else
{
	$db->query_write("
		UPDATE `" . TABLE_PREFIX . "setting` SET
			value = '" . $shortversion . "',
			defaultvalue = '" . $shortversion . "'
		WHERE varname = '{$shortname}_versionnumber'
	");
	
	$db->query_write("
		UPDATE `" . TABLE_PREFIX . "setting` SET
			value = '" . $code['version'] . "',
			defaultvalue = '" . $code['version'] . "'
		WHERE varname = '{$shortname}_versionnumber_text'
	");
}

require_once(DIR . '/includes/class_bitfield_builder.php');
if (vB_Bitfield_Builder::build(false) !== false)
{
	$myobj =& vB_Bitfield_Builder::init();
	$myobj->data = $myobj->fetch(DIR . '/includes/xml/bitfield_vbshout.xml', false, true);
}
else
{
	echo "<strong>error</strong>\n";
	print_r(vB_Bitfield_Builder::fetch_errors());
}


$groupinfo = array();
foreach ($myobj->data['ugp']["{$shortname}permissions"] AS $permtitle => $permvalue)
{
	if (empty($permvalue['group']))
	{
		continue;
	}

	if (!empty($permvalue['install']))
	{
		foreach ($permvalue['install'] AS $gid)
		{
			$groupinfo["$gid"]["{$shortname}permissions"] += $permvalue['value'];
		}
	}
}

foreach ($groupinfo as $usergroupid => $permissions)
{
	$perms = $permissions["{$shortname}permissions"];
	$db->query_write("
		UPDATE " . TABLE_PREFIX . "usergroup
		SET {$shortname}permissions = $perms
		WHERE usergroupid = $usergroupid
	");
}
build_forum_permissions();

// Update settings
build_options();
vBulletinHook::build_datastore($db);
?>