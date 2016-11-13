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

$displays = array(
	0 => $vbphrase['disabled'],
	1 => $vbphrase['dbtech_vbshout_after_navbar'],
	2 => $vbphrase['dbtech_vbshout_above_footer']
);

print_cp_header($vbphrase['dbtech_vbshout_instance_management']);

print_form_header('', '');
print_table_header($vbphrase['dbtech_vbshout_additional_functions']);
print_description_row("<b>
	<a href=\"vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=instancepermissions\">" . $vbphrase['dbtech_vbshout_view_instance_permissions'] . "</a>
	" . (file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml') ? "| <a href=\"vbshout.php?" . $vbulletin->session->vars['sessionurl'] . "do=massinstancepermissions\">" . $vbphrase['dbtech_vbshout_quick_instance_permission_setup'] . "</a>" : '') . "
</b>", 0, 2, '', 'center');
print_table_footer();

// Table header
$headings = array();
$headings[] = $vbphrase['title'];
$headings[] = $vbphrase['description'];
$headings[] = $vbphrase['dbtech_vbshout_sticky'];
$headings[] = $vbphrase['active'];
$headings[] = $vbphrase['permissions'];
$headings[] = $vbphrase['dbtech_vbshout_auto_display'];
$headings[] = $vbphrase['dbtech_vbshout_deployment'];
$headings[] = $vbphrase['templates'];
$headings[] = $vbphrase['dbtech_vbshout_sounds_shout'];
$headings[] = $vbphrase['dbtech_vbshout_sounds_invite'];
$headings[] = $vbphrase['dbtech_vbshout_sounds_pm'];

// Hook goes here

$headings[] = $vbphrase['edit'];
$headings[] = $vbphrase['delete'];

if (count($vbshout->cache['instance']))
{
	print_form_header('vbshout', 'modifyinstance');	
	print_table_header($vbphrase['dbtech_vbshout_instance_management'], count($headings));
	print_description_row($vbphrase['dbtech_vbshout_instance_management_descr'], false, count($headings));	
	
	print_cells_row($headings, 0, 'thead');
	
	foreach ($vbshout->cache['instance'] as $instanceid => $instance)
	{
		// Table data
		$cell = array();
		$cell[] = $instance['name'];
		$cell[] = $instance['description'];
		$cell[] = $instance['sticky'];
		$cell[] = ($instance['active'] ? $vbphrase['yes'] : $vbphrase['no']);
		$cell[] = construct_link_code($vbphrase['edit_permissions'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=changeinstancepermissions&amp;instanceid=' . $instanceid);
		$cell[] = $displays["$instance[autodisplay]"];
		$cell[] = $instance['deployment'];
		$cell[] = $instance['templates'];
		$cell[] = $instance['shoutsound'];
		$cell[] = $instance['invitesound'];
		$cell[] = $instance['pmsound'];
		
		// Hook goes here
		
		$cell[] = construct_link_code($vbphrase['edit'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=modifyinstance&amp;instanceid=' . $instanceid);
		$cell[] = ($instanceid != 1 ? construct_link_code($vbphrase['delete'], 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=deleteinstance&amp;instanceid=' . $instanceid) : '[N/A]');
		
		// Print the data
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
	}
	
	print_submit_row($vbphrase['dbtech_vbshout_add_new_instance'], false, count($headings));	
}
else
{
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