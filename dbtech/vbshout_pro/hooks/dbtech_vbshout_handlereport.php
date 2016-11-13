<?php
$templater = vB_Template::create('dbtech_vbshout_viewreport_shoutmanage');
	$templater->register('reportinfo', $reportinfo);
$template_hook['dbtech_vbshout_below_shout'] .= $templater->render();
?>