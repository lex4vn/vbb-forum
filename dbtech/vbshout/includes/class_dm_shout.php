<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2007-2009 Fillip Hannisdal AKA Revan/NeoRevan/Belazor # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

if (!class_exists('vB_DataManager', false))
{
	exit;
}

// required for convert_to_valid_html() and others
require_once(DIR . '/includes/adminfunctions.php');

/**
* Class to do data save/delete operations for SHOUTS
*
* Example usage (updates shout with shoutid = 12):
*
* $f = new vB_DataManager_vBShout();
* $f->set_condition('shoutid = 12');
* $f->set_info('shoutid', 12);
* $f->set('message', 'Shout with changed message');
* $f->save();
*
* @package	vBShout
* @version	$Revision: 32878 $
* @date		$Date: 2009-10-28 11:38:49 -0700 (Wed, 28 Oct 2009) $
*/
class vB_DataManager_Shout extends vB_DataManager
{
	/**
	* Array of recognised and required fields for forums, and their types
	*
	* @var	array
	*/
	var $validfields = array(
		'shoutid' 		=> array(TYPE_UINT,       REQ_INCR, VF_METHOD, 'verify_nonzero'),
		'userid' 		=> array(TYPE_INT,        REQ_AUTO),
		'dateline' 		=> array(TYPE_UNIXTIME,   REQ_AUTO),
		'message' 		=> array(TYPE_STR,        REQ_YES,	VF_METHOD),
		'message_raw'	=> array(TYPE_STR,        REQ_YES),
		'type' 			=> array(TYPE_UINT,       REQ_AUTO),
		'id' 			=> array(TYPE_INT,        REQ_NO),
		'notification' 	=> array(TYPE_STR,        REQ_NO,	'if (!in_array($data, array(\'\', \'thread\', \'reply\'))) { return false; } return true;'),
		'forumid' 		=> array(TYPE_INT,        REQ_NO, 	VF_METHOD),
		'instanceid' 	=> array(TYPE_INT,        REQ_NO, 	VF_METHOD),
		'chatroomid' 	=> array(TYPE_INT,        REQ_NO, 	VF_METHOD),
		'allowsmilie'    => array(TYPE_UINT,      REQ_NO),
	);
	
	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_vbshout_shout';

	/**
	* Condition template for update query
	*
	* @var	array
	*/
	var $condition_construct = array('shoutid = %1$d', 'shoutid');

	/**
	 * Hook for constructor.
	 *
	 * @var string
	 */
	var $hook_start = 'dbtech_vbshout_data_start';

	/**
	 * Hook for post_delete.
	 *
	 * @var string
	 */
	var $hook_delete = 'dbtech_vbshout_data_delete';

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function vB_DataManager_Shout(&$registry, $errtype = ERRTYPE_ARRAY)
	{
		parent::vB_DataManager($registry, $errtype);
	}

	/**
	* Verifies that a message is valid
	*
	* @param	integer	The message
	*
	* @return	boolean
	*/
	function verify_message(&$message)
	{
		$message = unhtmlspecialchars(str_replace(array("\n"), '', trim(convert_urlencoded_unicode($message))));
		if (empty($message))
		{
			$this->error('dbtech_vbshout_invalid_message_specified');			
			return false;
		}
		
		return true;
	}

	/**
	* Verifies that a forum exists
	*
	* @param	integer	The forum id
	*
	* @return	boolean
	*/
	function verify_forumid(&$forumid)
	{
		if ($forumid === 0)
		{
			// Forumid 0 is valid
			return true;
		}
		
		if (!$this->registry->forumcache["$forumid"])
		{
			$this->error('invalid_forum_specified');
			return false;
		}
		return true;
	}

	/**
	* Verifies that an instance exists and is valid
	*
	* @param	integer	The instance id
	*
	* @return	boolean
	*/
	function verify_instanceid(&$instanceid)
	{
		if ($instanceid === 0)
		{
			// 0 is a valid instance
			return true;
		}
		
		if (!VBSHOUT::$cache['instance']["$instanceid"])
		{
			$this->error('dbtech_vbshout_invalid_instanceid_specified');
			return false;
		}
		return true;
	}

