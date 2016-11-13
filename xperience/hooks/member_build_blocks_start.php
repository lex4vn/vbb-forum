<?php	
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz / Phalynx. All Rights Reserved. # ||
|| #################################################################### ||
\*======================================================================*/

require_once('./includes/functions_xperience.php');

if ($vbulletin->options['xperience_use_awards']) 
{
		$output = '';
		$html = '';
		$id = 'xperience_block_awards';
						
		$awards = GetAwards($userinfo['xperience_awardt'], true); 	
		
		$title = $vbphrase['xperience_awards_short'];
				
		if (strlen($awards) < 3)
		{
			$html = $vbphrase['xperience_awards_empty'];
		}
		else
		{
			$html = $awards;
		}
				
		$templater = vB_Template::create('xperience_memberinfo_showcase');
		$templater->register('title', $title);
		$templater->register('id', $id);
		$templater->register('html', $html);
		$template_hook['profile_sidebar_groups'] .= $templater->render(); 
}

if ($vbulletin->options['xperience_use_achievements']) 
{
		$output = '';
		$title = $vbphrase['xperience_achievements_short'];
		$id = 'xperience_block_achievements';
				
		$achievements = GetAchievementsFull($userinfo['userid'], true); 	
		
		$title = $vbphrase['xperience_achievements_short'];
				
		if (strlen($achievements) < 3)
		{
			$html = $vbphrase['xperience_achievements_empty'];
		}
		else
		{
			$html = $achievements;
		}
				
	
		$templater = vB_Template::create('xperience_memberinfo_showcase');
		$templater->register('title', $title);
		$templater->register('id', $id);
		$templater->register('html', $html);
		$template_hook['profile_sidebar_groups'] .= $templater->render(); 
}

global $selected_tab;


