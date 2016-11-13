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

// Begin cleaning page variables
$vbulletin->input->clean_array_gpc('r', array(
	'perpage' 		=> TYPE_UINT,
	'pagenumber' 	=> TYPE_UINT,
	'orderby' 		=> TYPE_NOHTML,	
	'instanceid' 	=> TYPE_UINT,	
));

if (!$instance = $vbshout->cache['instance']["{$vbulletin->GPC[instanceid]}"])
{
	// Invalid instance
	eval(standard_error(fetch_error('dbtech_vbshout_error_x', $vbphrase['vbshout_invalid_instanceid'])));
}

// Init permissions
$vbshout->init_permissions($instance['permissions']);

if (!$vbshout->permissions['canviewarchive'])
{
	// Keine permissions
	print_no_permission();
}

// By default, we have 10 top shouters
$numtopshouters = 10;

// Possibly expand this to allow hooking into any query, we'll see
$hook_query_select = $hook_query_join = $hook_query_and = '';

$memberof = $vbshout->fetch_chatroom_memberships($vbulletin->userinfo, '1', $instance['instanceid']);
$memberof[] = 0;

$hook_query_and .= " AND vbshout.chatroomid IN(" . implode(',', $memberof) . ")";

if ($vbshout->permissions['cansearcharchive'])
{
	$vbulletin->input->clean_array_gpc('r', array(
		'message'    => TYPE_STR,
	));	
	
	if ($vbulletin->GPC['message'])
	{
		// Limit by message
		$hook_query_and .= " AND vbshout.message LIKE '%" . $db->escape_string($vbulletin->GPC['message']) . "%'";
		$pagevars['message'] = $vbulletin->GPC['message'];
	}	
}

($hook = vBulletinHook::fetch_hook('dbtech_vbshout_archive_search_query')) ? eval($hook) : false;

// Create the archive template
$templater = vB_Template::create('dbtech_vbshout_archive');
	$templater->register('instance', $instance);
	$templater->register('instanceid', $instance['instanceid']);
