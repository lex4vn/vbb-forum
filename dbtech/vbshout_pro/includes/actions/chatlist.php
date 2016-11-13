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
	// Gtfo.
	eval(standard_error(fetch_error('dbtech_vbshout_cannot_access_list')));
}

// Set page titles
$pagetitle = $navbits[] = $vbphrase['dbtech_vbshout_chat_room_list'];

// Begin the page template
$page_templater = vB_Template::create('dbtech_vbshout_chatlist');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('permissions', $vbshout->permissions);
	$page_templater->register('creator', ($chatroom['creator'] == $vbulletin->userinfo['userid']));
	
$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
$perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);

// Shorthands to faciliate easy copypaste
$pagenumber = ($pagenumber ? $pagenumber : 1);
$perpage = ($perpage ? $perpage : 25);

foreach ((array)$vbshout->cache['chatroom'] AS $chatroomid => $chatroom)
{
	if ($chatroom['instanceid'] != $instanceid AND $chatroom['instanceid'] != 0)
	{
		// Wrong instance
		continue;
	}
	
	if (!$chatroom['active'])
	{
		// Wrong instance
		continue;
	}
	
	// Count number of entries
	$entries['totalentries']++;
}

// Ensure every result is as it should be
sanitize_pageresults($entries['totalentries'], $pagenumber, $perpage);

// Find out where to start
$startat = ($pagenumber - 1) * $perpage;

// Constructs the page navigation
$pagenav = construct_page_nav(
	$pagenumber,
	$perpage,
	$entries['totalentries'],
	'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . "do=chatlist",
	"&amp;instanceid=$instanceid&amp;perpage=$perpage"
);

// Page navigation registration
$page_templater->register('pagenav', $pagenav);

// Array of all active users
$userbits = '';

// Fetch activeusers
$activeusers_q = $db->query_read_slave("
	SELECT
		chatroom.*,
		user.userid,
		username,
		usergroupid,
		infractiongroupid,
		displaygroupid
	FROM " . TABLE_PREFIX . "dbtech_vbshout_chatroom AS chatroom
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = chatroom.creator)
	WHERE instanceid IN($instanceid, 0)
		AND chatroom.active = 1
	ORDER BY chatroom.title ASC
	LIMIT $startat, " . $perpage
);
while ($activeusers_r = $db->fetch_array($activeusers_q))
{
	// fetch the markup-enabled username
	fetch_musername($activeusers_r);
	
	// Fetch the SEO'd URL to a member's profile
	if (intval($vbulletin->versionnumber) == 3)
	{
		$users = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $activeusers_r['userid'] . '" target="_blank">' . $activeusers_r['username'] . '</a>';
	}
	else
	{
		$users = '<a href="' . fetch_seo_url('member', $activeusers_r) . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
	}
	
	// Fetch last active time
	$mtime = intval(@file_get_contents(DIR . '/dbtech/vbshout/aop/chatroom_' . $activeusers_r['chatroomid'] . '_' . $activeusers_r['instanceid'] . '.txt'));
	$activeusers_r['lastactive'] = ($mtime ?
		vbdate($vbulletin->options['dateformat'], $mtime, $vbulletin->options['yestoday']) . ' ' .
		vbdate($vbulletin->options['timeformat'], $mtime, $vbulletin->options['yestoday'])  :
	'N/A');
	
	$templater = vB_Template::create('dbtech_vbshout_chatlist_bit');
		$templater->register('users', $users);
		$templater->register('info', $activeusers_r);
		$templater->register('instance', $instance);
	$userbits .= $templater->render();	
}
$db->free_result($activeusers_q);
unset($activeusers_r);		
	
	$page_templater->register('userbits', $userbits);
$HTML = $page_templater->render();

/*======================================================================*\
|| #################################################################### ||
|| # Created: 17:12, Sat Sep 27th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>