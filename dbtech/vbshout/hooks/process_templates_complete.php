<?php
if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
{
	// We need the template class
	require_once(DIR . '/dbtech/vbshout/includes/class_template.php');
}

$show['vb414compat'] = version_compare($vbulletin->versionnumber, '4.1.4 Alpha 1', '>=');

if (intval($vbulletin->versionnumber) == 3)
{
	$headinclude .= '<style type="text/css">' . vB_Template::create('dbtech_vbshout.css')->render() . '</style>';
	$headinclude .= '<style type="text/css">' . vB_Template::create('dbtech_vbshout_colours.css')->render() . '</style>';
}

// Sneak the CSS into the headinclude
$templater = vB_Template::create('dbtech_vbshout_css');
	$templater->register('versionnumber', VBSHOUT::$versionnumber);
$headinclude .= $templater->render();

if (file_exists(DIR . '/dbtech/vbshout_pro/hooks/process_templates_complete.php'))
{
	// If the pro version exists, load it
	require(DIR . '/dbtech/vbshout_pro/hooks/process_templates_complete.php');
}

// Global loading code
require_once(DIR . '/dbtech/vbshout/includes/global.php');
?>