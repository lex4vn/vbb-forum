<?php
$currentries = $vbulletin->db->query_first("
	SELECT dbtech_usertag_quotes AS received
	FROM " . TABLE_PREFIX . "user 
	WHERE userid = " . $usertaginfo['userid']
);

foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
{
	if (!$instance['options']['quoteping_interval'])
	{
		// Not having notices here
		continue;
	}
	
	if ($currentries['received'] % $instance['options']['quoteping_interval'] != 0)
	{
		// We only want matching intervals
		continue;
	}

	$shout = VBSHOUT::datamanager_init('Shout', $vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('automated', true);	
		$shout->set('message', construct_phrase(
			$vbphrase['dbtech_vbshout_has_been_quoted_x_times'],
			$currentries['received']
		))
		->set('userid', $usertaginfo['userid'])
		->set('instanceid', $instanceid)
		->set('type', VBSHOUT::$shouttypes['notif']);
	$shout->save();
	unset($shout);
}				
?>