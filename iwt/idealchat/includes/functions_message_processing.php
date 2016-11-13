<?php
/**
|*  Ideal Chat Pro v1.3.0
|*  Created: August 18th, 2011
|*  Last Modified: Never
|*  Author: Ideal Web Technologies (www.idealwebtech.com)
|*
|*  Copyright (c) 2011 Ideal Web Technologies
|*  This file is only to be used with the consent of Ideal Web Technologies 
|*  and may not be redistributed in whole or significant part!  By using
|*  this file, you agree to the Ideal Web Technologies' Terms of Service
|*  at www.idealwebtech.com/documents/tos.html
**/

/**
|*	@func	convert_url_to_bbcode_callback
|*	@desc	Used by parse_chat_message to work correctly.
**/
function convert_url_to_bbcode_callback($messagetext, $prepend)
{
	$messagetext = str_replace('\"', '"', $messagetext);
	
	$messagetext = preg_replace(
		'#((https?|ftp|gopher|news|telnet)://|www\.)((\[(?!/)|[^\s[^$`"{}<>])+)(?!\[/url|\[/img)(?=[,.!\')]*(\)\s|\)$|[\s[]|$))#siU',
		"[url]\\1\\3[/url]",
		$messagetext
	);

	if (strpos($text, "@"))
	{
		$messagetext = preg_replace(array(
				'/([ \n\r\t])([_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[^\s]+(\.[a-z0-9-]+)*(\.[a-z]{2,6}))/si',
				'/^([_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[^\s]+(\.[a-z0-9-]+)*(\.[a-z]{2,6}))/si'
			), array(
				"\\1[email]\\2[/email]",
				"[email]\\0[/email]"
			), $messagetext
		);
	}

	return str_replace('\"', '"', $prepend) . $messagetext;
}

/**
|*	@func	parse_chat_message
|*	@desc	Parses a User-to-User chat message.
|*
|*	@param	String	message		The message to parse.
**/
function parse_chat_message($message)
{
	// Make sure we got a message
	if (empty($message)) { return ''; }

	global $vbulletin;

	// Check for admin slash commands and return out if one exists
	if (parse_slash_command_admin($message)) { return ''; } // We return an empty string becuase admin commands don't get saved in the database

	// Check for slash commands
	if (substr($message, 0, 1) == '/')
	{
		//Lets strip html as we don't need it and since we didn't get to the bbcode parser we need this extra level of security
		$message = htmlspecialchars_uni(trim(strval($message)));

		// Because of the unique nature of a slash command, we have to parse it when the data is served.
		return $message;
	}


	// First Convert urls to bbcode
	$skiptaglist = 'url|email|noparse';
	$message = preg_replace(
		'#(^|\[/(' . $skiptaglist . ')\])(.*(\[(' . $skiptaglist . ')|$))#siUe',
		"convert_url_to_bbcode_callback('\\3', '\\1')",
		$message
	);

	// Second handle bbcode
	require_once(DIR . '/includes/class_bbcode.php');
	$bbcode_parser = new vB_BbCodeParser($vbulletin, array(
		'no_option' => array(
			'noparse' => array('html' => '%1$s', 'strip_empty' => true, 'stop_parse' => true, 'disable_similies' => true),
			'highlight' => array('html' => '<span class="highlight">%1$s</span>', 'strip_empty' => true),
			'b' => array('html' => '<b>%1$s</b>', 'strip_empty' => true),
			'i' => array('html' => '<i>%1$s</i>', 'strip_empty' => true),
			'u' => array('html' => '<u>%1$s</u>', 'strip_empty' => true),
			'email' => array('callback' => 'handle_bbcode_email', 'strip_empty' => true),
			'url' => array('callback' => 'handle_bbcode_url', 'strip_empty' => true)
		),
		'option' => array(
			'color' => array('html' => '<font color="%2$s">%1$s</font>', 'option_regex' => '#^\#?\w+$#', 'strip_empty' => true),
			'email' => array('callback' => 'handle_bbcode_email', 'strip_empty' => true),
			'url' => array('callback' => 'handle_bbcode_url', 'strip_empty' => true)
		)
	));

	return $bbcode_parser->parse($message);
}

