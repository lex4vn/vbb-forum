<?php
$currentries = $vbulletin->db->query_first("
	SELECT dbtech_usertag_tags AS received
	FROM " . TABLE_PREFIX . "user 
	WHERE userid = " . $results_r['userid']
);

foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
{
	if (!$instance['options']['tagping_interval'])
	{
		// Not having notices here
		continue;
	}
	
	if ($currentries['received'] % $instance['options']['tagping_interval'] != 0)
	{
		// We only want matching intervals
		continue;
	}

	$shout = VBSHOUT::datamanager_init('Shout', $vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('automated', true);	
		$shout->set('message', construct_phrase(
			$vbphrase['dbtech_vbshout_has_been_tagged_x_times'],
			$currentries['received']
		))
		->set('userid', $results_r['userid'])
		->set('instanceid', $instanceid)
		->set('type', VBSHOUT::$shouttypes['notif']);
	$shout->save();
	unset($shout);
}				
?>