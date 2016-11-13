<?php

if ($this->registry->options['dbtech_vbshout_enablepms'] AND $this->registry->options['dbtech_vbshout_enablepmnotifs'])
{
	// Insert the shout
	global $vbshout, $vbphrase;
	
	$shout = $vbshout->datamanager_init('vBShout', $this->registry, ERRTYPE_ARRAY);
	$shout->set('message', construct_phrase(
			$vbphrase['dbtech_vbshout_i_sent_you_a_forum_pm_x'],
			$this->registry->options['bburl'],
			$this->registry->session->vars['sessionurl'],
			$this->dbobject->insert_id(),
			$this->pmtext['title']							
		))
		->set('type', $vbshout->shouttypes['pm'])
		->set('userid', $fromuserid)
		->set('id', $userid);
	$shout->save();
	unset($shout);
}
?>