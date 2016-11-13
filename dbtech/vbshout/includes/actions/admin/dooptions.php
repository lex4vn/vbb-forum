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

require_once(DIR . '/includes/adminfunctions_misc.php');

$vbulletin->input->clean_array_gpc('r', array(
	'varname' => TYPE_STR,
	'dogroup' => TYPE_STR,
));

require_once(DIR . '/includes/adminfunctions_options.php');
require_once(DIR . '/includes/functions_misc.php');

// query settings phrases
$settingphrase = array();
$phrases = $db->query_read("
	SELECT varname, text
	FROM " . TABLE_PREFIX . "phrase
	WHERE fieldname = 'vbsettings' AND
		languageid IN(-1, 0, " . LANGUAGEID . ")
	ORDER BY languageid ASC
");
while($phrase = $db->fetch_array($phrases))
{
	$settingphrase["$phrase[varname]"] = $phrase['text'];
}

$vbulletin->input->clean_array_gpc('p', array(
	'setting'  => TYPE_ARRAY,
	'advanced' => TYPE_BOOL
));

if (!empty($vbulletin->GPC['setting']))
{
	save_settings($vbulletin->GPC['setting']);

	define('CP_REDIRECT', 'vbshout.php?do=options&amp;dogroup=' . $vbulletin->GPC['dogroup'] . '&amp;advanced=' . $vbulletin->GPC['advanced']);
	print_stop_message('saved_settings_successfully');
}
else
{
	print_stop_message('nothing_to_do');
}

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>