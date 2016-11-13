<?php

if (!is_array($cache))
{
	$cache  = array();
}

$cache[] = 'dbtech_vbshout_shoutbox_css_pro';

// We can shout
$cache[] = 'dbtech_vbshout_shoutbox_shoutarea_horizontal';
$cache[] = 'dbtech_vbshout_activeusers';
	
if ($vbulletin->options['dbtech_vbshout_editors'])
{
	if ($vbulletin->options['dbtech_vbshout_editors'] & 256)
	{
		// Color template
		$cache[] = 'editor_jsoptions_size';
		$cache[] = 'editor_toolbar_fontsize';
	}
	
	// We can shout, and there's editor tools
	$cache = array_merge($cache, array(
		'dbtech_vbshout_editortools_pro',
		'dbtech_vbshout_editortools_pro2',
	));
}

if (in_array('usercp_nav_folderbit', (array)$cache) OR in_array('usercp_nav_folderbit', (array)$globaltemplates))
{
	$cache[] = 'dbtech_vbshout_options';
	$cache[] = 'dbtech_vbshout_ignorelist';
	$cache[] = 'dbtech_vbshout_customcommands';
	$cache[] = 'dbtech_vbshout_usercp_settings_link';
	$cache[] = 'dbtech_vbshout_options_chatrooms';
	$cache[] = 'dbtech_vbshout_options_chattab';
}

if (THIS_SCRIPT == 'vbshout')
{
	$cache[] = 'dbtech_vbshout_archive_search';
	$cache[] = 'dbtech_vbshout_chatlist';
	$cache[] = 'dbtech_vbshout_chatlist_bit';
	$cache[] = 'dbtech_vbshout_viewreport_shoutmanage';
}

if (intval($vbulletin->versionnumber) == 3)
{
	$cache[] = 'dbtech_vbshout_options_bit';
	$cache[] = 'dbtech_vbshout_options_bit_bit';
	$cache[] = 'dbtech_vbshout_ignorelist_bit';
	$cache[] = 'dbtech_vbshout_customcommands_bit';
	$globaltemplates = array_merge($globaltemplates, $cache);
}
?>