<?php

switch ($matches[1])
{
	case '/silence':
	case '/unsilence':
		$handled = true;	
		if (!$this->vbshout->permissions['cansilence'])
		{
			$this->error('dbtech_vbshout_cannot_silence');
			break;
		}
		
		// Silence an user
		if (!$exists = $this->registry->db->query_first_slave("
			SELECT userid, username, usergroupid, membergroupids
			FROM " . TABLE_PREFIX . "user
			WHERE username = " . $this->registry->db->sql_prepare($matches[2])
		))
		{
			// We has an error
			$this->error('dbtech_vbshout_invalid_user');
			break;
		}
		
		if ($exists['userid'] == $this->registry->userinfo['userid'])
		{
			// Ourselves, duh
			$this->error('dbtech_vbshout_cannot_silence_self');			
			break;
		}
		
		// Checks for a protected usergroup
		if ($this->vbshout->check_protected_usergroup($exists, true))
		{
			// We had an error
			$this->error('dbtech_vbshout_protected_usergroup');
			$return_value = false;
			break;
		}
		
		// Ignore the user
		$this->registry->db->query_write("
			UPDATE " . TABLE_PREFIX . "user
			SET dbtech_vbshout_silenced = " . ($matches[1] == '/silence' ? '1' : '0') . "
			WHERE userid = " . intval($exists['userid']) . "
		");
		
		// Log the silence command
		$this->vbshout->log_command(($matches[1] == '/silence' ? 'silence' : 'unsilence'), $exists['userid']);		
		
		// Print success message
		//$this->fetched['success'] = construct_phrase($vbphrase['dbtech_vbshout_silenced_successfully'], $matches[2]);
		//$this->fetched['success'] = construct_phrase($vbphrase['dbtech_vbshout_unsilenced_successfully'], $matches[2]);		
		//$message = false;
		
		$return_value = false;
		break;
		
	case '/prune':
		$handled = true;	
		if (!$this->vbshout->permissions['canprune'])
		{
			$this->error('dbtech_vbshout_cannot_prune');			
			break;
		}
		
		// Prune an user's posts
		if (!$exists = $this->registry->db->query_first_slave("
			SELECT userid, username, usergroupid, membergroupids
			FROM " . TABLE_PREFIX . "user
			WHERE username = " . $this->registry->db->sql_prepare($matches[2])
		))
		{
			// We has an error
			$this->error('dbtech_vbshout_invalid_user');			
			break;
		}
		
		// Prune the entire shoutbox
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE userid = " . intval($exists['userid']));
		
		// Reset users' shouts
		$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "user SET dbtech_vbshout_shouts = 0 WHERE userid = " . intval($exists['userid']));
		
		// Log the prune command
		$this->vbshout->log_command('pruneuser', $exists['userid']);
		
		// Blank out the message and change type
		$this->set('userid', 	-1);
		$this->set('type', 		$this->vbshout->shouttypes['system']);
		$this->set('message', 	construct_phrase($vbphrase['dbtech_vbshout_x_shouts_pruned'], $matches[2]));
		$return_value = true;
		break;
}

if (!$handled)
{
	// The finished array of all commands
	$commandlist = array();
	
	// Query all commands we own
	$commandlist_q = $this->registry->db->query_read_slave("
		SELECT *
		FROM " . TABLE_PREFIX . "dbtech_vbshout_command
		WHERE userid = " . intval($this->registry->userinfo['userid']) . "
		ORDER BY command ASC
	");
	
	while ($commandlist_r = $this->registry->db->fetch_array($commandlist_q))
	{
		// Grab the list of all our current commands
		$commandlist["$commandlist_r[command]"] = $commandlist_r;
	}
	
	if ($commandlist["$matches[1]"])
	{
		// This command exists - we already know it's a 2-stage command
		$this->set('message', str_replace('{1}', $matches[2], $commandlist["$matches[1]"]['output']));
		
		// Run this through the parser again just in case
		$return_value = $this->parse_action_codes();
		
		// Call this handled
		$handled = true;
	}
}
?>