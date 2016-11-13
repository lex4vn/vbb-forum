<?php

if (!empty($vbulletin->VBSHOUT->permissions))
{
	// Sneak the CSS into the headinclude
	$templater = vB_Template::create('dbtech_vbshout_shoutbox_css');
		$templater->register('permissions', $vbulletin->VBSHOUT->permissions);
	$headinclude .= $templater->render();
	
	if (intval($vbulletin->versionnumber) == 3)
	{
		$headinclude .= '<style type="text/css">' . vB_Template::create('dbtech_vbshout.css')->render() . '</style>';
		$headinclude .= '<style type="text/css">' . vB_Template::create('dbtech_vbshout_colours.css')->render() . '</style>';
	}
}
?>