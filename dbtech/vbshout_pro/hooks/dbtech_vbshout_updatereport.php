<?php
$vbulletin->input->clean_array_gpc('p', array(
	'message' 	=> TYPE_STR,
	'delete'	=> TYPE_BOOL,
));

if ($vbulletin->GPC['delete'])
{
	// Deleted
	if ($vbulletin->GPC['shoutinfo'] = $db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE shoutid = " . $db->sql_prepare($reportinfo['shoutid'])))
	{
		// Fix AOP
		VBSHOUT::$tabid = 'shouts' . $vbulletin->GPC['shoutinfo']['instanceid'];
		
		// Init the Shout DM
		$shout = VBSHOUT::datamanager_init('Shout', $vbulletin, ERRTYPE_ARRAY);
		
		// Set the existing data
		$shout->set_existing($vbulletin->GPC['shoutinfo']);
		
		// Delete
		$shout->delete();
	}
}
else if ($vbulletin->GPC['message'] != $reportinfo['shout'])
{
	// Saved
	if ($vbulletin->GPC['shoutinfo'] = $db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE shoutid = " . $db->sql_prepare($reportinfo['shoutid'])))
	{
		// Fix AOP
		VBSHOUT::$tabid = 'shouts' . $vbulletin->GPC['shoutinfo']['instanceid'];
		
		// Init the Shout DM
		$shout = VBSHOUT::datamanager_init('Shout', $vbulletin, ERRTYPE_ARRAY);
		
		// Set the existing data
		$shout->set_existing($vbulletin->GPC['shoutinfo']);
		
		// Only thing that's changed
		$vbulletin->GPC['shoutinfo']['message'] = $vbulletin->GPC['message'];
		
		foreach ($vbulletin->GPC['shoutinfo'] as $varname => $value)
		{
			// Set everything
			$shout->set($varname, $value);
		}
		
		// Now finally save
		$shout->save();
	}
}
?>