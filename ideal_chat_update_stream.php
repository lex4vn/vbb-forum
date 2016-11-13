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
ignore_user_abort(true);

// ### DEFINE IMPORTANT CONSTANTS #########################################
define('THIS_SCRIPT', 'ideal_chat_update_stream');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');
define('LOCATION_BYPASS', 1);
define('NOSHUTDOWNFUNC', 1);
define('NOPMPOPUP', 1);
define('VB_ENTRY', 'ideal_chat_update_stream.php');

//Turn off hooks so we can avoid loading any hooks that load on a global basis, as the chat doesn't need them and they will just waste resources
define('DISABLE_HOOKS', true);

// ### PRE-CACHE TEMPLATES AND DATA #######################################
// Get special phrase groups
$phrasegroups = array();

// Get special data templates from the datastore
$specialtemplates = array(
	'idealchat_kickedusers',
);

// Pre-cache templates used by all actions
$globaltemplates = array(
	'iwt_idealchat_bb_chat_message_bit_special',
	'iwt_idealchat_bb_chat_message_bit'
);

// Pre-cache templates used by specific actions
$actiontemplates = array();

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

// Lets check for chatroom sessions that have been killed (We do this up here instead of the loop to save processing power, and because the datacache won't change after this point!)
if (is_array($vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']]))
{
	// Lets unpack the cookie
	$cookiedata = json_decode($_COOKIE['iwt_idealChatData']);

	// Lets start stepping thru the rooms the user has been kicked from
	if ($vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']][0])
	{
		unset($cookiedata->openChatRooms);
	}
	else
	{
		foreach ($vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']] as $roomid => $value)
		{
			unset($cookiedata->openChatRooms->$value);
		}
	}

	$cookiedata = json_encode($cookiedata);
	setcookie('iwt_idealChatData', $cookiedata, 0, '/'); 

	// unset the kicks from the datastore
	unset($vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']]);
	build_datastore('idealchat_kickedusers', serialize($vbulletin->idealchat_kickedusers), 1);

	output_ajax_notice($vbphrase['iwt_idealchat_userkicked']);
}

// ### SEND THE HEADERS ###################################################
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header('Content-Type: text/xml');

echo '<?xml version="1.0" encoding="' . vB_Template_Runtime::fetchStyleVar('charset') . '"?>';

// ### SETUP THE XML BUILDER ##############################################
$xml = new vB_XML_Builder($vbulletin);
$xml->add_group('ajaxresponse');

// ########################################################################
// ### START MAIN SCRIPT ##################################################
// ########################################################################

$posted_lastmsgid = $_POST['lastmsgid'];
$posted_lastchatmsgid = $_POST['lastchatmsgid'];

$vbulletin->input->clean_array_gpc('p', array(
	'openchats' => TYPE_STR,
	'lastmsgid' => TYPE_UINT,
	'lastchatmsgid' => TYPE_UINT,
	'friendsonline' => TYPE_UINT,
));

// Setup last message id variables
if ($posted_lastmsgid == -1)
{
	$lastmsgid_orig = -1;
}
else
{
	$lastmsgid_orig = $vbulletin->GPC['lastmsgid'];
}

$lastmsgid = $lastmsgid_orig;

// Setup chat last message id variables
if ($posted_lastchatmsgid == -1)
{
	$lastchatmsgid_orig = -1;
}
else
{
	$lastchatmsgid_orig = $vbulletin->GPC['lastchatmsgid'];
}

$lastchatmsgid = $lastchatmsgid_orig;

$firstrun = true;
$datecut = (TIMENOW - ($vbulletin->options['iwt_idealchat_resetconnection'] * 60)) + 60;

do {
	// Sleep for a while so we don't kill the server
	if (!$firstrun)
	{
		sleep($vbulletin->options['iwt_idealchat_chatsleep']);
		$datecut += $vbulletin->options['iwt_idealchat_chatsleep'];
	}

	// Lets check if the browser is still there
    echo " ";

	// Bugfix temporary patch: Check if we need to flush ob_buffer as well, solves the problem people have with having to reload to see messages.  I am still looking for a more permanent fix but for now a setting to enable the fix if needed should suffice.
	if ($vbulletin->options['iwt_idealchat_obbugfix'])
	{
		ob_end_flush();
		ob_flush();
	}

	flush();
    if (connection_aborted()) { break; }

	// Check if we are using mutual friends or all friends
	if($vbulletin->options['iwt_idealchat_friendstype'])
	{
		// Query the session information and friends information
		$usercache = $vbulletin->db->query_first("
			SELECT stream.lastmsgid AS lastmsgid, stream.lastchatmsgid AS lastchatmsgid, COUNT(ulist.relationid) AS friendsonline
			FROM " . TABLE_PREFIX . "iwt_chat_updatestream AS stream
			LEFT JOIN " . TABLE_PREFIX . "userlist AS ulist ON (ulist.userid=stream.uid AND ulist.friend='yes' AND ulist.relationid IN (
				SELECT stream2.uid
				FROM " . TABLE_PREFIX . "iwt_chat_updatestream AS stream2
				WHERE stream2.lasttouch > $datecut
				GROUP BY stream2.uid
			))
			WHERE stream.uid={$vbulletin->userinfo[userid]}
			GROUP BY stream.uid
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

		// Query the session information and friends information
		$usercache = $vbulletin->db->query_first("
			SELECT stream.lastmsgid AS lastmsgid, stream.lastchatmsgid AS lastchatmsgid, COUNT(stream2.uid) AS friendsonline
			FROM " . TABLE_PREFIX . "iwt_chat_updatestream AS stream
			LEFT JOIN " . TABLE_PREFIX . "iwt_chat_updatestream AS stream2 ON (stream2.lasttouch > $datecut AND stream2.uid IN ($friends))
			WHERE stream.uid={$vbulletin->userinfo[userid]}
		");


	}

	// Check if we need to run any chat message related work
	if ($usercache['lastmsgid'] > $lastmsgid)
	{
		if ($lastmsgid != -1)
		{
			$whereclause = "id > $lastmsgid";
		}
		else
		{
			$whereclause = "timestamp > " . (TIMENOW - 86400);
			$lastmsgid = 0;
		}

		$messagesquery = $vbulletin->db->query_read("
			SELECT chat.*, user.username AS sender, user2.username AS reciever
			FROM " . TABLE_PREFIX . "iwt_chat_convos AS chat
			INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=chat.uidfrom)
			INNER JOIN " . TABLE_PREFIX . "user AS user2 ON (user2.userid=chat.uidto)
			WHERE $whereclause AND ((chat.uidto={$vbulletin->userinfo[userid]}) OR (chat.uidfrom={$vbulletin->userinfo[userid]}))
			ORDER BY timestamp ASC
		");

		$messages = array();

		while ($message2 = $vbulletin->db->fetch_array($messagesquery))
		{
			$userid = (($message2['uidfrom']==$vbulletin->userinfo['userid'] ? $message2['uidto'] : $message2['uidfrom']));
			$messages[$userid][] = $message2;
		}

		foreach ($messages AS $userid => $messagetemp)
		{
			$xml->add_group('tabupdates');
			$xml->add_tag('userid', $userid);
			$bits = '';
			$flashtab = false;

			foreach ($messagetemp AS $message)
			{
				if (!$flashtab)
				{
					$flashtab = ($message['sender'] != $vbulletin->userinfo['username']);
				}

				$messageSlashCommand = parse_slash_command($message['message'], $message['sender'], $message['reciever']);

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

				if ($message['id'] > $lastmsgid)
				{
					$lastmsgid = $message['id'];
				}
			}

			//spit out tab info
			$xml->add_tag('messages', $bits, $attr = array(), $cdata = true);
			$xml->add_tag('flashtab', (($flashtab && $lastmsgid_orig != 0) ? 1 : 0));
			$xml->close_group('tabupdates');
		}

		$xml->add_tag('lastmsgid', $lastmsgid);
	}

	// Check if we need to run any chatroom message related work
	if ($usercache['lastchatmsgid'] > $lastchatmsgid)
	{
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

		if ($lastchatmsgid != -1)
		{
			$whereclause = "msgid > $lastchatmsgid";
			$limit = '';
		}
		else
		{
			// Right now get messages from last 6 hours as 24 would just be to much, really need a setting for this
			$whereclause = "timestamp > " . (TIMENOW - 21600);
			$limit = "LIMIT 100";
			$lastchatmsgid = 0;
		}

		if (!empty($openrooms))
		{
			$openrooms = implode(',', $openrooms);

			$messagesquery = $vbulletin->db->query_read("
				SELECT chatroom.*, user.username AS sender
				FROM " . TABLE_PREFIX . "iwt_chatroom_messages AS chatroom
				INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid=chatroom.fromid)
				WHERE $whereclause AND chatroom.roomid IN ($openrooms)
				ORDER BY timestamp ASC
				$limit
			");

			$messages = array();

			while ($message = $vbulletin->db->fetch_array($messagesquery))
			{
				$roomid = $message['roomid'];
				$messages[$roomid][] = $message;
			}

			foreach ($messages AS $roomid => $messagetemp)
			{
				$xml->add_group('roomtabupdates');
				$xml->add_tag('roomid', $roomid);
				$roomname = $roomnames[$roomid];
				$bits = '';
				$flashtab = false;

				foreach ($messagetemp AS $message)
				{
					if (!$flashtab)
					{
						$flashtab = ($message['sender'] != $vbulletin->userinfo['username']);
					}

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

					if ($message['msgid'] > $lastchatmsgid)
					{
						$lastchatmsgid = $message['msgid'];
					}
				}

				//spit out tab info
				$xml->add_tag('messages', $bits, $attr = array(), $cdata = true);
				$xml->add_tag('flashtab', (($flashtab && $lastchatmsgid_orig != 0) ? 1 : 0));
				$xml->close_group('roomtabupdates');
			}
		}
		else
		{
			$lastchatmsgid = 0;
		}

		$xml->add_tag('lastchatmsgid', $lastchatmsgid);
	}

	// Check if the friends online count has changed if so push the update to the stream
	if ($usercache['friendsonline'] != $vbulletin->GPC['friendsonline'])
	{
		$xml->add_tag('friendsonline', $usercache['friendsonline']);
		$xml->add_tag('friendsonlinetext', $usercache['friendsonline'] . ' ' . ($usercache['friendsonline'] != 1 ? $vbphrase['iwt_idealchat_friendsonline'] : $vbphrase['iwt_idealchat_friendonline']));
		break;
	}

	$firstrun = false;
} while ($lastmsgid == $lastmsgid_orig && $lastchatmsgid == $lastchatmsgid_orig);

// ### GLOBAL SHUTDOWN ####################################################
// Close the response container
$xml->close_group('ajaxresponse');

// Run the shutdown
execute_shutdown_processes();

// Send the output and exit
exit("\r\n" . $xml->output());

/**
|*  This file was downloaded from http://www.idealwebtech.com at 15:46:27, Thursday December 29th, 2011 
|*
|*  This product has been licensed to Brenda Covey.
|*  License Key: 2b01811ed4f45876dbd9392ea3a0a4ad
**/