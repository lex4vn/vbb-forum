<?php
if (VBSHOUT::$instance['options']['autodelete'])
{
	// Convert the hours to seconds
	$cutoff = VBSHOUT::$instance['options']['autodelete'] * 3600;
	
	// Now get rid of the shouts
	$this->registry->db->query_write("
		DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
		WHERE (
			`instanceid` IN(0, " . intval($this->fetch_field('instanceid')) . ") OR
			`chatroomid` = " . intval($this->fetch_field('chatroomid')) . "
		)
			AND dateline <= " . (TIMENOW - $cutoff) . "		
	");
	
	if ($this->registry->db->affected_rows())
	{
		// Rebuild shout counts
		VBSHOUT::build_shouts_counter();
	}
}
?>