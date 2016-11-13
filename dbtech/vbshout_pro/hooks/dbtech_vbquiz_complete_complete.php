<?php
$currquizzes = $vbulletin->db->query_first("
	SELECT COUNT(*) AS taken
	FROM " . TABLE_PREFIX . "dbtech_vbquiz_taken
	WHERE userid = " . $vbulletin->userinfo['userid'] . "
		AND completed = '1'
");
foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
{
	if (!$instance['options']['quiztakenping_interval'])
	{
		// Not having notices here
		continue;
	}

	if ($currquizzes['taken'] % $instance['options']['quiztakenping_interval'] != 0)
	{
		// We only want matching intervals
		continue;
	}
	
	$shout = VBSHOUT::datamanager_init('Shout', $vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('automated', true);	
		$shout->set('message', construct_phrase(
			$vbphrase['dbtech_vbshout_has_completed_x_quizzes'],
			$currquizzes['taken']
		))
		->set('instanceid', $instanceid)
		->set('type', VBSHOUT::$shouttypes['notif']);
	$shout->save();
	unset($shout);
}
?>