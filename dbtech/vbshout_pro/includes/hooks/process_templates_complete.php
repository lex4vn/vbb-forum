<?php

if (!empty($vbulletin->VBSHOUT->permissions))
{
	// Sneak the CSS into the headinclude
	$templater = vB_Template::create('dbtech_vbshout_shoutbox_css_pro');
		$templater->register('permissions', $vbulletin->VBSHOUT->permissions);
	$headinclude .= $templater->render();
}
?>