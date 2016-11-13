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

print_cp_header($vbphrase['dbtech_vbshout_download_archive']);

$instances = array();
$instances[0] = $vbphrase['dbtech_vbshout_all_instances'];
foreach ($vbshout->cache['instance'] as $instanceid => $instance)
{
	// Store the instance
	$instances["$instanceid"] = $instance['name'];
}	
asort($instances);

print_form_header('vbshout', 'dodownload');
print_table_header($vbphrase['dbtech_vbshout_download_archive']);
print_select_row($vbphrase['dbtech_vbshout_file_format'], 'format', array('csv' => 'CSV', 'xml' => 'XML', 'txt' => 'TXT'), 'txt');
print_time_row($vbphrase['start_date'], 'startdate', 0, 0);
print_time_row($vbphrase['end_date'], 'enddate', 0, 0);
print_yes_no_row($vbphrase['dbtech_vbshout_include_bbcode'], 'bbcode', 1);
print_select_row($vbphrase['dbtech_vbshout_instance'], 'instanceid', $instances, 0);
print_submit_row($vbphrase['dbtech_vbshout_download_archive'], 0);

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>