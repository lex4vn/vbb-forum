<?php
if (intval($vbulletin->versionnumber) == 3)
{
}
else
{
	// Sneak the CSS into the headinclude
	$templater = vB_Template::create('dbtech_vbshout_css_pro');
		$templater->register('versionnumber', VBSHOUT::$versionnumber);
	$headinclude .= $templater->render();
}

?>