<?php
/*======================================================================*\
|| #################################################################### ||
|| # Ideal Web Technologies - Time Spent Online - Leader Board        # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright © 2010 Ideal Web Technologies  (www.idealwebtech.com)  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'tsoleaderboard');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
	'iwt_timespentonline_leaderboard',
	'iwt_timespentonline_leaderboard_bit'
);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

//Verify if the system is active
if (!$vbulletin->options['iwt_timespentonline_leaderboard_enabled'])
{
	print_no_permission();
}

// get permissions to view forumhome
//$permissions is legacy and should be updated
if (!($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']))
{
	print_no_permission();
}

$perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

//Enable the userfield join
//$include_userfield_join = true;

//Setting up condition as a variable so we can swap things out on the fly.
$condition = "user.timespentonline > 0";

//Lets get the count of members we have that meet the criteria to be displayed on the list
$leaderscount = $db->query_first_slave("
	SELECT COUNT(*) AS users, SUM(timespentonline) AS totaltime
	FROM " . TABLE_PREFIX . "user AS user
	" . ($include_userfield_join ? "LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield USING (userid)" : '') . "
	WHERE $condition
");
$totalleaders = $leaderscount['users'];
$cumulativetimeonline = calc_timespent($leaderscount['totaltime']);

// set defaults
sanitize_pageresults($totalleaders, $pagenumber, $perpage, 100, $vbulletin->options['iwt_timespentonline_leaderboard_pp']);

$limitlower = ($pagenumber - 1) * $perpage + 1;
$limitupper = ($pagenumber) * $perpage;
//$counter = 0;

if ($limitupper > $totalleaders)
{
	$limitupper = $totalleaders;
	if ($limitlower > $totalleaders)
	{
		$limitlower = $totalleaders - $perpage;
	}
}
if ($limitlower <= 0)
{
	$limitlower = 1;
}

//Get sorted user records loop thro and run templater to build the template data
$leaders = $db->query_read_slave("
	SELECT user.*
	FROM " . TABLE_PREFIX . "user AS user
	" . ($include_userfield_join ? "LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield USING (userid)" : '') . "
	WHERE $condition
	ORDER BY user.timespentonline DESC
	LIMIT " . ($limitlower - 1) . ", $perpage
");

$content = '';
$itemcount = ($pagenumber - 1) * $perpage;
$first = $itemcount + 1;

while ($leader = $db->fetch_array($leaders))
{
	/*Code for avatars for if we want them on the list
	$avwidth = '';
	$avheight = '';
	if ($userinfo['avatarid'])
	{
		$avatarurl = $userinfo['avatarpath'];
	}
	else
	{
		if ($userinfo['hascustomavatar'] AND $vbulletin->options['avatarenabled'] AND ($userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canuseavatar'] OR $userinfo['adminavatar']))
		{
			if ($vbulletin->options['usefileavatar'])
			{
				$avatarurl = $vbulletin->options['avatarurl'] . "/avatar$userinfo[userid]_$userinfo[avatarrevision].gif";
			}
			else
			{
				$avatarurl = 'image.php?' . $vbulletin->session->vars['sessionurl'] . "u=$userinfo[userid]&amp;dateline=$userinfo[avatardateline]";
			}
			if ($userinfo['avheight'] AND $userinfo['avwidth'])
			{
				$avheight = "height=\"$userinfo[avheight]\"";
				$avwidth = "width=\"$userinfo[avwidth]\"";
			}
		}
		else
		{
			$avatarurl = '';
		}
	}
	if ($avatarurl == '')
	{
		$show['avatar'] = false;
	}
	else
	{
		$show['avatar'] = true;
	}*/

	fetch_musername($leader);
	$leader['timespentonline_formatted'] = calc_timespent($leader['timespentonline']);
	$leader['timespentonline_perday_formatted'] = calc_timeperday($leader['timespentonline'],$leader['joindate']);
	$templater = vB_Template::create('iwt_timespentonline_leaderboard_bit');
		$templater->register('leader', $leader);
	$content .= $templater->render();

	$itemcount++;
}

$last = $itemcount;

//Generate the pagination
$pagenav = construct_page_nav($pagenumber, $perpage, $totalleaders, 'tsoleaderboard.php?' . $vbulletin->session->vars['sessionurl'], ''
	. (!empty($vbulletin->GPC['perpage']) ? "&amp;pp=$perpage" : "")
);

// ### ALL DONE! SPIT OUT THE HTML AND LET'S GET OUTTA HERE... ###

$navbits = array();
$navbits[''] = $vbphrase['iwt_timespentonline_leaderboard'];

$navbar = render_navbar_template(construct_navbits($navbits));
$templater = vB_Template::create('iwt_timespentonline_leaderboard');
	$templater->register_page_templates();
	$templater->register('navbar', $navbar);
	$templater->register('pagenav', $pagenav);
	$templater->register('pagetitle', $vbulletin->options['bbtitle'] . ' - ' . $pagetitle);
	$templater->register('content', $content);
	$templater->register('first', $first);
	$templater->register('last', $last);
	$templater->register('perpage', $perpage);
	$templater->register('totalleaders', $totalleaders);
	$templater->register('cumulativetimeonline', $cumulativetimeonline);
print_output($templater->render());

/*======================================================================*\
|| ####################################################################
|| # Thats All Folks!!!
|| ####################################################################
\*======================================================================*/