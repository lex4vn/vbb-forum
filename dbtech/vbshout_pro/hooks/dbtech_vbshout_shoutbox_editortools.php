<?php
$templater = vB_Template::create('dbtech_vbshout_menu');
	$templater->register('instance', 	$instance);
	$templater->register('permissions', $instance['permissions_parsed']);
$template_hook['dbtech_vbshout_popupbody'] .= $templater->render();

$templater = vB_Template::create('dbtech_vbshout_editortools_pro');
	$templater->register('editorid', 	'dbtech_shoutbox_editor_wrapper');
	$templater->register('fontsizes', 	$fontsizes);
	$templater->register('permissions', $instance['permissions_parsed']);
$template_hook['dbtech_vbshout_editortools_end'] .= $templater->render();

$chosensize = (self::$shoutstyle["$instance[instanceid]"]['size'] ? self::$shoutstyle["$instance[instanceid]"]['size'] : '11px');
$shoutbox->register('size', $chosensize);
?>