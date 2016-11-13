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

print_cp_header($vbphrase['dbtech_vbshout_maintenance']);

print_form_header('vbshout', 'resetstyles');
print_table_header($vbphrase['dbtech_vbshout_reset_shout_styles'], 2, 0);
print_description_row($vbphrase['dbtech_vbshout_reset_shout_styles_descr']);
print_yes_no_row($vbphrase['dbtech_vbshout_are_you_sure_resetstyles'], 'doresetstyles', 0);
print_submit_row($vbphrase['dbtech_vbshout_reset_shout_styles']);

($hook = vBulletinHook::fetch_hook('dbtech_vbshout_maintenance')) ? eval($hook) : false;

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>