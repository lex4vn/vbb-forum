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

print_cp_header($vbphrase['dbtech_vbshout_instance_permissions']);

// Table header
$headings = array();
$headings[] = $vbphrase['usergroup'];
foreach ((array)$permissions as $permissionname => $bit)
{
	$headings[] = $vbphrase["dbtech_vbshout_permission_{$permissionname}"];
}
$headings[] = $vbphrase['edit'];

if (count($vbshout->cache['instance']))
{
	print_form_header('', '');	
	print_table_header($vbphrase['dbtech_vbshout_instance_permissions'], count($headings));
	print_cells_row($headings, 0, 'thead');
	
	foreach ($vbshout->cache['instance'] as $instanceid => $instance)
	{
		print_description_row($instance['name'] . ' - ' . $instance['description'], false, count($headings), 'optiontitle');
		
		$instance['permissions'] = @unserialize($instance['permissions']);
		foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
		{
			// Table data
			$cell = array();
			$cell[] = $usergroup['title'];
			foreach ((array)$permissions as $permissionname => $bit)
			{
				$cell[] = ($instance['permissions']["$usergroupid"] & $bit ? $vbphrase['yes'] : $vbphrase['no']);
			}
			$cell[] = construct_link_code($vbphrase['edit'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=changeinstancepermissions&amp;instanceid=' . $instanceid);
			
			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}
	}
	
	print_table_footer();
}
else
{
	$formvar = '';
	($hook = vBulletinHook::fetch_hook('dbtech_vbshout_instance')) ? eval($hook) : false;
	
	print_form_header('vbshout', $formvar);	
	print_table_header($vbphrase['dbtech_vbshout_instance_management'], count($headings));
	print_description_row($vbphrase['dbtech_vbshout_no_instances'], false, count($headings));
	($formvar ? print_submit_row($vbphrase['dbtech_vbshout_add_new_instance'], false, count($headings)) : print_table_footer());	
}

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: instance.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>