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

print_cp_header($vbphrase['dbtech_vbshout_silence_management']);

$users = $db->query_read("
	SELECT
		commandlog.comment,
		commandlog.userid AS silenceruserid,	
		commandlog.dateline,
		commandlog.username AS cmdusername,
		silenceduser.userid,
		silenceduser.dbtech_vbshout_shouts AS shouts,
		silenceduser.username,
		silencedusergroup.title,		
		silenceruser.username AS silencerusername,
		silencerusergroup.title AS silencertitle		
	FROM " . TABLE_PREFIX . "dbtech_vbshout_log AS commandlog
	LEFT JOIN " . TABLE_PREFIX . "user AS silenceduser ON(silenceduser.userid = commandlog.comment)
	LEFT JOIN " . TABLE_PREFIX . "user AS silenceruser ON(silenceruser.userid = commandlog.userid)
	LEFT JOIN " . TABLE_PREFIX . "usergroup AS silencedusergroup ON(silencedusergroup.usergroupid = silenceduser.usergroupid)
	LEFT JOIN " . TABLE_PREFIX . "usergroup AS silencerusergroup ON(silencerusergroup.usergroupid = silenceruser.usergroupid)
	WHERE commandlog.command = 'silence'
		AND silenceduser.dbtech_vbshout_silenced = 1
	ORDER BY silenceduser.username ASC
");

if ($numusers = $db->num_rows($users))
{
	// Begin ugly hack
	$loguser = array();
	while ($user = $db->fetch_array($users))
	{
		if (!$loguser["$user[userid]"] OR $loguser["$user[userid]"]['dateline'] < $user['dateline'])
		{
			// Overwrite array as needed
			$loguser["$user[userid]"] = $user;
		}
	}
	$numusers = count($loguser);
	// End ugly hack
	
	print_form_header('vbshout', 'managesilenced');
	print_table_header(construct_phrase($vbphrase['showing_users_x_to_y_of_z'], 1, $numusers, $numusers), 6);
	print_cells_row(array(
		$vbphrase['userid'],
		$vbphrase['username'],
		$vbphrase['dbtech_vbshout_silenced_by'],
		$vbphrase['dbtech_vbshout_silenced_on'],
		$vbphrase['dbtech_vbshout_shout_count'],
		'<input type="checkbox" name="allbox" onclick="js_check_all(this.form)" title="' . $vbphrase['check_all'] . '" />'
	), 1);

	foreach ($loguser as $user)
	{
		$cell = array();
		$cell[] = $user['userid'];
		$cell[] = "<a href=\"user.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&u=$user[userid]\" target=\"_blank\">$user[username]</a><br /><span class=\"smallfont\">$user[title]</span>";
		$cell[] = "<a href=\"user.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&u=$user[silenceruserid]\" target=\"_blank\">$user[silencerusername]</a><br /><span class=\"smallfont\">$user[silencertitle]</span>";
		$cell[] = vbdate($vbulletin->options['dateformat'], $user['dateline']);
		$cell[] = vb_number_format($user['shouts']);
		$cell[] = "<input type=\"checkbox\" name=\"users[$user[userid]]\" value=\"1\" tabindex=\"1\" />";
		print_cells_row($cell);
	}
	print_submit_row($vbphrase['dbtech_vbshout_unsilence'], false, 6);
}
else
{
	print_stop_message('no_users_matched_your_query');
}
		
print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>