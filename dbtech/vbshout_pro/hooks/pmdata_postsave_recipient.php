<?php
global $vbphrase;

$pmid = $this->dbobject->insert_id();
foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
{
	if (!$instance['options']['enablepms'] OR !$instance['options']['enablepmnotifs'])
	{
		// Not having notices here
		continue;
	}
	
	// Insert the shout
	$shout = VBSHOUT::datamanager_init('Shout', $this->registry, ERRTYPE_ARRAY);
		$shout->set_info('automated', true);	
		$shout->set('message', construct_phrase(
			$vbphrase['dbtech_vbshout_i_sent_you_a_forum_pm_x'],
			$this->registry->options['bburl'],
			$this->registry->session->vars['sessionurl'],
			$pmid,
			$this->pmtext['title']							
		))
		->set('type', VBSHOUT::$shouttypes['pm'])
		->set('userid', $fromuserid)
		->set('instanceid', $instanceid)
		->set('id', $userid);
	$shout->save();
	unset($shout);
}

?>