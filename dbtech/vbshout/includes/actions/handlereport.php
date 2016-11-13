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
$reportid = $vbulletin->input->clean_gpc('r', 'reportid', TYPE_UINT);

if (!$reportinfo = $db->query_first("
	SELECT
		report.*,
		vbshout.message,
	
		user.username,
		user.usergroupid,
		user.infractiongroupid,
		user.displaygroupid,
		
		reporter.userid AS reportuserid,
		reporter.username AS reportusername,
		reporter.usergroupid AS reportusergroupid,
		reporter.infractiongroupid AS reportinfractiongroupid,
		reporter.displaygroupid AS reportdisplaygroupid			
	FROM " . TABLE_PREFIX . "dbtech_vbshout_report AS report
	LEFT JOIN " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout USING(shoutid)
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = report.userid)
	LEFT JOIN " . TABLE_PREFIX . "user AS reporter ON(reporter.userid = report.reportuserid)
	WHERE reportid = " . $db->sql_prepare($reportid)
))
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_shoutid_specified')));
}

// fetch the markup-enabled username
fetch_musername($reportinfo);

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

// Shorthand
$instance = $vbshout->cache['instance']["$reportinfo[instanceid]"];

if (!$instance)
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_invalid_instanceid_specified')));
}

// Init permissions
$vbshout->init_permissions($instance['permissions']);

if (!$vbshout->permissions['canmodchat'])
{
	// Invalid chat room
	print_no_permission();
}

// Initialise BBCode Permissions
$vbshout->init_bbcode_permissions($instance['bbcodepermissions'], $reportinfo);

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
	$reportinfo['shout'] = convert_url_to_bbcode($reportinfo['shout']);
}

// BBCode parsing
$reportinfo['shout'] = $parser->parse($reportinfo['shout'], 'nonforum');

foreach ($backup as $vbopt => $val)
{
	// Reset the settings
	$vbulletin->options["$vbopt"] = $val;
}

// Setup array for reportd by user
$reportuser = array(
	'userid'			=> $reportinfo['reportuserid'],
	'username' 			=> $reportinfo['reportusername'],
	'usergroupid' 		=> $reportinfo['reportusergroupid'],
	'infractiongroupid' => $reportinfo['reportinfractiongroupid'],
	'displaygroupid' 	=> $reportinfo['reportdisplaygroupid'],
);

if ($reportuser['userid'])
{
	// fetch the markup-enabled username
	fetch_musername($reportuser);
	
	// Fetch the SEO'd URL to a member's profile
	if (intval($vbulletin->versionnumber) == 3)
	{
		$reportusers = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $reportuser['userid'] . '" target="_blank">' . $reportuser['username'] . '</a>';
	}
	else
	{
		$reportusers = '<a href="' . fetch_seo_url('member', $reportuser) . '" target="_blank">' . $reportuser['musername'] . '</a>';
	}
}
else
{
	// Didn't exist
	$reportusers = 'N/A';
}

// Fetch the SEO'd URL to a member's profile	
if (intval($vbulletin->versionnumber) == 3)
{
	$users = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $reportinfo['userid'] . '" target="_blank">' . $reportinfo['username'] . '</a>';
}
else
{
	$users = '<a href="' . fetch_seo_url('member', $reportinfo) . '" target="_blank">' . $reportinfo['musername'] . '</a>';
}

// Is handled?
$reportinfo['ishandled'] = ($reportinfo['handled'] ? ' checked="checked"' : '');

// Set page titles
$pagetitle = $navbits[] = construct_phrase($vbphrase['dbtech_vbshout_handling_report'], $reportinfo['reportid']);

($hook = vBulletinHook::fetch_hook('dbtech_vbshout_handlereport')) ? eval($hook) : false;

// Begin the page template
$page_templater = vB_Template::create('dbtech_vbshout_viewreport');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('reportinfo', $reportinfo);
	$page_templater->register('users', $users);
	$page_templater->register('reportusers', $reportusers);	
	$page_templater->register('template_hook', $template_hook);	
$HTML = $page_templater->render();

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>