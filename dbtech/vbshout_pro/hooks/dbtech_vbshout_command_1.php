<?php
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
	// We did handle it
	$handled = true;
	
	// This command exists - we already know it's a 1-stage command
	$this->set('message', $commandlist["$matches[1]"]['output']);
	
	// Run this through the parser again just in case
	$return_value = $this->parse_action_codes();
}
?>