/**
|*	@func	parse_slash_command_admin
|*	@desc	Parses an admin slash command.
|*
|*	@param	String	message		The command message to parse.
**/
function parse_slash_command_admin($message)
{
	// Bring in the object that we need
	global $vbulletin, $vbphrase;

	// Check if user is an admin (should make usergroup settings)
	if ($vbulletin->userinfo['usergroupid'] != 6) { return false; }

	// Check for slash commands
	if (empty($message)) { return false; }
	if (substr($message, 0, 7) != '/admin:') { return false; }

	// Lets process the admin command and take the needed action
	preg_match("/\S+/", $message, $matches);
	$command = substr($matches[0], 7);

	$extra = substr($message, strlen($command)+8);

	if (!empty($extra))
	{
		// Setup our extra array and prcess the data to strip unneeded whitespace
		$extra = explode(';', $extra);

		foreach ($extra AS $key => $value)
		{
			$extra[$key] = trim($value);
		}
	}

	switch ($command)
	{
		case 'kick':
			// $extra[0] = username (required)
			// $extra[1] = roomname (optional)

			if ( $extra[0] )
			{
				// Lets grab the userid and verify they exist
				$userid = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username='" . $vbulletin->db->escape_string($extra[0]) . "'");
				$userid = $userid['userid'];

				if (!$userid)
				{
					output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
				}

				// Find the room id and verify it exists
				$roomid = 0;

				if (isset($extra[1]))
				{
					$roomid = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "iwt_chatrooms WHERE roomname='" . $vbulletin->db->escape_string($extra[1]) . "'");
					$roomid = $roomid['id'];

					if (!$roomid)
					{
						output_ajax_error($vbphrase['iwt_idealchat_invalidroom2']);
					}
				}

				$vbulletin->idealchat_kickedusers[$userid][$roomid] = true;
				build_datastore('idealchat_kickedusers', serialize($vbulletin->idealchat_kickedusers), 1);
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "iwt_chatroom_chatting WHERE userid = $userid". ($roomid ? " AND roomid = $roomid" : ''));
			}
			else
			{
				output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
			}

			break;
		/*case 'kickall':*/
		case 'ban':
			// $extra[0] = username (required)
			// $extra[1] = ban lift time (optional, if not suplied ban will last till the admin removes it)
			// $extra[2] = roomname (optional, if left blank user will be banned from all channel)

			if ( $extra[0] )
			{
				// Lets grab the userid and verify they exist
				$userid = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username='" . $vbulletin->db->escape_string($extra[0]) . "'");
				$userid = $userid['userid'];

				if (!$userid)
				{
					output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
				}

				// Lets check if we have a time provided otherwise lets set it to an indefinate ban
				if (!$extra[1])
				{
					$extra[1] = 0;
				}
				else
				{
					$extra[1] = TIMENOW + (intval($extra[1]) * 60);
				}

				// Find the room id and verify it exists
				$roomid = 0;

				if (isset($extra[2]))
				{
					$roomid = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "iwt_chatrooms WHERE roomname='" . $vbulletin->db->escape_string($extra[2]) . "'");
					$roomid = $roomid['id'];

					if (!$roomid)
					{
						output_ajax_error($vbphrase['iwt_idealchat_invalidroom2']);
					}
				}

				$vbulletin->idealchat_kickedusers[$userid][$roomid] = true;
				build_datastore('idealchat_kickedusers', serialize($vbulletin->idealchat_kickedusers), 1);

				$vbulletin->idealchat_bannedusers[$userid][$roomid] = $extra[1];
				build_datastore('idealchat_bannedusers', serialize($vbulletin->idealchat_bannedusers), 1);
				
				$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "iwt_chatroom_chatting WHERE userid = $userid". ($roomid ? " AND roomid = $roomid" : ''));
			}
			else
			{
				output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
			}

			break;
		case 'unban':
			// $extra[0] = username (required)
			// $extra[1] = roomname (optional, if left blank removes all channel bans from users)

			if ( $extra[0] )
			{
				// Lets grab the userid and verify they exist
				$userid = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username='" . $vbulletin->db->escape_string($extra[0]) . "'");
				$userid = $userid['userid'];

				if (!$userid)
				{
					output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
				}

				// Find the room id and verify it exists
				$roomid = 0;

				if (isset($extra[1]))
				{
					$roomid = $vbulletin->db->query_first("SELECT id FROM " . TABLE_PREFIX . "iwt_chatrooms WHERE roomname='" . $vbulletin->db->escape_string($extra[1]) . "'");
					$roomid = $roomid['id'];

					if (!$roomid)
					{
						output_ajax_error($vbphrase['iwt_idealchat_invalidroom2']);
					}
				}

				if (is_array($vbulletin->idealchat_bannedusers[$userid]) && array_key_exists($roomid, $vbulletin->idealchat_bannedusers[$userid]) )
				{
					unset($vbulletin->idealchat_bannedusers[$userid][$roomid]);
					build_datastore('idealchat_bannedusers', serialize($vbulletin->idealchat_bannedusers), 1);
				}
			}
			else
			{
				output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
			}

			break;
		case 'mute':
			// $extra[0] = username (required)
			// $extra[1] = timelength (optional, if not suplied mute will last till the admin removes it)

			if ( $extra[0] )
			{
				// Lets grab the userid and verify they exist
				$userid = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username='" . $vbulletin->db->escape_string($extra[0]) . "'");
				$userid = $userid['userid'];

				if (!$userid)
				{
					output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
				}

				// Lets check if we have a time provided otherwise lets set it to an indefinate ban
				if (!$extra[1])
				{
					$extra[1] = 0;
				}
				else
				{
					$extra[1] = TIMENOW + (intval($extra[1]) * 60);
				}

				$vbulletin->idealchat_mutedusers[$userid] = $extra[1];
				build_datastore('idealchat_mutedusers', serialize($vbulletin->idealchat_mutedusers), 1);
			}
			else
			{
				output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
			}

			break;
		case 'unmute':
			// $extra[0] = username (required)

			if ( $extra[0] )
			{
				// Lets grab the userid and verify they exist
				$userid = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username='" . $vbulletin->db->escape_string($extra[0]) . "'");
				$userid = $userid['userid'];

				if (!$userid)
				{
					output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
				}

				if (array_key_exists($userid, $vbulletin->idealchat_mutedusers) )
				{
					unset($vbulletin->idealchat_mutedusers[$userid]);
					build_datastore('idealchat_mutedusers', serialize($vbulletin->idealchat_mutedusers), 1);
				}
			}
			else
			{
				output_ajax_error($vbphrase['iwt_idealchat_invaliduser']);
			}

			break;
	}

	return true;
}