$stats_q = $vbulletin->db->query_read("SELECT * FROM
	" . TABLE_PREFIX . "xperience_stats
	WHERE userid=".$vbulletin->GPC['userid']."
	LIMIT 0,1"); 


if ($vbulletin->db->num_rows($stats_q) > 0)
{
	$stat_q = $vbulletin->db->fetch_array($stats_q);
	
	$block_data['xperience_points_shop'] = vb_number_format($stat_q['points_shop']);
	$block_data['xperience_points_misc'] = vb_number_format($stat_q['points_misc']);
	$block_data['xperience_points_misc_ldm'] = vb_number_format($stat_q['points_misc_ldm']);
	$block_data['xperience_points_misc_dl2'] = vb_number_format($stat_q['points_misc_dl2']);
	$block_data['xperience_points_misc_ppd'] = vb_number_format($stat_q['points_misc_ppd']);
	$block_data['xperience_points_misc_vbblog'] = vb_number_format($stat_q['points_misc_vbblog']);
	$block_data['xperience_points_misc_vbcms'] = vb_number_format($stat_q['points_misc_vbcms']);
	$block_data['xperience_points_misc_events'] = vb_number_format($stat_q['points_misc_events']);
	$block_data['xperience_points_misc_custom'] = vb_number_format($stat_q['points_misc_custom']);
	$block_data['xperience_points_user_socialgroup'] = vb_number_format($stat_q['points_user_socialgroup']);
	$block_data['xperience_points_user_friends'] = vb_number_format($stat_q['points_user_friends']);
	$block_data['xperience_points_user_visitormessages'] = vb_number_format($stat_q['points_user_visitormessages']);
	$block_data['xperience_points_user'] = vb_number_format($stat_q['points_user']);
	$block_data['xperience_points_user_infractions'] = vb_number_format($stat_q['points_user_infractions']);
	$block_data['xperience_points_user_law'] = vb_number_format($stat_q['points_user_law']);
	$block_data['xperience_points_user_referrals'] = vb_number_format($stat_q['points_user_referrals']);
	$block_data['xperience_points_user_albumpictures'] = vb_number_format($stat_q['points_user_albumpictures']);
	$block_data['xperience_points_user_reputation'] = vb_number_format($stat_q['points_user_reputation']);
	$block_data['xperience_points_user_reputation_use'] = vb_number_format($stat_q['points_user_reputation_use']);
	$block_data['xperience_points_user_online'] = vb_number_format($stat_q['points_user_online']);
	$block_data['xperience_points_user_profile'] = vb_number_format($stat_q['points_user_profile']);
	$block_data['xperience_points_thread'] = vb_number_format($stat_q['points_thread']);
	$block_data['xperience_points_threads'] = vb_number_format($stat_q['points_threads']);
	$block_data['xperience_points_threads_sg'] = vb_number_format($stat_q['points_threads_sg']);
	$block_data['xperience_points_thread_votes'] = vb_number_format($stat_q['points_thread_votes']);
	$block_data['xperience_points_thread_rate'] = vb_number_format($stat_q['points_thread_rate']);
	$block_data['xperience_points_thread_replycount'] = vb_number_format($stat_q['points_thread_replycount']);
	$block_data['xperience_points_thread_views'] = vb_number_format($stat_q['points_thread_views']);
	$block_data['xperience_points_thread_tags'] = vb_number_format($stat_q['points_thread_tags']);
	$block_data['xperience_points_thread_stickies'] = vb_number_format($stat_q['points_thread_stickies']);
	$block_data['xperience_points_post'] = vb_number_format($stat_q['points_post']);
	$block_data['xperience_points_posts'] = vb_number_format($stat_q['points_posts']);
	$block_data['xperience_points_posts_sg'] = vb_number_format($stat_q['points_posts_sg']);
	$block_data['xperience_points_post_avg'] = vb_number_format($stat_q['points_post_avg'], 2);
	$block_data['xperience_points_post_thanks'] = vb_number_format($stat_q['points_post_thanks']);
	$block_data['xperience_points_post_thanks_use'] = vb_number_format($stat_q['points_post_thanks_use']);
	$block_data['xperience_points_post_attachment'] = vb_number_format($stat_q['points_post_attachment']);
	$block_data['xperience_points_post_attachment_views'] = vb_number_format($stat_q['points_post_attachment_views']);

	($hook = vBulletinHook::fetch_hook('xperience_memberprofile')) ? eval($hook) : false;
	
	$block_data['xperience_points'] = vb_number_format($userinfo['xperience']);
	$block_data['xperience_level'] = vb_number_format($userinfo['xperience_level']);
	$block_data['xperience_levelp'] = vb_number_format($userinfo['xperience_levelp'], 1);
	$block_data['xperience_level_up'] = vb_number_format($userinfo['xperience_next_level']);
	$block_data['xperience_level_up_points'] = vb_number_format($userinfo['xperience_next_level_points']);
	$block_data['xperience_activity'] = vb_number_format($userinfo['xperience_ppd'], 1);
	$block_data['xperience_activity30'] = vb_number_format($userinfo['xperience_ppd30'], 1);
	$block_data['xperience_activity7'] = vb_number_format($userinfo['xperience_ppd7'], 1);
	
	
	$block_data['xperience_gfx_level'] = construct_phrase($vbphrase['xperience_gfx_level'], $block_data['xperience_points'], $block_data['xperience_level']); 
	$block_data['xperience_gfx_levelup'] = construct_phrase($vbphrase['xperience_gfx_levelup'], $block_data['xperience_level_up'], $block_data['xperience_level_up_points'], ""); 
	$block_data['xperience_gfx_activity'] = construct_phrase($vbphrase['xperience_gfx_activity'], $block_data['xperience_activity']); 
	$block_data['xperience_gfx_activity30'] = construct_phrase($vbphrase['xperience_gfx_activity'], $block_data['xperience_activity30']); 
	$block_data['xperience_gfx_activity7'] = construct_phrase($vbphrase['xperience_gfx_activity'], $block_data['xperience_activity7']); 


	if (!$vbulletin->options['xperience_points_datecut']==0)
	{
		$checkdatecut = strtotime($vbulletin->options['xperience_points_datecut']);	
		if ($checkdatecut > 0)
		{
			if ($userinfo['joindate'] > $checkdatecut)
			{
				$block_data['xperience_date_cut'] = vbdate($vbulletin->options['dateformat'], $userinfo['joindate'], false); 
			}
			else
			{
				$block_data['xperience_date_cut'] = vbdate($vbulletin->options['dateformat'], $checkdatecut, false);  				
			}
		}
	}
		
	
	
	
	if ($vbulletin->options['xperience_use_awards']) 
	{
		if ($vbulletin->options['xperience_award_block'] == 0) 
		{
			//$block_data['xperience_awards'] = GetAwards($userinfo['xperience_awards']);
		}
	}
	
	
	$date_start = mktime(0, 0, 0, date("m"), date("d") - 180 , date("Y"));
	$date_end = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
	$do = $_REQUEST['do'];
	if (strlen($do) < 1)
	{
		$do = "all";
	}
	require_once(DIR . '/includes/functions_xperience.php');
	GetActivityAll($date_start, $date_end, $do, 20, $userinfo['userid'], 1);
	$block_data['xperience_activities'] = $activity;

	
	
	$templater = vB_Template::create('xperience_memberinfo_tab_xp');
	$templater->register('selected_tab', $selected_tab);
	$templater->register('relpath', $relpath);
	$template_hook['profile_tabs_last'] .= $templater->render();
	
	
	
	$templater = vB_Template::create('xperience_memberinfo_block');
	$templater->register('selected_tab', $selected_tab);
	$templater->register('block_data', $block_data);
	$template_hook['profile_tabs'] .= $templater->render(); 

}

?>
