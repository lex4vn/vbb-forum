<?php

$templater = vB_Template::create('dbtech_vbshout_usercp_settings_link');
	$templater->register('navclass', 	$navclass);
$template_hook['usercp_dbtech_vbshout_menu_top'] .= $templater->render();
?>