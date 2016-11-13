<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz / Phalynx. All Rights Reserved. # ||
|| #################################################################### ||
\*======================================================================*/

error_reporting(E_ALL & ~E_NOTICE);
define('THIS_SCRIPT', 'xperience');

$gap_notallowed = array("points_shop", "points_user_level", "points_user_infractions", "points_thread_tnc", "points_user_activity", "points_user_activity30", "points_user_activity7", "points_post_den", "points_xperience", "points_thread", "points_user", "points_post", "points_misc");

$phrasegroups = array('xperience', 'user', 'search', 'cpoption', 'socialgroups', 'cppermission', 'showthread');


$specialtemplates = array(
	'xperience_singleaward'
);

$globaltemplates = array(
	'headinclude',
	'navbar',	
	'xperience_overview',
	'xperience_ranking',
	'xperience_ranking_bit',
	'xperience_awards',
	'xperience_singleaward',
	'xperience_awards_awardbit',
	'xperience_awards_awardbit_empty',
	'xperience_awards_awardbit_category',
	'xperience_navbar',
	'xperience_stats',
	'xperience_stats_entries',
	'xperience_stats_entry',
	'xperience_stats_header',
	'xperience_stats_entries_empty',
	'xperience_stats_entry_empty',
	'xperience_navbar_css',
	'xperience_promotion_entry',
	'xperience_promotion_overview_entry',
	'xperience_promotions',
	'xperience_promotion_overview',
	'xperience_promotion_overview_entry_header',
	'xperience_promotion_overview_heighest',
	'xperience_promotion_overview_benefits_per',
	'xperience_promotion_overview_benefits_set',
	'xperience_earn',
	'xperience_earn_entry',
	'xperience_earn_category',
	'xperience_groups_ranking',
	'xperience_groups_ranking_bit',
	'xperience_gap_choose',
	'xperience_gap_choose_fields',
	'xperience_gap_entry',
	'xperience_activities',
	'xperience_activities_dateentry',
	'xperience_activities_entry',
	'xperience_awards_logbit',
	'xperience_achievements',
	'xperience_achievements_bit',
	'xperience_achievements_bit_category',
	'xperience_achievements_field',
	'xperience_achievements_logbit',
	'xperience_insight',
	'xperience_user_block_mau',
	'xperience_user_block_sg',
	'xperience_user_block_maa',
	'xperience_icon_aaa'
);

$actiontemplates = array();

require_once('./global.php');


if (!$vbulletin->options['xperience_enabled']) 
{
	eval('standard_error($vbphrase[xperience_disabled]);');

	exit;
}

