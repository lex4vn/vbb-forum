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

$vbulletin->input->clean_array_gpc('r', array(
	'doresetstyles' => TYPE_BOOL
));

if (!$vbulletin->GPC['doresetstyles'])
{
	// Nothing to do
	print_stop_message('nothing_to_do');
}

$users = $db->query_read_slave("UPDATE " . TABLE_PREFIX . "user SET dbtech_vbshout_shoutstyle = ''");

define('CP_REDIRECT', 'vbshout.php?do=maintenance');
print_stop_message('dbtech_vbshout_style_customisations_reset');

print_cp_footer();


/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>