/**
|*	@func	parse_slash_command_chatroom
|*	@desc	Parses a Chatroom chat message for slash commands.
|*
|*	@param	String	message			The message to parse.
|*	@param	String	sendersName		The name of the user that sent the message.
**/
function parse_slash_command_chatroom($message, $sendersName)
{
	if (empty($message) || substr($message, 0, 1) != '/') { return $message; }
	else if (substr($message, 0, 3) == '/me') { return parse_slash_command($message, $sendersName, ''); }

	global $vbulletin;
	
	preg_match("/\S+/", $message, $matches);
	$command = $matches[0];
	$receiversName = 'everyone';
	$extra = substr($message, strlen($command)+1);

	if (!empty($extra))
	{
		$extra = explode(';', $extra);
		$receiversName = $extra[0];
		unset($extra[0]);
		$extra = implode(';', $extra);
	}

	return parse_slash_command($command . ' ' . $extra, $sendersName, $receiversName);
}

/**
|*	@func	parse_slash_command
|*	@desc	Parses a User-to-User chat message for slash commands.
|*
|*	@param	String	message			The message to parse.
|*	@param	String	sendersName		The name of the user that sent the message.
|*	@param	String	receiversName	The name of the user receiving the message.
**/
function parse_slash_command($message, $sendersName, $receiversName)
{
	if (empty($message) || substr($message, 0, 1) != '/') { return $message; }

	global $vbulletin, $vbphrase;
	
	preg_match("/\S+/", $message, $matches);
	$command = substr($matches[0], 1);
	$sendersNameOrig = $sendersName;

	if ($sendersName == $vbulletin->userinfo['username'])
	{
		$sendersName = $vbphrase['iwt_idealchat_you'];
		$showS = '';
	}
	else if ($receiversName == $vbulletin->userinfo['username'])
	{
		$receiversName = strtolower($vbphrase['iwt_idealchat_you']);
		$showS = 's';
	}
	else
	{
		$showS = 's';
	}

	switch ($command)
	{
		case 'me':
			$extra = substr($message, 3);

			if (empty($extra))
			{
				$message = $sendersName . ' want' . $showS . ' attention!';
			}
			else
			{
				$message = $sendersNameOrig . $extra;
			}

			break;
		case 'lol':
			$message = $sendersName . ' laugh' . $showS . ' out loud!';
			break;
		case 'rofl':
			$message = $sendersName . ' roll' . $showS . ' on the floor laughing!';
			break;
		case 'brb':
			$message = $sendersName . ' will be right back.';
			break;
		case 'roll':
			$min = 0;
			$max = 100;
			$range = substr($message, 6);

			if (!empty($range))
			{
				$range = explode('-', $range);

				if (is_numeric($range[0]) && is_numeric($range[1]) && $range[0] <= $range[1])
				{
					$min = $range[0];
					$max = $range[1];
				}
			}

			$message = $sendersName . ' roll' . $showS . ' a number (' . $min . '-' . $max . ') and get' . $showS . ' a ' . rand($min, $max) . '.';
			break;
		case 'poke':
		case 'slap':
			$extra = substr($message, 5);
			$message = $sendersName . ' ' . $command . $showS . ' ' . $receiversName . $extra . '!';
			break;
		case 'hit':
			$extra = substr($message, 4);
			$message = $sendersName . ' hit' . $showS . ' ' . $receiversName . $extra . '!';
			break;
		case 'thanks':
		case 'ty':
			$command = 'thank';
		case 'thank':
		case 'lick':
		case 'comfort':
		case 'hug':
		case 'massage':
			$message = $sendersName . ' ' . $command . $showS . ' ' . $receiversName . '.';
			break;
		case 'grats':
		case 'congrats':
			$command = 'congratulate';
		case 'congratulate':
		case 'kick':
		case 'tease':
		case 'bite':
		case 'taunt':
		case 'mock':
		case 'love':
		case 'moon':
			$message = $sendersName . ' ' . $command . $showS . ' ' . $receiversName . '!';
			break;
		case 'kiss':
		case 'scratch':
			$message = $sendersName . ' ' . $command . ($showS ? 'es' : '') . ' ' . $receiversName . '!';
			break;
		case 'pity':
			$message = $sendersName . ' ' . ($showS ? 'pities' : 'pity') . ' ' . $receiversName . '.';
			break;
		case 'hi':
		case 'hello':
		case 'greetings':
			$command = 'greet';
		case 'greet':
		case 'welcome':
		case 'question':
		case 'tickle':
		case 'pounce':
		case 'sniff':
		case 'smell':
		case 'insult':
		case 'hail':
		case 'praise':
		case 'salute':
		case 'shake':
		case 'pester':
		case 'shoo':
		case 'soothe':
		case 'mourn':
		case 'beckon':
		case 'beg':
			$message = $sendersName . ' ' . $command . $showS . ' ' . $receiversName . '.';
			break;
		case 'agree':
		case 'spoon':
		case 'flirt':
		case 'cuddle':
		case 'plead':
			$message = $sendersName . ' ' . $command . $showS . ' with ' . $receiversName . '.';
			break;
		case 'rdy':
			$command = 'ready';
		case 'ready':
		case 'bored':
		case 'cold':
		case 'confused':
		case 'disappointed':
		case 'glad':
		case 'happy':
		case 'hungry':
		case 'thirsty':
		case 'lost':
		case 'puzzled':
		case 'scared':
		case 'surprised':
		case 'excited':
		case 'tired':
		case 'curious':
		case 'impatient':
		case 'insulted':
		case 'shy':
			$message = $sendersName . ' ' . ($showS ? 'is' : 'are') . ' ' . $command . '.';
			break;
		case 'jk':
			$message = $sendersName . ' ' . ($showS ? 'is' : 'are') . ' just kidding.';
			break;
		case 'helpme':
			$message = $sendersName . ' ' . ($showS ? 'is' : 'are') . ' in need of help.';
			break;
		case 'mad':
		case 'angry':
			$message = $sendersName . ' ' . ($showS ? 'is' : 'are') . ' ' . $command . ' at ' . $receiversName . '!';
			break;
		case 'laughat':
			$command = 'laugh';
		case 'bark':
		case 'growl':
		case 'spit':
		case 'glares':
		case 'gaze':
		case 'roar':
		case 'moo':
		case 'purr':
		case 'snarl':
		case 'smirk':
		case 'stare':
		case 'groan':
		case 'moan':
			$message = $sendersName . ' ' . $command . $showS . ' at ' . $receiversName . '!';
			break;
		case 'wave':
		case 'smile':
			$message = $sendersName . ' ' . $command . $showS . ' at ' . $receiversName . '.';
			break;
		case 'applause':
		case 'bravo':
			$command = 'applaud';
		case 'applaud':
		case 'cheer':
		case 'clap':
		case 'sob':
		case 'weep':
		case 'gasp':
		case 'surrender':
		case 'cringe':
		case 'panic':
		case 'shout':
		case 'grovel':
			$message = $sendersName . ' ' . $command . $showS . '!';
			break;
		case 'prey':
		case 'bow':
		case 'burp':
		case 'grin':
		case 'giggle':
		case 'chuckle':
		case 'snicker':
		case 'sigh':
		case 'yawn':
		case 'laugh':
		case 'cough':
		case 'fart':
		case 'duck':
		case 'shrug':
		case 'shiver':
		case 'frown':
		case 'gloat':
		case 'wink':
		case 'whistle':
		case 'kneel':
		case 'whine':
		case 'wait':
		case 'nod':
		case 'fidget':
		case 'curtsey':
		case 'listen':
		case 'ponder':
		case 'drool':
		case 'joke':
		case 'hide':
			$message = $sendersName . ' ' . $command . $showS . '.';
			break;
		case 'dance':
			$message = $sendersName . ' burst' . $showS . ' into dance!';
			break;
		case 'apologize':
		case 'sorry':
			$message = $sendersName . ' apologize' . $showS . '!';
			break;
		case 'cry':
			$message = $sendersName . ' ' . ($showS ? 'cries' : 'cry') . '!';
			break;
		case 'flex':
		case 'blush':
		case 'belch':
			$message = $sendersName . ' ' . $command . ($showS ? 'es' : '') . '!';
			break;
		case 'cower':
		case 'fear':
			$message = $sendersName . ' cower' . $showS . ' in fear!';
			break;
		case 'yes':
		case 'no':
		case 'goodbye':
		case 'bye':
		case 'cya':
			$message = $sendersName . ' say' . $showS . ' ' . $command . '!';
			break;
		case 'bed':
		case 'sleep':
		case 'work':
			$message = $sendersName . ' go' . ($showS ? 'es' : '') . ' to ' . $command . '.';
			break;
		case 'calm':
		case 'flatter':
			$message = $sendersName . ' attemp' . $showS . ' to ' . $command . ' ' . $receiversName . '!';
			break;
		case 'chicken':
			$message = $sendersName . ' strut' . $showS . ' around like a chicken! Bwak, Bwak!';
			break;
		case 'violin':
		case 'tinyviolin':
		case 'smallviolin':
			$message = $sendersName . ' play' . $showS . ' the world\'s smallest violin for ' . $receiversName . '!';
			break;
		default:
			return $message;
	}

	return "<b><i>* $message *</i></b>";
}

/**
|*  This file was downloaded from http://www.idealwebtech.com at 15:46:27, Thursday December 29th, 2011 
|*
|*  This product has been licensed to Brenda Covey.
|*  License Key: 2b01811ed4f45876dbd9392ea3a0a4ad
**/