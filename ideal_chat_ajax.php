<?php
/**
|*  Ideal Chat Pro v1.3.0
|*  Created: July 10th, 2011
|*  Last Modified: October 29th, 2011
|*  Author: Ideal Web Technologies (www.idealwebtech.com)
|*
|*  Copyright (c) 2011 Ideal Web Technologies
|*  This file is only to be used with the consent of Ideal Web Technologies 
|*  and may not be redistributed in whole or significant part!  By using
|*  this file, you agree to the Ideal Web Technologies' Terms of Service
|*  at www.idealwebtech.com/documents/tos.html
**/

// ### SET PHP ENVIRONMENT ################################################
error_reporting(E_ALL & ~E_NOTICE);

// ### DEFINE IMPORTANT CONSTANTS #########################################
define('THIS_SCRIPT', 'ideal_chat_ajax');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');
define('LOCATION_BYPASS', 1);
define('NOSHUTDOWNFUNC', 1);
define('NOPMPOPUP', 1);
define('VB_ENTRY', 'ideal_chat_ajax.php');

//Turn off hooks so we can avoid loading any hooks that load on a global basis, as the chat doesn't need them and they will just waste resources
define('DISABLE_HOOKS', true);

// ### PRE-CACHE TEMPLATES AND DATA #######################################
// Get special phrase groups
$phrasegroups = array();

// Get special data templates from the datastore
$specialtemplates = array(
	'idealchat_mutedusers',
	'idealchat_bannedusers',
	'idealchat_kickedusers',
);

// Pre-cache templates used by all actions
$globaltemplates = array();

// Pre-cache templates used by specific actions
$actiontemplates = array(
	'openchat' => array(
		'iwt_idealchat_bb_chat_message_bit_special',
		'iwt_idealchat_bb_chat_message_bit',
		'iwt_idealchat_bb_user_chat_tab'
	),
	'openchats' => array(
		'iwt_idealchat_bb_chat_message_bit_special',
		'iwt_idealchat_bb_chat_message_bit',
		'iwt_idealchat_bb_user_chat_tab'
	),
	'openchatroom' => array(
		'iwt_idealchat_bb_chat_message_bit_special',
		'iwt_idealchat_bb_chat_message_bit',
		'iwt_idealchat_bb_user_chat_tab'
	),
	'openroomlist' => array(
		'iwt_idealchat_bb_chatrooms_list'
	),
	'openfriendslist' => array(
		'iwt_idealchat_bb_onlineusers_nobit',
		'iwt_idealchat_bb_onlineusers_bit'
	),
	'showhelp' => array(
		'iwt_idealchat_bb_chat_help'
	),
	'showsettings' => array(
		'iwt_idealchat_bb_chat_settings'
	),
	'savesettings' => array(
		'iwt_idealchat_bb_chat_settings'
	)
);

// ### REQUIRE BACK-END ###################################################
require_once('./global.php');
require_once(DIR . '/iwt/idealchat/includes/functions_general.php');
require_once(DIR . '/iwt/idealchat/includes/functions_ajax_general.php');
require_once(DIR . '/iwt/idealchat/includes/functions_message_processing.php');
require_once(DIR . '/includes/class_xml.php');

// ### GLOBAL SETUP #######################################################
// Verify the user can use the chat
if (!iwt_chat_can_use_chat())
{
	output_ajax_error($vbphrase['iwt_idealchat_nopermission']);
}

// Update the users chatting session
iwt_chat_update_lasttouch();

// ### SETUP THE XML BUILDER ##############################################
$xml = new vB_XML_Builder($vbulletin);
$xml->add_group('ajaxresponse');

// ########################################################################
// ### START MAIN SCRIPT ##################################################
// ########################################################################