// permissions check
if (!($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canviewmembers']))
{
	print_no_permission();
}

// ######################### CLEAN GPC ############################
$go = $vbulletin->db->escape_string($vbulletin->input->clean_gpc('r', 'go', TYPE_NOHTML));
$do = $vbulletin->db->escape_string($vbulletin->input->clean_gpc('r', 'do', TYPE_NOHTML));
$perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
$pagenumber = $vbulletin->input->clean_gpc('r', 'page', TYPE_UINT);
$userid = $vbulletin->input->clean_gpc('r', 'userid', TYPE_UINT);
$username = $vbulletin->db->escape_string(unhtmlspecialchars($vbulletin->input->clean_gpc('r', 'username', TYPE_STR)));
$sortfield = $vbulletin->db->escape_string(unhtmlspecialchars($vbulletin->input->clean_gpc('r', 'sortfield', TYPE_NOHTML)));
$sortorder = $vbulletin->db->escape_string(unhtmlspecialchars($vbulletin->input->clean_gpc('r', 'sortorder', TYPE_NOHTML)));

$navbits = array('xperience.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['xperience_name']);

$xperience['version'] = $vbphrase['xperience_name']." 4.1.0";

if (empty($go))
{
	$go = 'insight';
}


require_once('./includes/functions_misc.php');
require_once('./includes/functions_xperience.php');
require_once('./includes/class_xperience.php');

if ($go == "promotions")
{
	$navbits[] = $vbphrase['xperience_promotions'];
	if (!$vbulletin->options['xperience_use_promotions'])
	{
		eval('standard_error($vbphrase[xperience_disabled]);');
		exit;
	}


	require_once(DIR . '/includes/class_bitfield_builder.php');
	$BFBo =& vB_Bitfield_Builder::init(); 
	vB_Bitfield_Builder::build(false);


	$data = fetch_xperience($vbulletin->userinfo['userid']);
	$oldusergrouparray = array();
			
	$promotionlugq = $vbulletin->db->query_read("SELECT
		p.*,
		u_to.*,
		u_to.title as fr_title
		FROM " . TABLE_PREFIX . "xperience_promotion AS p
		LEFT JOIN " . TABLE_PREFIX . "usergroup AS u_to ON u_to.usergroupid=p.from_ug
		WHERE u_to.usergroupid=".$vbulletin->options['xperience_promotions_lug']);

	if ($vbulletin->db->num_rows($promotionlugq) > 0)
	{
		
		$promotionlug = $vbulletin->db->fetch_array($promotionlugq);
		$xperience['promotions'] .= GetPromotionEntry($promotionlug, $BFBo, $data, $oldusergrouparray, 0);
		$oldusergrouparray = $promotionlug;
		
	}
		
		
	$promotionq = $vbulletin->db->query_read("SELECT
		p.*,
		u_fr.*,
		u_fr.title as fr_title
		FROM " . TABLE_PREFIX . "xperience_promotion AS p
		LEFT JOIN " . TABLE_PREFIX . "usergroup AS u_fr ON u_fr.usergroupid=p.from_ug
		WHERE NOT u_fr.usergroupid=".$vbulletin->options['xperience_promotions_lug']."
		GROUP BY from_ug
		ORDER BY u_fr.title, p.sortorder, p.field		
	");
		
		
	if ($vbulletin->db->num_rows($promotionq) > 0)
	{
		while ($promotion = $vbulletin->db->fetch_array($promotionq))
		{
		
			$xperience['promotions'] .= GetPromotionEntry($promotion, $BFBo, $data, $oldusergrouparray, 0);
			$oldusergrouparray = $promotion;
		}
	}


		
	$promotionuugq = $vbulletin->db->query_read("SELECT
		p.*,
		u_to.*,
		u_to.title as fr_title
		FROM " . TABLE_PREFIX . "xperience_promotion AS p
		LEFT JOIN " . TABLE_PREFIX . "usergroup AS u_to ON u_to.usergroupid=p.to_ug
		WHERE u_to.usergroupid=".$vbulletin->options['xperience_promotions_uug']);



	if ($vbulletin->db->num_rows($promotionuugq) > 0)
	{
		$promotionuug = $vbulletin->db->fetch_array($promotionuugq);
		$xperience['promotions'] .= GetPromotionEntry($promotionuug, $BFBo, $data, $oldusergrouparray, 1);
		$oldusergrouparray = $promotionuug;	
	}
	
		
	$date_start = mktime(0, 0, 0, date("m"), date("d") - 21, date("Y"));
	$date_end = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
		
	$all = array();
	$all = GetActivityPromotions($date_start, $date_end, 0, $vbulletin->options['xperience_activities_limit']);

	if (count($all) > 0)
	{
		arsort($all); 
		$foundactivity = 1;

		foreach ($all AS $key => $item)
		{
			
			$activities['date'] = vbdate($vbulletin->options['dateformat'], $item[0]);
			if ($currentdate <> $activities['date'])
			{
				if ($currentdate <> "")
				{
					$xperience['promotionslogbits'] .="<br/>";
				}
					$templater = vB_Template::create('xperience_activities_dateentry');
				 	$templater->register('activities', $activities);
					$xperience['promotionslogbits'] .= $templater->render();  

			}
			$currentdate = $activities['date'];
		
			$xperience['promotionslogbits'] .= $item[1];
			
		}
		$xperience['promotionslogbits'] .= "<br/>";
	}

	$template = 'xperience_promotions';

}
elseif ($go == "activities")
{
	$navbits[''] = $vbphrase['xperience_activities'];
	if (!$vbulletin->options['xperience_use_activities'])
	{
		eval('standard_error($vbphrase[xperience_disabled]);');
		exit;
	}


	$year = $vbulletin->input->clean_gpc('r', 'year', TYPE_INT);
	$month = $vbulletin->input->clean_gpc('r', 'month', TYPE_INT);
	$day = $vbulletin->input->clean_gpc('r', 'day', TYPE_INT);
	$name = "";
	
	if ($year > 0 AND $month > 0 AND $day > 0)
	{
		$date_start = mktime(0, 0, 0, $month, $day, $year);
		$date_end = mktime(23, 59, 59, $month, $day, $year);
		GetActivityAll($date_start, $date_end, $do, 999);
	}
	else
	{
		$date_start = mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"));
		$date_end = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
		GetActivityAll($date_start, $date_end, $do, $vbulletin->options['xperience_activities_limit']);
	}
	
	if (strlen($activity) > 0)
	{
		$xperience['foundactivity'] = 1;
	}
	$xperience['activities'] = $activity;
	$template = 'xperience_activities';


		
		
} 
elseif ($go == "stats")
{
	$navbits[''] = $vbphrase['xperience_stats'];
	if (!$vbulletin->options['xperience_use_stats'])
	{
		eval('standard_error($vbphrase[xperience_disabled]);');
		exit;
	}

	$xperience['stats_user'] .= fetch_statistics("user");
	$xperience['stats_post'] .= fetch_statistics("post");
	$xperience['stats_thread'] .= fetch_statistics("thread");
	$xperience['stats_misc'] .= fetch_statistics("misc");
	
	$template = 'xperience_stats';
 }
elseif ($go == "insight")
{
	$navbits[''] = $vbphrase['xperience_insight'];
	$userinfo_x = fetch_userinfo($vbulletin->userinfo['userid']);
	$block_data = fetch_xperience($vbulletin->userinfo['userid']);

	$block_data['xperience_points'] = vb_number_format($userinfo_x['xperience']);
	$block_data['xperience_level'] = vb_number_format($userinfo_x['xperience_level']);
	$block_data['xperience_levelp'] = vb_number_format($userinfo_x['xperience_levelp'], 1);
	$block_data['xperience_level_up'] = vb_number_format($userinfo_x['xperience_next_level']);
	$block_data['xperience_level_up_points'] = vb_number_format($userinfo_x['xperience_next_level_points']);
	$block_data['xperience_activity'] = vb_number_format($userinfo_x['xperience_ppd'], 1);
	$block_data['xperience_activity30'] = vb_number_format($userinfo_x['xperience_ppd30'], 1);
	$block_data['xperience_activity7'] = vb_number_format($userinfo_x['xperience_ppd7'], 1);
	
	
	$block_data['xperience_gfx_level'] = construct_phrase($vbphrase['xperience_gfx_level'], $block_data['xperience_points'], $block_data['xperience_level']); 
	$block_data['xperience_gfx_levelup'] = construct_phrase($vbphrase['xperience_gfx_levelup'], $block_data['xperience_level_up'], $block_data['xperience_level_up_points'], ""); 
	$block_data['xperience_gfx_activity'] = construct_phrase($vbphrase['xperience_gfx_activity'], $block_data['xperience_activity']); 
	$block_data['xperience_gfx_activity30'] = construct_phrase($vbphrase['xperience_gfx_activity'], $block_data['xperience_activity30']); 
	$block_data['xperience_gfx_activity7'] = construct_phrase($vbphrase['xperience_gfx_activity'], $block_data['xperience_activity7']); 






	$xperience['awards'] = GetAwards($userinfo_x['xperience_awardt'], false, true); 	
	
	$xperience['achievements'] = GetAchievementsFull($userinfo_x['userid']); 	


	$most_active_userq = $vbulletin->db->query_read("
		SELECT u.userid, u.username, u.xperience_level, s.points_xperience, s.points_user_activity7 as activitiy
		FROM " . TABLE_PREFIX . "xperience_stats AS s
		INNER JOIN " . TABLE_PREFIX . "user AS u ON u.userid=s.userid
		ORDER BY points_user_activity7 DESC
		LIMIT 0,1
	");

	if ($vbulletin->db->num_rows($most_active_userq) > 0)
	{
		$most_active_user = $vbulletin->db->fetch_array($most_active_userq);
		require_once('./includes/functions_user.php');
		$userinfo = fetch_userinfo($most_active_user['userid'], 2);
		fetch_avatar_from_userinfo($userinfo, true, true);
	
		$templater = vB_Template::create('xperience_user_block_mau');
		$templater->register('most_active_user', $most_active_user);
		$templater->register('userinfo', $userinfo);
		$xperience['most_active_user'] .= $templater->render(); 
	}

	$xperience['most_achieved'] = GetMostAchievements("DESC", construct_phrase($vbphrase['xperience_most_achieved_achievement']));
	$xperience['most_exclusive'] = GetMostAchievements("ASC", construct_phrase($vbphrase['xperience_most_exclusive_achievement']));

	$xperience['best_group'] = GetBestGroup();


	$date_start = mktime(0, 0, 0, date("m"), date("d")-30, date("Y"));
	$date_end = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
	$limit = 10;
	$all = array();

	$arr_awards = array();
	$arr_awards = GetActivityAwards($date_start, $date_end, $userid, $limit);
	if (count($arr_awards) > 0)
	{
		$all = array_merge($all, $arr_awards);
	}

	$arr_achievements = array();
	$arr_achievements = GetActivityAchievements($date_start, $date_end, $userid, $limit);
	if (count($arr_achievements) > 0)
	{
		$all = array_merge($all, $arr_achievements);
	}
	DeleteNotification('xperience_promotioncount', $vbulletin->userinfo['userid']);
	DeleteNotification('xperience_awardcount', $vbulletin->userinfo['userid']);
	DeleteNotification('xperience_achievementcount', $vbulletin->userinfo['userid']);


		
	arsort($all); 
	
	$i=0;
	foreach ($all AS $key => $item)
	{			
		$i++;
		$activities['date'] = vbdate($vbulletin->options['dateformat'], $item[0]);
		if ($currentdate <> $activities['date'])
		{
			if ($currentdate <> "")
			{
				$activity .="<br/>";
			}
			$templater = vB_Template::create('xperience_activities_dateentry');
 			$templater->register('activities', $activities);
			$activity .= $templater->render();  

		}
		$currentdate = $activities['date'];

		$activity .= $item[1];
	}
	$activity .= "<br/>";



	$xperience['recent_aa'] = $activity;




	$date_start = mktime(0, 0, 0, date("m"), date("d")-30, date("Y"));
	$date_end = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
	$limit = 20;
	$all = array();
	$activity = "";
	$level = 2;
	$currentdate = "";

	$arr_points = array();
	$arr_points = GetActivityActivities($date_start, $date_end, $level, $userid, $limit);
	if (count($arr_points) > 0)
	{
		$all = array_merge($all, $arr_points);
	}
		
	arsort($all); 
	
	$i=0;
	foreach ($all AS $key => $item)
	{			
		$i++;
		$activities['date'] = vbdate($vbulletin->options['dateformat'], $item[0]);
		if ($currentdate <> $activities['date'])
		{
			if ($currentdate <> "")
			{
				$activity .="<br/>";
			}
			$templater = vB_Template::create('xperience_activities_dateentry');
 			$templater->register('activities', $activities);
			$activity .= $templater->render();  

		}
		$currentdate = $activities['date'];

		$activity .= $item[1];
	}
	$activity .= "<br/>";



	$xperience['recent_activity'] = $activity;



	
	$template = 'xperience_insight';
 }
elseif ($go == "earn")
{
	$navbits[''] = $vbphrase['xperience_earn'];
	if (!$vbulletin->options['xperience_use_earn'])
	{
		eval('standard_error($vbphrase[xperience_disabled]);');
		exit;
	}
			
	$userxp = fetch_xperience($vbulletin->userinfo['userid']);
	
	$settingphrase = array();
	$phrases = $db->query_read("
		SELECT varname, text
		FROM " . TABLE_PREFIX . "phrase
		WHERE fieldname = 'vbsettings' AND
			languageid=-1 OR languageid=" . LANGUAGEID . "
			ORDER BY languageid ASC
	");
	while($phrase = $db->fetch_array($phrases))
	{
		$settingphrase["$phrase[varname]"] = $phrase['text'];
	}

		$xperience['name'] = $vbphrase['xperience_points_for_thread'];
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pt", "", "points_threads");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_psgt", "","points_threads_sg");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pb", "", "points_thread_tags");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_po", "", "points_thread_votes");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_tr", "", "points_thread_rate");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pr", "", "points_thread_replycount");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pv", "", "points_thread_views");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_ps", "", "points_thread_stickies");
		
		($hook = vBulletinHook::fetch_hook('xperience_earn_thread')) ? eval($hook) : false;
		
		$templater = vB_Template::create('xperience_earn_category');
		$templater->register('xperience', $xperience);
		$xperience['categories'] .= $templater->render(); 
		
		
		$xperience['earnpoints'] = "";
		$xperience['name'] = $vbphrase['xperience_points_for_post'];
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pp", "", "points_posts");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_psgp", "", "points_posts_sg");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pa", "", "points_post_attachment");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pav", "attachmentviewslog", "points_post_attachment_views");
		
		($hook = vBulletinHook::fetch_hook('xperience_earn_post')) ? eval($hook) : false;
		
		$templater = vB_Template::create('xperience_earn_category');
		$templater->register('xperience', $xperience);
		$xperience['categories'] .= $templater->render(); 
		
	
		$xperience['earnpoints'] = "";
		$xperience['name'] = $vbphrase['xperience_points_for_misc'];
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pw", "local_linkslink", "points_misc_ldm");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pw", "dl_files", "points_misc_dl2");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_py", "cybppdonate", "points_misc_ppd");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pn", "blog", "points_misc_vbblog");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_cms", "cms_node", "points_misc_vbcms");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_events", "", "points_misc_events");
		
		($hook = vBulletinHook::fetch_hook('xperience_earn_misc')) ? eval($hook) : false;
	
		$xperience['name'] = $vbphrase['xperience_points_misc_custom'];
		$xperience['description'] = $vbphrase['xperience_points_misc_custom_desc'];
		
		$templater = vB_Template::create('xperience_earn_entry');
		$templater->register('xperience', $xperience);
		$xperience['earnpoints'] .= $templater->render(); 
		
		$templater = vB_Template::create('xperience_earn_category');
		$templater->register('xperience', $xperience);
		$xperience['categories'] .= $templater->render(); 
		
		$xperience['earnpoints'] = "";
		$xperience['name'] = $vbphrase['xperience_points_for_user'];
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pi", "", "points_user_infractions");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pu", "", "points_user_reputation");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_puu", "", "points_user_reputation_use");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pd", "", "points_user_online");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pg", "", "points_user_socialgroup");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pe", "", "points_user_friends");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pm", "", "points_user_visitormessages");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pc", "", "points_user_albumpictures");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_pf", "", "points_user_referrals");
		$xperience['earnpoints'] .= ResolveAssociation($userxp, "xperience_points_upr", "", "points_user_profile");			
		($hook = vBulletinHook::fetch_hook('xperience_earn_user')) ? eval($hook) : false;		
		
		$templater = vB_Template::create('xperience_earn_category');
		$templater->register('xperience', $xperience);
		$xperience['categories'] .= $templater->render(); 



	$template = 'xperience_earn';



}
elseif ($go == "awards") 
{
	global $bgclass, $altbgclass, $vbphrase;
	$navbits[''] = $vbphrase['xperience_awards'];
	
	if (!$vbulletin->options['xperience_use_awards'])
	{
		eval('standard_error($vbphrase[xperience_disabled]);');
		exit;
	}


	
	$awardcatq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_award_categories WHERE categoryorder>0 ORDER BY categoryorder");
	if ($vbulletin->db->num_rows($awardcatq) > 0)
	{
		while ($awardcat = $vbulletin->db->fetch_array($awardcatq))
		{
	

			$awardsq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_awards WHERE awardstatus > 0 AND awardcategory=".$awardcat['categoryid']." ORDER BY awardstatus");
			if ($vbulletin->db->num_rows($awardsq) > 0)
			{
				while ($myawards = $vbulletin->db->fetch_array($awardsq))
				{
		
		
					$myawards['awardxy'] = 32;
					$myawards['awardurl'] = $vbulletin->options['bburl'].'/xperience/icons/default_32.png';
					
					if (strlen($myawards['awardurl']) > 0)
					{
						$myawards['awardxy'] = 16;
					}
					
					if (strlen($myawards['awardbigurl']) > 0)
					{
						$myawards['awardxy'] = 32;
						$myawards['awardurl'] = $myawards['awardbigurl'];
					}

		
						$awards['fields_raw'] = explode("+", $myawards['awardfields']);
						$awards['fields'] = "";
						foreach ($awards['fields_raw'] AS $field)
						{
							$phrase = $vbphrase['xperience_'.$field];
							if (strlen($phrase) == 0)
							{
								$phrase = $field;
							}
							$awards['fields'] .= $phrase.", "; 
						}
						if (strlen($awards['fields']) == 0)
						{
							$awards['fields'] = "-, ";
						}
						$awards['fields'] = substr($awards['fields'], 0, strlen($awards['fields'])-2);


						$awards['awardissues'] = $vbulletin->db->query_read("SELECT
							i.*,
							user.username
							FROM " . TABLE_PREFIX . "xperience_award_issues AS i 
							INNER JOIN " . TABLE_PREFIX . "user as user ON i.userid=user.userid
							WHERE i.dateline_out=0 AND i.awardid=".$myawards['awardid']);  
						$awards['awardissued'] = "?";
						if ($vbulletin->db->num_rows($awards['awardissues']) > 0)
						{
							$awards['multiple'] = false;
							if ($vbulletin->db->num_rows($awards['awardissues']) > 1)
							{
								$awards['multiple'] = true;
							}
							
							$awards['hasbeenissued'] = true;
							$awardissue = $vbulletin->db->fetch_array($awards['awardissues']);
							$awards['awardissued'] = vbdate($vbulletin->options['dateformat'], $awardissue['dateline_in'])." ".vbdate($vbulletin->options['timeformat'], $awardissue['dateline_in']);	
							$awards['awardissuedtouserid'] = $awardissue['userid'];
							$awards['awardissuedtousername'] = $awardissue['username'];
						}
						else
						{
							$awards['hasbeenissued'] = false;							
						}
						
						exec_switch_bg();
						$templater = vB_Template::create('xperience_awards_awardbit');
					 	$templater->register('awards', $awards);
					 	$templater->register('myawards', $myawards);
					 	$templater->register('myrows', $myrows);						 	

						if (!$bOther)
						{
							$bOther = true;
							$awardbitleft .= $templater->render();  
						}
						else
						{
							$bOther = false;
							$awardbitright .= $templater->render();  
						}


				}
			}


			$templater = vB_Template::create('xperience_awards_awardbit_category');
		 	$templater->register('awardcat', $awardcat);
		 	$templater->register('awardbitleft', $awardbitleft);
		 	$templater->register('awardbitright', $awardbitright);
			$xperience['awards'] .= $templater->render();  
			$awardbitleft = "";
			$awardbitright = "";
			$bOther = false;

		}

		$date_start = mktime(0, 0, 0, date("m"), date("d") - 21, date("Y"));
		$date_end = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
			
		$all = array();
		$all = GetActivityAwards($date_start, $date_end, 0, $vbulletin->options['xperience_activities_limit']);

		if (count($all) > 0)
		{
			arsort($all); 
			$foundactivity = 1;
			
			foreach ($all AS $key => $item)
			{
				
				$activities['date'] = vbdate($vbulletin->options['dateformat'], $item[0]);
				if ($currentdate <> $activities['date'])
				{
					if ($currentdate <> "")
					{
						$awardlogbits .="<br/>";
					}
					$templater = vB_Template::create('xperience_activities_dateentry');
				 	$templater->register('activities', $activities);
					$xperience['awardlogbits'] .= $templater->render();  
					
				}
				$currentdate = $activities['date'];
			
				$xperience['awardlogbits'] .= $item[1];
				
			}

			
			$xperience['awardlogbits'] .= "<br/>";
		}
		
	$template = 'xperience_awards';
	

	
	}

} 
elseif ($go == "achievements") 
{
	global $bgclass, $altbgclass, $vbphrase;
	$navbits[''] = $vbphrase['xperience_achievements'];
	
	if (!$vbulletin->options['xperience_use_achievements'])
	{
		eval('standard_error($vbphrase[xperience_disabled]);');
		exit;
	}
	
	// get total members
	$totalmembers = $vbulletin->db->query_first("
		SELECT
		COUNT(*) AS users,
		MAX(userid) AS maxid
		FROM " . TABLE_PREFIX . "user
	");
	
	$data = fetch_xperience($vbulletin->userinfo['userid']);
	
	$achievementcatq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements_categories WHERE categoryorder>0 ORDER BY categoryorder");
	if ($vbulletin->db->num_rows($achievementcatq) > 0)
	{
		while ($achievementcat = $vbulletin->db->fetch_array($achievementcatq))
		{
 
			
			if ($vbulletin->userinfo['userid'] > 0)
			{
				$achievements['usersql'] = " AND i.userid=".$vbulletin->userinfo['userid'];
			}
			
			$achievementsq =$vbulletin->db->query_read("SELECT *, a.achievementid as achievementid, i.dateline as issuedate FROM " . TABLE_PREFIX . "xperience_achievements AS a
				LEFT JOIN " . TABLE_PREFIX . "xperience_achievements_issues AS i ON i.achievementid=a.achievementid ".$achievements['usersql']."
				WHERE sortorder > 0 AND categoryid=".$achievementcat['categoryid']."
				GROUP BY a.achievementid
				ORDER BY sortorder");


			if ($vbulletin->db->num_rows($achievementsq) > 0)
			{
				$xperience['foundachievements'] = 1;
				while ($achievementsarr = $vbulletin->db->fetch_array($achievementsq))
				{
						
					
					$achievements['imagexy'] = 32;
					$achievements['imageurl'] = $vbulletin->options['bburl'].'/xperience/images/icon_achievements_default.png';
					if (strlen($achievementsarr['imagesmall']) > 0)
					{
						$achievements['imagexy'] = 16;
						$achievements['imageurl'] = $achievementsarr['imagesmall'];
					}
					if (strlen($achievementsarr['imagebig']) > 0)
					{
						$achievements['imagexy'] = 32;
						$achievements['imageurl'] = $achievementsarr['imagebig'];
					}

					
					$achievements['canpurchase'] = $achievementsarr['canpurchase'];
					$achievements['canlose'] = $achievementsarr['canlose'];
					$achievements['issecret'] = $achievementsarr['issecret'];
					$achievements['title'] = $achievementsarr['title'];
					$achievements['description'] = $achievementsarr['description'];
					$achievements['users'] = GetAchievementUsersCount($achievementsarr['achievementid']);
					$achievements['userspercent'] = vb_number_format(($achievements['users']/$totalmembers['users'])*100);
					$achievements['users'] = vb_number_format($achievements['users']);
					
					
					$achievementsfieldsq = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements_fields WHERE achievementid=".$achievementsarr['achievementid']." ORDER BY field");
					$achievements['fields'] = "";
					if ($vbulletin->db->num_rows($achievementsfieldsq) > 0)
					{
						$xperience['foundachievements'] = 1;

						while ($achievementsfields = $vbulletin->db->fetch_array($achievementsfieldsq))
						{
							$achievement['fieldname'] = $vbphrase['xperience_'.$achievementsfields['field']];
							if (strlen($achievement['fieldname']) == 0)
							{
								$achievement['fieldname'] = $field;
							}
							$achievement['fieldvalue'] = vb_number_format($achievementsfields['value']);
							$achievement['compare'] = $vbphrase['xperience_achievements_'.$achievementsfields['compare']];
							$achievement['yours'] = vb_number_format($data[$achievementsfields[field]]);
			
							$achievement['state_image'] = "icon_state_no_12.png";
							switch ($achievementsfields['compare'])
							{
								case "-1":
									if ($data[$achievementsfields[field]] < $achievementsfields['value'])
									{
										$achievement['state_image'] = "icon_state_yes_12.png";
									}
									break;
								case "1":
									if ($data[$achievementsfields[field]] > $achievementsfields['value'])
									{
										$achievement['state_image'] = "icon_state_yes_12.png";
									}
									break;
									break;
								default:
									if ($data[$achievementsfields[field]] == $achievementsfields['value'])
									{
										$achievement['state_image'] = "icon_state_yes_12.png";
									}
									break;
							}
							
							$templater = vB_Template::create('xperience_achievements_field');
						 	$templater->register('achievement', $achievement);
							$achievements['fields'] .= $templater->render();  
						}
					}
						
					exec_switch_bg();
					$templater = vB_Template::create('xperience_achievements_bit');
				 	$templater->register('achievements', $achievements);
 
					if (!$bOther)
					{
						$bOther = true;
						$achievementbitleft .= $templater->render();  
					}
					else
					{
						$bOther = false;
						$achievementbitright .= $templater->render();  
					}
					
							
				}
			}
			
						$templater = vB_Template::create('xperience_achievements_bit_category');
		 	$templater->register('achievementcat', $achievementcat);
		 	$templater->register('achievementbitleft', $achievementbitleft);
		 	$templater->register('achievementbitright', $achievementbitright);
			$xperience['achievements'] .= $templater->render(); 
			$achievementbitleft = "";
			$achievementbitright = "";
			$bOther = false;
			
			
		}
	}

	
	$date_start = mktime(0, 0, 0, date("m"), date("d") - 30, date("Y"));
	$date_end = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
		
	$all = array();
	$all = GetActivityAchievements($date_start, $date_end, 0, $vbulletin->options['xperience_activities_limit']);

	if (count($all) > 0)
	{
		arsort($all); 
		$foundactivity = 1;

		foreach ($all AS $key => $item)
		{
			
			$activities['date'] = vbdate($vbulletin->options['dateformat'], $item[0]);
			if ($currentdate <> $activities['date'])
			{
				if ($currentdate <> "")
				{
					$xperience['achievementslogbits'] .="<br/>";
				}
				$templater = vB_Template::create('xperience_activities_dateentry');
			 	$templater->register('activities', $activities);
				$xperience['achievementslogbits'] .= $templater->render();  
			}
			$currentdate = $activities['date'];
		
			$xperience['achievementslogbits'] .= $item[1];
			
		}
		$xperience['achievementslogbits'] .= "<br/>";
	}

	$template = 'xperience_achievements';

}
elseif ($go == "groups")
{

	if (!$vbulletin->options['xperience_use_groups'])
	{
		eval('standard_error($vbphrase[xperience_disabled]);');
		exit;
	}


	$vbulletin->input->clean_array_gpc('r', array(
		'agroupname'      => TYPE_NOHTML,
	));

	$search_username = $vbulletin->GPC['agroupname'];

	$condition = '1=1';
	if (strlen($vbulletin->GPC['agroupname']) > 0)
	{
		$condition  .=  " AND name LIKE '%" . $vbulletin->db->escape_string_like(htmlspecialchars_uni($vbulletin->GPC['agroupname'])) . "%' ";
	}

	$groups = $vbulletin->db->query_first("SELECT
		COUNT(*) AS groups
		FROM " . TABLE_PREFIX . "xperience_groups as x
		INNER JOIN " . TABLE_PREFIX . "socialgroup AS g ON g.groupid=x.groupid
		WHERE ".$condition); 
	$groupcount = $groups['groups'];


	sanitize_pageresults($groupcount, $pagenumber, $perpage, 100, $vbulletin->options['memberlistperpage']);

	$limitlower = ($pagenumber - 1) * $perpage + 1;
	$limitupper = $pagenumber * $perpage;
	if ($limitupper > $groupcount)
	{
		$limitupper = $groupcount;
		if ($limitlower > $groupcount)
		{
			$limitlower = $groupcount - $perpage;
		}
	}
	if ($limitlower <= 0)
	{
		$limitlower = 1;
	}

	$sortorder = strtolower($sortorder);

	$oppositesort = iif($sortorder == 'desc', 'asc', 'desc');

	if ($sortorder != 'asc')
	{
		$sortorder = 'desc';
		$oppositesort = 'asc';
	}
	else
	{ 
		$sortorder = 'asc';
		$oppositesort = 'desc';
	}
	
	if ($sortfield != '') 
	{
		$switchSort = ($sortfield)?$sortfield:"";
	} else {
		$switchSort = "";
	}

	switch ($switchSort) 
	{
		case "xp":
		default :
			$sort_query = "x.points " . $sortorder; 
			break;
		case "groupname":
			$sort_query = "g.name " . $sortorder;
			break;
		case "members":
			$sort_query = "x.members " . $sortorder. ", x.points DESC"; 
			break;
		case "pmin":
			$sort_query = "x.points_min " . $sortorder. ", x.points DESC"; 
			break;
		case "pmax":
			$sort_query = "x.points_max " . $sortorder. ", x.points DESC"; 
			break;
	}


	//construct_page_nav($pagenumber, $perpage, $results, $address, $address2 = '', $anchor = '')
	// 
	$sortstring = "sortfield=".$switchSort."&amp;sortorder=".$sortorder."&amp;agroupname=".urlencode($search_username);	
	$pagenav = construct_page_nav($pagenumber, $perpage, $totalusers, 'xperience.php?do=groups' . $vbulletin->session->vars['sessionurl'], $sortstring);


global $bgclass, $altbgclass;
	$myquery="SELECT 
		x.*,
		g.name
		" . ($vbulletin->options['sg_enablesocialgroupicons'] ? ', socialgroupicon.dateline AS icondateline, socialgroupicon.width AS iconwidth, socialgroupicon.height AS iconheight, socialgroupicon.thumbnail_width AS iconthumb_width, socialgroupicon.thumbnail_height AS iconthumb_height' : '') . " 
		FROM " . TABLE_PREFIX . "xperience_groups AS x
		INNER JOIN " . TABLE_PREFIX . "socialgroup AS g ON g.groupid=x.groupid
		".($vbulletin->options['sg_enablesocialgroupicons'] ?
			"LEFT JOIN " . TABLE_PREFIX . "socialgroupicon AS socialgroupicon ON
				(socialgroupicon.groupid = g.groupid)" : '')." 
		WHERE ".$condition." 
		ORDER BY " . $sort_query . "
		LIMIT " . ($limitlower - 1) . "," . $perpage;

	$myrows = $vbulletin->db->query_read($myquery);
	
	$counter = 0;
	// initialize counters
	$itemcount = ($pagenumber - 1) * $perpage;
	$first = $itemcount + 1;
	
	if ($vbulletin->db->num_rows($myrows) > 0)
	{
		
		$groupcount = 0;
		require_once('./includes/functions_socialgroup.php');
		
		while ($myrow = $vbulletin->db->fetch_array ($myrows)) 
		{

				$xperience['members']=vb_number_format($myrow['members']);
				$xperience['points']=vb_number_format($myrow['points']);
				$xperience['points_max']=vb_number_format($myrow['points_max']);
				$xperience['points_min']=vb_number_format($myrow['points_min']);
				$xperience['name'] = $myrow['name'];
				$xperience['groupid'] = $myrow['groupid'];
				$groupcount++;
				$xperience['groupiconurl'] = '';
				if ($groupcount < 4)
				{
					$xperience['groupiconurl'] = fetch_socialgroupicon_url($myrow, true);
				}
				
				$itemcount++;
				
				exec_switch_bg(); 
				
				$templater = vB_Template::create('xperience_groups_ranking_bit');
			 	$templater->register('xperience', $xperience);
				$xperience['ranking_bits'] .= $templater->render();  

			
			}
		}
	$last = $itemcount;
	$sorturl = 'xperience.php?go=groups' . $vbulletin->session->vars['sessionurl'] . $sortaddon;
	
	$templater_nb = vB_Template::create('xperience_navbar');
	$templater_nb->register('go', $go);
	$xperience_navbar = $templater_nb->render();  
	
	$navbits[''] = $vbphrase['xperience_groups'];
	$navbar = render_navbar_template(construct_navbits($navbits));	
	
	$searchtime = vb_number_format(fetch_microtime_difference($searchstart), 2);
		
	$templater = vB_Template::create('xperience_groups_ranking');
  $templater->register_page_templates(); 
  $templater->register('xperience_navbar', $xperience_navbar);
  $templater->register('xperience', $xperience);
  $templater->register('perpage', $perpage);
	$templater->register('sortfield', $sortfield);
	$templater->register('oppositesort', $oppositesort);
	$templater->register('customfieldsheader', $customfieldsheader);
	$templater->register('first', $first);
	$templater->register('gobutton', $gobutton);
	$templater->register('last', $last);
	$templater->register('ltr', $ltr);
	$templater->register('pagenav', $pagenav);
	$templater->register('perpage', $perpage);
	$templater->register('searchtime', $searchtime);
	$templater->register('sortarrow', $sortarrow);
	$templater->register('sorturl', $sorturl);
	$templater->register('spacer_close', $spacer_close);
	$templater->register('spacer_open', $spacer_open);
	$templater->register('totalusers', $totalusers);
	$templater->register('sortorder', $sortorder);
 	$templater->register('navbar', $navbar);
	print_output($templater->render());  
	quit();
}
	elseif ($go == "gap")
	{
	$navbits[''] = $vbphrase['xperience_gap'];
		
		if (!$vbulletin->options['xperience_use_gap'])
		{
				eval('standard_error($vbphrase[xperience_disabled]);');
		}
		
		if ($userid > 0)
		{
			$gapuserinfo = fetch_userinfo($userid); 
			$xperience['receiveusername'] = $gapuserinfo['username'];			
		}
		else
		{
			$xperience['receiveusername'] = $username;
		}
		
		$data = fetch_xperience($vbulletin->userinfo['userid']);
		
		$optionsq =$vbulletin->db->query_read("SHOW COLUMNS
			FROM " . TABLE_PREFIX . "xperience_stats LIKE 'points_%'");
					
			if ($vbulletin->db->num_rows($optionsq) > 0)
			{
				while ($options = $vbulletin->db->fetch_array($optionsq)) 
				{
					
					if (!in_array($options['Field'], $gap_notallowed))
					{
						$gap['fielddescription'] = $vbphrase['xperience_'.$options[Field]]; 
						if (strlen($gap['fielddescription']) < 1)
						{
							$gap['fielddescription'] = $options["Field"];
						}
	
						$gap['fieldvalue'] = $options['Field'];
						$gap['fieldamount'] = vb_number_format($data[$options[Field]]);
						if ($gap['fieldamount'] > 0)
						{
							$templater = vB_Template::create('xperience_gap_choose_fields');
						 	$templater->register('gap', $gap);
							$xperience['fields'] .= $templater->render();  

						}
					}
				}
			}
	
		$template = 'xperience_gap_choose';
	}
	elseif ($go == "dogap")
	{
		if (!$vbulletin->options['xperience_use_gap'])
		{
			eval('standard_error($vbphrase[xperience_disabled]);');
		}
		
		$username = $vbulletin->db->escape_string($vbulletin->input->clean_gpc('p', 'choose_gap_user', TYPE_STR)); 
		$fieldamount = $vbulletin->input->clean_gpc('p', 'fieldamount', TYPE_ARRAY);
		$data = fetch_xperience($vbulletin->userinfo['userid']);


		
		$getuseridq = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "user WHERE username='".$username."'");
		if ($db->num_rows($getuseridq) > 0)
		{
			$userinfo = $vbulletin->db->fetch_array($getuseridq);


			foreach ($fieldamount AS $fieldkey => $fieldvalue)
			{
				if ($fieldvalue > 0)
				{
					if ($fieldvalue <= $data[$fieldkey])
					{
						if (!in_array($fieldkey, $gap_notallowed))
						{
							$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_gap
								(userid, toid, field, amount, dateline) VALUES (
								".$vbulletin->userinfo['userid'].",
								".$userinfo['userid'].",
								'".$fieldkey."',
								".$fieldvalue.",
								".mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"))."
								)");
							$fieldkeyfriendly = $vbphrase['xperience_'.$fieldkey]; 
							
							$fieldamounttext .= $fieldkeyfriendly.": ".$fieldvalue.", ";
						}
					}
				}
			}

			if (strlen($fieldamounttext) < 2)
			{
				eval(print_standard_redirect('xperience_gap_failed', true, true));
			}

			
			$fieldamounttext = substr($fieldamounttext, 0, strlen($fieldamounttext) - 2);

			if ($vbulletin->options['xperience_gap_write_pm'])
			{
				$message = construct_phrase($vbphrase['xperience_gap_text'], $vbulletin->options['bburl']."/member.php?u=".$vbulletin->userinfo['userid'], $vbulletin->userinfo['username'], $fieldamounttext);
				$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_SILENT);
				$pmdm->overridequota = true;
				$pmdm->set_info('is_automated', true);
				$pmdm->set('fromuserid', $vbulletin->userinfo['userid']);
				$pmdm->set('fromusername', $vbulletin->userinfo['username']);
				$pmdm->set('title', $vbphrase['xperience_gap_title']);
				$pmdm->set('message', $message);
			 	$pmdm->set_recipients($userinfo['username'], $botpermissions, 'cc');
				$pmdm->set('dateline', TIMENOW);
				$pmdm->save(); 
			}

 			$xPerience = new xPerience;
 			$xPerience->CalculateXP($userinfo, 0);
 			$xPerience->CalculateXP($vbulletin->userinfo, 0);
			$xPerience->CalculateAwards();

			$vbulletin->url = $vbulletin->options['bburl']."/xperience.php";
			eval(print_standard_redirect('xperience_gap_successfully', true, true));
		}
		else
		{
			eval(print_standard_redirect('xperience_gap_failed', true, true));
			
		}

} else {

$go = "ranking";

	$searchstart = microtime();
	
	$vbulletin->input->clean_array_gpc('r', array(
		'ausername'      => TYPE_NOHTML,
	));

	$search_username = $vbulletin->GPC['ausername'];

	$condition = '1=1';
	if (strlen($vbulletin->GPC['ausername']) > 0)
	{
		$condition  .=  " AND username LIKE '%" . $db->escape_string_like(htmlspecialchars_uni($vbulletin->GPC['ausername'])) . "%' ";
	}

	if (strlen($vbulletin->options['xperience_ignore_users']) > 0)
	{
		$condition .= " AND u.userid NOT IN(".$vbulletin->options['xperience_ignore_users'].") ";
	}
	
	
	$members = $vbulletin->db->query_first("SELECT
		COUNT(*) AS users
		FROM " . TABLE_PREFIX . "xperience_stats AS x
		INNER JOIN " . TABLE_PREFIX . "user AS u
		ON u.userid=x.userid 
		WHERE ".$condition); 
	$totalusers = $members['users'];


	sanitize_pageresults($totalusers, $pagenumber, $perpage, 100, $vbulletin->options['memberlistperpage']);

	$limitlower = ($pagenumber - 1) * $perpage + 1;
	$limitupper = $pagenumber * $perpage;
	if ($limitupper > $totalusers)
	{
		$limitupper = $totalusers;
		if ($limitlower > $totalusers)
		{
			$limitlower = $totalusers - $perpage;
		}
	}
	if ($limitlower <= 0)
	{
		$limitlower = 1;
	}

	$sortorder = strtolower($sortorder);

	$oppositesort = iif($sortorder == 'desc', 'asc', 'desc');

	if ($sortorder != 'asc')
	{
		$sortorder = 'desc';
		$oppositesort = 'asc';
	}
	else
	{ // $sortorder = 'ASC'
		$oppositesort = 'desc';
	}

	
	if ($sortfield != '') 
	{
		$switchSort = ($sortfield)?$sortfield:"";
	} else {
		$switchSort = "";
	}

	switch ($switchSort) 
	{
		case "xp":
		default : $sort_query = "u.xperience " . $sortorder;
			break;
		case "xu":
			$sort_query = "s.points_user " . $sortorder. ", u.xperience DESC"; 
			break;
		case "xt":
			$sort_query = "s.points_thread " . $sortorder. ", u.xperience DESC"; 
			break;
		case "xo":
			$sort_query = "s.points_post " . $sortorder. ", u.xperience DESC"; 
			break;
		case "xn":
			$sort_query = "u.username " . $sortorder;
			break;
		case "xl":
			$sort_query = "u.xperience_level " . $sortorder. ", u.xperience DESC"; 
			break;
		case "xm":
			$sort_query = "s.points_misc " . $sortorder. ", u.xperience DESC"; 
			break;
		case "xd":
			$sort_query = "u.xperience_ppd " . $sortorder. ", s.points_post_avg DESC"; 
			break;
		case "xr":
			$sort_query = "u.reputation " . $sortorder. ", u.xperience ASC"; 
			break;
	}


	//construct_page_nav($pagenumber, $perpage, $results, $address, $address2 = '', $anchor = '')
	// 
	$sortstring = "sortfield=".$switchSort."&amp;sortorder=".$sortorder."&amp;ausername=".urlencode($search_username);	
	$pagenav = construct_page_nav($pagenumber, $perpage, $totalusers, 'xperience.php?go=ranking' . $vbulletin->session->vars['sessionurl'], $sortstring);


global $bgclass, $altbgclass;
	$myquery="SELECT 
		u.userid,
		u.usergroupid,
		u.membergroupids,
		u.username,
		u.xperience_level,
		u.xperience,
		u.xperience_ppd,
		u.xperience_awardt,
		u.reputation,
		s.points_user,
		s.points_thread,
		s.points_post,
		s.points_misc
		FROM " . TABLE_PREFIX . "xperience_stats AS s
		INNER JOIN " . TABLE_PREFIX . "user AS u ON u.userid=s.userid
		WHERE ".$condition." 
		ORDER BY " . $sort_query . "
		LIMIT " . ($limitlower - 1) . "," . $perpage;


	$myrows = $vbulletin->db->query_read($myquery);

	$counter = 0;
	// initialize counters
	$itemcount = ($pagenumber - 1) * $perpage;
	$first = $itemcount + 1;


	if ($vbulletin->db->num_rows($myrows) > 0)
	{
		while ($myrow = $db->fetch_array($myrows) AND $counter++ < $perpage)
		{
		
			$DisplayUser=1;
			if (strlen($vbulletin->options['xperience_ignore_usergroupsids']) > 0) 
			{
				$usergroups=explode(",", $vbulletin->options['xperience_ignore_usergroupsids']);
				for ($i = 0; $i < count($usergroups); $i ++) 
				{
					if (is_member_of($myrow, $usergroups[$i])) 
					{
						$DisplayUser = 0;
					}
				}
			}
		
		
			if ($DisplayUser == 1) 
			{
				$xperience['reputation'] = $myrow['reputation'];
				$xperience['xperience_level'] = $myrow['xperience_level'];
				$xperience['username'] = $myrow['username'];
				$xperience['userid'] = $myrow['userid'];
				$xperience['xperience'] = vb_number_format($myrow['xperience']);
				$xperience['points_user'] = vb_number_format($myrow['points_user']);
				$xperience['points_thread'] = vb_number_format($myrow['points_thread']);
				$xperience['points_post'] = vb_number_format($myrow['points_post']);
				$xperience['points_misc'] = vb_number_format($myrow['points_misc']);
				$xperience['xperience_ppd'] = vb_number_format($myrow['xperience_ppd'], 1);
				
				$xperience['awards'] = GetAwards($myrow['xperience_awardt']);
				
				$itemcount++;
				
				exec_switch_bg(); 
				$t_xperience_ranking_bit = vB_Template::create('xperience_ranking_bit');
				$t_xperience_ranking_bit->register('xperience', $xperience);
				$xperience_ranking_bit .= $t_xperience_ranking_bit->render(); 
			}
		}
	}
	
	$last = $itemcount;
	$sorturl = 'xperience.php?go=ranking' . $vbulletin->session->vars['sessionurl'] . $sortaddon;
	

	
	$templater_nb = vB_Template::create('xperience_navbar');
	$templater_nb->register('go', $go);
	$xperience_navbar = $templater_nb->render(); 
	
	$navbits[''] = $vbphrase['xperience_ranking'];
	$navbar = render_navbar_template(construct_navbits($navbits));	
	$searchtime = vb_number_format(fetch_microtime_difference($searchstart), 2);
		
		 	
	$templater = vB_Template::create('xperience_ranking');
  $templater->register_page_templates(); 
	$templater->register('xperience', $xperience);
  $templater->register('xperience_navbar', $xperience_navbar);
  $templater->register('xperience_ranking_bit', $xperience_ranking_bit);
  $templater->register('perpage', $perpage);
	$templater->register('sortfield', $sortfield);
	$templater->register('oppositesort', $oppositesort);
	$templater->register('customfieldsheader', $customfieldsheader);
	$templater->register('first', $first);
	$templater->register('gobutton', $gobutton);
	$templater->register('last', $last);
	$templater->register('ltr', $ltr);
	$templater->register('pagenav', $pagenav);
	$templater->register('perpage', $perpage);
	$templater->register('searchtime', $searchtime);
	$templater->register('sortarrow', $sortarrow);
	$templater->register('sorturl', $sorturl);
	$templater->register('spacer_close', $spacer_close);
	$templater->register('spacer_open', $spacer_open);
	$templater->register('totalusers', $totalusers);
	$templater->register('sortorder', $sortorder);
 	$templater->register('navbar', $navbar);
	print_output($templater->render());  
	quit();

}

	$templater_nb = vB_Template::create('xperience_navbar');
	$templater_nb->register('go', $go);
	$xperience_navbar = $templater_nb->render();  
	
	$navbar = render_navbar_template(construct_navbits($navbits));	
	

	$templater = vB_Template::create($template);
  $templater->register_page_templates(); 
  $templater->register('block_data', $block_data);
  $templater->register('userinfo_x', $userinfo_x);
  $templater->register('xperience_navbar', $xperience_navbar);
 	$templater->register('navbar', $navbar);
 	$templater->register('xperience', $xperience);


 		print_output($templater->render());  


	
?>
