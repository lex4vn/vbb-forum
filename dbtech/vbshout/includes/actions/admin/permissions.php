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

// Ensure we can fetch bitfields
//require_once(DIR . '/includes/adminfunctions_options.php');
//$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');

$forumids = array();
foreach ((array)$vbulletin->forumcache as $forumid => $forum)
{
	if (!file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml') AND $forum['parentid'] != -1)
	{
		// This forum isn't a parent forum
		continue;
	}
	
	$forumids[] = $forumid;
}


$headings = array();
$headings[] = $vbphrase['forum'];
foreach ((array)$vbshout->cache['instance'] as $instanceid => $instance)
{
	$headings[] = $instance['name'];
}

print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['permissions'], $vbphrase['dbtech_vbshout_instance'])));
print_form_header('vbshout', 'updatepermissions');
print_table_header(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['permissions'], $vbphrase['dbtech_vbshout_instance']), count($headings));
print_cells_row($headings, 0, 'thead');

foreach ((array)$forumids as $forumid)
{
	// Shorthand
	$forum = $vbulletin->forumcache["$forumid"];
	$cell = array();
	$cell[] = construct_depth_mark($forum['depth'],'- - ') . $forum['title'];
	foreach ((array)$vbshout->cache['instance'] as $instanceid => $instance)
	{
		$instance['notices'] = @unserialize($instance['notices']);
		$cell[] = '
			<center>
				<input type="hidden" name="forum[' . $instanceid . '][' . $forumid . '][newthread]" value="0" />
				<label for="cb_forum_' . $instanceid . '_' . $forumid . '_newthread">
					<input type="checkbox" name="forum[' . $instanceid . '][' . $forumid . '][newthread]" id="cb_forum_' . $instanceid . '_' . $forumid . '_newthread" value="1"' . (($instance['notices']["$forumid"] & 1) ? ' checked="checked"' : '') . '/>
					' . $vbphrase['dbtech_vbshout_new_thread'] . '
				</label>
				
				<input type="hidden" name="forum[' . $instanceid . '][' . $forumid . '][newreply]" value="0" />
				<label for="cb_forum_' . $instanceid . '_' . $forumid . '_newreply">
					<input type="checkbox" name="forum[' . $instanceid . '][' . $forumid . '][newreply]" id="cb_forum_' . $instanceid . '_' . $forumid . '_newreply" value="2"' . (($instance['notices']["$forumid"] & 2) ? ' checked="checked"' : '') . '/>
					' . $vbphrase['dbtech_vbshout_new_reply'] . '
				</label>
			</center>
		';
	}
	print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);	
}
print_submit_row($vbphrase['save'], false, count($headings));

/*

// Table header
$headings = array();
$headings[] = $vbphrase['usergroup'];
foreach ((array)$permissions as $permissionname => $bit)
{
	$headings[] = $vbphrase["dbtech_vbshout_permission_{$permissionname}"];
}

print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['permissions'], $instance['name'])));
print_form_header('vbshout', 'updateinstancepermissions');
print_table_header(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['permissions'], $instance['name']), count($headings));
print_cells_row($headings, 0, 'thead');

foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
{
	// Table data
	$cell = array();
	$cell[] = $usergroup['title'];	
	foreach ((array)$permissions as $permissionname => $bit)
	{
		$cell[] = '<center>
			<input type="hidden" name="permissions[' . $instanceid . '][' . $usergroupid . '][' . $permissionname . ']" value="0" />
			<input type="checkbox" name="permissions[' . $instanceid . '][' . $usergroupid . '][' . $permissionname . ']" value="1"' . ($instance['permissions']["$usergroupid"] & $bit ? ' checked="checked"' : '') . '/>
		</center>';
	}
	
	// Print the data
	print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
}
if (!file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml'))
{
	print_description_row('These permissions will apply to ALL instances!', false, count($headings));
}

print_submit_row($vbphrase['save'], false, count($headings));

*/
print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: instance.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>