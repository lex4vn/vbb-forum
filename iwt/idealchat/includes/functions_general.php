<?php
/**
|*  Ideal Chat Pro v1.3.0
|*  Created: July 10th, 2011
|*  Last Modified: August 18th, 2011
|*  Author: Ideal Web Technologies (www.idealwebtech.com)
|*
|*  Copyright (c) 2011 Ideal Web Technologies
|*  This file is only to be used with the consent of Ideal Web Technologies 
|*  and may not be redistributed in whole or significant part!  By using
|*  this file, you agree to the Ideal Web Technologies' Terms of Service
|*  at www.idealwebtech.com/documents/tos.html
**/

$iwt_chat_notification_sounds = array(
	'0' => array('name' => 'None', 'file' => ''),
	'1' => array('name' => 'Strong Beep', 'file' => 'strong_beep.mp3'),
	'2' => array('name' => 'Simple Beep', 'file' => 'simple_beep.mp3'),
	'3' => array('name' => 'Notice', 'file' => 'notice.mp3'),
	'4' => array('name' => 'Power Up', 'file' => 'power_up.mp3'),
	'5' => array('name' => 'Enchant', 'file' => 'enchant.mp3'),
	'6' => array('name' => 'Wood Clacking', 'file' => 'wood_clacking.mp3'),
	'7' => array('name' => 'Breaking Glass', 'file' => 'breaking_glass.mp3')
);

function iwt_chat_update_lasttouch()
{
	global $vbulletin;

	$vbulletin->db->query_write("
		INSERT INTO " . TABLE_PREFIX . "iwt_chat_updatestream (uid,lasttouch) VALUES 
		({$vbulletin->userinfo[userid]}, " . TIMENOW . ")
		ON DUPLICATE KEY UPDATE lasttouch=" . TIMENOW 
	);
}

function iwt_chat_get_avatar_url($userinfo)
{
	global $vbulletin;

	if ($userinfo['avatarid'])
	{
		return $userinfo['avatarpath'];
	}
	else if ($userinfo['hascustomavatar'] AND $vbulletin->options['avatarenabled'])
	{
		if ($vbulletin->options['usefileavatar'])
		{
			return $vbulletin->options['avatarurl'] . "/avatar$userinfo[userid]_$userinfo[avatarrevision].gif";
		}
		else
		{
			return 'image.php?' . $vbulletin->session->vars['sessionurl'] . "u=$userinfo[userid]&amp;dateline=$userinfo[avatardateline]";
		}
	}

	return '';
}

function iwt_chat_can_use_chat()
{
	global $vbulletin;

	return (
		$vbulletin->options['iwt_idealchat_active'] && 
		$vbulletin->userinfo['userid'] &&
		($vbulletin->userinfo['permissions']['idealchatpermissions'] & $vbulletin->bf_ugp_idealchatpermissions['canusechat']) && 
		!in_array($vbulletin->userinfo['userid'], explode(',', $vbulletin->options['iwt_idealchat_bannedusers']))
	);
}

function iwt_chat_can_use_chatroom()
{
	global $vbulletin;

	return (
		$vbulletin->options['iwt_idealchat_active'] && 
		$vbulletin->userinfo['userid'] &&
		($vbulletin->userinfo['permissions']['idealchatpermissions'] & $vbulletin->bf_ugp_idealchatpermissions['canusechatroom']) && 
		!in_array($vbulletin->userinfo['userid'], explode(',', $vbulletin->options['iwt_idealchat_bannedusers']))
	);
}

function iwt_chat_can_message($userinfo)
{
	global $vbulletin;
	$chatstaff = explode(',', $vbulletin->options['iwt_idealchat_staffgroups']);

	return (
		$vbulletin->userinfo['userid'] != $userid &&
		(
			!$vbulletin->options['iwt_idealchat_friendstype'] || $userinfo['isfriend'] ||
			in_array($userinfo['usergroupid'], $chatstaff) ||
			in_array($vbulletin->userinfo['usergroupid'], $chatstaff)
		)
	);
}

function iwt_chat_is_muted($userid = -1)
{
	global $vbulletin;

	if ($userid == -1)
	{
		$userid = $vbulletin->userinfo['userid'];
	}

	if ( isset($vbulletin->idealchat_mutedusers[$userid]) )
	{
		if ( $vbulletin->idealchat_mutedusers[$userid] != 0 && $vbulletin->idealchat_mutedusers[$userid] <= TIMENOW )
		{
			// User was muted but the mute time has expired
			unset($vbulletin->idealchat_mutedusers[$userid]);
			build_datastore('idealchat_mutedusers', serialize($vbulletin->idealchat_mutedusers), 1);
		}
		else
		{
			return true;
		}
	}

	return false;
}

function iwt_chat_was_kicked_from_room($roomid)
{
	global $vbulletin;

	if ( $vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']][0] || $vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']][$roomid] )
	{
		$cookiedata = json_decode($_COOKIE['iwt_idealChatData']);

		if ( $vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']][0] )
		{
			unset($cookiedata->openChatRooms);
			unset($vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']]);
		}
		else
		{
			unset($cookiedata->openChatRooms->$roomid);
			unset($vbulletin->idealchat_kickedusers[$vbulletin->userinfo['userid']][$roomid]);
		}

		$cookiedata = json_encode($cookiedata);
		setcookie('iwt_idealChatData', $cookiedata, 0, '/'); 
		build_datastore('idealchat_kickedusers', serialize($vbulletin->idealchat_kickedusers), 1);

		return true;
	}

	return false;
}

function iwt_chat_is_banned_from_room($roomid)
{
	global $vbulletin;

	if ( isset($vbulletin->idealchat_bannedusers[$vbulletin->userinfo['userid']][0]))
	{
		if ( $vbulletin->idealchat_bannedusers[$vbulletin->userinfo['userid']][0] != 0 && $vbulletin->idealchat_bannedusers[$vbulletin->userinfo['userid']][0] <= TIMENOW )
		{
			// User was banned but the ban time has expired
			unset($vbulletin->idealchat_bannedusers[$vbulletin->userinfo['userid']][0]);
			build_datastore('idealchat_bannedusers', serialize($vbulletin->idealchat_bannedusers), 1);
		}
		else
		{
			return true;
		}
	}
	
	if ( isset($vbulletin->idealchat_bannedusers[$vbulletin->userinfo['userid']][$roomid]))
	{
		if ( $vbulletin->idealchat_bannedusers[$vbulletin->userinfo['userid']][$roomid] <= TIMENOW && $vbulletin->idealchat_bannedusers[$vbulletin->userinfo['userid']][$roomid] != 0 )
		{
			// User was banned but the ban time has expired
			unset($vbulletin->idealchat_bannedusers[$vbulletin->userinfo['userid']][$roomid]);
			build_datastore('idealchat_bannedusers', serialize($vbulletin->idealchat_bannedusers), 1);
		}
		else
		{
			return true;
		}
	}

	return false;
}

/**
|*  This file was downloaded from http://www.idealwebtech.com at 15:46:27, Thursday December 29th, 2011 
|*
|*  This product has been licensed to Brenda Covey.
|*  License Key: 2b01811ed4f45876dbd9392ea3a0a4ad
**/