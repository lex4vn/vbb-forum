<?php
// Delete all ignored users from this user
$this->dbobject->query_write("
	DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist
	WHERE userid = " . $this->existing['userid'] . "
		OR ignoreuserid = " . $this->existing['userid']
);

// Delete all custom commands by this user
$this->dbobject->query_write("
	DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_command
	WHERE userid = " . $this->existing['userid']
);

// Update deep log entries from this user
$this->dbobject->query_write("
	UPDATE " . TABLE_PREFIX . "dbtech_vbshout_deeplog
	SET username = " . $this->dbobject->sql_prepare($this->existing['username']) . "
	WHERE userid = " . $this->existing['userid']
);
?>