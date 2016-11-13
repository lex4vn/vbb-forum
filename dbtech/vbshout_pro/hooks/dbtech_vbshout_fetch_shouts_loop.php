<?php
if (!($shouts_r['shoutsettings'] & 128) OR !(self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 128) OR !self::$instance['options']['enablepms'])
{
	// You plain can't pm this person or PMs are disabled globally
	$canpm = false;
}

/*
if ($shouts_r['type'] == self::$shouttypes['pm'])
{
	// Override phrase
	$vbphrase['dbtech_vbshout_pm'] = construct_phrase($vbphrase['dbtech_vbshout_pm_pro'], $shouts_r['pmusername']);
}
*/

if (self::$instance['options']['editors'] & 256 AND $shouts_r['shoutstyle']['size'])
{
	// Color
	//$styleprops[] = 'font-size:' . $shouts_r['shoutstyle']['size'] . ';';
}

if (self::$instance['options']['avatars_normal'] AND !((int)self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 262144))
{
	if (!function_exists('fetch_avatar_from_userinfo'))
	{
		// Get the avatar function
		require_once(DIR . '/includes/functions_user.php');
	}
	
	// grab avatar from userinfo
	fetch_avatar_from_userinfo($shouts_r);
	
	$shoutusers["$shouts_r[userid]"]['musername_backup'] = (!$shoutusers["$shouts_r[userid]"]['musername_backup'] ? $shoutusers["$shouts_r[userid]"]['musername'] : $shoutusers["$shouts_r[userid]"]['musername_backup']);
	$shoutusers["$shouts_r[userid]"]['musername'] = '<img border="0" src="' . $shouts_r['avatarurl'] . '" alt="" width="' . self::$instance['options']['avatar_width_normal'] . '" height="' . self::$instance['options']['avatar_height_normal'] . '" /> ' . $shoutusers["$shouts_r[userid]"]['musername_backup'];
}

$time = (self::$instance['options']['timeformat'] ? '[' . vbdate(self::$instance['options']['timeformat'], 	$shouts_r['dateline'], self::$vbulletin->options['yestoday']) . ']' : '');

if (!self::$instance['permissions_parsed']['canpm'])
{
	// We don't have permissions to PM
	$canpm = false;
}
?>