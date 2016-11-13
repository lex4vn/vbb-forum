<?php

if (!is_array($cache))
{
	$cache  = array();
}
$cache[] = 'dbtech_vbshout_shoutbox_css';
if ($vbulletin->VBSHOUT)
{
	// Add the main shoutbox template to the cache
	$cache = array_merge($cache, array(
		'dbtech_vbshout_activeusers',
		'dbtech_vbshout_shoutbox',
		'dbtech_vbshout_shoutbox_frames',
		'dbtech_vbshout_shoutbox_me',
		'dbtech_vbshout_shoutbox_pm',
		'dbtech_vbshout_shoutbox_shout',
		'dbtech_vbshout_shoutbox_system',
		'dbtech_vbshout_memberaction_dropdown',
		'dbtech_vbshout_memberaction_dropdown_link',
	));

	$cache[] = 'dbtech_vbshout_shoutbox_shoutarea_vertical';
	$cache[] = 'dbtech_vbshout_shoutbox_shoutcontrols';

	$cache[] = 'dbtech_vbshout_shoutbox_editortools';
			
	$cache = array_merge($cache, array(
		'editor_jsoptions_font',
		'editor_toolbar_fontname'
	));

	$cache[] = 'editor_toolbar_colors';
}

if (THIS_SCRIPT == 'vbshout')
{
	$cache[] = 'dbtech_vbshout_chataccess';
	$cache[] = 'dbtech_vbshout_chataccess_bit';
	$cache[] = 'dbtech_vbshout_report';
	$cache[] = 'dbtech_vbshout_reportlist';
	$cache[] = 'dbtech_vbshout_reportlist_bit';
	$cache[] = 'dbtech_vbshout_viewreport';
}

if (in_array('usercp_nav_folderbit', (array)$cache) OR in_array('usercp_nav_folderbit', (array)$globaltemplates))
{
	$cache[] = 'dbtech_vbshout_usercp_nav_link';
}

if (intval($vbulletin->versionnumber) == 3)
{
	$cache[] = 'dbtech_vbshout.css';
	$cache[] = 'dbtech_vbshout_colours.css';
	$cache[] = 'dbtech_vbshout_archive_shoutbit';
	$cache[] = 'dbtech_vbshout_archive_topshoutbit';	
	
	$globaltemplates = array_merge($globaltemplates, $cache);
}
?>