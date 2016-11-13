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
require_once(DIR . '/includes/adminfunctions_options.php');
$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
$permissions2 = fetch_bitfield_definitions('nocache|allowedbbcodesfull');

$usergrouplist = array();
foreach($vbulletin->usergroupcache AS $usergroup)
{
	$usergrouplist[] = "<input type=\"checkbox\" name=\"usergrouplist[$usergroup[usergroupid]]\" value=\"1\" /> $usergroup[title]";
}
$usergrouplist = implode("<br />\n", $usergrouplist);

print_cp_header($vbphrase['dbtech_vbshout_instance_permissions']);

if (count($vbshout->cache['instance']))
{
	// Table header
	$headings = array();
	$headings[] = $vbphrase['dbtech_vbshout_instance'];
	foreach ((array)$permissions as $permissionname => $bit)
	{
		$headings[] = $vbphrase["dbtech_vbshout_permission_{$permissionname}"];
	}
	
	print_form_header('vbshout', 'updateinstancepermissions');	
	print_table_header($vbphrase['dbtech_vbshout_instance_permissions'], count($headings));
	print_cells_row($headings, 0, 'thead');
	
	foreach ($vbshout->cache['instance'] as $instanceid => $instance)
	{
		// Table data
		$cell = array();
		$cell[] = $instance['name'];
		foreach ((array)$permissions as $permissionname => $bit)
		{
			$cell[] = '<center>
				<input type="hidden" name="permissions[' . $instanceid . '][' . $permissionname . ']" value="0" />
				<input type="checkbox" name="permissions[' . $instanceid . '][' . $permissionname . ']" value="1"/>
			</center>';
		}
		
		// Print the data
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
	}
	print_table_header($vbphrase['dbtech_vbshout_permission_targets'], count($headings));
	$class = fetch_row_bgclass();
	echo "<tr valign=\"top\">
	<td class=\"$class\"" . ($dowidth ? " width=\"$left_width%\"" : '') . ">" . $vbphrase['dbtech_vbshout_copy_permissions_to_groups'] . "</td>
	<td class=\"$class\"" . ($dowidth ? " width=\"$right_width%\"" : '') . " colspan=\"" . (count($headings) - 1) . "\"><span class=\"smallfont\">$usergrouplist</span></td>\n</tr>\n";
	print_submit_row($vbphrase['save'], false, count($headings));
	
	
	
	// Table header
	$headings = array();
	$headings[] = $vbphrase['dbtech_vbshout_instance'];
	foreach ((array)$permissions2 as $permissionname => $bit)
	{
		$headings[] = $vbphrase["{$permissionname}"];
	}
	
	print_form_header('vbshout', 'updateinstancepermissions');		
	print_table_header($vbphrase['dbtech_vbshout_bbcode_permissions'], count($headings));
	print_cells_row($headings, 0, 'thead');
	
	foreach ($vbshout->cache['instance'] as $instanceid => $instance)
	{
		// Table data
		$cell = array();
		$cell[] = $instance['name'];
		foreach ((array)$permissions2 as $permissionname => $bit)
		{
			$cell[] = '<center>
				<input type="hidden" name="bbcodepermissions[' . $instanceid . '][' . $permissionname . ']" value="0" />
				<input type="checkbox" name="bbcodepermissions[' . $instanceid . '][' . $permissionname . ']" value="1"/>
			</center>';
		}
		
		// Print the data
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
	}
	print_table_header($vbphrase['dbtech_vbshout_permission_targets'], count($headings));
	$class = fetch_row_bgclass();
	echo "<tr valign=\"top\">
	<td class=\"$class\"" . ($dowidth ? " width=\"$left_width%\"" : '') . ">" . $vbphrase['dbtech_vbshout_copy_permissions_to_groups'] . "</td>
	<td class=\"$class\"" . ($dowidth ? " width=\"$right_width%\"" : '') . " colspan=\"" . (count($headings) - 1) . "\"><span class=\"smallfont\">$usergrouplist</span></td>\n</tr>\n";
	print_submit_row($vbphrase['save'], false, count($headings));
}
else
{
	$formvar = '';
	//($hook = vBulletinHook::fetch_hook('dbtech_vbshout_instance')) ? eval($hook) : false;
	
	print_form_header('vbshout', 'modifyinstance');	
	print_table_header($vbphrase['dbtech_vbshout_instance_management'], count($headings));
	print_description_row($vbphrase['dbtech_vbshout_no_instances'], false, count($headings));
	print_submit_row($vbphrase['dbtech_vbshout_add_new_instance'], false, count($headings));	
}

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: instance.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>