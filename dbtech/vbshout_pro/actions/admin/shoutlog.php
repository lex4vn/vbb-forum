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

// #############################################################################
if ($_REQUEST['action'] == 'shoutlog' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_vbshout_shout_log']);
	
	// ###################### Start modify #######################
	$users = $db->query_read("
		SELECT DISTINCT shoutlog.userid, shoutlog.username AS cmdusername, user.username
		FROM " . TABLE_PREFIX . "dbtech_vbshout_deeplog AS shoutlog
		LEFT JOIN " . TABLE_PREFIX . "user AS user USING(userid)
		ORDER BY cmdusername, username
	");
	$userlist = array('no_value' => $vbphrase['all_log_entries']);
	while ($user = $db->fetch_array($users))
	{
		$userlist["$user[userid]"] = ($user['username'] ? $user['username'] : $user['cmdusername']);
	}
	
	print_form_header('vbshout', 'shoutlog');
	construct_hidden_code('action', 'view');
	print_table_header($vbphrase['dbtech_vbshout_shout_log_viewer']);
	//print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
	print_select_row($vbphrase['show_only_entries_generated_by'], 'userid', $userlist);
	print_time_row($vbphrase['start_date'], 'startdate', 0, 0);
	print_time_row($vbphrase['end_date'], 'enddate', 0, 0);
	print_select_row($vbphrase['order_by'], 'orderby', array('date' => $vbphrase['date'], 'user' => $vbphrase['username']), 'date');
	print_submit_row($vbphrase['view'], 0);
}

// #############################################################################
if ($_REQUEST['action'] == 'view')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage'    => TYPE_UINT,
		'pagenumber' => TYPE_UINT,
		'userid'     => TYPE_UINT,
		'modaction'  => TYPE_STR,
		'orderby'    => TYPE_NOHTML,
		'product'    => TYPE_STR,
		'startdate'  => TYPE_UNIXTIME,
		'enddate'    => TYPE_UNIXTIME,
	));
	
	$sqlconds = array();
	$hook_query_fields = $hook_query_joins = '';
	
	if ($vbulletin->GPC['perpage'] < 1)
	{
		$vbulletin->GPC['perpage'] = 15;
	}
	
	if ($vbulletin->GPC['userid'] OR $vbulletin->GPC['modaction'])
	{
		if ($vbulletin->GPC['userid'])
		{
			$sqlconds[] = "shoutlog.userid = " . $vbulletin->GPC['userid'];
		}
		if ($vbulletin->GPC['modaction'])
		{
			$sqlconds[] = "shoutlog.shout LIKE '%" . $db->escape_string_like($vbulletin->GPC['modaction']) . "%'";
		}
	}
	
	if ($vbulletin->GPC['startdate'])
	{
		$sqlconds[] = "shoutlog.dateline >= " . $vbulletin->GPC['startdate'];
	}
	
	if ($vbulletin->GPC['enddate'])
	{
		$sqlconds[] = "shoutlog.dateline <= " . $vbulletin->GPC['enddate'];
	}
	
	//($hook = vBulletinHook::fetch_hook('admin_modlogviewer_query')) ? eval($hook) : false;
	
	$counter = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "dbtech_vbshout_deeplog AS shoutlog
		" . (!empty($sqlconds) ? "WHERE " . implode("\r\n\tAND ", $sqlconds) : "") . "
	");
	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);
	
	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];
	
	switch($vbulletin->GPC['orderby'])
	{
		case 'user':
			$order = 'user.username ASC, dateline DESC';
			break;
		case 'modaction':
			$order = 'shout ASC, dateline DESC';
			break;
		case 'date':
		default:
			$order = 'dateline ASC';
	}
	
	$logusers = array(-1 => $vbphrase['dbtech_vbshout_system']);
	$users = $db->query_read("
		SELECT user.userid, user.username
		FROM " . TABLE_PREFIX . "dbtech_vbshout_deeplog AS shoutlog
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = shoutlog.id)
		WHERE shoutlog.type = " . VBSHOUT::$shouttypes['pm']
	);
	while ($user = $db->fetch_array($users))
	{
		// Cache the users mentioned in the shout
		$logusers["$user[userid]"] = ($user['username'] ? $user['username'] : 'N/A');
	}
	
	$logs = $db->query_read("
		SELECT shoutlog.*, shoutlog.username AS cmdusername, user.username
			$hook_query_fields
		FROM " . TABLE_PREFIX . "dbtech_vbshout_deeplog AS shoutlog
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = shoutlog.userid)
		$hook_join_fields
		" . (!empty($sqlconds) ? "WHERE " . implode("\r\n\tAND ", $sqlconds) : "") . "
		ORDER BY $order
		###LIMIT $startat, " . $vbulletin->GPC['perpage'] . "
	");
	
	if ($db->num_rows($logs))
	{
		echo "<pre>";
		while ($log = $db->fetch_array($logs))
		{
			echo "[" . vbdate($vbulletin->options['timeformat'], 	$log['dateline'], false) . " " . vbdate($vbulletin->options['dateformat'], 	$log['dateline'], false) . "] ";
			echo ($log['username'] ? $log['username'] : ($log['cmdusername'] ? $log['cmdusername'] : $vbphrase['dbtech_vbshout_system'])) . ': ';
			echo $log['message'];
			echo "\n";
		}
		echo "</pre>";
	}
	else
	{
		print_stop_message('no_results_matched_your_query');
	}
}

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>