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

// Shorthand
$instance = $vbshout->cache['instance']["$instanceid"];

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

// Set page titles
$pagetitle = $navbits[] = construct_phrase($vbphrase['dbtech_vbshout_reports_in_x'], $instance['name']);

// Begin the page template
$page_templater = vB_Template::create('dbtech_vbshout_reportlist');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('permissions', $vbshout->permissions);
	$page_templater->register('creator', ($chatroom['creator'] == $vbulletin->userinfo['userid']));
	
$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
$perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);

// Shorthands to faciliate easy copypaste
$pagenumber = ($pagenumber ? $pagenumber : 1);
$perpage = ($perpage ? $perpage : 25);

// Count number of entries
$entries = $db->query_first_slave("
	SELECT COUNT(*) AS totalentries
	FROM " . TABLE_PREFIX . "dbtech_vbshout_report
	WHERE instanceid IN(0, $instanceid)
");

// Ensure every result is as it should be
sanitize_pageresults($entries['totalentries'], $pagenumber, $perpage);

// Find out where to start
$startat = ($pagenumber - 1) * $perpage;

// Constructs the page navigation
$pagenav = construct_page_nav(
	$pagenumber,
	$perpage,
	$entries['totalentries'],
	'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . "do=chataccess",
	"&amp;instanceid=$instanceid&amp;chatroomid=$chatroomid&amp;perpage=$perpage"
);

// Page navigation registration
$page_templater->register('pagenav', $pagenav);

// Array of all active users
$userbits = '';

// Fetch activeusers
$activeusers_q = $db->query_read_slave("
	SELECT
		vbshout.*,
	
		user.username,
		user.usergroupid,
		user.infractiongroupid,
		user.displaygroupid,
		
		reporter.userid AS reportuserid,
		reporter.username AS reportusername,
		reporter.usergroupid AS reportusergroupid,
		reporter.infractiongroupid AS reportinfractiongroupid,
		reporter.displaygroupid AS reportdisplaygroupid			
	FROM " . TABLE_PREFIX . "dbtech_vbshout_report AS vbshout
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
	LEFT JOIN " . TABLE_PREFIX . "user AS reporter ON(reporter.userid = vbshout.reportuserid)
	WHERE vbshout.instanceid = " . intval($instanceid) . "
	ORDER BY handled ASC, reportid ASC
	LIMIT $startat, " . $perpage
);

// Store these settings
$backup = array(
	'allowedbbcodes' 	=> $vbulletin->options['allowedbbcodes'],
	'allowhtml' 		=> $vbulletin->options['allowhtml'],
	'allowbbcode' 		=> $vbulletin->options['allowbbcode'],
	'allowsmilies' 		=> $vbulletin->options['allowsmilies'],
	'allowbbimagecode' 	=> $vbulletin->options['allowbbimagecode']
);

// Ensure we got BBCode Parser
require_once(DIR . '/includes/class_bbcode.php');

if (!function_exists('convert_url_to_bbcode'))
{
	require_once(DIR . '/includes/functions_newpost.php');
}		

while ($activeusers_r = $db->fetch_array($activeusers_q))
{
	// Initialise BBCode Permissions
	$vbshout->init_bbcode_permissions($instance['bbcodepermissions'], $activeusers_r);
	
	// Override allowed bbcodes
	$vbulletin->options['allowedbbcodes'] 	= $vbshout->bbcodepermissions;
	
	// Initialise the parser
	$parser = new vB_BbCodeParser($vbulletin, $vbshout->fetch_tag_list((array)$vbshout->tag_list));
	
	if ($vbshout->bbcodepermissions & 64)
	{
		// We can use the URL BBCode, so convert links
		$activeusers_r['shout'] = convert_url_to_bbcode($activeusers_r['shout']);
	}
	
	// By default, we can't pm or edit
	$canpm = $canedit = false;
	
	// BBCode parsing
	$activeusers_r['shout'] = $parser->parse($activeusers_r['shout'], 'nonforum');

	// Override the BBCode list
	$vbulletin->options['allowhtml'] 			= false;
	$vbulletin->options['allowbbcode'] 			= true;
	$vbulletin->options['allowsmilies'] 		= $vbulletin->options['dbtech_vbshout_allowsmilies'];
	$vbulletin->options['allowbbimagecode'] 	= ($vbshout->bbcodepermissions & 1024);
	
	// fetch the markup-enabled username
	fetch_musername($activeusers_r);
	
	// Setup array for reportd by user
	$reportuser = array(
		'userid'			=> $activeusers_r['reportuserid'],
		'username' 			=> $activeusers_r['reportusername'],
		'usergroupid' 		=> $activeusers_r['reportusergroupid'],
		'infractiongroupid' => $activeusers_r['reportinfractiongroupid'],
		'displaygroupid' 	=> $activeusers_r['reportdisplaygroupid'],
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
		$users = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $activeusers_r['userid'] . '" target="_blank">' . $activeusers_r['username'] . '</a>';
	}
	else
	{
		$users = '<a href="' . fetch_seo_url('member', $activeusers_r) . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
	}
	
	$templater = vB_Template::create('dbtech_vbshout_reportlist_bit');
		$templater->register('users', $reportusers);
		$templater->register('reportusers', $users);
		$templater->register('info', $activeusers_r);
	$userbits .= $templater->render();	
}
$db->free_result($activeusers_q);
unset($activeusers_r);		

foreach ($backup as $vbopt => $val)
{
	// Reset the settings
	$vbulletin->options["$vbopt"] = $val;
}
	
	$page_templater->register('userbits', $userbits);
$HTML = $page_templater->render();

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>