	/**
	* Verifies that an image filename prefix is valid
	*
	* @param	string	The image prefix filename
	*
	* @return	boolean
	*/
	function verify_chatroomid(&$chatroomid)
	{
		if ($chatroomid === 0)
		{
			// 0 is a valid chat room
			return true;
		}
		
		if (!VBSHOUT::$cache['chatroom']["$chatroomid"])
		{
			$this->error('dbtech_vbshout_invalid_chatroomid_specified');
			return false;
		}
		return true;
	}

	/**
	* Checks for various chat commands that may interrupt saving
	*
	* @return	boolean
	*/
	function parse_action_codes()
	{
		global $vbphrase;		
		
		if ($this->fetch_field('type') != VBSHOUT::$shouttypes['shout'])
		{
			// We're not doing anything with a non-shout type
			// Notifications / PMs are already parsed and ready
			return true;
		}
		
		$instanceid = $this->fetch_field('instanceid');
		$this->info['instance'] = (!$this->info['instance'] ? VBSHOUT::$cache['instance']["$instanceid"] : $this->info['instance']);
		
		// The PM command is special and can't be prettified.
		// It's also the only 3-stage command we have, so it doesn't matter
		if (preg_match("#^(\/pm)\s+?(.+?);\s+?(.+?)$#i", $this->fetch_field('message'), $matches))
		{
			if (!$this->info['instance']['permissions_parsed']['canpm'])
			{
				// We has an error
				$this->error('dbtech_vbshout_pming_disabled_usergroup');
				return false;
			}
			
			if ($matches[2] == $this->registry->userinfo['username'])
			{
				// We has an error
				$this->error('dbtech_vbshout_cannot_pm_self');
				return false;
			}
			
			if (!$exists = $this->registry->db->query_first_slave("
				SELECT userid, dbtech_vbshout_settings
				FROM " . TABLE_PREFIX . "user
				WHERE username = " . $this->registry->db->sql_prepare(htmlspecialchars_uni($matches[2]))
			))
			{
				// We has an error
				$this->error('dbtech_vbshout_invalid_user');
				return false;
			}
			
			$return_value = true;
			($hook = vBulletinHook::fetch_hook('dbtech_vbshout_parsecommand_pm')) ? eval($hook) : false;
	
			if (!$return_value)
			{
				// We're ending it early
				return $return_value;
			}
			
			// Override some values
			$this->set('id', 			$exists['userid']);
			$this->set('type', 			VBSHOUT::$shouttypes['pm']);
			$this->set('message', 		$matches[3]);
			$this->set('chatroomid', 	0);
		}
		else if (preg_match("#^(\/[a-z0-9]*?)$#i", $this->fetch_field('message'), $matches))
		{
			// 1-stage command
			switch ($matches[1])
			{
				case '/prune':
					
					if (!$this->fetch_field('chatroomid'))
					{
						if (!$this->info['instance']['permissions_parsed']['canprune'])
						{
							// We has an error
							$this->error('dbtech_vbshout_cannot_prune');
							return false;
						}
					}
					else
					{
						if (VBSHOUT::$cache['chatroom']["{$this->fetch_field(chatroomid)}"]['creator'] != $this->registry->userinfo['userid'] AND !$this->info['instance']['permissions_parsed']['canprune'])
						{
							// Only chat room creators can prune
							$this->error('dbtech_vbshout_cannot_prune');
							return false;
						}
					}
					
					// Now get rid of the shouts
					$this->registry->db->query_write("
						DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
						WHERE `instanceid` = " . intval($this->fetch_field('instanceid')) . "
							AND `chatroomid` = " . intval($this->fetch_field('chatroomid')) . "
					");
					
					// Rebuild shout counts
					VBSHOUT::build_shouts_counter();
					
					// Log the prune command
					VBSHOUT::log_command('prune');
					
					// Blank out the message and change type
					$this->set('type', 		VBSHOUT::$shouttypes['system']);
					$this->set('message', 	$vbphrase['dbtech_vbshout_shoutbox_pruned']);
					if (!$this->info['instance']['permissions_parsed']['showaction'])
					{
						// We're not showing action
						$this->set('userid', 	-1);
					}
					break;
				
				case '/editsticky':
					if (!$this->info['instance']['permissions_parsed']['cansticky'])
					{
						$this->error('dbtech_vbshout_cannot_sticky');
						return false;
					}
					
					// What we need to put in the editor
					VBSHOUT::$fetched['editor'] = '/sticky ' . $this->info['instance']['sticky_raw'];
					
					// We're not continuing
					return false;
					break;
					
				case '/createchat':
					$this->error('dbtech_vbshout_invalid_chatroom_name');
					return false;
					break;
					
				case '/removenotice':
				case '/removesticky':
				case '/sticky':
					if (!$this->info['instance']['permissions_parsed']['cansticky'])
					{
						$this->error('dbtech_vbshout_cannot_sticky');
						return false;
					}
					
					// Remove the sticky note
					VBSHOUT::set_sticky('');
					
					// Log the removesticky command
					VBSHOUT::log_command('removesticky');						
						
						// Blank out the message
					$this->set('type', 		VBSHOUT::$shouttypes['system']);
					$this->set('message', 	$vbphrase['dbtech_vbshout_sticky_note_removed']);
					if (!$this->info['instance']['permissions_parsed']['showaction'])
					{
						// We're not showing action
						$this->set('userid', 	-1);
					}
					break;
				
				default:
					$return_value = false;
					$handled = false;
					
					($hook = vBulletinHook::fetch_hook('dbtech_vbshout_command_1')) ? eval($hook) : false;
					
					if (!$handled)
					{
						// We didn't have any errors, we just returned false
						$this->error('dbtech_vbshout_invalid_command');
						return false;
					}
					return $return_value;
					break;
			}
		}
		else if (preg_match("#^(\/[a-z0-9]*?)\s(.+?)$#i", $this->fetch_field('message'), $matches))
		{
			// 2-stage command
			switch ($matches[1])
			{
				case '/me':
					// ZE ME COMMAND, IT DOEZ NOZING
					break;
					
				case '/invite':
				case '/chatinvite':
					// Invite an user to chat
					$chatroomid = $this->fetch_field('chatroomid');
					
					if (!$title = VBSHOUT::$cache['chatroom']["$chatroomid"]['title'] OR VBSHOUT::$cache['chatroom']["$chatroomid"]['membergroupids'])
					{
						$this->error('dbtech_vbshout_invalid_chat_room');
						return false;
					}
					
					if (!$userid = $this->registry->db->query_first_slave("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username = " . $this->registry->db->sql_prepare(htmlspecialchars_uni($matches[2]))))
					{
						$this->error('dbtech_vbshout_invalid_user');
						return false;
					}
					
					// Invite to join the chat room
					VBSHOUT::invite_chatroom(VBSHOUT::$cache['chatroom']["$chatroomid"], $userid['userid'], $this->registry->userinfo['userid']);

					//$this->fetched['success'] = $vbphrase['dbtech_vbshout_chat_invited_successfully'];
					
					return false;
					break;
					
				case '/ignore':
				case '/unignore':
					// Ignore an user
					if (!$exists = $this->registry->db->query_first_slave("
						SELECT userid, username, usergroupid, membergroupids
						FROM " . TABLE_PREFIX . "user
						WHERE username = " . $this->registry->db->sql_prepare(htmlspecialchars_uni($matches[2]))
					))
					{
						// We has an error
						$this->error('dbtech_vbshout_invalid_user');
						return false;
					}
					
					if ($exists['userid'] == $this->registry->userinfo['userid'])
					{
						// Ourselves, duh
						$this->error('dbtech_vbshout_cannot_ignore_self');
						return false;
					}
					
					if (VBSHOUT::check_protected_usergroup($exists, true))
					{
						// We had an error
						$this->error('dbtech_vbshout_protected_usergroup');
						return false;
					}
					
					if ($matches[1] == '/ignore')
					{
						// Ignore the user
						$this->registry->db->query_write("
							REPLACE INTO " . TABLE_PREFIX . "dbtech_vbshout_ignorelist
								(userid, ignoreuserid)
							VALUES (
								" . intval($this->registry->userinfo['userid']) . ",
								" . intval($exists['userid']) . "
							)
						");
					}
					else
					{
						// Unignore the user
						$this->registry->db->query_write("
							DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist
							WHERE userid = " . intval($this->registry->userinfo['userid']) . "
								AND ignoreuserid = " . intval($exists['userid']) . "
						");
					}
					// Print success message
					//$this->fetched['success'] = construct_phrase($vbphrase['dbtech_vbshout_ignored_successfully'], $matches[2]);
					//$this->fetched['success'] = construct_phrase($vbphrase['dbtech_vbshout_unignored_successfully'], $matches[2]);					
					//$message = false;
					return false;
					break;
					
				case '/notice':
				case '/setnotice':
				case '/sticky':
				case '/setsticky':
					if (!$this->info['instance']['permissions_parsed']['cansticky'])
					{
						$this->error('dbtech_vbshout_cannot_sticky');
						return false;
					}
					
					// Set the sticky note
					VBSHOUT::set_sticky($matches[2]);
					
					// Log the setsticky command
					VBSHOUT::log_command('setsticky', $matches[2]);						
					
					// Blank out the message
					$this->set('type', 		VBSHOUT::$shouttypes['system']);
					$this->set('message', 	$vbphrase['dbtech_vbshout_sticky_note_set']);
					if (!$this->info['instance']['permissions_parsed']['showaction'])
					{
						// We're not showing action
						$this->set('userid', 	-1);
					}
					break;
					
				case '/ban':
				case '/unban':				
					if (!$this->info['instance']['permissions_parsed']['canban'])
					{
						$this->error('dbtech_vbshout_cannot_ban');
						return false;
					}
					
					// Banning an user
					if (!$exists = $this->registry->db->query_first_slave("
						SELECT userid, username, usergroupid, membergroupids
						FROM " . TABLE_PREFIX . "user
						WHERE username = " . $this->registry->db->sql_prepare(htmlspecialchars_uni($matches[2]))
					))
					{
						// We has an error
						$this->error('dbtech_vbshout_invalid_user');
						return false;
					}
					
					if ($exists['userid'] == $this->registry->userinfo['userid'])
					{
						// Ourselves, duh
						$this->error('dbtech_vbshout_cannot_ban_self');
						return false;
					}
					
					if (VBSHOUT::check_protected_usergroup($exists, true))
					{
						// We had an error
						$this->error('dbtech_vbshout_protected_usergroup');
						return false;
					}
					
					// Log the ban command
					VBSHOUT::log_command(($matches[1] == '/ban' ? 'ban' : 'unban'), $exists['userid']);		
					
					// Ban the user
					$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "user SET dbtech_vbshout_banned = " . ($matches[1] == '/ban' ? '1' : '0') . " WHERE userid = " . $this->registry->db->sql_prepare($exists['userid']));
					
					if ($this->info['instance']['permissions_parsed']['showaction'])
					{					
						// Print success message
						$this->set('type', 		VBSHOUT::$shouttypes['system']);
						$this->set('message', 	($matches[1] == '/ban' ? construct_phrase($vbphrase['dbtech_vbshout_banned_successfully'], $matches[2]) : construct_phrase($vbphrase['dbtech_vbshout_unbanned_successfully'], $matches[2])));
					}
					else
					{
						//$this->fetched['success'] = construct_phrase($vbphrase['dbtech_vbshout_banned_successfully'], $matches[2]);
						//$this->fetched['success'] = construct_phrase($vbphrase['dbtech_vbshout_unbanned_successfully'], $matches[2]);
						//$message = false;
						return false;
					}
					break;
					
				case '/createchat':
					if (!$this->info['instance']['permissions_parsed']['cancreatechat'])
					{
						$this->error('dbtech_vbshout_cant_create_chat');
						return false;
					}
					
					if ($this->info['instance']['options']['maxchats'])
					{
						$i = 0;
						foreach ((array)VBSHOUT::$cache['chatroom'] as $chatroomid => $chatroom)
						{
							if (!$chatroom['active'])
							{
								// Don't count closed rooms
								continue;
							}
							
							if ($chatroom['creator'] == $this->registry->userinfo['userid'])
							{
								// We're the creator
								$i++;
							}
						}
						
						if ($i >= $this->info['instance']['options']['maxchats'])
						{
							// Waaaay too many chats, slow down tiger!
							$this->error('dbtech_vbshout_too_many_chats');							
							return false;
						}
					}
					
					// Grab the title
					$title = $matches[2];
					
					// init data manager
					$dm =& VBSHOUT::datamanager_init('Chatroom', $this->registry, ERRTYPE_SILENT);
						$dm->set('title', 		$title);
						$dm->set('instanceid', 	$this->info['instance']['instanceid']);
						$dm->set('creator', 	$this->registry->userinfo['userid']);
						$dm->set('members', 	array($this->registry->userinfo['userid'] => '1'));
					$chatroomid = $dm->save();
					unset($dm);					
					
					// Insert the chat member
					$this->registry->db->query_write("
						INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
							(chatroomid, userid, status)
						VALUES (
							" . $this->registry->db->sql_prepare($chatroomid) . ",
							" . $this->registry->db->sql_prepare($this->registry->userinfo['userid']) . ",
							1
						)
					");
					
					// Set chat room info
					VBSHOUT::$fetched['chatroomid'] = $chatroomid;
					VBSHOUT::$fetched['roomname'] 	= $title;
					
					return false;
					break;

				default:
					$return_value = false;
					$handled = false;
					
					($hook = vBulletinHook::fetch_hook('dbtech_vbshout_command_2')) ? eval($hook) : false;
					
					if (!$handled)
					{
						$this->error('dbtech_vbshout_invalid_command');
						return false;
					}
					return $return_value;
					break;
			}
		}
		
		
		
		return true;
	}

	/**
	* Verifies the number of images in the post text. Call it from pre_save() after pagetext/allowsmilie has been set
	*
	* @return	bool	Whether the post passes the image count check
	*/
	function verify_image_count($pagetext = 'pagetext', $allowsmilie = 'allowsmilie', $parsetype = 'nonforum', $table = null)
	{
		global $vbulletin;

		$_allowsmilie =& $this->fetch_field($allowsmilie, $table);
		$_pagetext =& $this->fetch_field($pagetext, $table);

		if ($_allowsmilie !== null AND $_pagetext !== null)
		{
			// check max images
			require_once(DIR . '/includes/functions_misc.php');
			require_once(DIR . '/includes/class_bbcode_alt.php');
			$bbcode_parser = new vB_BbCodeParser_ImgCheck($this->registry, fetch_tag_list());
			$bbcode_parser->set_parse_userinfo($vbulletin->userinfo);

			if ($this->registry->options['maximages'] AND !$this->info['is_automated'])
			{
				$imagecount = fetch_character_count($bbcode_parser->parse($_pagetext, $parsetype, $_allowsmilie, true), '<img');
				if ($imagecount > $this->registry->options['maximages'])
				{
					$this->error('toomanyimages', $imagecount, $this->registry->options['maximages']);
					return false;
				}
			}
		}

		return true;
	}

	/**
	* Any checks to run immediately before saving. If returning false, the save will not take place.
	*
	* @param	boolean	Do the query?
	*
	* @return	boolean	True on success; false if an error occurred
	*/
	function pre_save($doquery = true)
	{
		if ($this->presave_called !== null)
		{
			return $this->presave_called;
		}
		
		$instanceid = $this->fetch_field('instanceid');
		$this->info['instance'] = (!$this->info['instance'] ? VBSHOUT::$cache['instance']["$instanceid"] : $this->info['instance']);
		
		if (!$this->condition)
		{
			if ($this->fetch_field('dateline') === null)
			{
				$this->set('dateline', TIMENOW);
			}
			
			if ($this->fetch_field('type') === null)
			{
				$this->set('type', VBSHOUT::$shouttypes['shout']);
			}
			
			if ($this->fetch_field('userid') === null)
			{
				$this->set('userid', $this->registry->userinfo['userid']);
			}
		}
		
		if (count($this->info['instance']))
		{
			// Grab userinfo
			$userinfo = ($this->fetch_field('userid') == $this->registry->userinfo['userid'] ? $this->registry->userinfo : fetch_userinfo($this->fetch_field('userid')));			
			
			// Initialise BBCode Permissions
			$permarray = array(
				'permissions_parsed' 		=> VBSHOUT::load_instance_permissions($this->info['instance'], $userinfo),
				'bbcodepermissions_parsed' 	=> VBSHOUT::load_instance_bbcodepermissions($this->info['instance'], $userinfo)
			);
			
			// Shorthand
			$message = $this->fetch_field('message');
			
			if (!$this->info['automated'])
			{
				// Do character count checking
				if ($this->info['instance']['options']['maxchars'] != 0 AND ($postlength = vbstrlen($message)) > $this->info['instance']['options']['maxchars'] AND !VBSHOUT::$permissions['ismanager'])
				{
					$this->error('dbtech_vbshout_charlimit', $postlength, $this->info['instance']['options']['maxchars']);
					return false;
				}
		
				if ($this->info['instance']['options']['maximages'])
				{
					// Set whether we're allowed to use smilies
					$this->set('allowsmilie', $this->info['instance']['options']['allowsmilies']);
					
					// Hack this
					$maximages = $this->registry->options['maximages'];
					$this->registry->options['maximages'] = $this->info['instance']['options']['maximages'];
					
					if (!$this->verify_image_count('message', 'allowsmilie', 'nonforum'))
					{
						return false;
					}
					
					// Restore hack
					$this->registry->options['maximages'] = $maximages;
				}
				
				if ($this->info['instance']['options']['maxsize'])
				{
					// Replace the SIZE BBCode if needed
					$message = preg_replace("#\[size=(\d+)\]#ie", "\VBSHOUT::process_bbcode_size('\\1')", $message);
					$message = preg_replace("#\[size=\"(\d+)\"\]#ie", "\VBSHOUT::process_bbcode_size('\\1')", $message);
					$message = preg_replace("#\[size=\'(\d+)\'\]#ie", "\VBSHOUT::process_bbcode_size('\\1')", $message);
					
					// Set raw message
					$this->set('message', $message);
				}
			}
			
			if ($this->condition)
			{
				// Update
				if ($this->existing['userid'] == $this->registry->userinfo['userid'] AND (!$this->info['instance']['permissions_parsed']['caneditown'] AND $this->fetch_field('instanceid') > 0))
				{
					// We can't edit our own shouts
					$this->error('dbtech_vbshout_may_not_edit_own');
					return false;
				}
				
				if ($this->existing['userid'] != $this->registry->userinfo['userid'] AND (!$this->info['instance']['permissions_parsed']['caneditothers'] AND $this->fetch_field('instanceid') > 0))
				{
					// We can't edit our own shouts
					$this->error('dbtech_vbshout_may_not_edit_others');
					return false;
				}
			}
			else
			{
				if (!$this->info['instance']['permissions_parsed']['canshout'] AND $this->fetch_field('instanceid') != 0 AND !$this->info['automated'])
				{
					// We aren't allowed to post shouts
					$this->error('dbtech_vbshout_may_not_shout');
					return false;
				}
			}			
		}
		
		// Set raw message
		$this->set('message_raw', $message);
				
		// Parse message for /pm command and other on-the-fly messages that's not supposed to return true
		if (!$this->parse_action_codes())
		{
			// We had sum error
			return false;
		}
		
		// Re-grab this (custom command support pretty much)
		$message = $this->fetch_field('message');
		
		// This is no longer needed
		$this->do_unset('allowsmilie');
		
		// Ensure we got BBCode Parser
		require_once(DIR . '/includes/class_bbcode.php');
		if (!function_exists('convert_url_to_bbcode'))
		{
			require_once(DIR . '/includes/functions_newpost.php');
		}
		if (!function_exists('vbshout_fetch_tag_list'))
		{
			require_once(DIR . '/dbtech/vbshout/includes/functions.php');
		}		
		
		if (count($this->info['instance']) AND !$this->info['automated'])
		{
			// Shorthand
			$instance = $this->info['instance'];
			
			// Store these settings
			$backup = array(
				'allowedbbcodes' 	=> $this->registry->options['allowedbbcodes'],
				'allowhtml' 		=> $this->registry->options['allowhtml'],
				'allowbbcode' 		=> $this->registry->options['allowbbcode'],
				'allowsmilies' 		=> $this->registry->options['allowsmilies'],
				'allowbbimagecode' 	=> $this->registry->options['allowbbimagecode']
			);	
			
			// Initialise the parser (use proper BBCode)
			$parser = new vB_BbCodeParser($this->registry, vbshout_fetch_tag_list((array)VBSHOUT::$tag_list, $permarray));
			
			// Override allowed bbcodes
			$this->registry->options['allowedbbcodes'] = $permarray['bbcodepermissions_parsed']['bit'];
			
			// Override the BBCode list
			$this->registry->options['allowhtml'] 			= false;
			$this->registry->options['allowbbcode'] 		= true;
			$this->registry->options['allowsmilies'] 		= $instance['options']['allowsmilies'];
			$this->registry->options['allowbbimagecode'] 	= ($permarray['bbcodepermissions_parsed']['bit'] & 1024);
			
			if ($permarray['bbcodepermissions_parsed']['bit'] & 64)
			{
				// We can use the URL BBCode, so convert links
				$message = convert_url_to_bbcode($message);
			}

			// BBCode parsing
			$message = $parser->parse($message, 'nonforum');	
		}
		else
		{
			// Initialise the parser (use proper BBCode)
			$parser = new vB_BbCodeParser($this->registry, fetch_tag_list());
			
			if ($this->registry->options['allowedbbcodes'] & 64)
			{
				// We can use the URL BBCode, so convert links
				$message = convert_url_to_bbcode($message);
			}
			
			// BBCode parsing
			$message = $parser->parse($message, 'nonforum');		
		}
		
		// Set raw message
		$this->set('message', htmlspecialchars_uni($message));		
		
		$return_value = true;
		//($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_preinsert')) ? eval($hook) : false;
		
		// Ensure that shit doesn't return true if there's errors
		$return_value = (!empty($this->errors) ? false : $return_value);
		
		$this->presave_called = $return_value;
		return $return_value;
	}

	/**
	* Sets the supplied data to be part of the data to be saved. Use setr() if a reference to $value is to be passed
	*
	* @param	string	The name of the field to which the supplied data should be applied
	* @param	mixed	The data itself
	* @param	boolean	Clean data, or insert it RAW (used for non-arbitrary updates, like posts = posts + 1)
	* @param	boolean	Whether to verify the data with the appropriate function. Still cleans data if previous arg is true.
	* @param	string	Table name to force. Leave as null to use the default table
	*
	* @return	boolean	Returns false if the data is rejected for whatever reason
	*/
	function set($fieldname, $value, $clean = true, $doverify = true, $table = null)
	{
		parent::set($fieldname, $value, $clean, $doverify, $table);
		
		return $this;
	}
	
	/**
	 * Overridding parent function to add hook support
	 *
	* @param	boolean	Do the query?
	* @param	mixed	Whether to run the query now; see db_update() for more info
	* @param 	bool 	Whether to return the number of affected rows.
	* @param 	bool	Perform REPLACE INTO instead of INSERT
	8 @param 	bool	Perfrom INSERT IGNORE instead of INSERT
	*
	* @return	mixed	If this was an INSERT query, the INSERT ID is returned
	*/
	function save($doquery = true, $delayed = false, $affected_rows = false, $replace = false, $ignore = false)
	{
		if (!$this->pre_save($doquery))
		{
			// Pre-save failed
			if (!empty($this->errors))
			{
				// Set the errors
				VBSHOUT::$fetched['error'] = implode('<br />', $this->errors);
				
				return false;
			}
			
			return false;
		}
		
		// Call and get the new id
		$result = parent::save($doquery, $delayed, $affected_rows, $replace, $ignore);
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_insert')) ? eval($hook) : false;				

		return $result;
	}
	
	/**
	* Additional data to update after a save call (such as denormalized values in other tables).
	* In batch updates, is executed once after all records are updated.
	*
	* @param	boolean	Do the query?
	*/
	function post_save_once($doquery = true, $return = null)
	{
		$return_value = true;
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_post_save_once')) ? eval($hook) : false;

		return $return_value;
	}	

	/**
	* Additional data to update after a save call (such as denormalized values in other tables).
	* In batch updates, is executed for each record updated.
	*
	* @param	boolean	Do the query?
	*/
	function post_save_each($doquery = true, $return = null)
	{
		global $vbphrase;
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shout_post_save_each')) ? eval($hook) : false;				
		
		if ($this->condition)
		{
			// Log this command
			VBSHOUT::log_command('shoutedit', serialize(array('old' => $this->existing['message'], 'new' => $this->fetch_field('message'))));			
			
			// And a winrar is us
			//$this->fetched['success'] = $vbphrase['dbtech_vbshout_edited_shout_successfully'];			
		}
		else
		{
			if ($this->fetch_field('id') > 0)
			{
				// We sent this to someone
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET dbtech_vbshout_pm = " . TIMENOW . "
					WHERE userid = " . intval($this->fetch_field('id'))
				);
			}
			
			if ($this->fetch_field('userid') > 0)
			{
				// increment shouts count
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET dbtech_vbshout_shouts = dbtech_vbshout_shouts + 1
					WHERE userid = " . intval($this->fetch_field('userid'))
				);
				
				if (VBSHOUT::$isPro)
				{
					$currshouts = $this->registry->db->query_first("
						SELECT dbtech_vbshout_shouts AS shouts
						FROM " . TABLE_PREFIX . "user 
						WHERE userid = " . intval($this->fetch_field('userid'))
					);
					foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
					{
						if (!$instance['options']['shoutping_interval'])
						{
							// Not having notices here
							continue;
						}
				
						if ($currshouts['shouts'] % $instance['options']['shoutping_interval'] != 0)
						{
							// We only want matching intervals
							continue;
						}
						
						$shout = VBSHOUT::datamanager_init('Shout', $this->registry, ERRTYPE_ARRAY);
							$shout->set_info('automated', true);
							$shout->set('message', construct_phrase(
								$vbphrase["dbtech_vbshout_has_reached_x_shouts"],
								$currshouts['shouts']
							))
							->set('instanceid', $instanceid)				
							->set('type', VBSHOUT::$shouttypes['notif'])
							->set('userid', $this->fetch_field('userid'));
						$shout->save();
						unset($shout);
					}	
				}
			}
		}
		
		// Update the AOP
		VBSHOUT::set_aop(($this->fetch_field('chatroomid') ? 'chatroom_' . $this->fetch_field('chatroomid') . '_' : 'shouts'), $this->fetch_field('instanceid'));
		
		if ($this->fetch_field('type') == VBSHOUT::$shouttypes['notif'])
		{
			// Update the AOP
			VBSHOUT::set_aop('shoutnotifs', $this->fetch_field('instanceid'));
		}
		
		if ($this->fetch_field('type') == VBSHOUT::$shouttypes['system'])
		{
			// Update the AOP
			VBSHOUT::set_aop('systemmsgs', $this->fetch_field('instanceid'));
		}
	}
	
	/**
	* Additional data to update before a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function pre_delete($doquery = true)
	{
		$instanceid = $this->fetch_field('instanceid');
		$this->info['instance'] = (!$this->info['instance'] ? VBSHOUT::$cache['instance']["$instanceid"] : $this->info['instance']);
		
		if ($this->existing['userid'] == $this->registry->userinfo['userid'] AND (!$this->info['instance']['permissions_parsed']['caneditown'] AND $this->fetch_field('instanceid') > 0))
		{
			// We can't edit our own shouts
			$this->error('dbtech_vbshout_may_not_edit_own');
			return false;
		}
		
		if ($this->existing['userid'] != $this->registry->userinfo['userid'] AND (!$this->info['instance']['permissions_parsed']['caneditothers'] AND $this->fetch_field('instanceid') > 0))
		{
			// We don't have permission to edit others' shouts
			$this->error('dbtech_vbshout_may_not_edit_others');
			return false;
		}
		
		return true;
	}
	

	/**
	* Deletes the specified data item from the database
	*
	* @return	integer	The number of rows deleted
	*/
	function delete($doquery = true)
	{
		if (!$this->pre_delete($doquery))
		{
			// Pre-save failed
			if (!empty($this->errors))
			{
				// Set the errors
				VBSHOUT::$fetched['error'] = implode('<br />', $this->errors);
				
				return false;
			}
			
			return false;
		}
		
		parent::delete($doquery);
		
		return true;
	}	
	
	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		// Decrement shout counters
		$this->registry->db->query_write("
			UPDATE " . TABLE_PREFIX . "user
			SET dbtech_vbshout_shouts = dbtech_vbshout_shouts - 1
			WHERE userid = " . $this->existing['userid']
		);
		
		// Log this command
		VBSHOUT::log_command('shoutdelete', $this->existing['message']);
		
		// Update the AOP
		VBSHOUT::set_aop('shouts', $this->existing['instanceid'], false);
		
		if ($this->fetch_field('type') == VBSHOUT::$shouttypes['notif'])
		{
			// Update the AOP
			VBSHOUT::set_aop('shoutnotifs', $this->existing['instanceid'], false);
		}
		else if ($this->fetch_field('type') == VBSHOUT::$shouttypes['system'])
		{
			// Update the AOP
			VBSHOUT::set_aop('systemmsgs', $this->existing['instanceid'], false);
		}
		
		$return_value = true;
		//($hook = vBulletinHook::fetch_hook($this->hook_delete)) ? eval($hook) : false;

		return $return_value;
	}	
}


/*======================================================================*\
|| ####################################################################
|| # Downloaded: 18:46, Thu Apr 8th 2010
|| # CVS: $RCSfile$ - $Revision: 32878 $
|| ####################################################################
\*======================================================================*/
?>