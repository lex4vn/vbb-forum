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

if (!$instance = VBSHOUT::$cache['instance']["{$instanceid}"])
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_error_x', $vbphrase['vbshout_invalid_instanceid'])));
}

if (THIS_SCRIPT == 'vbshout' AND $_REQUEST['do'] == 'detach' AND $instance['permissions_parsed']['canviewshoutbox'] AND $instance['active'] AND $vbulletin->options['dbtech_vbshout_active'])
{
	// I so cba to reverse this
}
else
{
	print_no_permission();
}

$templater = vB_Template::create('dbtech_vbshout_css');
	$templater->register('versionnumber', VBSHOUT::$versionnumber);
$headinclude_bottom .= $templater->render();

$instance['options']['height'] 	= $instance['options']['height_detached'];
$instance['options']['maxshouts'] = $instance['options']['maxshouts_detached'];

if ($instance['options']['shoutboxsize_detached'])
{
	// Override detached height
	$instance['options']['height'] = $instance['options']['shoutboxsize_detached'];
}

// Render the shoutbox
$HTML = VBSHOUT::render($instance);
$pagetitle = $navbits[] = $instance['name'];

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>