<?php
$currquizzes = $vbulletin->db->query_first("
	SELECT COUNT(*) AS created
	FROM " . TABLE_PREFIX . "dbtech_vbquiz_quiz
	WHERE userid = " . $vbulletin->userinfo['userid']
);
foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
{
	if (!$instance['options']['quizmadeping_interval'])
	{
		// Not having notices here
		continue;
	}

	if ($currquizzes['created'] % $instance['options']['quizmadeping_interval'] != 0)
	{
		// We only want matching intervals
		continue;
	}
	
	$shout = VBSHOUT::datamanager_init('Shout', $vbulletin, ERRTYPE_ARRAY);
		$shout->set_info('automated', true);	
		$shout->set('message', construct_phrase(
			$vbphrase['dbtech_vbshout_has_made_x_quizzes'],
			$currquizzes['created']
		))
		->set('instanceid', $instanceid)
		->set('type', VBSHOUT::$shouttypes['notif']);
	$shout->save();
	unset($shout);
}
?>