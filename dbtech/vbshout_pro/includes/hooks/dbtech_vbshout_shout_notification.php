<?php

if ($shoutid AND $vbulletin->options['dbtech_vbshout_logging_deep'])
{
	// Insert into deep log
	$vbulletin->db->query_write("
		INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_deeplog
			(shoutid, userid, dateline, message, type, notification)
		VALUES (
			" . intval($shoutid) . ",
			" . $vbulletin->userinfo['userid'] . ",
			" . TIMENOW . ",
			" . $vbulletin->db->sql_prepare(
				construct_phrase(
					$vbphrase["dbtech_vbshout_notif_$type"],
					$notif
				)
			) . ",
			8,
			'$type'
		)
	");
}

?>