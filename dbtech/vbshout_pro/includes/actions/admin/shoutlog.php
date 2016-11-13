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

print_form_header('vbshout', 'viewshoutlog');
print_table_header($vbphrase['dbtech_vbshout_shout_log_viewer']);
//print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
print_select_row($vbphrase['show_only_entries_generated_by'], 'userid', $userlist);
print_time_row($vbphrase['start_date'], 'startdate', 0, 0);
print_time_row($vbphrase['end_date'], 'enddate', 0, 0);
print_select_row($vbphrase['order_by'], 'orderby', array('date' => $vbphrase['date'], 'user' => $vbphrase['username']), 'date');
print_submit_row($vbphrase['view'], 0);

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>