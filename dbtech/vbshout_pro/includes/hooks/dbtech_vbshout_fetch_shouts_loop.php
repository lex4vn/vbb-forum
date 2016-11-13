<?php

if (!($shouts_r['shoutsettings'] & 128) OR !($this->registry->userinfo['dbtech_vbshout_settings'] & 128) OR !$this->registry->options['dbtech_vbshout_enablepms'])
{
	// You plain can't pm this person or PMs are disabled globally
	$canpm = false;
}

if ($shouts_r['type'] == $this->shouttypes['pm'])
{
	// Override phrase
	$vbphrase['dbtech_vbshout_pm'] = construct_phrase($vbphrase['dbtech_vbshout_pm_pro'], $shouts_r['pmusername']);
}

if ($this->registry->options['dbtech_vbshout_editors'] & 256 AND $shouts_r['shoutstyle']['size'])
{
	// Color
	//$styleprops[] = 'font-size:' . $shouts_r['shoutstyle']['size'] . ';';
}
?>