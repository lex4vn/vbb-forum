<?php

do
{
	if (!$this->registry->options['dbtech_vbshout_logging_deep'])
	{
		// We're not logging anything
		break;
	}
	
	if (!$this->registry->options['dbtech_vbshout_logging_deep_system'] AND $this->fetch_field('userid') == -1)
	{
		// We're not logging system messages
		break;
	}
	
	// Insert into deep log
	$this->registry->db->query_write("
		INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_deeplog
			(shoutid, userid, dateline, message, type, id, notification)
		VALUES (
			" . intval($this->fetch_field('shoutid')) . ",
			" . intval($this->fetch_field('userid')) . ",
			" . intval($this->fetch_field('dateline')) . ",
			" . $this->registry->db->sql_prepare($this->fetch_field('message')) . ",
			" . $this->registry->db->sql_prepare($this->fetch_field('type')) . ",
			" . $this->registry->db->sql_prepare($this->fetch_field('id')) . ",
			" . $this->registry->db->sql_prepare($this->fetch_field('notification')) . "
		)
	");
}
while (false);
?>