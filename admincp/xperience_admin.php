<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz / Phalynx. All Rights Reserved. # ||
|| #################################################################### ||
\*======================================================================*/


// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('cpuser', 'forum', 'user', 'cpglobal', 'maintenance');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_stats.php');
require_once(DIR . '/includes/functions_xperience.php');
require_once(DIR . '/includes/install_xperience.php');
require_once(DIR . '/includes/class_xperience.php');
// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminusers'))
{
	print_cp_no_permission();
}
 
// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['xperience_name']);


$vbulletin->input->clean_array_gpc('r', array(
	'do' => TYPE_STR,
	'id' => TYPE_INT,
	'perpage' => TYPE_UINT,
	'startat' => TYPE_UINT
));


$current_timestamp=mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));
if ($_REQUEST['do'] == 'manplugins')
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_manplugins'], 2);

	print_label_row("Plugin 1", '<input type="button" class="button" onclick="window.location.href(\'xperience_admin.php?do=manplugins&dostatus=1\');" tabindex="1" name="plugin1" value="active" /> <input type="button" class="button" onclick="window.location.href(\'xperience_admin.php?do=manplugins&dostatus=0\');" tabindex="1" name="plugin1" value="deactivate" />');
	print_table_footer(1);
	
	 
}

if ($_REQUEST['do'] == 'delactivities')
{
	print_form_header('xperience_admin', 'dodelactivities');
	print_table_header($vbphrase['xperience_admin_delactivities'], 1);
	print_description_row('<input type="checkbox" name="delpromotions" value="1" tabindex="1" />'.$vbphrase['xperience_promotions']);
	print_description_row('<input type="checkbox" name="delawards" value="1" tabindex="1" />'.$vbphrase['xperience_awards']);
	print_description_row('<input type="checkbox" name="delachievements" value="1" tabindex="1" />'.$vbphrase['xperience_achievements']);
	print_description_row('<input type="checkbox" name="delpoints" value="1" tabindex="1" />'.$vbphrase['xperience_points']);
	print_submit_row($vbphrase['go']);
	print_table_footer(1);
}

