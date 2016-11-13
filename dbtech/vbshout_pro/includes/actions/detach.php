<?php

/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2007-2009 Fillip Hannisdal AKA Revan/NeoRevan/Belazor # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// Grab the instance id
$instanceid = $vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);

if (!$instance = $vbshout->cache['instance']["{$instanceid}"])
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_error_x', $vbphrase['vbshout_invalid_instanceid'])));
}

// Init permissions
$vbshout->init_permissions($instance['permissions']);

if (THIS_SCRIPT == 'vbshout' AND $_REQUEST['do'] == 'detach' AND $vbshout->permissions['canviewshoutbox'] AND $vbulletin->options['dbtech_vbshout_active'])
{
	// I so cba to reverse this
}
else
{
	print_no_permission();
}

$templater = vB_Template::create('dbtech_vbshout_shoutbox_css');
	$templater->register('permissions', $vbshout->permissions);
$headinclude_bottom .= $templater->render();

$vbulletin->options['dbtech_vbshout_height'] 	= $vbulletin->options['dbtech_vbshout_height_detached'];
$vbulletin->options['dbtech_vbshout_maxshouts'] = $vbulletin->options['dbtech_vbshout_maxshouts_detached'];

if ($vbulletin->userinfo['dbtech_vbshout_shoutboxsize_detached'])
{
	// Override detached height
	$vbulletin->options['dbtech_vbshout_height'] = $vbulletin->userinfo['dbtech_vbshout_shoutboxsize_detached'];
}

// Render the shoutbox
$HTML = $vbshout->render($vbshout->cache['instance']["$instanceid"]);
$pagetitle = $navbits[] = $vbshout->cache['instance']["$instanceid"]['name'];

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>