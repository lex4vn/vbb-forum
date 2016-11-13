<?php
$status = self::$vbulletin->input->clean_gpc(self::$fetchtype, 'status', TYPE_UINT);

// Chat leave
self::leave_chatroom(self::$chatroom, self::$vbulletin->userinfo['userid']);
?>