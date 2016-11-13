<?php
$currentries = $vbulletin->db->query_first("
	SELECT {$varname}_given AS given
	FROM " . TABLE_PREFIX . "dbtech_thanks_statistics 
	WHERE userid = " . $vbulletin->userinfo['userid']
);
foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
{
	if (!$instance['options']['aptlping_interval'])
	{
		// Not having notices here
		continue;
	}

	if ($currentries['given'] % $instance['options']['aptlping_interval'] != 0)
	{
		// We only want matching intervals
		continue;
	}
	
	$shout = VBSHOUT::datamanager_init('Shout', $vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('automated', true);	
		$shout->set('message', construct_phrase(
			$vbphrase['dbtech_vbshout_has_reached_x_y_z'],
			$currentries['given'],
			$buttoninfo['title'],
			$vbphrase['dbtech_thanks_given']
		))
		->set('instanceid', $instanceid)
		->set('type', VBSHOUT::$shouttypes['notif']);
	$shout->save();
	unset($shout);
}
	
$currentries = $vbulletin->db->query_first("
	SELECT {$varname}_received AS received
	FROM " . TABLE_PREFIX . "dbtech_thanks_statistics 
	WHERE userid = " . $post['userid']
);

foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
{
	if (!$instance['options']['aptlping_interval'])
	{
		// Not having notices here
		continue;
	}
	
	if ($currentries['received'] % $instance['options']['aptlping_interval'] != 0)
	{
		// We only want matching intervals
		continue;
	}

	$shout = VBSHOUT::datamanager_init('Shout', $vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('automated', true);	
		$shout->set('message', construct_phrase(
			$vbphrase['dbtech_vbshout_has_reached_x_y_z'],
			$currentries['received'],
			$buttoninfo['title'],
			$vbphrase['dbtech_thanks_received']
		))
		->set('instanceid', $instanceid)
		->set('userid', $post['userid'])
		->set('type', VBSHOUT::$shouttypes['notif']);
	$shout->save();
	unset($shout);
}	
?>