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

$instanceid = $vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);
$instance = ($instanceid ? $vbshout->cache['instance']["$instanceid"] : false);

if (!is_array($instance))
{
	print_cp_message($vbphrase['dbtech_vbshout_invalid_instance']);
}

$instance['permissions'] = @unserialize($instance['permissions']);
$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');

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

print_table_break();

$instance['bbcodepermissions'] = @unserialize($instance['bbcodepermissions']);
$permissions = fetch_bitfield_definitions('nocache|allowedbbcodesfull');

// Table header
$headings = array();
$headings[] = $vbphrase['usergroup'];
foreach ((array)$permissions as $permissionname => $bit)
{
	$headings[] = $vbphrase["{$permissionname}"];
}

print_cells_row($headings, 0, 'thead');

foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
{
	// Table data
	$cell = array();
	$cell[] = $usergroup['title'];	
	foreach ((array)$permissions as $permissionname => $bit)
	{
		$cell[] = '<center>
			<input type="hidden" name="bbcodepermissions[' . $instanceid . '][' . $usergroupid . '][' . $permissionname . ']" value="0" />
			<input type="checkbox" name="bbcodepermissions[' . $instanceid . '][' . $usergroupid . '][' . $permissionname . ']" value="1"' . ($instance['bbcodepermissions']["$usergroupid"] & $bit ? ' checked="checked"' : '') . '/>
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

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: instance.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>