if ($vbulletin->GPC['do'] == 'dodelactivities') 
{
	
		$vbulletin->input->clean_array_gpc('r', array(
		'delpromotions' => TYPE_UINT,
		'delawards' => TYPE_UINT,
		'delachievements' => TYPE_UINT,
		'delpoints' => TYPE_UINT
	));
	
	if ($vbulletin->GPC['delpromotions'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_promotion_issues");
	}

	if ($vbulletin->GPC['delawards'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_award_issues");
	}

	if ($vbulletin->GPC['delachievements'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_log");
	}

	if ($vbulletin->GPC['delpoints'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_stats_changes");
	}
	
	print_done();
}



if ($_REQUEST['do'] == 'recount')
{
	print_form_header('xperience_admin', 'xperience_admin_recount_xp');
	print_table_header($vbphrase['xperience_admin_recount_xp'], 2, 0);
	print_description_row($vbphrase['xperience_admin_recount_xp_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 150);
	print_input_row($vbphrase['xperience_admin_user_id_to_start_at'], 'startat', 1);
	print_submit_row($vbphrase['xperience_admin_recount_xp']);
	
	print_form_header('xperience_admin', 'xperience_admin_recount_ppd');
	print_table_header($vbphrase['xperience_admin_recount_ppd'], 2, 0);
	print_description_row($vbphrase['xperience_admin_recount_ppd_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 500);
	print_input_row($vbphrase['xperience_admin_user_id_to_start_at'], 'startat', 1);
	print_submit_row($vbphrase['xperience_admin_recount_ppd']);
	
	print_form_header('xperience_admin', 'xperience_admin_recount_ach');
	print_table_header($vbphrase['xperience_admin_recount_ach'], 2, 0);
	print_description_row($vbphrase['xperience_admin_recount_ach_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 100);
	print_submit_row($vbphrase['xperience_admin_recount_ach']);
	
	print_form_header('xperience_admin', 'xperience_admin_recount_aw');
	print_table_header($vbphrase['xperience_admin_recount_aw'], 2, 0);
	print_description_row($vbphrase['xperience_admin_recount_aw_help']);
	print_submit_row($vbphrase['xperience_admin_recount_aw']);
	
	print_form_header('xperience_admin', 'xperience_admin_recount_pro');
	print_table_header($vbphrase['xperience_admin_recount_pro'], 2, 0);
	print_description_row($vbphrase['xperience_admin_recount_pro_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 500);
	print_input_row($vbphrase['xperience_admin_user_id_to_start_at'], 'startat', 1);
	print_submit_row($vbphrase['xperience_admin_recount_pro']);
	
	
}

if ($_REQUEST['do'] == 'xperience_admin_recount_ppd')
{
	
	$vbulletin->input->clean_array_gpc('r', array('avgppd' => TYPE_UINT)); 
	

	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 150;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	echo '<p>' . $vbphrase['xperience_admin_recount_ppd'] . '</p>';


	$xPerience =& new xPerience;
	if ($vbulletin->GPC['avgppd'] !== 1)
	{
		$avgppd = $xPerience->GetAVGPPD($vbulletin->options['xperience_ppd_days']);
		$avgppd7 = $xPerience->GetAVGPPD(7);
		$avgppd30 = $xPerience->GetAVGPPD(30);
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd." WHERE varname='xperience_avgppd'");		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd30." WHERE varname='xperience_avgppd30'");		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd7." WHERE varname='xperience_avgppd7'");		
		
		echo construct_phrase($vbphrase['processing_x'], "Average PPD ".$vbulletin->options['xperience_ppd_days']." days: ".$avgppd90) . "<br />\n";
		echo construct_phrase($vbphrase['processing_x'], "Average PPD 30 days: ".$avgppd30) . "<br />\n";
		echo construct_phrase($vbphrase['processing_x'], "Average PPD 7 days: ".$avgppd7) . "<br />\n";
		vbflush();
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_ppd=0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_ppd30=0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_ppd7=0");
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats SET points_user_activity=0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats SET points_user_activity30=0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats SET points_user_activity7=0");
		
	}
	
	$users = $vbulletin->db->query_read("SELECT 
		u.userid, u.lastactivity
		FROM " . TABLE_PREFIX . "user as u
		WHERE u.userid >= " . $vbulletin->GPC['startat'] . " AND u.userid < $finishat
		ORDER BY u.userid
		"); 

	while ($user = $vbulletin->db->fetch_array($users))
	{
		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		$userppd = $xPerience->GetPPD($vbulletin->options['xperience_ppd_days'], $user, $vbulletin->options['xperience_avgppd']); 
		$userppd30 = $xPerience->GetPPD(30, $user, $vbulletin->options['xperience_avgppd30']); 
		$userppd7 = $xPerience->GetPPD(7, $user, $vbulletin->options['xperience_avgppd7']); 
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_ppd=".$userppd.", xperience_ppd30=".$userppd30.", xperience_ppd7=".$userppd7." WHERE userid=".$user['userid']);
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats SET points_user_activity=".$userppd.", points_user_activity30=".$userppd30.", points_user_activity7=".$userppd7." WHERE userid=".$user['userid']);
		vbflush();
		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);

	}

	//$finishat++; 
	if ($checkmore = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_ppd&avgppd=1&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_ppd&amp;avgppd=1&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		echo construct_phrase($vbphrase['processing_x'], "Awards") . "<br />\n";
		ValidateActivity();
		$xPerience->CalculateAwards();		
		define('CP_REDIRECT', 'xperience_admin.php?do=recount');
		print_stop_message('updated_post_counts_successfully');
	}

}



if ($_REQUEST['do'] == 'xperience_admin_recount_xp')
{
	
	$vbulletin->input->clean_array_gpc('r', array('avgppd' => TYPE_UINT)); 

	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 150;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	echo '<p>' . $vbphrase['xperience_admin_recount_xp'] . '</p>';


	$xPerience =& new xPerience;
	if ($vbulletin->GPC['avgppd'] !== 1)
	{
		$avgppd = $xPerience->GetAVGPPD($vbulletin->options['xperience_ppd_days']);
		$avgppd7 = $xPerience->GetAVGPPD(7);
		$avgppd30 = $xPerience->GetAVGPPD(30);
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd." WHERE varname='xperience_avgppd'");		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd30." WHERE varname='xperience_avgppd30'");		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value=".$avgppd7." WHERE varname='xperience_avgppd7'");		
		
		echo construct_phrase($vbphrase['processing_x'], "Average PPD ".$vbulletin->options['xperience_ppd_days']." days: ".$avgppd90) . "<br />\n";
		echo construct_phrase($vbphrase['processing_x'], "Average PPD 30 days: ".$avgppd30) . "<br />\n";
		echo construct_phrase($vbphrase['processing_x'], "Average PPD 7 days: ".$avgppd7) . "<br />\n";
		vbflush();
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_ppd=0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_ppd30=0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_ppd7=0");
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats SET points_user_activity=0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats SET points_user_activity30=0");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats SET points_user_activity7=0");
		
	}
	
	$users = $vbulletin->db->query_read("SELECT 
		u.usertitle,
		u.customtitle,
		u.lastactivity, 
		u.username, 
		u.userid, 
		u.joindate, 
		u.lastactivity,
		u.reputation,
		u.usergroupid,
		u.membergroupids,
		u.ipoints,
		u.posts,
		u.xperience_ppd
		FROM " . TABLE_PREFIX . "user as u
		WHERE u.userid >= " . $vbulletin->GPC['startat'] . " AND u.userid < $finishat
		ORDER BY u.userid
		"); 

	while ($user = $vbulletin->db->fetch_array($users))
	{
		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		$xPerience->CalculateXP($user, 0); 
		vbflush();
		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);

	}

	//$finishat++; 
	if ($checkmore = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_xp&avgppd=1&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_xp&amp;avgppd=1&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		
		ValidateActivity();
		if ($vbulletin->options['xperience_use_awards'])
		{	
			echo construct_phrase($vbphrase['processing_x'], "Awards") . "<br />\n";
			$xPerience->CalculateAwards();
		}

		if ($vbulletin->options['xperience_use_groups'])
		{	
			echo construct_phrase($vbphrase['processing_x'], "Social Group Ranking") . "<br />\n";
			$groups = $vbulletin->db->query_read_slave("SELECT 
				groupid
				FROM " . TABLE_PREFIX . "socialgroup as s
				WHERE type='public' OR type='moderated' ");
			
			while ($group = $vbulletin->db->fetch_array($groups))
			{
				$xPerience->CalculateGroupXP($group, 0);
			}
		}
		
		define('CP_REDIRECT', 'xperience_admin.php?do=recount');
		print_stop_message('updated_post_counts_successfully');
	}

}
	
if ($_REQUEST['do'] == 'xperience_admin_recount_pro')
{
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 500;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	echo '<p>' . $vbphrase['xperience_admin_recount_pro'] . '</p>';


	$xPerience =& new xPerience;
	
	$users = $vbulletin->db->query_read("SELECT 
		u.usergroupid, 
		u.usertitle, 
		u.customtitle, 
		u.username, 
		u.userid
		FROM " . TABLE_PREFIX . "user as u
		WHERE u.userid >= " . $vbulletin->GPC['startat'] . " AND u.userid < $finishat
		ORDER BY u.userid
		"); 

	while ($user = $vbulletin->db->fetch_array($users))
	{
		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";

		DoPromotions($user, fetch_xperience($user['userid']));
		vbflush();
		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);
	}

	//$finishat++; 
	if ($checkmore = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_pro&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_pro&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		
		define('CP_REDIRECT', 'xperience_admin.php?do=recount');
		print_stop_message('updated_post_counts_successfully');
	}

}

if ($_REQUEST['do'] == 'xperience_admin_recount_aw')
{
	
	echo '<p>' . $vbphrase['xperience_admin_recount_aw'] . '</p>';

	require_once(DIR . '/includes/class_xperience.php');
	$xPerience =& new xPerience;
	$xPerience->CalculateAwards();

	define('CP_REDIRECT', 'xperience_admin.php?do=recount');
	print_stop_message('updated_post_counts_successfully');
}

if ($_REQUEST['do'] == 'xperience_admin_recount_ach')
{
	
	echo '<p>' . $vbphrase['xperience_admin_recount_ach'] . '</p>';

	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 150;
	}

	$processed = DoAchievements($vbulletin->GPC['perpage']);
	if ($processed > 0)
	{
			print_cp_redirect("xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_ach");
			echo "<p><a href=\"xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_ach\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'xperience_admin.php?do=recount');
		print_stop_message('updated_post_counts_successfully');
	}


}


if ($vbulletin->GPC['do'] == 'info') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_info'], 1);

	print_description_row("<b>vBExperience 4.0</b>");
	print_description_row("Created by Marius Czyz aka Phalynx.");
	print_description_row('Support via vBulletin.org: <a target="_blank" href="http://www.vbulletin.org/forum/misc.php?do=producthelp&pid=xperience40">Support Thread</a>');
	print_description_row('Support via vbAddict.net: <a target="_blank" href="http://www.vbAddict.net">Support Tracker</a>');
	print_description_row('The update to vBExperience 4.0 has been sponsered by <a target="_blank" href="http://www.t-tapp.com">T-Tapp.com</a>');
	
	print_description_row("Feel free to donate a small amount for my efforts.");
	print_table_footer(1);
	

	print_description_row('<div align="center">
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="ZS2CKBDNQXLAG">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
</form>
</div>');
		
	

} 

if ($vbulletin->GPC['do'] == 'mangap') 
{

	print_form_header('');

	$vbulletin->input->clean_array_gpc('r', array(
		'pagenumber' => TYPE_UINT,
		'orderby'    => TYPE_NOHTML,
	));

	$perpage = 25;
	$counter = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "xperience_gap AS g
	");
	$totalpages = ceil($counter['total'] / $perpage);

	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $perpage;

	print_table_header(construct_phrase($vbphrase['xperience_admin_mangap'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), 6);
	
	$gapq = $vbulletin->db->query_read("SELECT
		u.username AS uname, uto.username AS utoname, g.*
		FROM " . TABLE_PREFIX . "xperience_gap AS g
		INNER JOIN " . TABLE_PREFIX . "user AS u ON g.userid=u.userid
		INNER JOIN " . TABLE_PREFIX . "user AS uto ON g.toid=uto.userid
		ORDER BY g.dateline DESC, gapid DESC
		LIMIT ".$startat.", ".$perpage);
		
	
	if ($vbulletin->db->num_rows($gapq) > 0)
	{
		
		if ($vbulletin->GPC['pagenumber'] != 1)
		{
			$prv = $vbulletin->GPC['pagenumber'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" value=\"&laquo; " . $vbphrase['first_page'] . "\" tabindex=\"1\" onclick=\"window.location='xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=mangap&orderby=" . $vbulletin->GPC['orderby'] . "&page=1'\">";
			$prevpage = "<input type=\"button\" class=\"button\" value=\"&lt; " . $vbphrase['prev_page'] . "\" tabindex=\"1\" onclick=\"window.location='xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=mangap&orderby=" . $vbulletin->GPC['orderby'] . "&page=$prv'\">";
		}

		if ($vbulletin->GPC['pagenumber'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['pagenumber'] + 1;
			$nextpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['next_page'] . " &gt;\" tabindex=\"1\" onclick=\"window.location='xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=mangap&orderby=" . $vbulletin->GPC['orderby'] . "&page=$nxt'\">";
			$lastpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['last_page'] . " &raquo;\" tabindex=\"1\" onclick=\"window.location='xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=mangap&orderby=" . $vbulletin->GPC['orderby'] . "&page=$totalpages'\">";
		}
		
		
		$cellh = array();
		$cellh[] = $vbphrase['date'];
		$cellh[] = $vbphrase['username'];
		$cellh[] = $vbphrase['to'];
		$cellh[] = ""; 
		$cellh[] = ""; 
		$cellh[] = $vbphrase['delete']; 
		print_cells_row($cellh, true);


		while ($gap = $vbulletin->db->fetch_array($gapq))
		{
			$cell = array();
			$cell[] = vbdate($vbulletin->options['dateformat'] . ' ' .  $vbulletin->options['timeformat'], $gap['dateline']); 
			$cell[] = '<a href="'.$vbulletin->options['bburl'].'/member.php?u='.$gap['userid'].'">'.$gap['uname'].'</a>'; ;
			$cell[] = '<a href="'.$vbulletin->options['bburl'].'/member.php?u='.$gap['toid'].'">'.$gap['utoname'].'</a>'; ;
			$cell[] = $vbphrase['xperience_'.$gap[field]]." (".$gap[field].")"; 
			$cell[] = vb_number_format($gap['amount']); 
			$cell[] = '<a href="xperience_admin.php?do=delgap&gapid='.$gap['gapid'].'&userid1='.$gap['userid'].'&userid2='.$gap['toid'].'">'.$vbphrase['delete'].'</a>'; 
			print_cells_row($cell);
		}
	} else {
		$cellh = array();
		$cellh[] = $vbphrase['no_matches_found'];
		print_cells_row($cellh);
	}
		print_table_footer(6, "$firstpage $prevpage &nbsp; $nextpage $lastpage");
}

if ($vbulletin->GPC['do'] == 'delgap') 
{
	$vbulletin->input->clean_gpc('r', 'gapid', TYPE_UINT);
	$vbulletin->input->clean_gpc('r', 'userid1', TYPE_UINT);
	$vbulletin->input->clean_gpc('r', 'userid2', TYPE_UINT);

	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_gap WHERE gapid=".$vbulletin->GPC['gapid']);

	$calcuser1 = fetch_userinfo($vbulletin->GPC['userid1']);
	$calcuser2 = fetch_userinfo($vbulletin->GPC['userid2']);

 	$xPerience =& new xPerience;
 	$xPerience->CalculateXP($calcuser1, 0);
 	$xPerience->CalculateXP($calcuser2, 0);
 	
	print_cp_message('Redirecting...', 'xperience_admin.php?do=mangap', 0);
}


if ($vbulletin->GPC['do'] == 'mancpoints') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_mancpoints'], 1);

	$pointsq =$vbulletin->db->query_read("SELECT
		user.username, c.*
		FROM " . TABLE_PREFIX . "xperience_custompoints as c
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (c.userid = user.userid)
		ORDER BY dateline
		");
		
		

	
		
		if ($vbulletin->db->num_rows($pointsq) > 0)
		{
			while ($points = $vbulletin->db->fetch_array($pointsq)) 
			{
				print_description_row('<a href="xperience_admin.php?do=editpoints&id='.$points['pointid'].'">'.vbdate($vbulletin->options['dateformat'] . ' ' . $vbulletin->options['timeformat'], $points['dateline']).', '.$points['username'].'</a><dfn>Points: '.$points['points_misc_custom'].', '.$points['comment'].' ('.$points['category'].')</dfn>');
			}
		} else {
				print_description_row($vbphrase['xperience_admin_no_custom_points']);
		}
		print_table_footer(1);

	print_form_header('');
	print_table_header('Add Points', 1);
	print_description_row('<a href="xperience_admin.php?do=addpoints">'.$vbphrase[xperience_admin_add_custom_points].'</a>');		
	print_table_footer(1);
}
if ($vbulletin->GPC['do'] == 'addpoints') 
{

	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_custompoints (adminid, dateline) VALUES (".$vbulletin->userinfo['userid'].", ".$current_timestamp.")");
	print_cp_message('Redirecting...', 'xperience_admin.php?do=editpoints&id='.$vbulletin->db->insert_id(), 0);

}
 
if ($vbulletin->GPC['do'] == 'editpoints') 
{
	print_form_header('xperience_admin', 'savepoints');
	print_table_header($vbphrase['xperience_admin_edit_custom_points'], 2, 0);
	
	$pointsq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_custompoints WHERE pointid=".$vbulletin->GPC['id']);
		
		if ($vbulletin->db->num_rows($pointsq) > 0)
		{
			$points = $vbulletin->db->fetch_array($pointsq);
			
			
			print_input_row($vbphrase['userid'], 'userid', $points['userid']);	
			print_input_row($vbphrase['xperience_admin_custom_points_neg'], 'points_misc_custom', $points['points_misc_custom']);	
			print_input_row($vbphrase['comment'], 'comment', $points['comment']);	
			print_input_row($vbphrase['category'], 'category', $points['category']);	
			print_checkbox_row($vbphrase['delete'], 'delpoints', false, 1);
			echo "<input type=\"hidden\" name=\"pointid\" value=\"" . $points['pointid'] . "\" />\n";
			print_submit_row($vbphrase['save']);
	}

}
 
 
 if ($vbulletin->GPC['do'] == 'savepoints') 
 {

	$vbulletin->input->clean_array_gpc('r', array(
		'userid' => TYPE_INT,
		'points_misc_custom' => TYPE_INT,
		'comment' => TYPE_STR,
		'category' => TYPE_STR,
		'delpoints' => TYPE_INT,
		'pointid' => TYPE_INT
	));

	if ($vbulletin->GPC['delpoints'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_custompoints WHERE pointid=".$vbulletin->GPC['pointid']);
	}
	else
	{
		if ($vbulletin->GPC['userid'] == 0)
		{
			$vbulletin->GPC['userid'] = $vbulletin->userinfo['userid'];
		}
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_custompoints SET
		userid=".$vbulletin->GPC['userid'].",
		points_misc_custom=".$vbulletin->GPC['points_misc_custom'].",
		comment='".addslashes($vbulletin->GPC['comment'])."',
		category='".addslashes($vbulletin->GPC['category'])."'
		WHERE pointid=".$vbulletin->GPC['pointid']);
	}
	
	require_once(DIR . '/includes/class_xperience.php');
	$xPerience =& new xPerience;
	
	
	if (verify_id("user", $vbulletin->GPC['userid'], false))
	{
		$calcuser = fetch_userinfo($vbulletin->GPC['userid']);	
		$xPerience->CalculateXP($calcuser, 0);
	}
	$xPerience->CalculateAwards();
		
	print_done("xperience_admin.php?do=mancpoints");
}
 
 

if ($vbulletin->GPC['do'] == 'delstats') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_delete_stats'], 3);
	print_description_row('<a href="xperience_admin.php?do=dodelstats"">'.$vbphrase[xperience_admin_delete_confirm].'</a>');
	print_table_footer(1);
}

if ($vbulletin->GPC['do'] == 'dodelstats') 
{
	
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_stats");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
		SET
		xperience_done=1, 
		xperience=0,
		xperience_level=0,
		xperience_next_level=0,
		xperience_next_level_points=0,
		xperience_ppd=0");
		
	print_done();
}

function ManProTableHeader($title)
{
	if (strlen($title)==0)
	{
		$title= "?";
	}
	
	global $vbphrase;
	print_table_start();
	print_table_header($vbphrase['xperience_admin_manpro'].": ".$title, 7);
	
	$cellh = array();
	$cellh[] = $vbphrase['xperience_admin_promotions_fieldname'];
	$cellh[] = $vbphrase['xperience_admin_promotions_fieldcompare'];
	$cellh[] = $vbphrase['xperience_admin_promotions_fieldvalue'];
	$cellh[] = $vbphrase['usergroup'];
	$cellh[] = $vbphrase['display_order']."<br/>".$vbphrase['options'];
	$cellh[] = $vbphrase['save'];
	print_cells_row($cellh, true);
}


if ($vbulletin->GPC['do'] == 'manpro') 
{
	//print_form_header('');
	//ManProTableHeader();

	$promotionsfieldsq =$vbulletin->db->query_read("SELECT p.*, u.title FROM " . TABLE_PREFIX . "xperience_promotion AS p
		LEFT JOIN " . TABLE_PREFIX . "usergroup AS u ON u.usergroupid=p.from_ug
		ORDER BY p.sortorder, p.field");
	$old_ug = -1;
	
	
	if ($vbulletin->db->num_rows($promotionsfieldsq) > 0)
	{
		while ($promotionsfields = $vbulletin->db->fetch_array($promotionsfieldsq)) 
		{
			if ($old_ug<>$promotionsfields['from_ug'])
			{
				if ($old_ug<>-1)
				{
					print_table_footer(1);
				}
			
				ManProTableHeader($promotionsfields['title']);
			}
			
			$old_ug = $promotionsfields['from_ug'];
			
			
			
			$cell = array();
			
			$fields = array();
			$sel_fields = array();
			
	
			$optionsq =$vbulletin->db->query_read("SHOW COLUMNS
				FROM " . TABLE_PREFIX . "xperience_stats LIKE 'points_%'");
					
			if ($vbulletin->db->num_rows($optionsq) > 0)
			{
				while ($options = $vbulletin->db->fetch_array($optionsq)) 
				{
					$fields["$options[Field]"] = $vbphrase['xperience_'.$options[Field]]; 
					if (strlen($fields["$options[Field]"])<1)
					{
						$fields["$options[Field]"] = $options["Field"];
					}
				}
			}
			array_multisort($fields, SORT_ASC, SORT_STRING);
		
		
			$cell[] = print_select($vbphrase['xperience_admin_achievements_fields'], 'field', $fields, $promotionsfields['field']);
		
			$compare['-1'] = "<";
			$compare['0'] = "=";
			$compare['1'] = ">";
			$cell[] = print_select($vbphrase['xperience_admin_achievements_compare'], 'compare', $compare, $promotionsfields['compare']);
			
			$cell[] = '<input type="text" class="bginput" name="value" id="it_value_1" value="'.$promotionsfields['value'].'" size="20" dir="ltr" tabindex="1" />';
			
			$ugfields = array();
			$sel_fields = array();
			$usergroupsq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "usergroup
			ORDER BY title");
					
			if ($vbulletin->db->num_rows($usergroupsq) > 0)
			{
				while ($usergroups = $vbulletin->db->fetch_array($usergroupsq)) 
				{
					$ugfields["$usergroups[usergroupid]"] = $usergroups['title'];
				}
			}
			//array_multisort($ugfields, SORT_ASC, SORT_STRING);
			
			$cell[] = print_select($vbphrase['xperience_admin_achievements_fields'], 'from_ug', $ugfields, $promotionsfields['from_ug'])."<br/>".print_select($vbphrase['xperience_admin_achievements_fields'], 'to_ug', $ugfields, $promotionsfields['to_ug']);;
									
			$cell[] = '<input type="text" class="bginput" name="sortorder" id="it_value_1" value="'.$promotionsfields['sortorder'].'" size="5" dir="ltr" tabindex="1" /><br/><input disabled type="checkbox" name="demote" value="1" tabindex="1" />'.$vbphrase['xperience_admin_promotions_demote'].'<br/><input type="checkbox" name="delpro" value="1" tabindex="1" />'.$vbphrase['delete'];
			$cell[] = '<input type="submit" class="button" tabindex="1" value="'.$vbphrase['save'].'" accesskey="s" />';
			
			
			echo '<form action="xperience_admin.php?do=savepro&id='.$promotionsfields['promotionid'].'" method="post" name="proform'.$promotionsfields['promotionid'].'" id="proform'.$promotionsfields['promotionid'].'">';

			print_cells_row($cell);
			if (strlen($promotionsfields['comment']) == 0)
			{
				$promotionsfields['comment'] = "Comment";
			}
			print_description_row('<input type="text" class="bginput" name="comment" id="it_value_1" value="'.$promotionsfields['comment'].'" size="100" dir="ltr" tabindex="1" />', false, 8);
			
			
			echo '</form>';
			
		}
		
		
		
	}
	
	if ($old_ug==-1)
	{
		ManProTableHeader($promotionsfields['title']);
	}
			
				
	
	$cellf = array();
	$cellf[] = '<a href="xperience_admin.php?do=addpro">'.$vbphrase[xperience_admin_promotions_add].'</a>';
	$cellf[] = '&nbsp;';
	$cellf[] = '&nbsp;';
	$cellf[] = '&nbsp;';
	$cellf[] = '&nbsp;';
	$cellf[] = '&nbsp;';
	print_cells_row($cellf);

	

	print_table_footer(1);

	print_table_start(1);
	print_table_header($vbphrase['xperience_promotions'], 1);
	print_description_row($vbphrase['xperience_admin_promotions_notices']);	
	print_description_row('<a href="xperience_admin.php?do=importproxp">'.$vbphrase[xperience_admin_promotions_importproxp].'</a>');
	//print_description_row('<a href="xperience_admin.php?do=importprovb">'.$vbphrase[xperience_admin_promotions_importprovb].'</a>');
	print_description_row('<a href="xperience_admin.php?do=clearpro">'.$vbphrase[xperience_admin_promotions_clear].'</a>');
	print_description_row('<a href="options.php?do=options&dogroup=xperience_promotions">'.$vbphrase[options].'</a>');
	print_table_footer(1);
}

if ($vbulletin->GPC['do'] == 'importproxp') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_manpro'], 1);
	print_description_row($vbphrase['xperience_admin_promotions_importproxp']);
	print_description_row('<a href="xperience_admin.php?do=doimportproxp">'.$vbphrase[yes].'</a> - <a href="xperience_admin.php?do=manpro">'.$vbphrase[no].'</a>');
	print_table_footer(1);

}

if ($vbulletin->GPC['do'] == 'doimportproxp') 
{
	$queryq =$vbulletin->db->query_read("SELECT
		*
		FROM " . TABLE_PREFIX . "xperience_level
		GROUP BY usergroupid
		ORDER BY xperience_points
	");
				
		if ($vbulletin->db->num_rows($queryq) > 0)
		{
			$from_ug = 2;
			while ($query = $vbulletin->db->fetch_array($queryq)) 
			{
				$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_promotion (field, value, compare, from_ug, to_ug) VALUES
				(
				'points_xperience',
				".$query['xperience_points'].",
				1,
				".$from_ug.",
				".$query['usergroupid']."
				)");
				$from_ug = $query['usergroupid'];
			}
		}
	print_cp_message('Redirecting...', 'xperience_admin.php?do=manpro', 0);
}



if ($vbulletin->GPC['do'] == 'clearpro') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_manpro'], 1);
	print_description_row($vbphrase['xperience_admin_promotions_clear']);
	print_description_row('<a href="xperience_admin.php?do=doclearpro">'.$vbphrase[yes].'</a> - <a href="xperience_admin.php?do=manpro">'.$vbphrase[no].'</a>');
	print_table_footer(1);

}

if ($vbulletin->GPC['do'] == 'doclearpro') 
{
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_promotion");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_promotion_issues");
	//$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_promotion_log");
	print_cp_message('Redirecting...', 'xperience_admin.php?do=manpro', 0);

}
if ($vbulletin->GPC['do'] == 'addpro') 
{
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_promotion (field, value, compare) VALUES ('points_xperience', 9999999, 1)");
	print_cp_message('Redirecting...', 'xperience_admin.php?do=manpro', 0);
}

if ($vbulletin->GPC['do'] == 'savepro') 
{

	$vbulletin->input->clean_array_gpc('r', array(
		'field' => TYPE_STR,
		'comment' => TYPE_STR,
		'value' => TYPE_INT,
		'compare' => TYPE_INT,
		'from_ug' => TYPE_INT,
		'to_ug' => TYPE_INT,
		'sortorder' => TYPE_INT,
		'delpro' => TYPE_UINT,
		'demote' => TYPE_UINT
	));



	if ($vbulletin->GPC['delpro'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_promotion WHERE promotionid=".$vbulletin->GPC['id']);
	}
	elseif ($vbulletin->GPC['demote'] == 1) 
	{
		print_cp_message('Redirecting...', 'xperience_admin.php?do=xperience_admin_recount_pro_demote&id='.$vbulletin->GPC['id'].'&from_ug='.$vbulletin->GPC['from_ug'].'&to_ug='.$vbulletin->GPC['to_ug'], 0);
	}
	else
	{
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_promotion SET
			field='".addslashes($vbulletin->GPC['field'])."',
			comment='".addslashes($vbulletin->GPC['comment'])."',
			value=".$vbulletin->GPC['value'].",
			compare=".$vbulletin->GPC['compare'].",
			from_ug=".$vbulletin->GPC['from_ug'].",
			to_ug=".$vbulletin->GPC['to_ug'].",
			sortorder=".$vbulletin->GPC['sortorder']."
			WHERE promotionid=".$vbulletin->GPC['id']);
	}
	
	
	print_cp_message('Redirecting...', 'xperience_admin.php?do=manpro', 0);
}

	
if ($_REQUEST['do'] == 'xperience_admin_recount_pro_demote')
{
	
	$vbulletin->input->clean_array_gpc('r', array(
		'from_ug' => TYPE_INT,
		'to_ug' => TYPE_INT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 200;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	echo '<p>' . $vbphrase['xperience_admin_recount_pro'] . '</p>';

	$users = $db->query_read("SELECT
		usertitle, customtitle, userid
		FROM " . TABLE_PREFIX . "user
		WHERE usergroupid=".$vbulletin->GPC['to_ug']."
		AND userid >= " . $vbulletin->GPC['startat'] . " AND userid < $finishat
	");

	while ($user = $db->fetch_array($users))
	{
		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		PromoteUser($user, $vbulletin->GPC['to_ug'], $vbulletin->GPC['from_ug'], $vbulletin->GPC['id'], 2);
		vbflush();
		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);

	}

	//$finishat++; 
	if ($checkmore = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("xperience_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=xperience_admin_recount_pro_demote&id=".$vbulletin->GPC['id']."&from_ug=".$vbulletin->GPC['from_ug']."&to_ug=".$vbulletin->GPC['to_ug']."&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
	}
	else
	{
		
		define('CP_REDIRECT', 'xperience_admin.php?do=manpro');
		print_stop_message('updated_post_counts_successfully');
	}

}


//####################################################
if ($vbulletin->GPC['do'] == 'manach') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_manach'], 1);

	$awardcatq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements_categories ORDER BY categoryorder");
	if ($vbulletin->db->num_rows($awardcatq) > 0)
	{
		while ($awardcat = $vbulletin->db->fetch_array($awardcatq))
		{

			print_description_row("<b>".$awardcat['categorytitle']."</b> (<a href=\"xperience_admin.php?do=editachcat&id=".$awardcat['categoryid']."\">".$vbphrase['edit']."</a>)");

			$dataq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements
				WHERE categoryid=".$awardcat['categoryid']." 
				ORDER BY sortorder");
				
			if ($vbulletin->db->num_rows($dataq) > 0)
			{
				while ($data = $vbulletin->db->fetch_array($dataq))
				{
					PrintAchievementRow($data);
				}
			}
			else
			{
					print_description_row($vbphrase['xperience_admin_achievements_none']);
			}
		}
	}
	
	
	$awardsq = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements AS A
		LEFT JOIN " . TABLE_PREFIX . "xperience_achievements_categories AS C ON A.categoryid=C.categoryid
		WHERE C.categoryid IS NULL
		");
	if ($vbulletin->db->num_rows($awardsq) > 0)
	{
		print_description_row("<b>?</b>");
		while ($awards = $vbulletin->db->fetch_array($awardsq)) 
		{
			PrintAchievementRow($awards);
		}
	}

	
	print_table_footer(1);

	print_form_header('');
	print_table_header($vbphrase['xperience_achievements'], 1);
	print_description_row('<a href="xperience_admin.php?do=addachcat">'.$vbphrase[xperience_admin_achievements_add_category].'</a>');
	print_description_row('<a href="xperience_admin.php?do=addach">'.$vbphrase[xperience_admin_achievements_add].'</a>');
	print_description_row('<a href="xperience_admin.php?do=adddefaultach">'.$vbphrase[xperience_admin_achievements_add_default].'</a>');
	print_description_row('<a href="xperience_admin.php?do=clearachissues">'.$vbphrase[xperience_admin_achievements_clear_issues].'</a>');
	print_description_row('<a href="xperience_admin.php?do=clearach">'.$vbphrase[xperience_admin_achievements_clear].'</a>');
	print_table_footer(1);
}


function PrintAchievementRow($data)
{
	global $vbulletin, $vbphrase;

	if (strlen($data['imagesmall']) > 0)
	{
		$image = '<img class="inlineimg" border="0" src="'.$vbulletin->options['bburl'].'/xperience/icons/'.$data['imagesmall'].'" title="'.$data['title'].'" />';
	}
	else
	{
		$image = '<img class="inlineimg" border="0" src="'.$vbulletin->options['bburl'].'/xperience/images/icon_achievements_default.png" title="'.$data['title'].'" />';
	}
	print_description_row($image." ".$data['title']." (<a href=\"xperience_admin.php?do=editach&id=".$data['achievementid']."\">".$vbphrase['edit']."</a>)");

}




if ($vbulletin->GPC['do'] == 'addach') 
{
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_achievements (title, description) VALUES ('New Achievement!', 'Edit the details')");
	print_cp_message('Redirecting...', 'xperience_admin.php?do=editach&id='.$vbulletin->db->insert_id(), 0);
}

if ($vbulletin->GPC['do'] == 'clearachissues') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_manach'], 1);
	print_description_row($vbphrase['xperience_admin_achievements_clear_issues']);
	print_description_row('<a href="xperience_admin.php?do=doclearachissues">'.$vbphrase[yes].'</a> - <a href="xperience_admin.php?do=manach">'.$vbphrase[no].'</a>');
	print_table_footer(1);

}


if ($vbulletin->GPC['do'] == 'doclearachissues') 
{
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_issues");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_log");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_achievements=''"); 
	print_cp_message('Redirecting...', 'xperience_admin.php?do=manach', 0);

}


if ($vbulletin->GPC['do'] == 'adddefaultach') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_manach'], 1);
	print_description_row($vbphrase['xperience_admin_achievements_add_default']);
	print_description_row('<a href="xperience_admin.php?do=doadddefaultach">'.$vbphrase[yes].'</a> - <a href="xperience_admin.php?do=manach">'.$vbphrase[no].'</a>');
	print_table_footer(1);

}


if ($vbulletin->GPC['do'] == 'doadddefaultach') 
{
	CreateDefaultAchievements();
	print_cp_message('Redirecting...', 'xperience_admin.php?do=manach', 0);

}

if ($vbulletin->GPC['do'] == 'clearach') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_manach'], 1);
	print_description_row($vbphrase['xperience_admin_achievements_clear']);
	print_description_row('<a href="xperience_admin.php?do=doclearach">'.$vbphrase[yes].'</a> - <a href="xperience_admin.php?do=manach">'.$vbphrase[no].'</a>');
	print_table_footer(1);

}

if ($vbulletin->GPC['do'] == 'doclearach') 
{
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_fields");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_categories");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_issues");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_log");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_achievements=''"); 
	CreateDefaultAchievements();	
	print_cp_message('Redirecting...', 'xperience_admin.php?do=manach', 0);

}


if ($vbulletin->GPC['do'] == 'editachcat') 
{
	print_form_header('xperience_admin', 'saveachcat');
	print_table_header($vbphrase['xperience_achievements_award_cat_edit'], 2, 0);
	
	$achievementscatq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements_categories WHERE categoryid=".$vbulletin->GPC['id']);
		
		if ($vbulletin->db->num_rows($achievementscatq) > 0)
		{
			$achievementscat = $vbulletin->db->fetch_array($achievementscatq);
			
			print_input_row($vbphrase['xperience_admin_achievements_cat_title'], 'categorytitle', $achievementscat['categorytitle']);	
			print_input_row($vbphrase['xperience_admin_achievements_cat_desc'], 'categorydesc', $achievementscat['categorydesc']);
			print_input_row("$vbphrase[display_order]<dfn>$vbphrase[zero_equals_no_display]</dfn>", 'categoryorder', $achievementscat['categoryorder']);
			
			print_checkbox_row($vbphrase['delete'], 'delcategory', false, 1);
			echo "<input type=\"hidden\" name=\"categoryid\" value=\"" . $achievementscat['categoryid'] . "\" />\n";
			print_submit_row($vbphrase['save']);
	}

}

if ($vbulletin->GPC['do'] == 'addachcat') 
{
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_achievements_categories (categorytitle, categorydesc, categoryorder) VALUES ('New Category', 'Description for new Category', 99)");
	print_cp_message('Redirecting...', 'xperience_admin.php?do=editachcat&id='.$vbulletin->db->insert_id(), 0);

}

if ($vbulletin->GPC['do'] == 'saveachcat') 
{

	$vbulletin->input->clean_array_gpc('r', array(
		'categorytitle' => TYPE_STR,
		'categorydesc' => TYPE_STR,
		'categoryorder' => TYPE_INT,
		'categoryid' => TYPE_INT,
		'delcategory' => TYPE_UINT
	));



	if ($vbulletin->GPC['delcategory'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_categories WHERE categoryid=".$vbulletin->GPC['categoryid']);
	} else {
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_achievements_categories SET
		categorytitle='".addslashes($vbulletin->GPC['categorytitle'])."',
		categorydesc='".addslashes($vbulletin->GPC['categorydesc'])."',
		categoryorder=".addslashes($vbulletin->GPC['categoryorder'])."
		WHERE categoryid=".$vbulletin->GPC['categoryid']);
	}
	
	print_done("xperience_admin.php?do=manach");
}
###############



if ($vbulletin->GPC['do'] == 'editach') 
{
	print_form_header('xperience_admin', 'saveach');
	print_table_header($vbphrase['xperience_admin_achievements_edit'], 2, 0);
	
	$achievementsq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements
	WHERE achievementid=".$vbulletin->GPC['id']);
		
		if ($vbulletin->db->num_rows($achievementsq) > 0)
		{
			$achievements = $vbulletin->db->fetch_array($achievementsq);

			print_input_row($vbphrase['title'], 'title', $achievements['title']);				
			print_textarea_row($vbphrase['description'], 'description', $achievements['description'], 5, 80);
			print_checkbox_row($vbphrase['delete'], 'delachievement', false, 1);

			print_table_header($vbphrase['options'], 2, 0);
			print_checkbox_row($vbphrase['xperience_admin_achievements_delissued'], 'delissued', false, 1);
			print_checkbox_row($vbphrase['xperience_admin_achievements_canlose'], 'canlose', $achievements['canlose'], 1);
			print_checkbox_row($vbphrase['xperience_admin_achievements_canpurchase'], 'canpurchase', $achievements['canpurchase'], 1);
			print_checkbox_row($vbphrase['xperience_admin_achievements_issecret'], 'issecret', $achievements['issecret'], 1);
			
			$rpls["0"] = "-";
			$achievementreplaceq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements ORDER BY categoryid, sortorder, title");
			if ($vbulletin->db->num_rows($achievementreplaceq) > 0)
			{
				while ($achievementreplace = $vbulletin->db->fetch_array($achievementreplaceq))
				{
					$rpls["$achievementreplace[achievementid]"] = $achievementreplace['title'];
				}
				
			}
			print_select_row($vbphrase['xperience_admin_achievements_replace'], "replaceid", $rpls, $achievements['replaceid']);
			
			print_table_header($vbphrase['xperience_admin_achievements_edit_path'], 2, 0);
			//print_input_row($vbphrase['xperience_admin_achievements_imagesmall'], 'imagesmall', $achievements['imagesmall'], true, 80);
			//print_input_row($vbphrase['xperience_admin_achievements_imagebig'], 'imagebig', $achievements['imagebig'], true, 80);
			

		
			
			$imagesmall .= "<select name=\"imagesmall\" onchange=\"document.getElementById('oIMG16').src='".$vbulletin->options['bburl']."/xperience/icons/' + this.options[this.selectedIndex].value;\" >";
			
			$files16 = GetFiles("_16.png");
			foreach ($files16 as $file)
			{
				$selected = iif($achievements['imagesmall'] == $file, "selected ");
	      $imagesmall .= '<option '.$selected.'value="'.$file.'" >'.$file.'</option>';
			}
			$imagesmall .= '</select>';
			$imagesmall .= '<img id="oIMG16" src="'.$vbulletin->options['bburl'].'/xperience/icons/'.$achievements['imagesmall'].'" width="16" height="16" />';
			print_label_row($vbphrase['xperience_admin_achievements_imagesmall'], $imagesmall);
			

			$imagebig = "<select name=\"imagebig\" onchange=\"document.getElementById('oIMG32').src='".$vbulletin->options['bburl']."/xperience/icons/' + this.options[this.selectedIndex].value;\" >";
			
			$files32 = GetFiles("_32.png");
			foreach ($files32 as $file)
			{
				$selected = iif($achievements['imagebig'] == $file, "selected ");
	      $imagebig .= '<option '.$selected.'value="'.$file.'" >'.$file.'</option>';
			}
			
			$imagebig .= '</select>';
			$imagebig .= '<img id="oIMG32" src="'.$vbulletin->options['bburl'].'/xperience/icons/'.$achievements['imagebig'].'" width="32" height="32" />';
				
			print_label_row($vbphrase['xperience_admin_achievements_imagebig'], $imagebig);		
			
			
			
			print_table_header($vbphrase['xperience_admin_achievements_edit_settings'], 2, 0);
			
			print_input_row("$vbphrase[display_order]<dfn>$vbphrase[zero_equals_no_display]</dfn>", 'sortorder', $achievements['sortorder']);
			$achievementscatq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements_categories ORDER BY categoryorder");
			if ($vbulletin->db->num_rows($achievementscatq) > 0)
			{
				while ($achievementscat = $vbulletin->db->fetch_array($achievementscatq))
				{
					$cats["$achievementscat[categoryid]"] = $achievementscat['categorytitle'];
				}
				
			}
			print_select_row($vbphrase['xperience_admin_awards_category'], "categoryid", $cats, $achievements['categoryid']);

			echo "<input type=\"hidden\" name=\"achievementid\" value=\"" . $achievements['achievementid'] . "\" />\n";
			
	

		print_submit_row($vbphrase['save'], $vbphrase['reset'], 5);
		
		print_table_start();
		print_table_header($vbphrase['xperience_admin_achievements_edit_fields'], 5, 0);
	
	
		$cellh = array();
		$cellh[] = $vbphrase['xperience_admin_achievements_fieldname'];
		$cellh[] = $vbphrase['xperience_admin_achievements_fieldcompare'];
		$cellh[] = $vbphrase['xperience_admin_achievements_fieldvalue'];
		$cellh[] = $vbphrase['delete'];
		$cellh[] = $vbphrase['save'];
		print_cells_row($cellh, true);
	
		$achievementsfieldsq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_achievements_fields
		WHERE achievementid=".$vbulletin->GPC['id']."
		ORDER BY field");
		
		if ($vbulletin->db->num_rows($achievementsfieldsq) > 0)
		{
			while ($achievementsfields = $vbulletin->db->fetch_array($achievementsfieldsq)) 
			{
				
				
				$cell = array();
				
				$fields = array();
				$sel_fields = array();
				$optionsq =$vbulletin->db->query_read("SHOW COLUMNS
				FROM " . TABLE_PREFIX . "xperience_stats LIKE 'points_%'");
						
				if ($vbulletin->db->num_rows($optionsq) > 0)
				{
					while ($options = $vbulletin->db->fetch_array($optionsq)) 
					{
						$fields["$options[Field]"] = $vbphrase['xperience_'.$options[Field]]; 
						if (strlen($fields["$options[Field]"])<1)
						{
							$fields["$options[Field]"] = $options["Field"];
						}
					}
				}
				array_multisort($fields, SORT_ASC, SORT_STRING);
			
			
				$cell[] = print_select($vbphrase['xperience_admin_achievements_fields'], 'field', $fields, $achievementsfields['field']);
					
				$compare['-1'] = $vbphrase['xperience_achievements_m1'];
				$compare['0'] = $vbphrase['xperience_achievements_0'];
				$compare['1'] = $vbphrase['xperience_achievements_1'];
				$cell[] = print_select($vbphrase['xperience_admin_achievements_compare'], 'compare', $compare, $achievementsfields['compare']);
				
				$cell[] = '<input type="text" class="bginput" name="value" id="it_value_1" value="'.$achievementsfields['value'].'" size="20" dir="ltr" tabindex="1" />';
				
				$cell[] = '<input type="checkbox" name="delachievementfield" value="1" tabindex="1" />'.$vbphrase['delete'];
				$cell[] = '<input type="submit" class="button" tabindex="1" value="'.$vbphrase['save'].'" accesskey="s" />';
				
				
				echo '<form action="xperience_admin.php?do=saveachfield&id='.$achievements['achievementid'].'&fieldid='.$achievementsfields['fieldid'].'" method="post" name="achform'.$achievementsfields['fieldid'].'" id="achform'.$achievementsfields['fieldid'].'">';
				
				print_cells_row($cell);
				
				echo '</form>';
				
			}
		}

		$cellf = array();
		$cellf[] = '<a href="xperience_admin.php?do=addachfield&id='.$achievements['achievementid'].'">'.$vbphrase[xperience_admin_achievements_add_field].'</a>';
		$cellf[] = '&nbsp;';
		$cellf[] = '&nbsp;';
		$cellf[] = '&nbsp;';
		$cellf[] = '&nbsp;';
		print_cells_row($cellf);

		
		print_table_footer(5);
		

	}

}


if ($vbulletin->GPC['do'] == 'addachfield') 
{
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_achievements_fields (achievementid, field, value, compare) VALUES (".$vbulletin->GPC['id'].", 'points_xperience', 10, 1)");
	print_cp_message('Redirecting...', 'xperience_admin.php?do=editach&id='.$vbulletin->GPC['id'], 0);
}


if ($vbulletin->GPC['do'] == 'saveachfield') 
{

	$vbulletin->input->clean_array_gpc('r', array(
		'fieldid' => TYPE_INT,
		'field' => TYPE_STR,
		'value' => TYPE_INT,
		'compare' => TYPE_INT,
		'delachievementfield' => TYPE_UINT
	));



	if ($vbulletin->GPC['delachievementfield'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_fields WHERE fieldid=".$vbulletin->GPC['fieldid']);
	}
	else
	{
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_achievements_fields SET
			field='".addslashes($vbulletin->GPC['field'])."',
			value=".$vbulletin->GPC['value'].",
			compare=".$vbulletin->GPC['compare']."
			WHERE fieldid=".$vbulletin->GPC['fieldid']);
	}
	
	
	print_cp_message('Redirecting...', 'xperience_admin.php?do=editach&id='.$vbulletin->GPC['id'], 0);
}




if ($vbulletin->GPC['do'] == 'saveach') 
{

	$vbulletin->input->clean_array_gpc('r', array(
		'title' => TYPE_STR,
		'description' => TYPE_STR,
		'imagesmall' => TYPE_STR,
		'imagebig' => TYPE_STR,
		'sortorder' => TYPE_INT,
		'categoryid' => TYPE_INT,
		'achievementid' => TYPE_UINT,
		'canlose' => TYPE_UINT,
		'canpurchase' => TYPE_UINT,
		'issecret' => TYPE_UINT,
		'delachievement' => TYPE_UINT,
		'delissued' => TYPE_UINT,
		'replaceid' => TYPE_UINT
		
	));



	if ($vbulletin->GPC['delachievement'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_issues WHERE achievementid=".$vbulletin->GPC['achievementid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_fields WHERE achievementid=".$vbulletin->GPC['achievementid']);
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements WHERE achievementid=".$vbulletin->GPC['achievementid']);
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_achievements=''"); 
	}
	else
	{
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_achievements SET
			title='".addslashes($vbulletin->GPC['title'])."',
			description='".addslashes($vbulletin->GPC['description'])."',
			imagesmall='".addslashes($vbulletin->GPC['imagesmall'])."',
			imagebig='".addslashes($vbulletin->GPC['imagebig'])."',
			canlose=".$vbulletin->GPC['canlose'].",
			canpurchase=".$vbulletin->GPC['canpurchase'].",
			issecret=".$vbulletin->GPC['issecret'].",
			sortorder=".$vbulletin->GPC['sortorder'].",
			categoryid=".$vbulletin->GPC['categoryid'].",
			replaceid=".$vbulletin->GPC['replaceid']."
			WHERE achievementid=".$vbulletin->GPC['achievementid']);
			
		if ($vbulletin->GPC['delissued'] == 1) 
		{
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_issues WHERE achievementid=".$vbulletin->GPC['achievementid']);
		}

	}
	
	
	print_cp_message('Success! Redirecting...', 'xperience_admin.php?do=manach', 0);
}


//###############

if ($vbulletin->GPC['do'] == 'manawards') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_admin_manawards'], 1);


	$awardcatq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_award_categories ORDER BY categoryorder");
	if ($vbulletin->db->num_rows($awardcatq) > 0)
	{
		while ($awardcat = $vbulletin->db->fetch_array($awardcatq))
		{

			print_description_row("<b>".$awardcat['categorytitle']."</b> (<a href=\"xperience_admin.php?do=editawardcat&id=".$awardcat['categoryid']."\">".$vbphrase['edit']."</a>)");

			$awardsq =$vbulletin->db->query_read("SELECT
				*
				FROM " . TABLE_PREFIX . "xperience_awards
				WHERE awardcategory=".$awardcat['categoryid']." 
				ORDER BY awardstatus
				");
				
			if ($vbulletin->db->num_rows($awardsq) > 0)
			{
				while ($awards = $vbulletin->db->fetch_array($awardsq)) 
				{
					PrintAwardRow($awards);
				}
			} else {
					print_description_row($vbphrase['xperience_admin_awards_none']);
			}
		}
	}
	
	
	$awardsq = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_awards AS A
		LEFT JOIN " . TABLE_PREFIX . "xperience_award_categories AS C ON A.awardcategory=C.categoryid
		WHERE C.categoryid IS NULL
		");
	if ($vbulletin->db->num_rows($awardsq) > 0)
	{
		print_description_row("<b>?</b>");
		while ($awards = $vbulletin->db->fetch_array($awardsq)) 
		{
			PrintAwardRow($awards);
		}
	}
	
	
	print_table_footer(1);

	print_form_header('');
	print_table_header($vbphrase['xperience_awards'], 1);
	print_description_row('<a href="xperience_admin.php?do=addawardcat">'.$vbphrase[xperience_admin_awards_add_category].'</a>');
	print_description_row('<a href="xperience_admin.php?do=addaward">'.$vbphrase[xperience_admin_awards_add].'</a>');
	print_description_row('<a href="xperience_admin.php?do=clearawards">'.$vbphrase[xperience_admin_awards_clear].'</a>');
	print_table_footer(1);
}

function PrintAwardRow($awards)
{
	global $vbulletin, $vbphrase;
	
	if (strlen($awards['awardurl']) > 0)
	{
		$image = '<img class="inlineimg" border="0" src="'.$vbulletin->options['bburl'].'/xperience/icons/'.$awards['awardurl'].'" title="'.$awards['awardtitle'].'" />';
	}
	else
	{
		$image = '<img class="inlineimg" border="0" src="'.$vbulletin->options['bburl'].'/xperience/icons/default_16.png" title="'.$awards['awardtitle'].'" />';
	}

	
	if ($awards['manualassign'] == 0)
	{
		$teststring = ', <a href="xperience_admin.php?do=testaward&id='.$awards['awardid'].'">'.$vbphrase['test'].'</a>';	
	}
	print_description_row($image.' '.$awards['awardstatus'].' '.$awards['awardtitle'].' (<a href="xperience_admin.php?do=editaward&id='.$awards['awardid'].'">'.$vbphrase['edit'].'</a>'.$teststring.') (Pos. '.$awards['awardlimit'].')<dfn>'.$awards['awarddesc'].'</dfn>');
	
}

if ($vbulletin->GPC['do'] == 'testaward') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_awards'], 3);

	$awardsq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_awards WHERE awardid=".$vbulletin->GPC['id']);
		
	if ($vbulletin->db->num_rows($awardsq) > 0)
	{
		$awards = $vbulletin->db->fetch_array($awardsq);

	$query = "SELECT 
    u.userid, u.username,
    ".$awards['awardfields']." 
    AS get_count 
    FROM " . TABLE_PREFIX . "xperience_stats as s 
    INNER JOIN " . TABLE_PREFIX . "user as u ON u.userid = s.userid 
    ORDER BY get_count DESC, points_xperience DESC 
    LIMIT 10
   ";

		$genawardq = $vbulletin->db->query_read($query); 

		$query = str_replace(",", ",<br/>", $query);
		$query = str_replace("SELECT,", "SELECT<br/>", $query);
		$query = str_replace("+", "<br/>+", $query);
		$query = str_replace("FROM", "<br/>FROM", $query);
		$query = str_replace("LEFT", "<br/>LEFT", $query);
		$query = str_replace("INNER", "<br/>INNER", $query);
		$query = str_replace("ORDER", "<br/>ORDER", $query);

		print_description_row("SQL: <br/>".$query, false, 3);

		$cellh = array();
		$cellh[] = $vbphrase['userid'];
		$cellh[] = $vbphrase['username'];
		$cellh[] = "Count";
		print_cells_row($cellh, true);

		while ($genaward = $vbulletin->db->fetch_array($genawardq)) 
		{
			$cell = array();
			$cell[] = $genaward['userid'];
			$cell[] = "<a href='".$vbulletin->options['bburl']."/member.php?u=".$genaward['userid']."'>".$genaward['username']."</a>";
			$cell[] = $genaward['get_count'];
			print_cells_row($cell);
		}
	}
	





	print_table_footer(1);

}


if ($vbulletin->GPC['do'] == 'clearawards') 
{
	print_form_header('');
	print_table_header($vbphrase['xperience_awards'], 1);
	print_description_row($vbphrase['xperience_admin_awards_clear_c']);
	print_description_row('<a href="xperience_admin.php?do=doclearawards">'.$vbphrase[yes].'</a> - <a href="xperience_admin.php?do=manawards">'.$vbphrase[no].'</a>');
	print_table_footer(1);

}

if ($vbulletin->GPC['do'] == 'doclearawards') 
{
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_awards");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_award_categories");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET xperience_awardt=''"); 
	CreateDefaultAwards();	
	print_cp_message('Redirecting...', 'xperience_admin.php?do=manawards', 0);

}


if ($vbulletin->GPC['do'] == 'addawardcat') 
{
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_award_categories (categorytitle, categorydesc, categoryorder) VALUES ('New Category', 'Description for new Category', 99)");
	print_cp_message('Redirecting...', 'xperience_admin.php?do=editawardcat&id='.$vbulletin->db->insert_id(), 0);

}

if ($vbulletin->GPC['do'] == 'editawardcat') 
{
	print_form_header('xperience_admin', 'saveaawardcat');
	print_table_header($vbphrase['xperience_admin_award_cat_edit'], 2, 0);
	
	$awardcatq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_award_categories WHERE categoryid=".$vbulletin->GPC['id']);
		
		if ($vbulletin->db->num_rows($awardcatq) > 0)
		{
			$awardcat = $vbulletin->db->fetch_array($awardcatq);
			
			print_input_row($vbphrase['xperience_admin_award_cat_title'], 'categorytitle', $awardcat['categorytitle']);	
			print_input_row($vbphrase['xperience_admin_award_cat_desc'], 'categorydesc', $awardcat['categorydesc']);
			print_input_row("$vbphrase[display_order]<dfn>$vbphrase[zero_equals_no_display]</dfn>", 'categoryorder', $awardcat['categoryorder']);
			
			print_checkbox_row($vbphrase['delete'], 'delcategory', false, 1);
			echo "<input type=\"hidden\" name=\"categoryid\" value=\"" . $awardcat['categoryid'] . "\" />\n";
			print_submit_row($vbphrase['save']);
	}

}

if ($vbulletin->GPC['do'] == 'saveaawardcat') 
{

	$vbulletin->input->clean_array_gpc('r', array(
		'categorytitle' => TYPE_STR,
		'categorydesc' => TYPE_STR,
		'categoryorder' => TYPE_INT,
		'categoryid' => TYPE_INT,
		'delcategory' => TYPE_UINT
	));



	if ($vbulletin->GPC['delcategory'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_award_categories WHERE categoryid=".$vbulletin->GPC['categoryid']);
	} else {
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_award_categories SET
		categorytitle='".addslashes($vbulletin->GPC['categorytitle'])."',
		categorydesc='".addslashes($vbulletin->GPC['categorydesc'])."',
		categoryorder=".addslashes($vbulletin->GPC['categoryorder'])."
		WHERE categoryid=".$vbulletin->GPC['categoryid']);
	}
	
	require_once(DIR . '/includes/class_xperience.php');
	$xPerience =& new xPerience;
	$xPerience->CalculateAwards();
	
	print_done("xperience_admin.php?do=manawards");
}




if ($vbulletin->GPC['do'] == 'addaward') 
{
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_awards (awardlimit, awardtitle, awarddesc, awardstatus) VALUES (0, 'New Award', 'New great Award!', 0)");
	print_cp_message('Redirecting...', 'xperience_admin.php?do=editaward&id='.$vbulletin->db->insert_id(), 0);

}




if ($vbulletin->GPC['do'] == 'editaward') 
{
	print_form_header('xperience_admin', 'saveaward');
	print_table_header($vbphrase['xperience_admin_awards_edit'], 2, 0);
	
	$awardsq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_awards WHERE awardid=".$vbulletin->GPC['id']);
		
		if ($vbulletin->db->num_rows($awardsq) > 0)
		{
			$awards = $vbulletin->db->fetch_array($awardsq);
			print_input_row($vbphrase['title'], 'awardtitle', $awards['awardtitle']);				
			print_textarea_row($vbphrase['description'], 'awarddesc', $awards['awarddesc'], 5, 80);
			print_checkbox_row($vbphrase['delete'], 'delaward', false, 1);
			
			print_table_header($vbphrase['xperience_admin_awards_edit_path'], 2, 0);
			

			//print_input_row($vbphrase['xperience_admin_awards_url'], 'awardurl', $awards['awardurl'], true, 80);
			//print_input_row($vbphrase['xperience_admin_awards_bigurl'], 'awardbigurl', $awards['awardbigurl'], true, 80);
			
			$awardurl .= "<select name=\"awardurl\" onchange=\"document.getElementById('oIMG16').src='".$vbulletin->options['bburl']."/xperience/icons/' + this.options[this.selectedIndex].value;\" >";
			$files16 = GetFiles("_16.png");
			foreach ($files16 as $file)
			{
				$selected = iif($awards['imagebig'] == $file, "selected ");
	      $awardurl .= '<option '.$selected.'value="'.$file.'" >'.$file.'</option>';
			}

			$awardurl .= '</select>';
			$awardurl .= '<img id="oIMG16" src="'.$vbulletin->options['bburl'].'/xperience/icons/'.$awards['awardurl'].'" width="16" height="16" />';
			print_label_row($vbphrase['xperience_admin_awards_url'], $awardurl);
			

			$awardbigurl = "<select name=\"awardbigurl\" onchange=\"document.getElementById('oIMG32').src='".$vbulletin->options['bburl']."/xperience/icons/' + this.options[this.selectedIndex].value;\" >";

			$files32 = GetFiles("_32.png");
			foreach ($files32 as $file)
			{
				$selected = iif($awards['awardbigurl'] == $file, "selected ");
	      $awardbigurl .= '<option '.$selected.'value="'.$file.'" >'.$file.'</option>';
			}
			
			$awardbigurl .= '</select>';
			$awardbigurl .= '<img id="oIMG32" src="'.$vbulletin->options['bburl'].'/xperience/icons/'.$awards['awardbigurl'].'" width="32" height="32" />';
				
			print_label_row($vbphrase['xperience_admin_awards_bigurl'], $awardbigurl);		
			
			
			print_table_header($vbphrase['xperience_admin_awards_edit_settings'], 2, 0);
			print_input_row($vbphrase['xperience_admin_awards_manualassign'], 'manualassign', $awards['manualassign']);
			
			
			$limits['0'] = "Gold";
			$limits['1'] = "Silver";
			$limits['2'] = "Bronze";
			
			print_select_row($vbphrase['xperience_admin_awards_limit'], 'awardlimit', $limits, $awards['awardlimit']);
			print_input_row("$vbphrase[display_order]<dfn>$vbphrase[zero_equals_no_display]</dfn>", 'awardstatus', $awards['awardstatus']);
			$awardcatq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_award_categories ORDER BY categoryorder");
			if ($vbulletin->db->num_rows($awardcatq) > 0)
			{
				while ($awardcat = $vbulletin->db->fetch_array($awardcatq))
				{
					$cats["$awardcat[categoryid]"] = $awardcat['categorytitle'];
				}
				
			}
			print_select_row($vbphrase['xperience_admin_awards_category'], "awardcategory", $cats, $awards['awardcategory']);

			
			
			print_table_header($vbphrase['xperience_admin_awards_edit_fields'], 2, 0);
			$fields = array();
			$sel_fields = array();
			$optionsq =$vbulletin->db->query_read("SHOW COLUMNS
			FROM " . TABLE_PREFIX . "xperience_stats LIKE 'points_%'");
					
			if ($vbulletin->db->num_rows($optionsq) > 0)
			{
				while ($options = $vbulletin->db->fetch_array($optionsq)) 
				{
					$fields["$options[Field]"] = $vbphrase['xperience_'.$options[Field]]; 
					if (strlen($fields["$options[Field]"])<1)
					{
						$fields["$options[Field]"] = $options["Field"];
					}
				}
			}
			array_multisort($fields, SORT_ASC, SORT_STRING);
			$sel_fields=explode("+", $awards['awardfields']);
			print_select_row($vbphrase['xperience_admin_awards_sum'], "awardfields[]", $fields, $sel_fields, false, $size = 50, $multiple = true);
			
			//TODO
			//print_input_row('Exclude Usergroups<dfn>These usergroups cannot get this award. Seperate them by a colon, e.g. 16,65,71</dfn>', 'awardexclusions', $awards['awardexclusions']);	
			

			echo "<input type=\"hidden\" name=\"awardid\" value=\"" . $awards['awardid'] . "\" />\n";
			print_submit_row($vbphrase['save']);
	}

}


if ($vbulletin->GPC['do'] == 'saveaward') 
{

	$vbulletin->input->clean_array_gpc('r', array(
		'awardtitle' => TYPE_STR,
		'awardurl' => TYPE_STR,
		'awardbigurl' => TYPE_STR,
		'awarddesc' => TYPE_STR,
		'awardstatus' => TYPE_INT,
		'awardcategory' => TYPE_INT,
		'awardlimit' => TYPE_INT,
		'awardexclusions' => TYPE_STR,
		'awardid' => TYPE_UINT,
		'manualassign' => TYPE_STR,
		'delaward' => TYPE_UINT,
		'awardfields' => TYPE_ARRAY
	));



	if ($vbulletin->GPC['delaward'] == 1) 
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_awards WHERE awardid=".$vbulletin->GPC['awardid']);
	}
	else
	{
		
		$awardfields_str = implode("+", $vbulletin->GPC['awardfields']);
		if (strlen($awardfields_str)<2) $awardfields_str="+points_user";
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_awards SET
		awardtitle='".addslashes($vbulletin->GPC['awardtitle'])."',
		awardurl='".addslashes($vbulletin->GPC['awardurl'])."',
		awardbigurl='".addslashes($vbulletin->GPC['awardbigurl'])."',
		awarddesc='".addslashes($vbulletin->GPC['awarddesc'])."',
		awardstatus=".$vbulletin->GPC['awardstatus'].",
		awardcategory=".$vbulletin->GPC['awardcategory'].",
		awardlimit=".$vbulletin->GPC['awardlimit'].",
		manualassign='".addslashes($vbulletin->GPC['manualassign'])."',
		awardexclusions='".addslashes($vbulletin->GPC['awardexclusions'])."',
		awardfields='".addslashes($awardfields_str)."'
		WHERE awardid=".$vbulletin->GPC['awardid']);
	}
	
	require_once(DIR . '/includes/class_xperience.php');
	$xPerience =& new xPerience;
	$xPerience->CalculateAwards();
	
	print_cp_message('Success! Redirecting...', 'xperience_admin.php?do=manawards', 0);
	//print_done("xperience_admin.php?do=manawards");
}



if ($vbulletin->GPC['do'] == 'stats') 
{

	$members = $vbulletin->db->query_first("
		SELECT
		COUNT(*) AS users
		FROM " . TABLE_PREFIX . "user as u 
	"); 
	$userscount = $members['users'];

	// we'll need a poll image
	$style = $db->query_first("
		SELECT stylevars FROM " . TABLE_PREFIX . "style
		WHERE styleid = " . $vbulletin->options['styleid'] . "
		LIMIT 1
	");
	$stylevars = unserialize($style['stylevars']);
	unset($style);


	print_form_header('');
	print_table_header($vbphrase['xperience_admin_level_stats'], 3);
	print_cells_row(array($vbphrase['xperience_admin_level_level'], $vbphrase['xperience_admin_level_percent'], $vbphrase['xperience_admin_level_count']), 1);
        
	$levels = $vbulletin->db->query_read("SELECT
		xperience_level, COUNT(*) as levelcount
		FROM " . TABLE_PREFIX . "user
		GROUP BY xperience_level");
	
	while ($level = $vbulletin->db->fetch_array($levels)) 
	{ 
		$i++;
		$bar = ($i % 6) + 1; 
		print_statistic_result($level['xperience_level'], $bar, $level['levelcount'], ceil(($level['levelcount']/$userscount) * 100)); 
	}

	print_table_footer(3);
}


function print_done($Link = 'xperience_admin.php') 
{
	print_form_header('');
	print_table_header("Confirmation", 1);
	print_description_row('Done');
	print_description_row('<a href="'.$Link.'">Return</a>');
	print_table_footer(1);
}

print_cp_footer();


?>
