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

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$displays = array(
	0 => $vbphrase['disabled'],
	1 => $vbphrase['dbtech_vbshout_after_navbar'],
	2 => $vbphrase['dbtech_vbshout_above_footer']
);

$instanceid = $vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);
$instance = ($instanceid ? $vbshout->cache['instance']["$instanceid"] : false);

if (!is_array($instance))
{
	// Non-existinginstance instance
	$instanceid = 0;
}

$soundfiles = array('' => $vbphrase['none']);
if ($handle = @opendir('dbtech/vbshout/sounds'))
{
	while (false !== ($file = readdir($handle)))
	{
		if ($file != '.' AND $file != '..' AND $file != 'index.html')
		{
			// Store the icon
			$soundfiles["$file"] = $file;
		}
    }
    closedir($handle);
}

// Sort the array as a string
asort($soundfiles, SORT_STRING);

if ($instanceid)
{
	// Edit
	print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['dbtech_vbshout_instance'], $instance['name'])));
	print_form_header('vbshout', 'updateinstance');
	construct_hidden_code('instanceid', $instanceid);
	print_table_header(construct_phrase($vbphrase['dbtech_vbshout_editing_x_y'], $vbphrase['dbtech_vbshout_instance'], $instance['name']));
	
	print_input_row($vbphrase['title'], 								'name', 					$instance['name']);
	print_textarea_row($vbphrase['description'], 						'description', 				$instance['description']);
	print_yes_no_row($vbphrase['active'], 								'active', 					$instance['active']);
	print_select_row($vbphrase['dbtech_vbshout_auto_display_descr'],	'autodisplay', 	$displays, 	$instance['autodisplay']);
	print_textarea_row($vbphrase['dbtech_vbshout_deployment_descr'], 	'deployment', 				$instance['deployment']);
	print_textarea_row($vbphrase['dbtech_vbshout_templates_descr'], 	'templates', 				$instance['templates']);
	print_select_row($vbphrase['dbtech_vbshout_shout_shound_descr'],	'shoutsound', 	$soundfiles,$instance['shoutsound']);
	print_select_row($vbphrase['dbtech_vbshout_invite_sound_descr'],	'invitesound', 	$soundfiles,$instance['invitesound']);
	print_select_row($vbphrase['dbtech_vbshout_pm_sound_descr'],		'pmsound', 		$soundfiles,$instance['pmsound']);
	print_submit_row($vbphrase['save']);
}
else
{
	// Add
	print_cp_header($vbphrase['dbtech_vbshout_add_new_instance']);
	print_form_header('vbshout', 'updateinstance');	
	print_table_header($vbphrase['dbtech_vbshout_add_new_instance']);
	print_input_row($vbphrase['title'], 								'name', 					$instance['name']);
	print_textarea_row($vbphrase['description'], 						'description', 				$instance['description']);
	print_select_row($vbphrase['dbtech_vbshout_auto_display_descr'],	'autodisplay', 	$displays, 	1);
	print_textarea_row($vbphrase['dbtech_vbshout_deployment_descr'], 	'deployment', 				$instance['deployment']);
	print_textarea_row($vbphrase['dbtech_vbshout_templates_descr'], 	'templates', 				$instance['templates']);
	print_select_row($vbphrase['dbtech_vbshout_shout_shound_descr'],	'shoutsound', 	$soundfiles,$instance['shoutsound']);
	print_select_row($vbphrase['dbtech_vbshout_invite_sound_descr'],	'invitesound', 	$soundfiles,$instance['invitesound']);
	print_select_row($vbphrase['dbtech_vbshout_pm_sound_descr'],		'pmsound', 		$soundfiles,$instance['pmsound']);
	print_submit_row($vbphrase['save']);	
}

print_cp_footer();


/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: instance.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>