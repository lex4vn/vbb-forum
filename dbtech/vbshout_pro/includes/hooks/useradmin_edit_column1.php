<?php

if (can_administer('canadminvbshout'))
{
	$shoutareas = array(
		'default' 	=> $vbphrase['use_forum_default'],
		'left' 		=> $vbphrase['dbtech_vbshout_left_of_shouts'],
		'right' 	=> $vbphrase['dbtech_vbshout_right_of_shouts'],
		'above' 	=> $vbphrase['dbtech_vbshout_above_shouts'],
		'below' 	=> $vbphrase['dbtech_vbshout_below_shouts']
	);
	
	print_table_break('', $INNERTABLEWIDTH);
	print_table_header($vbphrase['dbtech_vbshout_full']);
	print_yes_no_row($vbphrase['dbtech_vbshout_isbanned'], 		'user[dbtech_vbshout_banned]', 		$user['dbtech_vbshout_banned']);
	print_yes_no_row($vbphrase['dbtech_vbshout_issilenced'],	'user[dbtech_vbshout_silenced]', 	$user['dbtech_vbshout_silenced']);
	print_description_row($vbphrase['user_customizations'], false, 2, 'optiontitle');
	print_select_row($vbphrase['dbtech_vbshout_shout_area_location'], 'user[dbtech_vbshout_shoutarea]', $shoutareas, 	$user['dbtech_vbshout_shoutarea']);
	if ($vbulletin->GPC['userid'])
	{
		// Reset this user's customisations
		print_yes_no_row($vbphrase['dbtech_vbshout_reset_shout_styles'], 'dbtech_vbshout_shoutstyle', 	0);
	}
}
?>