// ### SUBMIT MESSAGE #####################################################
if ($_POST['do'] == 'submit_message')
{
	$userid = $vbulletin->input->clean_gpc('p', 'userid', TYPE_UINT);
	$userinfo = verify_id('user', $userid, 1, 1, (FETCH_USERINFO_ISFRIEND));

	if ($userinfo)
	{
		// Check that we can message this user
		if (!iwt_chat_can_message($userinfo))
		{
			output_ajax_error($vbphrase['iwt_idealchat_cantmessage']);
		}

		$ignoreusers = preg_split('#\s+#s', $userinfo['ignorelist'], -1, PREG_SPLIT_NO_EMPTY);

		if ( in_array($vbulletin->userinfo['userid'], $ignoreusers) )
		{
			output_ajax_error($vbphrase['iwt_idealchat_cantmsguser']);
		}

		// This is getting run thru the bbcode parser so we dont need to strip html as it will do it for us
		$message = $vbulletin->input->clean_gpc('p', 'message', TYPE_STR);
		$message = parse_chat_message($message);

		if ($message != '')
		{
			$vbulletin->db->query_write("
				INSERT INTO " . TABLE_PREFIX . "iwt_chat_convos (uidfrom,uidto,message,timestamp) VALUES
				({$vbulletin->userinfo[userid]}, {$userinfo[userid]}, '" . $vbulletin->db->escape_string($message) . "', '" . TIMENOW . "')
			");

			$lastmsgid = $vbulletin->db->insert_id();

			//We need to update the session table record now
			$vbulletin->db->query_write("
				INSERT INTO " . TABLE_PREFIX . "iwt_chat_updatestream (uid,lastmsgid) VALUES 
				({$userinfo[userid]},$lastmsgid),
				({$vbulletin->userinfo[userid]},$lastmsgid)
				ON DUPLICATE KEY UPDATE lastmsgid=$lastmsgid
			");
		}
	}
	else
	{
		output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
	}
}

// ### SUBMIT CHATROOM MESSAGE ############################################
elseif ($_POST['do'] == 'submit_chatroom_message')
{
	if ( $vbulletin->options['iwt_idealchat_chatroomsenabled']  && iwt_chat_can_use_chatroom() )
	{
		// Check if this user is muted
		if (iwt_chat_is_muted())
		{
			output_ajax_error($vbphrase['iwt_idealchat_usermuted']);
		}
		
		$roomid = $vbulletin->input->clean_gpc('p', 'roomid', TYPE_UINT);

		// Check for room session for the logged in user
		$roomcheck = $vbulletin->db->query_first("SELECT roomid FROM " . TABLE_PREFIX . "iwt_chatroom_chatting WHERE roomid=$roomid AND userid={$vbulletin->userinfo[userid]}");

		if ($roomcheck['roomid'])
		{
			// This is getting run thru the bbcode parser so we dont need to strip html as it will do it for us
			$message = $vbulletin->input->clean_gpc('p', 'message', TYPE_STR);
			$message = parse_chat_message($message);

			if ($message != '')
			{
				$vbulletin->db->query_write("
					INSERT INTO " . TABLE_PREFIX . "iwt_chatroom_messages (roomid,fromid,message,timestamp) VALUES
					($roomcheck[roomid], {$vbulletin->userinfo[userid]}, '" . $vbulletin->db->escape_string($message) . "', '" . TIMENOW . "')
				");

				$lastchatmsgid = $vbulletin->db->insert_id();

				//We need to update the session table record now
				$vbulletin->db->query_write("
					UPDATE " . TABLE_PREFIX . "iwt_chat_updatestream SET lastchatmsgid = $lastchatmsgid WHERE uid IN (SELECT chatting.userid FROM " . TABLE_PREFIX . "iwt_chatroom_chatting AS chatting WHERE roomid={$roomcheck[roomid]})
				");
			}
		}
		else
		{
			// Lets check if this user was kicked
			if ( iwt_chat_was_kicked_from_room($roomid) )
			{
				output_ajax_error($vbphrase['iwt_idealchat_userkicked']);
			}
			else
			{
				output_ajax_error($vbphrase['iwt_idealchat_invalidroom']);
			}
		}
	}
	else
	{
		output_ajax_error($vbphrase['iwt_idealchat_chatroomsdisabled']);
	}
}

// ### OPEN CHAT ##########################################################
else if ($_POST['do'] == 'openchat')
{
	$userid = $vbulletin->input->clean_gpc('p', 'userid', TYPE_UINT);
	$userinfo = verify_id('user', $userid, 1, 1, (FETCH_USERINFO_ISFRIEND));

	if ($userinfo)
	{
		// Check that we can message this user
		if (!iwt_chat_can_message($userinfo))
		{
			output_ajax_error($vbphrase['iwt_idealchat_cantmessage']);
		}

		$cutoff = TIMENOW - 86400;
		$messages = array();

		$messagesquery = $vbulletin->db->query_read("
			SELECT chat.*, user.username AS sender, user2.username AS reciever
			FROM " . TABLE_PREFIX . "iwt_chat_convos AS chat
			INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=chat.uidfrom)
			INNER JOIN " . TABLE_PREFIX . "user AS user2 ON (user2.userid=chat.uidto)
			WHERE timestamp >= $cutoff AND {$vbulletin->userinfo[userid]} IN (chat.uidfrom,chat.uidto) AND $userid IN (chat.uidfrom,chat.uidto)
			ORDER BY timestamp ASC
		");

		while($message = $vbulletin->db->fetch_array($messagesquery))
		{
			$messages[] = $message;
		}

		add_chattab_output($userinfo['userid'], $userinfo['username'], $messages);
	}
	else
	{
		output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
	}
}

// ### OPEN CHATROOM ######################################################
else if ($_POST['do'] == 'openchatroom')
{
	if ( $vbulletin->options['iwt_idealchat_chatroomsenabled'] && iwt_chat_can_use_chatroom() )
	{
		$roomid = $vbulletin->input->clean_gpc('p', 'roomid', TYPE_UINT);

		// Check if the user is banned from this chatroom
		if ( iwt_chat_is_banned_from_room($roomid) )
		{
			output_ajax_error($vbphrase['iwt_idealchat_userbanned']);
		}

		$datecut = (TIMENOW - ($vbulletin->options['iwt_idealchat_resetconnection']*60)) + 60;

		//Check for room session for the logged in user
		$roomrecord = $vbulletin->db->query_first("
			SELECT room.id, room.roomname, room.password, chatting.roomid AS inroom
			FROM " . TABLE_PREFIX . "iwt_chatrooms as room
			LEFT JOIN " . TABLE_PREFIX . "iwt_chatroom_chatting as chatting ON (chatting.roomid=room.id AND chatting.userid={$vbulletin->userinfo[userid]})
			WHERE room.id=$roomid
		");

		$cutoff = TIMENOW - 21600;

		if ($roomrecord['inroom'])
		{
			$messages = array();

			$messagesquery = $vbulletin->db->query_read("
				SELECT chatroom.*, user.username AS sender
				FROM " . TABLE_PREFIX . "iwt_chatroom_messages AS chatroom
				INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=chatroom.fromid)
				WHERE timestamp > $cutoff AND chatroom.roomid = {$roomid}
				ORDER BY timestamp ASC
				LIMIT 0, 100
			");

			while($message = $vbulletin->db->fetch_array($messagesquery))
			{
				$messages[] = $message;
			}

			add_chatroomtab_output($roomrecord['id'], $roomrecord['roomname'], $messages);
		}
		else
		{
			$roompassword = md5($vbulletin->input->clean_gpc('p', 'password', TYPE_STR));

			// Check that we found the room
			if (!$roomrecord['id'])
			{
				//Output error about invalid room
				output_ajax_error($vbphrase['iwt_idealchat_invalidroom']);
			}

			// Check if the room has a password
			if ( ($roomrecord['password'] == $roompassword) || ($roomrecord['password'] == ''))
			{
				// Lets insert the record into the chatroom session table
				$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "iwt_chatroom_chatting (roomid, userid) VALUES ({$roomrecord[id]}, {$vbulletin->userinfo[userid]})");

				// Load the chatroom
				$messages = array();

				$messagesquery = $vbulletin->db->query_read("
					SELECT chatroom.*, user.username AS sender
					FROM " . TABLE_PREFIX . "iwt_chatroom_messages AS chatroom
					INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=chatroom.fromid)
					WHERE timestamp > $cutoff AND chatroom.roomid = {$roomid}
					ORDER BY timestamp ASC
					LIMIT 0, 100
				");

				while($message = $vbulletin->db->fetch_array($messagesquery))
				{
					$messages[] = $message;
				}

				add_chatroomtab_output($roomrecord['id'], $roomrecord['roomname'], $messages);
			}
			else if (!$vbulletin->input->clean_gpc('p', 'skipifwrongpassword', TYPE_BOOL))
			{
				$xml->add_tag('roomid', $roomid);
				$xml->add_tag('password_protected', 'true');
			}
		}
	}
	else
	{
		$xml->add_tag('rooms_disabled', 'true');
	}
}

// ### CLOSE CHATROOM ######################################################
else if ($_POST['do'] == 'closechatroom')
{
	$roomid = $vbulletin->input->clean_gpc('p', 'roomid', TYPE_UINT);

	if ($roomid)
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "iwt_chatroom_chatting WHERE roomid=$roomid AND userid={$vbulletin->userinfo[userid]}");
	}
}

// ### OPEN CHATS #########################################################
else if ($_POST['do'] == 'openchats')
{
	$userids = $vbulletin->input->clean_gpc('p', 'userids', TYPE_STR);
	$lastmsgid = $vbulletin->input->clean_gpc('p', 'lastmsgid', TYPE_UINT);
	$cutoff = TIMENOW - 86400;
	$messages = array();

	$userids = explode(',', $userids);

	foreach ($userids AS $k => $userid)
	{
		if (!is_numeric($userid))
		{
			unset($userids[$k]);
		}
		else
		{
			$messages[$userid] = array();
		}
	}

	$userids = implode(',', $userids);

	if (!empty($userids))
	{
		$messagesquery = $vbulletin->db->query_read("
			SELECT chat.*, user.username AS sender, user2.username AS reciever
			FROM " . TABLE_PREFIX . "iwt_chat_convos AS chat
			INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=chat.uidfrom)
			INNER JOIN " . TABLE_PREFIX . "user AS user2 ON (user2.userid=chat.uidto)
			WHERE timestamp >= $cutoff AND chat.id <= $lastmsgid AND ((chat.uidfrom IN ($userids) AND chat.uidto={$vbulletin->userinfo[userid]}) OR (chat.uidfrom={$vbulletin->userinfo[userid]} AND chat.uidto IN ($userids)))
			ORDER BY timestamp ASC
		");

		while ($message = $vbulletin->db->fetch_array($messagesquery))
		{
			$userid = (($message['uidfrom']==$vbulletin->userinfo['userid'] ? $message['uidto'] : $message['uidfrom']));
			$messages[$userid][] = $message;
		}

		foreach ($messages AS $userid => $user_messages)
		{
			if (!empty($user_messages))
			{
				$username = (($user_messages[0]['sender']==$vbulletin->userinfo['username']) ? $user_messages[0]['reciever'] :  $user_messages[0]['sender']);
			}
			else
			{
				$username = $vbulletin->db->query_first("SELECT username FROM " . TABLE_PREFIX . "user WHERE userid=$userid");
				$username = $username['username'];
			}

			add_chattab_output($userid, $username, $user_messages);
		}
	}

	// Lets Get The Chatroom Tabs
	if ( !$vbulletin->input->clean_gpc('p', 'skiprooms', TYPE_BOOL) && $vbulletin->options['iwt_idealchat_chatroomsenabled'] && iwt_chat_can_use_chatroom() )
	{
		$messages = array();

		//Check for room session for the logged in user
		$openroomsquery = $vbulletin->db->query_read("
			SELECT room.id, room.roomname
			FROM " . TABLE_PREFIX . "iwt_chatrooms as room
			INNER JOIN " . TABLE_PREFIX . "iwt_chatroom_chatting as chatting ON (chatting.roomid=room.id AND chatting.userid={$vbulletin->userinfo[userid]})
		");

		$messages = array();
		$openrooms = array();
		$roomnames = array();

		while ($openroom = $vbulletin->db->fetch_array($openroomsquery))
		{
			$roomid = $openroom['id'];
			$openrooms[] = $roomid;
			$roomnames[$roomid] = $openroom['roomname'];
			$messages[$roomid] = array();
		}

		if (!empty($openrooms))
		{
			$openrooms = implode(',', $openrooms);
			$cutoff = TIMENOW - 21600;
			$limit = 100;

			//Check for room session for the logged in user
			$messagesquery = $vbulletin->db->query_read("
				SELECT messages.*, user.username as sender
				FROM " . TABLE_PREFIX . "iwt_chatroom_messages AS messages
				INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=messages.fromid)
				WHERE messages.roomid IN ($openrooms) AND messages.timestamp > $cutoff
				ORDER BY messages.timestamp ASC
				LIMIT 0, $limit
			");

			while ($message = $vbulletin->db->fetch_array($messagesquery))
			{
				$roomid = $message['roomid'];
				$messages[$roomid][] = $message;
			}

			foreach ($messages AS $roomid => $messagetemp)
			{
				$xml->add_group('chatroomtab');
				$xml->add_tag('roomid', $roomid);
				$roomname = $roomnames[$roomid];
				$bits = '';

				foreach ($messagetemp AS $message)
				{
					$messageSlashCommand = parse_slash_command_chatroom($message['message'], $message['sender']);

					if ($messageSlashCommand != $message['message'])
					{
						$templater = vB_Template::create('iwt_idealchat_bb_chat_message_bit_special');
							$templater->register('message', $messageSlashCommand);
							$templater->register('time_sent', vbdate('M j, g:i:s a', $message['timestamp']));
						$bits .= $templater->render(false);
					}
					else
					{
						$templater = vB_Template::create('iwt_idealchat_bb_chat_message_bit');
							$templater->register('sender', (($message['sender']==$vbulletin->userinfo['username']) ? $vbphrase['iwt_idealchat_you'] : $message['sender']));
							$templater->register('message', $message['message']);
							$templater->register('time_sent', vbdate('M j, g:i:s a',$message['timestamp']));
						$bits .= $templater->render(false);
					}
				}

				$templater = vB_Template::create('iwt_idealchat_bb_chatroom_tab');
					$templater->register('roomid', $roomid);
					$templater->register('roomname', $roomname);
					$templater->register('messages', $bits);
				$html = $templater->render(false);

				//spit out tab info
				$xml->add_tag('html', $html, $attr = array(), $cdata = true);
				$xml->close_group('chatroomtab');
			}
		}
	}
}

// ### OPEN ROOM LIST ##################################################
else if ($_POST['do'] == 'openroomlist')
{
	// Grab the rooms
	if ($vbulletin->options['iwt_idealchat_chatroomsenabled'])
	{
		$roomsquery = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "iwt_chatrooms");
		$rooms = array();

		while ($room = $vbulletin->db->fetch_array($roomsquery))
		{
			$rooms[$room['id']] = $room;
			$rooms[$room['id']]['online_count'] = 0;
			$rooms[$room['id']]['online_users'] = array();
		}

		// Grab the online users
		$datecut = (TIMENOW - ($vbulletin->options['iwt_idealchat_resetconnection']*60)) + 60;

		$onlineusersquery = $vbulletin->db->query_read("
			SELECT chatting.roomid, user.username
			FROM " . TABLE_PREFIX . "iwt_chatroom_chatting AS chatting
			INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=chatting.userid)
			INNER JOIN " . TABLE_PREFIX . "iwt_chat_updatestream AS stream ON (stream.uid=chatting.userid AND lasttouch > $datecut)
		");

		while ($temp = $vbulletin->db->fetch_array($onlineusersquery))
		{
			$rooms[$temp['roomid']]['online_count']++;
			$rooms[$temp['roomid']]['online_users'][] = $temp['username'];
		}

		foreach ($rooms AS $id => $values)
		{
			$rooms[$id]['online_users'] = implode(', ', $values['online_users']);
		}
	}

	// Send the ouput
	$templater = vB_Template::create('iwt_idealchat_bb_chatrooms_list');
		$templater->register('ispopup', 1);
		$templater->register('rooms', $rooms);
	$xml->add_tag('roomlistshtml', $templater->render(false));
}

// ### OPEN FRIENDS LIST ##################################################
else if ($_POST['do'] == 'openfriendslist')
{
	$datecut = (TIMENOW - ($vbulletin->options['iwt_idealchat_resetconnection']*60)) + 60;
	$avatars_enabled = ((2 & FETCH_USERINFO_AVATAR) AND $vbulletin->options['avatarenabled']);
	$xml->add_group('userlists');

	// Check if we are using mutual friends or all friends
	if($vbulletin->options['iwt_idealchat_friendstype'])
	{
		// Grab the user's online friends and their avatars
		$friendsquery = $vbulletin->db->query_read("
			SELECT user.userid, user.username, user.avatarrevision, user.avatarid" . iif($avatars_enabled, ', avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline, customavatar.filedata_thumb') . "
			FROM " . TABLE_PREFIX . "userlist AS ulist 
			INNER JOIN " . TABLE_PREFIX . "user AS user ON (ulist.relationid = user.userid) " 
			. iif($avatars_enabled, "
				LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON (avatar.avatarid = user.avatarid) 
				LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON (customavatar.userid = user.userid)
			") . "
			WHERE ulist.userid={$vbulletin->userinfo[userid]} AND ulist.friend='yes' AND ulist.relationid IN (
				SELECT stream.uid
				FROM " . TABLE_PREFIX . "iwt_chat_updatestream AS stream
				WHERE stream.lasttouch > $datecut
			)
			GROUP BY user.userid
		");
	}
	else
	{
		if ($vbulletin->userinfo['buddylist'] != '')
		{
			$friends = str_replace(' ', ',', $vbulletin->userinfo['buddylist']);
		}
		else
		{
			$friends = '0';
		}
		$friendsquery = $vbulletin->db->query_read("
			SELECT user.userid, user.username, user.avatarrevision, user.avatarid" . iif($avatars_enabled, ', avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline, customavatar.filedata_thumb') . "
			FROM " . TABLE_PREFIX . "iwt_chat_updatestream AS stream
			INNER JOIN " . TABLE_PREFIX . "user AS user ON (stream.uid = user.userid) "
			. iif($avatars_enabled, "
				LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON (avatar.avatarid = user.avatarid) 
				LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON (customavatar.userid = user.userid)
			") . "
			WHERE stream.lasttouch > $datecut AND stream.uid IN ($friends)
			GROUP BY user.userid
		");
	}

	//Setup friendbits
	$friendbits = '';

	//Loop thro friends online
	while ($friend = $vbulletin->db->fetch_array($friendsquery))
	{	
		$templater = vB_Template::create('iwt_idealchat_bb_onlineusers_bit');
			$templater->register('user', $friend);
			$templater->register('avatarurl', iwt_chat_get_avatar_url($friend));
		$friendbits .= $templater->render(false);
	}

	if (empty($friendbits))
	{
		$friendbits = vB_Template::create('iwt_idealchat_bb_onlineusers_nobit')->render(false);
	}

	$xml->add_tag('friendlist', $friendbits);

	if ($vbulletin->options['iwt_idealchat_staffgroups'] != '')
	{
		//Grab the staff that are online
		$staffquery = $vbulletin->db->query_read("
			SELECT user.userid, user.username, user.avatarrevision, user.avatarid" . iif($avatars_enabled, ', avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline, customavatar.filedata_thumb') . "
			FROM " . TABLE_PREFIX . "user AS user "
			. iif($avatars_enabled, "
				LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON (avatar.avatarid = user.avatarid) 
				LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON (customavatar.userid = user.userid)
			") . "
			WHERE user.usergroupid IN (" . $vbulletin->db->escape_string($vbulletin->options['iwt_idealchat_staffgroups']) . ") AND user.userid IN (
				SELECT stream.uid
				FROM " . TABLE_PREFIX . "iwt_chat_updatestream AS stream
				WHERE stream.lasttouch > $datecut
			)
			GROUP BY user.userid
		");

		//Setup staffbits
		$staffbits = '';

		//Loop thro friends online
		while ($staffmember = $vbulletin->db->fetch_array($staffquery))
		{
			//Lets hide ourself from the staff list if we are staff
			if ($staffmember['userid'] != $vbulletin->userinfo['userid'])
			{
				$templater = vB_Template::create('iwt_idealchat_bb_onlineusers_bit');
					$templater->register('user', $staffmember);
					$templater->register('avatarurl', iwt_chat_get_avatar_url($staffmember));
				$staffbits .= $templater->render(false);
			}
		}

		if (empty($staffbits))
		{
			$staffbits = vB_Template::create('iwt_idealchat_bb_onlineusers_nobit')->render(false);
		}

		$xml->add_tag('stafflist', $staffbits);
	}

	$xml->close_group('userlists');
}

// ### SHOW HELP ##########################################################
elseif ($_POST['do'] == 'showhelp')
{
	$commands = fetch_chat_commands();
	$templater = vB_Template::create('iwt_idealchat_bb_chat_help');
		$templater->register('ispopup', 1);
		$templater->register('commands', $commands);
		$templater->register('totalCommands', count($commands));
	$xml->add_tag('helphtml', $templater->render(false));
}

// ### SHOW SETTINGS ######################################################
elseif ($_POST['do'] == 'showsettings')
{
	$templater = vB_Template::create('iwt_idealchat_bb_chat_settings');
		$templater->register('ispopup', 1);
		$templater->register('message', '');
        $templater->register('sounds', $iwt_chat_notification_sounds); 
	$xml->add_tag('settingshtml', $templater->render(false));
}

// ### SAVE SETTINGS ######################################################
elseif ($_POST['do'] == 'savesettings')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'show_bar' => TYPE_BOOL,
		'notify_sound' => TYPE_UINT,
		'notify_method' => TYPE_UINT
	));
	
	$show_bar = $vbulletin->GPC['show_bar'];
	$notify_sound = $vbulletin->GPC['notify_sound'];
	$notify_method = $vbulletin->GPC['notify_method'];

	if (!in_array($notify_sound, array_keys($iwt_chat_notification_sounds)))
	{
		$notify_sound = 1;
	}

	$vbulletin->userinfo['iwt_chatshow'] = $show_bar;
	$vbulletin->userinfo['iwt_chatsound'] = $notify_sound;
	$vbulletin->userinfo['iwt_chatnotifs'] = $notify_method;

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET iwt_chatnotifs=$notify_method, iwt_chatshow=$show_bar, iwt_chatsound=$notify_sound WHERE userid={$vbulletin->userinfo[userid]}");

	$settings = array(
		'chatnotifs' => $notify_method,
		'chatsound' => $iwt_chat_notification_sounds[$notify_sound]['file']
	);
	
	$xml->add_tag('settingsjson', json_encode($settings));

	$templater = vB_Template::create('iwt_idealchat_bb_chat_settings');
		$templater->register('ispopup', 1);
		$templater->register('message', $vbphrase['iwt_idealchat_settingssaved']);
        $templater->register('sounds', $iwt_chat_notification_sounds); 
	$xml->add_tag('settingshtml', $templater->render(false));
}

// ### GLOBAL SHUTDOWN ####################################################
// Close the response container
$xml->close_group('ajaxresponse');

// Run the shutdown
execute_shutdown_processes();

// ### SEND THE HEADERS ###################################################
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header('Content-Type: text/xml');

// Send the output and exit
exit('<?xml version="1.0" encoding="' . vB_Template_Runtime::fetchStyleVar('charset') . '"?>' . "\r\n" . $xml->output());

/**
|*  This file was downloaded from http://www.idealwebtech.com at 15:46:27, Thursday December 29th, 2011 
|*
|*  This product has been licensed to Brenda Covey.
|*  License Key: 2b01811ed4f45876dbd9392ea3a0a4ad
**/