/*
(
			vbshout.userid IN(-1, " . $vbulletin->userinfo['userid'] . ") OR
			vbshout.id IN(0, " . $vbulletin->userinfo['userid'] . ")
		)
		AND vbshout.userid NOT IN(
			SELECT ignoreuserid
			FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist AS ignorelist
			WHERE userid = " . $vbulletin->userinfo['userid'] . "
		)
		AND 
*/
// Fetch all the shout info - BUG: make notifications not count here
$totalshouts = $db->query_first_slave("
	SELECT COUNT(shoutid) AS totalshouts
	FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
	WHERE userid > 0
		AND instanceid IN(-1, 0, " . $instance['instanceid'] . ")
");
$last24hrs = $db->query_first_slave("
	SELECT COUNT(shoutid) AS last24hrs
	FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
	WHERE dateline >= " . (TIMENOW - 86400) . "
		AND userid > 0
		AND instanceid IN(-1, 0, " . $instance['instanceid'] . ")
");
$ownshouts = $vbulletin->userinfo['dbtech_vbshout_shouts'];

// Store it in an easy-to-reach array since I cba to change code
$shoutinfo = array(
	'totalshouts' 	=> $totalshouts['totalshouts'],
	'last24hrs'		=> $last24hrs['last24hrs'],
	'ownshouts'		=> $ownshouts
);

// Register shoutinfo
$templater->quickRegister($shoutinfo);

// Init this array
$topshouters = array();

// Fetch top shouters
$topshouters_q = $db->query_read_slave("
	SELECT
		userid,
		username,
		usergroupid,
		infractiongroupid,
		displaygroupid,
		dbtech_vbshout_shouts AS numshouts
	FROM " . TABLE_PREFIX . "user
	HAVING numshouts > 0
	ORDER BY numshouts DESC
	LIMIT $numtopshouters
");
while ($topshouters_r = $db->fetch_array($topshouters_q))
{
	// fetch the markup-enabled username
	fetch_musername($topshouters_r);
	
	// Fetch the SEO'd URL to a member's profile
	$topshouters[] = $topshouters_r;
}

if (intval($vbulletin->versionnumber) == 3)
{
	foreach ($topshouters as $userinfo)
	{
		$templaterr = vB_Template::create('dbtech_vbshout_archive_topshoutbit');
			$templaterr->register('userinfo', $userinfo);
		$topshouterbits .= $templaterr->render();	
	}
	$templater->register('topshouterbits', 	$topshouterbits);
}
else
{
	// Register all our top shouters
	$templater->register('topshouters', $topshouters);
}

// Register our number of top shouters
$templater->register('numtopshouters', count($topshouters));


// Ensure valid default input
$vbulletin->GPC['orderby'] 	= (!in_array($vbulletin->GPC['orderby'], array('ASC', 'DESC')) ? 'DESC' : $vbulletin->GPC['orderby']);

// Ensure there's no errors or out of bounds with the page variables
if ($vbulletin->GPC['pagenumber'] < 1)
{
	$vbulletin->GPC['pagenumber'] = 1;
}
$pagenumber = $vbulletin->GPC['pagenumber'];
$perpage = (!$vbulletin->GPC['perpage'] OR $vbulletin->GPC['perpage'] > $vbulletin->options['dbtech_vbshout_maxshouts']) ? $vbulletin->options['dbtech_vbshout_maxshouts'] : $vbulletin->GPC['perpage'];

/*
		" . (!$vbshout->permissions['ismanager'] ? "
		AND (
			vbshout.userid IN(-1, " . $vbulletin->userinfo['userid'] . ") OR
			vbshout.id IN(0, " . $vbulletin->userinfo['userid'] . ")
		)
		"
		: '') . "
*/
$shouts_num = $db->query_first_slave("
	SELECT
		COUNT(shoutid) AS totalshouts
		$hook_query_select
	FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
	LEFT JOIN " . TABLE_PREFIX . "user AS user USING(userid)
	$hook_query_join
	WHERE vbshout.instanceid IN(-1, 0, " . intval($instance['instanceid']) . ")
		AND (
			vbshout.userid IN(-1, " . $vbulletin->userinfo['userid'] . ") OR
			vbshout.id IN(0, " . $vbulletin->userinfo['userid'] . ")
		)
		AND vbshout.userid NOT IN(
			SELECT ignoreuserid
			FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist AS ignorelist
			WHERE userid = " . $vbulletin->userinfo['userid'] . "
		)
		AND vbshout.forumid IN(" . implode(',', $vbshout->fetch_forumids()) . ")
		$hook_query_and
");

// Ensure every result is as it should be
sanitize_pageresults($shouts_num['totalshouts'], $pagenumber, $perpage);

// Find out where to start
$startat = ($pagenumber - 1) * $perpage;

// The pagevariable extra
$pagevar = '';

foreach ((array)$pagevars as $key => $value)
{
	// Add to the page var
	$pagevar .= '&amp;' . $key . '=' . $value;
}

// Constructs the page navigation
$pagenav = construct_page_nav(
	$pagenumber,
	$perpage,
	$shouts_num['totalshouts'],
	'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . "do=archive",
	"&amp;instanceid=$instance[instanceid]&amp;perpage=$perpage&amp;orderby=" . $vbulletin->GPC['orderby'] . $pagevar
);

// Page navigation registration
$templater->register('pagenav', $pagenav);

// Init list of shouts
$shouts = array();

/*
		" . (!$vbshout->permissions['ismanager'] ? "
		AND (
			vbshout.userid IN(-1, " . $vbulletin->userinfo['userid'] . ") OR
			vbshout.id IN(0, " . $vbulletin->userinfo['userid'] . ")
		)
		"
		: '') . "
*/
// Query all the shouts
$shouts_q = $db->query_read_slave("
	SELECT
		user.username,
		user.usergroupid,
		user.membergroupids,
		user.infractiongroupid,
		user.displaygroupid,
		user.dbtech_vbshout_settings AS shoutsettings,
		user.dbtech_vbshout_shoutstyle AS shoutstyle,
		vbshout.*
		$hook_query_select
	FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
	$hook_query_join
	WHERE vbshout.instanceid IN(-1, 0, " . intval($instance['instanceid']) . ")
		AND (
			vbshout.userid IN(-1, " . $vbulletin->userinfo['userid'] . ") OR
			vbshout.id IN(0, " . $vbulletin->userinfo['userid'] . ")
		)
		AND vbshout.userid NOT IN(
			SELECT ignoreuserid
			FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist AS ignorelist
			WHERE userid = " . $vbulletin->userinfo['userid'] . "
		)
		AND vbshout.forumid IN(" . implode(',', $vbshout->fetch_forumids()) . ")		
		$hook_query_and
	ORDER BY dateline " . $vbulletin->GPC['orderby'] . "
	LIMIT $startat, " . $perpage
);

// Store these settings
$backup = array(
	'allowhtml' 		=> $vbulletin->options['allowhtml'],
	'allowbbcode' 		=> $vbulletin->options['allowbbcode'],
	'allowsmilies' 		=> $vbulletin->options['allowsmilies'],
	'allowbbimagecode' 	=> $vbulletin->options['allowbbimagecode']
);

while ($shouts_r = $db->fetch_array($shouts_q))
{
	// Parses action codes like /me
	$vbshout->parse_action_codes($shouts_r['message'], $shouts_r['type']);
	
	// By default, we can't pm or edit
	$canpm = $canedit = false;
	
	if ($shouts_r['userid'] > -1)
	{
		// fetch the markup-enabled username
		fetch_musername($shouts_r);
	}
	else
	{
		// This was the SYSTEM
		$shouts_r['userid'] 	= 0;
		$shouts_r['username'] 	= $shouts_r['musername'] = $vbphrase['dbtech_vbshout_system'];
	}
	
	// Sort date stamp
	$shouts_r['date'] = 
		vbdate($vbulletin->options['dateformat'], $shouts_r['dateline'], $vbulletin->options['yestoday']) . ' ' .
		vbdate($vbulletin->options['timeformat'], $shouts_r['dateline'], $vbulletin->options['yestoday']);
	
	// Only registered users can have shoutbox styles
	if (!$shouts_r['shoutstyle'] = unserialize($shouts_r['shoutstyle']))
	{
		// This shouldn't be false
		$shouts_r['shoutstyle'] = array();
	}
	
	$styleprops = array();
	
	$shouts_r['shoutsettings'] = (int)($shouts_r['shoutsettings']);
	if ((bool)($shouts_r['shoutsettings'] & 1) AND (bool)($vbulletin->options['dbtech_vbshout_editors'] & 1) AND $shouts_r['shoutstyle']["$instance[instanceid]"]['bold'] > 0)
	{
		// Bold
		$styleprops[] = 'font-weight:bold;';
	}
	
	if ((bool)($shouts_r['shoutsettings'] & 2) AND (bool)($vbulletin->options['dbtech_vbshout_editors'] & 2) AND $shouts_r['shoutstyle']["$instance[instanceid]"]['italic'] > 0)
	{
		// Italic
		$styleprops[] = 'font-style:italic;';
	}
	
	if ((bool)($shouts_r['shoutsettings'] & 4) AND (bool)($vbulletin->options['dbtech_vbshout_editors'] & 4) AND $shouts_r['shoutstyle']["$instance[instanceid]"]['underline'] > 0)
	{
		// Underline
		$styleprops[] = 'text-decoration:underline;';
	}
	
	if ((bool)($shouts_r['shoutsettings'] & 16) AND (bool)($vbulletin->options['dbtech_vbshout_editors'] & 16) AND $shouts_r['shoutstyle']["$instance[instanceid]"]['font'])
	{
		// Underline
		$styleprops[] = 'font-family:' . $shouts_r['shoutstyle']["$instance[instanceid]"]['font'] . ';';
	}
	
	if ((bool)($shouts_r['shoutsettings'] & 8) AND (bool)($vbulletin->options['dbtech_vbshout_editors'] & 8) AND $shouts_r['shoutstyle']["$instance[instanceid]"]['color'])
	{
		// Underline
		$styleprops[] = 'color:' . $shouts_r['shoutstyle']["$instance[instanceid]"]['color'] . ';';
	}
	
	// Save style properties
	$shouts_r['styleprops'] = implode(' ', $styleprops);
	
	// Init this to allow for hooking
	$shouts_r['shouttype'] = '';
	
	($hook = vBulletinHook::fetch_hook('dbtech_vbshout_archive_loop')) ? eval($hook) : false;
	
	if ($shouts_r['type'] == $vbshout->shouttypes['pm'])
	{
		// This was a PM
		$shouts_r['shouttype'] .= '(' . $vbphrase['private_message'] . ')';
	}
	
	if (in_array($shouts_r['type'], array($vbshout->shouttypes['me'], $vbshout->shouttypes['notif'])))
	{
		// Make the shout look like intended
		if (intval($vbulletin->versionnumber) == 3)
		{
			$shouts_r['message'] = '*<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $shouts_r['userid'] . '" target="_blank">' . $shouts_r['username'] . '</a> ' . $shouts_r['message'] . '*';
		}
		else
		{
			$shouts_r['message'] = '*<a href="' . fetch_seo_url('member', $shouts_r) . '" target="_blank">' . $shouts_r['username'] . '</a> ' . $shouts_r['message'] . '*';
		}
	}

	// Now finally imprint the shout
	$shouts[] = $shouts_r;
}
$db->free_result($shouts_q);

foreach ($backup as $vbopt => $val)
{
	// Reset the settings
	$vbulletin->options["$vbopt"] = $val;
}

if ($vbshout->permissions['cansearcharchive'])
{
	// Begin the search parameters array
	$searchparams = array(
		'message'    => $vbulletin->GPC['message'],
	);
	
	$templater->register('searchparams', 	$searchparams);
}

($hook = vBulletinHook::fetch_hook('dbtech_vbshout_archive_complete')) ? eval($hook) : false;
	
if (intval($vbulletin->versionnumber) == 3)
{
	foreach ($shouts as $shout)
	{
		$templaterr = vB_Template::create('dbtech_vbshout_archive_shoutbit');
			$templaterr->register('shout', $shout);
		$shoutbits .= $templaterr->render();	
	}
	$templater->register('shoutbits', 	$shoutbits);
}
else
{
	// Finally register the shouts with the template
	$templater->register('shouts', 			$shouts);
}

$templater->register('template_hook', 	$template_hook);

// Add to the navbits
$navbits[''] = $vbphrase['dbtech_vbshout_archive'];

$HTML = $templater->render();

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>