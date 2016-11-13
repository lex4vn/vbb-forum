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

// Grab the shout id
$shoutid = $vbulletin->input->clean_gpc('r', 'shoutid', TYPE_UINT);

if (!$shoutinfo = $db->query_first("
	SELECT
		*,
		user.username,
		user.usergroupid,
		user.membergroupids,
		user.infractiongroupid,
		user.displaygroupid
	FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
	LEFT JOIN " . TABLE_PREFIX . "user AS user USING(userid)
	WHERE shoutid = " . $db->sql_prepare($shoutid)
))
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_shoutid_specified')));
}

$instance = $vbshout->cache['instance']["$shoutinfo[instanceid]"];

// Init permissions
$vbshout->init_permissions($instance['permissions']);

if (!$vbshout->permissions['canviewshoutbox'])
{
	// Invalid chat room
	print_no_permission();
}

if ($exists = $db->query_first("
	SELECT shoutid
	FROM " . TABLE_PREFIX . "dbtech_vbshout_report
	WHERE shoutid = " . $db->sql_prepare($shoutid)
))
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_already_reported')));
}

// fetch the markup-enabled username
fetch_musername($shoutinfo);

// Ensure we got BBCode Parser
require_once(DIR . '/includes/class_bbcode.php');
if (!function_exists('convert_url_to_bbcode'))
{
	require_once(DIR . '/includes/functions_newpost.php');
}

// Store these settings
$backup = array(
	'allowedbbcodes' 	=> $vbulletin->options['allowedbbcodes'],
	'allowhtml' 		=> $vbulletin->options['allowhtml'],
	'allowbbcode' 		=> $vbulletin->options['allowbbcode'],
	'allowsmilies' 		=> $vbulletin->options['allowsmilies'],
	'allowbbimagecode' 	=> $vbulletin->options['allowbbimagecode']
);

// Initialise BBCode Permissions
$vbshout->init_bbcode_permissions($vbshout->cache['instance']["$shoutinfo[instanceid]"]['bbcodepermissions'], $shoutinfo);

// Initialise the parser (use proper BBCode)
$parser = new vB_BbCodeParser($vbulletin, $vbshout->fetch_tag_list((array)$vbshout->tag_list));

// Override allowed bbcodes
$vbulletin->options['allowedbbcodes'] = $vbshout->bbcodepermissions;

// Override the BBCode list
$vbulletin->options['allowhtml'] 			= false;
$vbulletin->options['allowbbcode'] 		= true;
$vbulletin->options['allowsmilies'] 		= $vbulletin->options['dbtech_vbshout_allowsmilies'];
$vbulletin->options['allowbbimagecode'] 	= ($vbshout->bbcodepermissions & 1024);

if ($vbshout->bbcodepermissions & 64)
{
	// We can use the URL BBCode, so convert links
	$shoutinfo['message'] = convert_url_to_bbcode($shoutinfo['message']);
}

// Store the unparsed message also
$shoutinfo['message'] = $shoutinfo['message_raw'] = trim($shoutinfo['message']);

// BBCode parsing
$shoutinfo['message'] = $parser->parse($shoutinfo['message'], 'nonforum');

foreach ($backup as $vbopt => $val)
{
	// Reset the settings
	$vbulletin->options["$vbopt"] = $val;
}


// Set page titles
$pagetitle = $navbits[] = construct_phrase($vbphrase['dbtech_vbshout_reporting_shout'], $shoutinfo['shoutid']);

// Begin the page template
$page_templater = vB_Template::create('dbtech_vbshout_report');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('shoutinfo', $shoutinfo);
$HTML = $page_templater->render();

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>