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

// #############################################################################
// shoutbox functionality class

/**
* Handles everything to do with shouts.
*
* @package	vBShout
* @version	$ $Rev$ $
* @date		$ $Date$ $
*/
class DBTech_Framework_Shoutbox 
{
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	private $registry 		= NULL;
	
	/**
	* List of shout types
	*
	* @private	array
	*/	
	public $shouttypes 		= array(
		'shout'		=> 1,
		'pm'		=> 2,
		'me'		=> 4,
		'notif'		=> 8,
		'custom'	=> 16,
		'system'	=> 32,
		'mention'	=> 64,
		'tag'		=> 128,
		'thanks'	=> 256,
	);
	
	/**
	* List of shout styles for the current user
	*
	* @private	array
	*/	
	private $shoutstyle 	= array();
	
	/**
	* List of all permissions
	*
	* @public	array
	*/	
	public $permissions		= array();
	
	/**
	* List of all info returned by the fetcher
	*
	* @public	array
	*/	
	public $fetched 		= array();
	
	/**
	* The rendered shoutbox
	*
	* @public	boolean
	*/	
	public $template 		= false;
	
	/**
	* The source of our information.
	*
	* @public	string
	*/	
	public $fetchtype 		= 'p';
	
	/**
	* What instance we are working with
	*
	* @protected	array
	*/	
	public $instance 		= array();
	
	/**
	* Counter
	*
	* @public	integer
	*/	
	protected $i 			= 0;
	
	
	/**
	* Constructor. Captures the argument from the constructing function
	* and passes it along to the initialiser.
	*/
	public function __construct()
	{
		// Tarp the argument array
		$args = func_get_arg(0);
		
		// Get rid of the class name, we don't need their kind around here
		unset($args[0]);
		
		// Now initialise everything
		call_user_func_array(array($this, 'init'), $args);
	}
	
	/**
	* Initialises the Shoutbox and ensures we have
	* all the classes we need
	*
	* @param	vB_Registry	Registry object
	* @param	array		List of all classes we need
	*/
	private function init($registry, $classes)
	{
		// Check if the vBulletin Registry is an object
		if (!is_object($registry))
		{
			// Something went wrong here I think
			trigger_error("DBTech_Framework_Shoutbox::Registry object is not an object", E_USER_ERROR);
		}
		
		foreach ($classes as $classname => $classobj)
		{
			// Check if the Cache is an object
			if (!is_object($classobj))
			{
				// Something went wrong here I think
				trigger_error("DBTech_Framework_Shoutbox::" . ucfirst($classname) . " object is not an object", E_USER_ERROR);
			}
			
			$newclassname = $classname . 'class';
			$this->$newclassname = $classobj;
		}
		
		if ($registry->userinfo['userid'])
		{
			// Only registered users can have shoutbox styles
			if (!$this->shoutstyle = unserialize($registry->userinfo['dbtech_vbshout_shoutstyle']))
			{
				// This shouldn't be false
				$this->shoutstyle = array();
			}
		}
		
		// Set registry
		$this->registry =& $registry;

		// Ensure we got BBCode Parser
		require_once(DIR . '/includes/class_bbcode.php');

		// Fetch all tags
		$this->tag_list = fetch_tag_list();
	}
	
	/**
	* Sets up the permissions based on instance
	*
	* @param	string	Serialized array of permissions
	*/
	public function init_permissions($instancepermissions)
	{
		// Set permissions shorthand
		$this->permissions = array();
		
		// Ensure this is set
		$instancepermissions = @unserialize($instancepermissions);
		$instancepermissions = (is_array($instancepermissions) ? $instancepermissions : array());
		
		// Fetch all our usergroup ids
		$usergroupids = array_merge(array($this->registry->userinfo['usergroupid']), explode(',', $this->registry->userinfo['membergroupids']));
		
		// Ensure we can fetch bitfields
		require_once(DIR . '/includes/adminfunctions_options.php');
		$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
		
		foreach ($usergroupids as $usergroupid)
		{
			if (!$usergroupid)
			{
				// Just skip it
				continue;
			}
			
			foreach ((array)$permissions as $permname => $bit)
			{
				$yesno = (bool)($instancepermissions["$usergroupid"] & $bit);
				
				if (!isset($this->permissions["$permname"]))
				{
					// Default to false
					$this->permissions["$permname"] = false;
				}
				
				if (!$this->permissions["$permname"] AND $yesno)
				{
					// Override to true
					$this->permissions["$permname"] = true;
				}
			}			
		}
		
		// Some hardcoded ones
		$this->permissions['isprotected'] = (bool)($this->registry->userinfo['permissions']['dbtech_vbshoutpermissions'] & $this->registry->bf_ugp_dbtech_vbshoutpermissions['isprotected']);
		$this->permissions['ismanager'] = (bool)($this->registry->userinfo['permissions']['dbtech_vbshoutpermissions'] & $this->registry->bf_ugp_dbtech_vbshoutpermissions['ismanager']);
	}
	
	/**
	* Sets up the BBCode permissions based on instance
	*
	* @param	string	Serialized array of permissions
	*/
	public function init_bbcode_permissions($instancepermissions, $userinfo = NULL)
	{
		// Set permissions shorthand
		$this->bbcodepermissions2 = array();
		$this->bbcodepermissions = 0;
		
		// Ensure this is set
		$instancepermissions = @unserialize($instancepermissions);
		$instancepermissions = (is_array($instancepermissions) ? $instancepermissions : array());
		
		if ($userinfo === NULL)
		{
			// We're using our own user info
			$userinfo = $this->registry->userinfo;
		}
		
		// Fetch all our usergroup ids
		$usergroupids = array_merge(array($userinfo['usergroupid']), explode(',', $userinfo['membergroupids']));
		
		// Ensure we can fetch bitfields
		require_once(DIR . '/includes/adminfunctions_options.php');
		$permissions = fetch_bitfield_definitions('nocache|allowedbbcodesfull');
		
		foreach ($usergroupids as $usergroupid)
		{
			if (!$usergroupid)
			{
				// Just skip it
				continue;
			}
			
			foreach ((array)$permissions as $permname => $bit)
			{
				$yesno = (bool)($instancepermissions["$usergroupid"] & $bit);
				
				if (!isset($this->bbcodepermissions2["$permname"]))
				{
					// Default to false
					$this->bbcodepermissions2["$permname"] = false;
				}
				
				if (!$this->bbcodepermissions2["$permname"] AND $yesno)
				{
					// Override to true
					$this->bbcodepermissions2["$permname"] = true;
					$this->bbcodepermissions += $bit;
				}
			}			
		}
	}
	
	/**
	* Handles an AJAX request from the Shoutbox.
	*
	* @param	string	What we're upto
	*/
	public function ajax_handler($do)
	{
		global $vbphrase;
		
		// Grab instance id
		$instanceid = $this->registry->input->clean_gpc($this->fetchtype, 'instanceid', TYPE_UINT);
		
		if (!$this->instance = $this->cache['instance']["$instanceid"])
		{
			// Wrong instance
			$this->fetched['error'] = 'Invalid Instance: ' . $instanceid;
			
			// Prints the XML for reading by the AJAX script
			$this->print_ajax_xml();
			
			return false;
		}
		$this->init_permissions($this->instance['permissions']);
		
		// Any additional arguments we may be having to the fetching of shouts
		$args = array();
		
		$chatroomid = $this->registry->input->clean_gpc($this->fetchtype, 'chatroomid', TYPE_UINT);
		if ($chatroomid)
		{
			// Check if the chatroom is active
			$this->chatroom = $this->cache['chatroom']["$chatroomid"];
			
			if ($do != 'dbtech_vbshout_joinchat')
			{
				if (!$this->chatroom OR !$this->chatroom['active'])
				{
					// Wrong chatroom
					$this->fetched['error'] = 'disband_' . $chatroomid;
				}
				
				if (!$this->chatroom['membergroupids'])
				{
					// This is not a members-only group
					if (!isset($this->chatroom['members']["{$this->registry->userinfo[userid]}"]))
					{
						// We're not a member
						$this->fetched['error'] = 'disband_' . $chatroomid;
					}
				}
				else
				{
					if (!is_member_of($this->registry->userinfo, explode(',', $this->chatroom['membergroupids'])))
					{
						// Usergroup no longer a member
						$this->fetched['error'] = 'disband_' . $chatroomid;
					}			
				}
				
				// Override tabid for AOP purposes
				$this->tabid = 'chatroom_' . $chatroomid . '_' . $this->chatroom['instanceid'];
			}
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_ajax_handler_start')) ? eval($hook) : false;
		
		if ($this->fetched['error'])
		{
			// We had errors, don't bother
			
			// Prints the XML for reading by the AJAX script
			$this->print_ajax_xml();
			
			return false;
		}
		
		switch ($do)
		{
			case 'dbtech_vbshout_fetch':
				// Find out all the tabs we're at
				$tabs = $this->registry->input->clean_gpc($this->fetchtype, 'tabs', TYPE_ARRAY_BOOL);
				
				$chatrooms_q = $this->registry->db->query_read_slave("
					SELECT chatroomid, user.username
					FROM " . TABLE_PREFIX . "dbtech_vbshout_chatroommember AS vbshout
					LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.invitedby)
					WHERE vbshout.userid = " . intval($this->registry->userinfo['userid']) . "
						AND status = 0
				");
				while ($chatrooms_r = $this->registry->db->fetch_array($chatrooms_q))
				{
					$chatroom = $this->cache['chatroom']["$chatrooms_r[chatroomid]"];
					if (!$chatroom['active'] OR ($chatroom['instanceid'] != $this->instance['instanceid'] AND $chatroom['instanceid'] != 0))
					{
						// Inactive chat room
						continue;
					}
					
					$this->fetched['chatroomids'][] = $chatroom['chatroomid'];
					$this->fetched['roomnames'][] = $chatroom['title'];
					$this->fetched['usernames'][] = ($chatrooms_r['username'] ? $chatrooms_r['username'] : 'N/A');
				}		
				
				foreach ($tabs as $tabid => $enabled)
				{
					if (substr($tabid, 0, 8) == 'chatroom')
					{
						// Get the chatroom id
						$chatroomid = explode('_', $tabid);
						$chatroomid = $chatroomid[1];
						
						// Get the instance id
						$instanceid = $this->cache['chatroom']["$chatroomid"]['instanceid'];
					}
					else
					{
						// Just use the normal instance id
						$instanceid = $this->instance['instanceid'];
					}
					
					// File system
					$mtime = intval(@file_get_contents(DIR . '/dbtech/vbshout/aop/markread-' . $tabid . $instanceid . '.txt'));
					
					if ($mtime)
					{
						// Send back AOP times
						$this->fetched['aoptimes'][] = $mtime;
						$this->fetched['tabids'][] = $tabid;
					}
				}
				
				$pmtime = $this->registry->input->clean_gpc($this->fetchtype, 'pmtime', TYPE_UINT);
				if ($this->registry->userinfo['dbtech_vbshout_pm'] > $pmtime)
				{
					// Set new PM time
					$this->fetched['pmtime'] = $this->registry->userinfo['dbtech_vbshout_pm'];
				}

				// Find out why we're here
				$type = $this->registry->input->clean_gpc($this->fetchtype, 'type', TYPE_STR);
				
				if (!$this->tabid)
				{
					// Set tabid
					$this->tabid = (in_array($type, array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : $type) . $this->instance['instanceid'];
				}

				if (substr($type, 0, 2) == 'pm')
				{
					// Fetch AOP time
					$this->fetch_aop($type, 0);
					
					// Fetch the userid from the PM type
					$userid = explode('_', $type);
					$userid = $userid[1];
					
					// Set shout args to only include shouts made between self and result of substr
					//$args['userids'] 	= array($this->registry->userinfo['userid'], $userid);
					$args['types']		= $this->shouttypes['pm'];
					$args['onlyuser']	= $userid;
					
					// Override type
					$type = 'shouts';
				}	
				
				if (substr($type, 0, 8) == 'chatroom')
				{
					// Fetch the chatroomid from the chatroom type
					$chatroomid = explode('_', $type);
					$chatroomid = $chatroomid[1];
					
					// Set shout args to only include shouts posted to said chat room
					$args['chatroomid']	= $chatroomid;
					
					if (!$this->chatroom = $this->cache['chatroom']["$chatroomid"])
					{
						// Wrong chatroom
						$this->fetched['error'] = 'disband_' . $chatroomid;
					}	
					else
					{
						if (!$this->chatroom['membergroupids'])
						{
							// This is not a members-only group
							if (!isset($this->chatroom['members']["{$this->registry->userinfo[userid]}"]))
							{
								$this->fetched['error'] = 'disband_' . $chatroomid;
							}
						}
						else
						{
							// Override tabid for AOP purposes
							$this->tabid = 'chatroom_' . $chatroomid . '_' . $this->chatroom['instanceid'];
							
							if (!is_member_of($this->registry->userinfo, explode(',', $this->chatroom['membergroupids'])) OR !$this->chatroom['active'])
							{
								// Usergroup no longer a member
								$this->fetched['error'] = 'disband_' . $chatroomid;
							}			
						}
					}
					
					// Fetch AOP time
					$this->fetch_aop($type, $this->chatroom['instanceid']);
					
					$type = 'shouts';
				}							
				
				if ((
					!isset($this->registry->options['dbtech_vbshout_shoutboxtabs']) OR ($this->registry->options['dbtech_vbshout_shoutboxtabs'] & 4)) AND
					$this->permissions['canmodchat']
				)
				{
					$unhandledreports = $this->registry->db->query_first_slave("
						SELECT COUNT(*) AS numunhandled
						FROM " . TABLE_PREFIX . "dbtech_vbshout_report
						WHERE handled = 0
							AND instanceid = " . $this->registry->db->sql_prepare($this->instance['instanceid'])
					);
					$this->fetched['activereports'] = $unhandledreports['numunhandled'];
				}
				
				($hook = vBulletinHook::fetch_hook('dbtech_vbshout_ajax_handler_fetch')) ? eval($hook) : false;
				
				if ($type == 'shoutnotifs')
				{
					// Fetch AOP time
					$this->fetch_aop($type, $this->instance['instanceid']);
					
					$args['types']		= $this->shouttypes['notif'];
					
					// Override type
					$type = 'shouts';
				}
				
				if ($type == 'systemmsgs')
				{
					// Fetch AOP time
					$this->fetch_aop($type, $this->instance['instanceid']);
					
					$args['types']		= $this->shouttypes['system'];
					
					// Override type
					$type = 'shouts';
				}
				
				if ($type == 'shouts' OR $this->fetched['pmtime'])
				{
					// Fetch AOP time
					$this->fetch_aop('shouts', $this->instance['instanceid']);
					
					// Fetch shouts
					$this->fetch_shouts($args);
				}
				
				if ($type == 'shout')
				{
					// What shout we want to be editing
					$shoutid 	= $this->registry->input->clean_gpc($this->fetchtype, 'shoutid', TYPE_INT);
					
					if (!$exists = $this->registry->db->query_first_slave("
						SELECT userid, message
						FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
						WHERE shoutid = " . intval($shoutid)
					))
					{
						// The shout doesn't exist
						$this->fetched['error'] = $vbphrase['dbtech_vbshout_invalid_shout'];
						break;
					}
					
					if ($exists['userid'] == $this->registry->userinfo['userid'] AND !$this->permissions['caneditown'])
					{
						// We can't edit our own shouts
						$this->fetched['error'] = $vbphrase['dbtech_vbshout_may_not_edit_own'];
						break;
					}
					
					if ($exists['userid'] != $this->registry->userinfo['userid'] AND !$this->permissions['caneditothers'])
					{
						// We don't have permission to edit others' shouts
						$this->fetched['error'] = $vbphrase['dbtech_vbshout_may_not_edit_others'];
						break;
					}					
					
					// Set the editor content
					$this->fetched['editor'] = $exists['message'];					
				}
				
				if ($type == 'activeusers')
				{
					foreach (array(
						'dbtech_vbshout_memberaction_dropdown',
						'dbtech_vbshout_memberaction_dropdown_link',
					) AS $templatename)
					{
						// Register the instance variable on all these
						if (intval($this->registry->versionnumber) != 3)
						{
							// Register the instance variable on all these
							vB_Template::preRegister($templatename, array('instance' => $this->instance));
						}
						else
						{
							// vB3 code
							$GLOBALS['instance'] = $this->instance;
						}
					}
					
					// Array of all active users
					$this->fetch_active_users(true, true);
					
					// Finally set the content
					$this->fetched['content'] = (count($this->activeusers) ? implode(', ', $this->activeusers) : $vbphrase['dbtech_vbshout_no_active_users']);
					
					// Query for active users
					$this->fetched['activeusers'] = count($this->activeusers);
					
					if ($this->registry->options['dbtech_vbshout_separate_activeusers'])
					{
						// Array of all active users
						$this->fetched['activeusers2'] = (count($this->activeusers) ? implode('<br />', $this->activeusers) : $vbphrase['dbtech_vbshout_no_active_users']);
					}
				}
				
				// Hook goes here
				
				break;
				
			case 'dbtech_vbshout_fetchsticky':
				// Fetch sticky		
				$this->fetched['editor'] = '/sticky ' . $this->instance['sticky_raw'];
				break;
				
			case 'dbtech_vbshout_save':
				// Initialise saving
				$this->registry->input->clean_array_gpc($this->fetchtype, array(
					'shoutid' 		=> TYPE_INT,
					'message' 		=> TYPE_NOHTML,
					'type' 			=> TYPE_STR,
					'userid' 		=> TYPE_UINT,
					'chatroomid' 	=> TYPE_UINT,
					'tabid' 		=> TYPE_STR,
				));
				
				if (!$this->tabid)
				{
					// Set tabid
					$this->tabid = (in_array($this->registry->GPC['tabid'], array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : $this->registry->GPC['tabid']) . $this->instance['instanceid'];
				}
				
				// Make sure it's set
				$shouttype = ($this->shouttypes["{$this->registry->GPC[type]}"] ? $this->registry->GPC['type'] : 'shout');
				
				// Init the Shout DM
				$shout = $this->datamanager_init('vBShout', $this->registry, ERRTYPE_ARRAY);
				
				if ($this->registry->GPC['shoutid'])
				{
					if (!$this->registry->GPC['shoutinfo'] = $this->registry->db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE shoutid = " . $this->registry->db->sql_prepare($this->registry->GPC['shoutid'])))
					{
						// Shout didn't exist
						break;
					}
					
					// Set the existing data
					$shout->set_existing($this->registry->GPC['shoutinfo']);
					
					// Only thing that's changed
					$this->registry->GPC['shoutinfo']['message'] = $this->registry->GPC['message'];
				}
				else
				{
					// Construct the shout info on the fly
					$this->registry->GPC['shoutinfo'] = array(
						'id' 			=> $this->registry->GPC['userid'],
						'message' 		=> $this->registry->GPC['message'],
						'type'			=> $this->shouttypes["$shouttype"],
						'instanceid' 	=> $this->instance['instanceid'],
						'chatroomid'	=> $this->registry->GPC['chatroomid']
					);
				}
				
				// Shorthand
				$chatroomid = $this->registry->GPC['shoutinfo']['chatroomid'];
				if ($chatroom = $this->cache['chatroom']["$chatroomid"])
				{
					// Ensure the proper instance id is set
					$this->registry->GPC['shoutinfo']['instanceid'] = $chatroom['instanceid'];
				}

				foreach ($this->registry->GPC['shoutinfo'] as $varname => $value)
				{
					// Set everything
					$shout->set($varname, $value);
				}
				
				// Now finally save
				$shout->save();
				
				if ($this->fetched['error'])
				{
					// We haz error
					break;
				}
				
				$markread = true;
				if (substr($this->tabid, 0, 2) == 'pm')
				{
					$this->tabid = 'shouts' . $this->instance['instanceid'];
					$markread = false;
				}
				
				// Update the AOP
				$this->set_aop('shouts', $this->instance['instanceid'], $markread);
				
				if ($shouttype == $this->shouttypes['notif'])
				{
					// Update the AOP
					$this->set_aop('shoutnotifs', $this->instance['instanceid']);
				}
				
				if ($shouttype == $this->shouttypes['system'])
				{
					// Update the AOP
					$this->set_aop('systemmsgs', $this->instance['instanceid']);
				}
				
				// Shout fetching args
				$args = array();					
				if ($this->registry->GPC['userid'])
				{
					// Fetch only PMs
					$args['types'] 		= $this->shouttypes['pm'];
					$args['onlyuser'] 	= $this->registry->GPC['userid'];
				}
				
				// Fetch only from this chatroom
				$args['chatroomid'] = $this->registry->GPC['chatroomid'];
				
				// We want to fetch shouts
				$this->fetch_shouts($args);
				break;
				
			case 'dbtech_vbshout_delete':
				// Initialise deleting
				$this->registry->input->clean_array_gpc($this->fetchtype, array(
					'shoutid' 		=> TYPE_INT,
					'type' 			=> TYPE_STR,
					'userid' 		=> TYPE_UINT,					
					'tabid' 		=> TYPE_STR,					
				));
				
				if (!$this->tabid)
				{
					// Set tabid
					$this->tabid = (in_array($this->registry->GPC['tabid'], array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : $this->registry->GPC['tabid']) . $this->instance['instanceid'];
				}
				
				// Make sure it's set
				$shouttype = ($this->shouttypes["{$this->registry->GPC[type]}"] ? $this->registry->GPC['type'] : 'shout');
				
				// Init the Shout DM
				$shout = $this->datamanager_init('vBShout', $this->registry, ERRTYPE_ARRAY);
				
				if (!$this->registry->GPC['shoutid'])
				{
					// Invalid Shout ID
					break;
				}
				
				if (!$this->registry->GPC['shoutinfo'] = $this->registry->db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "dbtech_vbshout_shout WHERE shoutid = " . $this->registry->db->sql_prepare($this->registry->GPC['shoutid'])))
				{
					// Shout didn't exist
					break;
				}
				
				// Set the existing data
				$shout->set_existing($this->registry->GPC['shoutinfo']);
				
				// Delete
				$shout->delete();
				
				// Shout fetching args
				$args = array();					
				if ($this->registry->GPC['userid'])
				{
					// Fetch only PMs
					$args['types'] 		= $this->shouttypes['pm'];
					$args['onlyuser'] 	= $shout->fetch_field('id');
				}
				
				// Fetch only from this chatroom
				$args['chatroomid'] = $this->registry->GPC['chatroomid'];
				
				// We want to fetch shouts
				$this->fetch_shouts($args);
				break;
				
			case 'dbtech_vbshout_lookup':
				// Initialise deleting
				$username	= $this->registry->input->clean_gpc($this->fetchtype, 'username',	TYPE_STR);
				
				if (!$this->registry->options['dbtech_vbshout_enablepms'])
				{
					$this->fetched['error'] = $vbphrase['dbtech_vbshout_pms_disabled'];
					break;
				}
				
				if ($username == $this->registry->userinfo['username'])
				{
					$this->fetched['error'] = $vbphrase['dbtech_vbshout_invalid_username'];
					break;
				}
				
				if (!$userid = $this->registry->db->query_first("
					SELECT userid
					FROM " . TABLE_PREFIX . "user
					WHERE username = " . $this->registry->db->sql_prepare($username)
				))
				{
					$this->fetched['error'] = $vbphrase['dbtech_vbshout_invalid_username'];
					break;
				}
				
				// Return the userid
				$this->fetched['pmuserid'] = $userid['userid'];
				break;
				
			case 'dbtech_vbshout_styleprops':
				$this->registry->input->clean_array_gpc($this->fetchtype, array(
					'editor' 		=> TYPE_ARRAY,
					'tabid' 		=> TYPE_STR,					
				));
				
				if (!$this->tabid)
				{
					// Set tabid
					$this->tabid = (in_array($this->registry->GPC['tabid'], array('aop', 'activeusers', 'shoutnotifs', 'systemmsgs')) ? 'shouts' : $this->registry->GPC['tabid']) . $this->instance['instanceid'];				
				}
				
				// Set shout styles array
				$this->shoutstyle["{$this->instance[instanceid]}"] = preg_replace('/[^A-Za-z0-9 #(),]/', '', $this->registry->GPC['editor']);
				
				// Update the user's editor styles
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET dbtech_vbshout_shoutstyle = " . $this->registry->db->sql_prepare(serialize($this->shoutstyle)) . "
					WHERE userid = " . $this->registry->userinfo['userid']
				);
				
				// Set the AOP
				$this->set_aop('shouts', $this->instance['instanceid'], false);				
				
				// Fetch the shouts again¨
				$this->fetch_shouts($args);
				
				// Return success
				//$this->fetched['success'] = $vbphrase['dbtech_vbshout_editor_styles_updated'];
				break;
				
			case 'dbtech_vbshout_sounds':
				$this->registry->input->clean_array_gpc($this->fetchtype, array(
					'tabs' => TYPE_ARRAY_BOOL,
				));
				
				$soundsettings = @unserialize($this->registry->userinfo['dbtech_vbshout_soundsettings']);
				if (!is_array($soundsettings))
				{
					$soundsettings = array();
				}
				$soundsettings["{$this->instance[instanceid]}"] = $this->registry->GPC['tabs'];
				
				// Update the user's editor styles
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET dbtech_vbshout_soundsettings = " . $this->registry->db->sql_prepare(serialize($soundsettings)) . "
					WHERE userid = " . $this->registry->userinfo['userid']
				);
				
				// Return success
				//$this->fetched['success'] = $vbphrase['dbtech_vbshout_editor_styles_updated'];
				break;
				
			case 'dbtech_vbshout_leavechat':
				$status = $this->registry->input->clean_gpc($this->fetchtype, 'status', TYPE_UINT);
				
				// Chat leave
				$this->leave_chatroom($this->chatroom, $this->registry->userinfo['userid']);
				
				//$this->fetched['success'] = $vbphrase['dbtech_vbshout_left_chat_successfully'];
				break;
			
			case 'dbtech_vbshout_joinchat':
			
				// Chat join
				$this->join_chatroom($this->chatroom, $this->registry->userinfo['userid']);
			
				//$this->fetched['success'] = $vbphrase['dbtech_vbshout_joined_chat_successfully'];
				break;
			
			case 'dbtech_vbshout_createchat':
				$type 	= $this->registry->input->clean_gpc($this->fetchtype, 	'type', 	TYPE_NOHTML);
			
				// Init the Shout DM
				$shout = $this->datamanager_init('vBShout', $this->registry, ERRTYPE_ARRAY);
				$shout->set('instanceid', $this->instance['instanceid']);
				$shout->set('chatroomid', $this->chatroom['chatroomid']);
			
				$title = $this->registry->input->clean_gpc($this->fetchtype, 'title', TYPE_NOHTML);
				$shout->set('message', '/createchat ' . $title);
				
				// Now save it
				$shout->save();
				
				break;	
				
			case 'dbtech_vbshout_usermanage':
				$action = $this->registry->input->clean_gpc($this->fetchtype, 	'action', 	TYPE_STR);
				$userid = $this->registry->input->clean_gpc($this->fetchtype, 	'userid', 	TYPE_UINT);
				$type 	= $this->registry->input->clean_gpc($this->fetchtype, 	'type', 	TYPE_STR);
				
				// Grab the username
				$exists = $this->registry->db->query_first("SELECT username, dbtech_vbshout_banned, dbtech_vbshout_silenced FROM " . TABLE_PREFIX . "user WHERE userid = " . $this->registry->db->sql_prepare($userid));
				
				if (!$exists)
				{
					break;
				}
				
				// Init the Shout DM
				$shout = $this->datamanager_init('vBShout', $this->registry, ERRTYPE_ARRAY);
				$shout->set('instanceid', $this->instance['instanceid']);
				$shout->set('chatroomid', $this->chatroom['chatroomid']);
				
				$skip = false;
				switch ($action)
				{
					case 'ignoreunignore':
						$isignored = $this->registry->db->query_first_slave("
							SELECT userid
							FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist
							WHERE userid = " . intval($this->registry->userinfo['userid']) . "
								AND ignoreuserid = " . $this->registry->db->sql_prepare($userid)
						);
						$shout->set('message', ($isignored ? '/unignore ' : '/ignore ') . $exists['username']);
						break;
						
					case 'chatremove':
						// Remove an user from chat
						
						// Leave the chat room
						$this->leave_chatroom($this->chatroom, $userid);
						
						$shout->set('message', construct_phrase($vbphrase['dbtech_vbshout_x_removed_successfully'], $exists['username']));
						$shout->set('userid', -1);
						$shout->set('type', $this->shouttypes['system']);
						break;
						
					default:
						$skip = true;
						break;
				}
				
				if (!$skip)
				{
					// Now save it
					$shout->save();
					
					if ($this->fetched['error'])
					{
						// We haz error
						break;
					}
					
					// Shout fetching args
					$args = array();					
					if ($type == 'pm')
					{
						// Fetch only PMs
						$args['types'] 		= $this->shouttypes['pm'];
						$args['onlyuser'] 	= $userid;
					}
					
					// Fetch only from this chatroom
					$args['chatroomid'] = $this->chatroom['chatroomid'];
					
					// We want to fetch shouts
					$this->fetch_shouts($args);
				}
				break;							
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_ajax_handler')) ? eval($hook) : false;
		
		// Prints the XML for reading by the AJAX script
		$this->print_ajax_xml();
	}
	
	/**
	* Processes an AJAX fetching request using AOP.
	*
	* @param	string	When we last fetched shouts
	*/
	public function fetch_aop($tabid, $instanceid)
	{
		if (!is_writable(DIR . '/dbtech/vbshout/aop/'))
		{
			// Fall back to database
			$this->fetched['error'] = $vbphrase['dbtech_vbshout_aop_error'];
			
			// Time now
			$mtime = TIMENOW;
		}
		
		// File system
		$mtime = intval(@file_get_contents(DIR . '/dbtech/vbshout/aop/' . $tabid . $instanceid . '.txt'));
		
		if (!$mtime)
		{
			$mtime = 0;
		}
		
		if ((TIMENOW - $mtime) >= 60)
		{
			// Reset AOP
			$this->set_aop($tabid, $instanceid, false);
			return false;
		}
		
		if ($mtime > $aoptime)
		{
			// Include the new AOP time
			$this->fetched['aoptimes'][] = $mtime;
			$this->fetched['tabids'][] = $tabid;					
		}
		/*
		else
		{
			// Query for active users
			$activeusers = $this->registry->db->query_first_slave($this->print_activeusers_query());
			$this->fetched['activeusers'] = $activeusers['numactiveusers'];			
		}
		*/
	}
	
	/**
	* Sets the new AOP time.
	*/
	public function set_aop($tabid, $instanceid = 0, $markread = true)
	{
		// Ensure this is taken into account
		clearstatcache();
		
		if (!is_writable(DIR . '/dbtech/vbshout/aop'))
		{
			// Fall back to database
			$this->fetched['error'] = $vbphrase['dbtech_vbshout_aop_error'];
			return false;			
			//$this->registry->options['dbtech_vbshout_optimisation'] = 2;
		}
		
		// Touch the files
		@file_put_contents(DIR . '/dbtech/vbshout/aop/' . $tabid . $instanceid . '.txt', TIMENOW);
		
		if ($markread)
		{
			// Duplicate this
			@file_put_contents(DIR . '/dbtech/vbshout/aop/markread-' . $tabid . $instanceid . '.txt', TIMENOW);
		}
		
		// Include the new AOP time
		$this->fetched['aoptimes'][] = TIMENOW;
		$this->fetched['tabids'][] = $tabid;					
	}
	
	/**
	* Kill an AOP file.
	*/
	public function kill_aop($tabid, $instanceid)
	{
		// Ensure this is taken into account
		clearstatcache();
		
		// Touch the file
		@unlink(DIR . '/dbtech/vbshout/aop/' . $tabid . $instanceid . '.txt');
		
		// Include the new AOP time
		$this->fetched['aoptimes'][] = TIMENOW;
		$this->fetched['tabids'][] = $tabid;					
	}
	
	/**
	* Fetches shouts based on parameters.
	*
	* @param	array		(Optional) Additional arguments
	*/
	public function fetch_shouts($args = array())
	{
		global $vbphrase;
		
		foreach (array(
			'dbtech_vbshout_activeusers',
			'dbtech_vbshout_editortools_pro2',
			'dbtech_vbshout_memberaction_dropdown',
			'dbtech_vbshout_memberaction_dropdown_link',
			'dbtech_vbshout_shoutbox',
			'dbtech_vbshout_shoutbox_editortools',
			'dbtech_vbshout_shoutbox_frames',
			'dbtech_vbshout_shoutbox_me',
			'dbtech_vbshout_shoutbox_pm',
			'dbtech_vbshout_shoutbox_shout',
			'dbtech_vbshout_shoutbox_shoutarea_horizontal',
			'dbtech_vbshout_shoutbox_shoutarea_vertical',
			'dbtech_vbshout_shoutbox_shoutcontrols',
			'dbtech_vbshout_shoutbox_system'
		) AS $templatename)
		{
			// Register the instance variable on all these
			if (intval($this->registry->versionnumber) != 3)
			{
				// Register the instance variable on all these
				vB_Template::preRegister($templatename, array('instance' => $this->instance));
			}
			else
			{
				// vB3 code
				$GLOBALS['instance'] = $this->instance;
			}
		}
		
		// Cache array for fetch_musername()
		$shoutusers = array();
		
		// Various SQL hooks
		$hook_query_select = $hook_query_join = $hook_query_and = '';
		
		if ($args['type'] == -1 OR !$args['types'])
		{
			// Everything
			$hook_query_and .= 'AND (
				vbshout.userid IN(-1, ' . $this->registry->userinfo['userid'] . ') OR
				vbshout.id IN(0, ' . $this->registry->userinfo['userid'] . ')
			)';				// That either system or us posted, or was a message to us/anybody
			
			if (is_array($args['excludetypes']))
			{
				// Exclude types
				$hook_query_and .= 'AND vbshout.type NOT IN(' . implode(',', $args['excludetypes']) . ')';
			}
		}
		else
		{
			$types = array();
			foreach ($this->shouttypes as $key => $val)
			{
				// Go through all shout types
				if ($args['types'] & $this->shouttypes["$key"])
				{
					switch ($key)
					{
						case 'shout':
							if ($args['onlyuser'])
							{
								// Every PM posted by us to the user
								// or to us
								$hook_query_and .= "AND vbshout.userid = '" . intval($args['onlyuser']) . "'";
							}
							break;
						
						case 'pm':
							if ($args['onlyuser'])
							{
								// Every PM posted by us to the user
								// or to us
								$hook_query_and .= 'AND (
									vbshout.userid = ' . $this->registry->userinfo['userid'] . ' AND
										vbshout.id = ' . intval($args['onlyuser']) . '
								) OR (
									vbshout.id = ' . $this->registry->userinfo['userid'] . ' AND
										vbshout.userid = ' . intval($args['onlyuser']) . '
								)';
							}
							break;
					}
					
					// Set the type
					$types[] = $this->shouttypes["$key"];
				}
			}
			
			// Include all our types
			$hook_query_and .= 'AND vbshout.type IN(' . implode(',', $types) . ')';
		}
		
		// Fetch the shout order
		$shoutorder = $this->registry->input->clean_gpc($this->fetchtype, 'shoutorder', TYPE_STR);
		$shoutorder = (in_array($shoutorder, array('ASC', 'DESC')) ? $shoutorder : $this->registry->options['dbtech_vbshout_shoutorder']);
		
		$hook_query_and .= " AND vbshout.chatroomid = " . $this->registry->db->sql_prepare(intval($args['chatroomid']));
		
		if ($this->registry->options['dbtech_vbshout_separate_activeusers'])
		{
			$this->fetch_active_users(true, true);
			if ($args['chatroomid'])
			{
				// Array of all active users
				$this->fetched['activeusers2'] = (count($this->activeusers) ? implode('<br />', $this->activeusers) : $vbphrase['dbtech_vbshout_no_chat_users']);
				$this->fetched['activeusers2'] .= '<br /><br /><a href="vbshout.php?' . $this->registry->session->vars['sessionurl'] . 'do=chataccess&amp;instanceid=' . $this->instance['instanceid'] . '&amp;chatroomid=' . $args['chatroomid'] . '" target="_blank"><b>' . $vbphrase['dbtech_vbshout_chat_access'] . '</b></a>';
		
			}
			else
			{
				// Array of all active users
				$this->fetched['activeusers2'] = (count($this->activeusers) ? implode('<br />', $this->activeusers) : $vbphrase['dbtech_vbshout_no_active_users']);
			}
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_fetch_shouts_query')) ? eval($hook) : false;
		
		// Query the shouts
		$shouts_q = $this->registry->db->query_read_slave("
			SELECT
				user.username,
				user.usergroupid,
				user.membergroupids,
				user.infractiongroupid,
				user.displaygroupid,
				user.dbtech_vbshout_settings AS shoutsettings,
				user.dbtech_vbshout_shoutstyle AS shoutstyle,
				vbshout.*
				$hook_query_select
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
			$hook_query_join
			WHERE vbshout.instanceid IN(-1, 0, " . intval($this->instance['instanceid']) . ")
				AND vbshout.userid NOT IN(
					SELECT ignoreuserid
					FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist AS ignorelist
					WHERE userid = " . $this->registry->userinfo['userid'] . "
				)
				AND vbshout.forumid IN(" . implode(',', $this->fetch_forumids()) . ")
				$hook_query_and
			ORDER BY dateline DESC, shoutid DESC
			LIMIT " . $this->registry->options['dbtech_vbshout_maxshouts']			
		);
		
		if (!$this->registry->db->num_rows($shouts_q))
		{
			// We have no shouts
			$this->fetched['content'] = $vbphrase['dbtech_vbshout_nothing_to_display'];
			return false;
		}
		
		// Set sticky		
		$this->fetched['sticky'] = $this->instance['sticky'];
		
		// Set active users
		$activeusers = $this->registry->db->query_first_slave($this->print_activeusers_query());
		$this->fetched['activeusers'] = $activeusers['numactiveusers'];
			
		while ($shouts_r = $this->registry->db->fetch_array($shouts_q))
		{
			// Parses action codes like /me
			$this->parse_action_codes($shouts_r['message'], $shouts_r['type']);

			// By default, we can't pm or edit
			$canpm = $canedit = false;

			if ($shouts_r['userid'] > -1)
			{
				if (!$shoutusers["$shouts_r[userid]"])
				{
					// Uncached user
					$shoutusers["$shouts_r[userid]"] = array(
						'userid' 			=> $shouts_r['userid'],
						'username' 			=> $shouts_r['username'],
						'usergroupid' 		=> $shouts_r['usergroupid'],
						'infractiongroupid' => $shouts_r['infractiongroupid'],
						'displaygroupid' 	=> $shouts_r['displaygroupid'],
					);
				}
				
				// fetch the markup-enabled username
				fetch_musername($shoutusers["$shouts_r[userid]"]);
				
				if ($shouts_r['userid'] != $this->registry->userinfo['userid'])
				{
					// We can PM this user
					$canpm = true;
				}
			}
			else
			{
				// This was the SYSTEM
				$shoutusers["$shouts_r[userid]"] = array(
					'userid' 	=> 0,
					'username' 	=> $vbphrase['dbtech_vbshout_system'],
					'musername' => $vbphrase['dbtech_vbshout_system'],
				);
				
				// We can't PM the system
				$canpm = false;
			}
			
			// Only registered users can have shoutbox styles
			if (!$shouts_r['shoutstyle'] = unserialize($shouts_r['shoutstyle']))
			{
				// This shouldn't be false
				$shouts_r['shoutstyle'] = array();
			}
			
			// Ensure it's an array for the sake of bugfix
			$shouts_r['shoutstyle'] = (!$shouts_r['shoutstyle']["{$this->instance[instanceid]}"] ? array() : $shouts_r['shoutstyle']["{$this->instance[instanceid]}"]);
			
			// Init the styleprops
			$styleprops = array();
			
			if ($this->registry->userinfo['dbtech_vbshout_settings'] & 8192)
			{
				// Override!
				$shouts_r['shoutstyle'] = $this->shoutstyle["{$this->instance[instanceid]}"];
			}
			
			if ($this->registry->options['dbtech_vbshout_editors'] & 1 AND $shouts_r['shoutstyle']['bold'] > 0)
			{
				// Bold
				$styleprops[] = 'font-weight:bold;';
			}
			
			if ($this->registry->options['dbtech_vbshout_editors'] & 2 AND $shouts_r['shoutstyle']['italic'] > 0)
			{
				// Italic
				$styleprops[] = 'font-style:italic;';
			}
			
			if ($this->registry->options['dbtech_vbshout_editors'] & 4 AND $shouts_r['shoutstyle']['underline'] > 0)
			{
				// Underline
				$styleprops[] = 'text-decoration:underline;';
			}
			
			if ($this->registry->options['dbtech_vbshout_editors'] & 16 AND $shouts_r['shoutstyle']['font'])
			{
				// Font
				$styleprops[] = 'font-family:' . $shouts_r['shoutstyle']['font'] . ';';
			}
			
			if ($this->registry->options['dbtech_vbshout_editors'] & 8 AND $shouts_r['shoutstyle']['color'])
			{
				// Color
				$styleprops[] = 'color:' . $shouts_r['shoutstyle']['color'] . ';';
			}
			
			if (($shouts_r['userid'] == $this->registry->userinfo['userid'] AND $this->permissions['caneditown']) OR
				($shouts_r['userid'] != $this->registry->userinfo['userid'] AND $this->permissions['caneditothers']))
			{
				// We got the perms, give it to us
				$canedit = true;
			}
			
			switch ($shouts_r['type'])
			{
				case $this->shouttypes['me']:
				case $this->shouttypes['notif']:
					// slash me or notification
					$time = vbdate($this->registry->options['timeformat'], 	$shouts_r['dateline'], $this->registry->options['yestoday']);
					break;
					
				default:
					// Everything else
					$time = vbdate($this->registry->options['dateformat'], 	$shouts_r['dateline'], $this->registry->options['yestoday']) . ' ' .
							vbdate($this->registry->options['timeformat'], 	$shouts_r['dateline'], $this->registry->options['yestoday']);
					break;
			}
			
			// Get our usergroup permissions
			cache_permissions($shouts_r, false);
			
			// By default, we can't add infractions
			$this->permissions['giveinfraction'] = (
				// Must have 'cangiveinfraction' permission. Branch dies right here majority of the time
				$this->registry->userinfo['permissions']['genericpermissions'] & $this->registry->bf_ugp_genericpermissions['cangiveinfraction']
				// Can not give yourself an infraction
				AND $shouts_r['userid'] != $this->registry->userinfo['userid']
				// Can not give an infraction to a post that already has one
				// Can not give an admin an infraction
				AND !($shouts_r['permissions']['adminpermissions'] & $this->registry->bf_ugp_adminpermissions['cancontrolpanel'])
				// Only Admins can give a supermod an infraction
				AND (
					!($shouts_r['permissions']['adminpermissions'] & $this->registry->bf_ugp_adminpermissions['ismoderator'])
					OR $this->registry->userinfo['permissions']['adminpermissions'] & $this->registry->bf_ugp_adminpermissions['cancontrolpanel']
				)
			);
			
			($hook = vBulletinHook::fetch_hook('dbtech_vbshout_fetch_shouts_loop')) ? eval($hook) : false;
			
			// Store all the information regarding a shout 
			$shout = array(
				'shoutid' 		=> $shouts_r['shoutid'],
				'userid' 		=> $shouts_r['userid'],
				'time'			=> $time,
				'jsusername'	=> addslashes($shouts_r['username']),
				'username'		=> $shouts_r['username'],
				'musername'		=> $shoutusers["$shouts_r[userid]"]['musername'],
				'usertitle'		=> $shoutusers["$shouts_r[userid]"]['displayusertitle'],
				'message'		=> $shouts_r['message'],
				'message_raw'	=> htmlspecialchars_uni($shouts_r['message_raw']),
				'shoutuserinfo'	=> array(
					'userid'	=> $shouts_r['userid'],
					'username'	=> $shouts_r['username']
				),
				'canedit'		=> $canedit,
				'canpm'			=> $canpm,
				'isprotected' 	=> $this->check_protected_usergroup($shouts_r, true),
			);
			
			// Generate the dropdown
			$shout['memberaction_dropdown'] = $this->create_memberaction_dropdown(
				$shout['userid'],
				$shout['username'],
				$shout['jsusername'],
				$shout['musername'],
				$shout['usertitle'],
				$shout['canpm'],
				$shout['isprotected'],
				$shout['shoutid']
			);
			
			switch ($shouts_r['type'])
			{
				case $this->shouttypes['shout']:
					// Normal shout
					$template = 'shout';
					break;
					
				case $this->shouttypes['pm']:
					// PM
					$template = 'pm';
					break;
					
				case $this->shouttypes['me']:
				case $this->shouttypes['notif']:
					// slash me or a notification
					$template = 'me';
					break;
					
				default:
					// Error handler
					$template = 'shout';
					break;
			}
			
			if ($shouts_r['userid'] == -1)
			{
				// System message
				$template = 'system';
			}

			// Create the template rendering engine
			$templater = vB_Template::create('dbtech_vbshout_shoutbox_' . $template);
				$templater->quickRegister($shout);
				$templater->register('styleprops', implode(' ', $styleprops));
			$this->fetched['shouts'][] = array('template' => $templater->render());
		}
		
		if ($shoutorder == 'ASC')
		{
			// Reverse sort order
			krsort($this->fetched['shouts']);
		}
		
		if (!$this->fetched['shouts'])
		{
			// Show no content
			$this->fetched['content'] = $vbphrase['dbtech_vbshout_nothing_to_display'];
		}
		
		// No longer needed
		unset($shoutusers, $shout);
	}
	
	/**
	* Checks for action codes, and executes their meaning.
	* 
	* @param	string	The shout.
	* @param	string	The default shout type.
	* @param	integer	(Optional) The default id.
	* @param	integer	(Optional) The default userid.
	*
	* @return	mixed	Any new information we may have.
	*/
	public function parse_action_codes(&$message, &$type)
	{
		global $vbphrase;
		
		if (preg_match("#^(\/[a-z]*?)\s(.+?)$#i", $message, $matches))
		{
			// 2-stage command
			switch ($matches[1])
			{
				case '/me':
					// A slash me
					$message 	= trim($matches[2]);
					$type 		= $this->shouttypes['me'];
					break;
					
				default:
					($hook = vBulletinHook::fetch_hook('dbtech_vbshout_parsecommand_2')) ? eval($hook) : false;
					break;				
			}
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_command_complete')) ? eval($hook) : false;
		
		return array($retval['type'], $retval['id'], $retval['userid']);
	}

	/**
	* Renders the main shoutbox template.
	* A method because this needs to happen on
	* multiple locations under different conditions.
	*/
	public function render($instance)
	{
		global $vbphrase;
		global $show;
		
		if (intval($this->registry->versionnumber) == 3)
		{
			global $instance, $stylevar, $vbphrase, $session, $template_hook;
			global $bbuserinfo, $vboptions, $vbulletin, $css, $show, $cells;			
		}
		
		$this->instance = $instance;
		foreach (array(
			'dbtech_vbshout_activeusers',
			'dbtech_vbshout_editortools_pro2',
			'dbtech_vbshout_memberaction_dropdown',
			'dbtech_vbshout_memberaction_dropdown_link',
			'dbtech_vbshout_shoutbox',
			'dbtech_vbshout_shoutbox_editortools',
			'dbtech_vbshout_shoutbox_frames',
			'dbtech_vbshout_shoutbox_me',
			'dbtech_vbshout_shoutbox_pm',
			'dbtech_vbshout_shoutbox_shout',
			'dbtech_vbshout_shoutbox_shoutarea_horizontal',
			'dbtech_vbshout_shoutbox_shoutarea_vertical',
			'dbtech_vbshout_shoutbox_shoutcontrols',
			'dbtech_vbshout_shoutbox_system'
		) AS $templatename)
		{
			if (intval($this->registry->versionnumber) != 3)
			{
				// Register the instance variable on all these
				vB_Template::preRegister($templatename, array('instance' => $this->instance));
			}
			else
			{
				// vB3 code
				$GLOBALS['instance'] = $this->instance;
			}
		}
		
		if (!is_array($show))
		{
			// Init
			$show = array();
		}
		
		// Create the template containing the sticky and Error frames
		$frames = vB_Template::create('dbtech_vbshout_shoutbox_frames')->render();
				
		// Create the template rendering engine
		$shoutbox = vB_Template::create('dbtech_vbshout_shoutbox');
			$shoutbox->register('frames', $frames);
			$shoutbox->register('permissions', $this->permissions);
				
		// Whether we need to do a CSS Hack
		$csshack = ' dbtech_fullshouts';

		// The main components of the shoutbox link
		$title 	= $instance['name'];
		if ($this->permissions['canviewarchive'])
		{
			$start = '<a href="vbshout.php?' . $this->registry->session->vars['sessionurl'] . 'do=archive&amp;instanceid=' . $instance['instanceid'] . '">';
			$end 	= '</a>';
		}
		
		// Create the actual shoutbox variable
		//$headerlink = $start . $title . $end;
		$headerlink = '';
		
		$shoutstyle = $this->shoutstyle["{$this->instance[instanceid]}"];
		if ($this->registry->userinfo['userid'] AND $this->permissions['canshout'])
		{		
			// Local user overrides to this should only hide it from the template, not from initialisation
			if ($this->registry->options['dbtech_vbshout_editors'])
			{
				// Create the template containing the Editor Tools
				$tools = vB_Template::create('dbtech_vbshout_shoutbox_editortools');
					$tools->register('editorid', 	'dbtech_shoutbox_editor_wrapper');
					$tools->register('permissions', $this->permissions);
				
				if ($this->registry->options['dbtech_vbshout_editors'] & 1 AND $shoutstyle['bold'])
				{
					// Bold
					$shoutbox->register('bold', 		$shoutstyle['bold']);
				}
				
				if ($this->registry->options['dbtech_vbshout_editors'] & 2 AND $shoutstyle['italic'])
				{
					// Italic
					$shoutbox->register('italic', 		$shoutstyle['italic']);
				}
				
				if ($this->registry->options['dbtech_vbshout_editors'] & 4 AND $shoutstyle['underline'])
				{
					// Underline
					$shoutbox->register('underline', 	$shoutstyle['underline']);
				}
				
				if ($this->registry->options['dbtech_vbshout_editors'] & 16)
				{
					// Check if we need to go with the default font
					$foundfont 	= false;
					
					// Grab the user's font
					$chosenfont = $shoutstyle['font'];
										
					$templater = vB_Template::create('editor_jsoptions_font');
					$string = $templater->render(true);
					$fonts = preg_split('#\r?\n#s', $string, -1, PREG_SPLIT_NO_EMPTY);
					foreach ($fonts AS $font)
					{
						if (strpos($font, 'editor_jsoptions_font'))
						{
							// We don't need template comments
							continue;
						}
						
						if (trim($font) == $chosenfont)
						{
							// Yay we found the font
							$foundfont = true;
						}
						
						if (intval($this->registry->versionnumber) == 3)
						{
							$fontnames .= '<option value="' . trim($font) . '" style="font-family:' . trim($font) . '">' . trim($font) . '</option>';
						}
						else
						{
							$templater = vB_Template::create('editor_toolbar_fontname');
								$templater->register('fontname', trim($font));
							$fontnames .= $templater->render(true);
						}
					}
					if (!$foundfont)
					{
						if (intval($this->registry->versionnumber) != 3)
						{
							// Find the default font
							$chosenfont = explode(' ', trim(vB_Template_Runtime::fetchStyleVar('font')));
							$chosenfont = str_replace(',', '', $chosenfont[1]);
						}
						else
						{
							// vB3 code
							$chosenfont = 'Tahoma';
						}
					}
					
					// Register font stuff
					$tools->register('fontnames', 	$fontnames);
					$shoutbox->register('font', 	$chosenfont);
				}
			
				if ($this->registry->options['dbtech_vbshout_editors'] & 8)
				{
					// Grab the user's chosen color / default colour
					$chosencolor = ($shoutstyle['color'] ? $shoutstyle['color'] : '');
					
					if (intval($this->registry->versionnumber) == 3)
					{
						/*
						$colors = '';
						$colours = array('Black', 'Sienna', 'DarkOliveGreen', 'DarkGreen', 'DarkSlateBlue', 'Navy', 'Indigo',
							'DarkSlateGray', 'DarkRed', 'DarkOrange', 'Olive', 'Green', 'Teal', 'Blue', 'SlateGray', 'DimGray',
							'Red', 'SandyBrown', 'YellowGreen', 'SeaGreen', 'MediumTurquoise', 'RoyalBlue', 'Purple', 'Gray',
							'Magenta', 'Orange', 'Yellow', 'Lime', 'Cyan', 'DeepSkyBlue', 'DarkOrchid', 'Silver', 'Pink',
							'Wheat', 'LemonChiffon', 'PaleGreen', 'PaleTurquoise', 'LightBlue', 'Plum', 'White'
						);
						foreach ($colours as $colour)
						{
							// Grab the colours
							$colors .= '<option value="' . trim($colour) . '" style="color:' . trim($colour) . '">' . trim($colour) . '</option>';
						}
						*/
					}
					else
					{
						// Begin checking colours
						$colors = vB_Template::create('editor_toolbar_colors')->render();
					}
					
					// Register colour stuff
					$tools->register('colors', 		$colors);
					$shoutbox->register('color', 	$chosencolor);
				}
				
				($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shoutbox_editortools')) ? eval($hook) : false;
				
				// Finally render the editor tools
				$tools->register('template_hook', $template_hook);
				$editortools = $tools->render();
				
				// Set the rendered Editor Tools
				//$shoutbox->register('editortools', $editortools);	
			}
		}
		
		$domenu = false;
		$chatrooms = '';
		$direction = 'left';
		$addedpx = 0;
		$chattabs = array();
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shoutbox_start')) ? eval($hook) : false;
		
		$chattabs['shoutreports'] = (((!isset($this->registry->options['dbtech_vbshout_shoutboxtabs']) OR ($this->registry->options['dbtech_vbshout_shoutboxtabs'] & 4)) AND $this->permissions['canmodchat']) ? "vBShout" . $this->instance['instanceid'] . ".add_tab('shoutreports', '" . $vbphrase['dbtech_vbshout_unhandled_reports'] . ": <span id=\"dbtech_shoutbox_shoutreports" . $this->instance['instanceid'] . "\">0</span>', false, 'show_reports()');" : '');
		
		foreach ((array)$this->cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['active'])
			{
				// Inactive chat room
				continue;
			}
			
			if ($chatroom['instanceid'] != $this->instance['instanceid'] AND $chatroom['instanceid'] != 0)
			{
				// Wrong instance id
				continue;
			}
			
			if ($chatroom['membergroupids'])
			{
				if (is_member_of($this->registry->userinfo, explode(',', $chatroom['membergroupids'])))
				{
					// Do join it
					$chattabs["chatroom_{$chatroomid}_"] = "vBShout" . $this->instance['instanceid'] . ".create_chatroom_tab($chatroomid, '" . addslashes($chatroom['title']) . "', false);";
				}
			}
			else
			{
				if ($chatroom['members']["{$this->registry->userinfo[userid]}"] == '1')
				{
					// Do join it
					$chattabs["chatroom_{$chatroomid}_"] = "vBShout" . $this->instance['instanceid'] . ".create_chatroom_tab($chatroomid, '" . addslashes($chatroom['title']) . "', true);";
				}
			}
		}
		
		$tabdisplayorder = @unserialize($this->registry->userinfo['dbtech_vbshout_displayorder']);
		if (is_array($tabdisplayorder["{$this->instance[instanceid]}"]))
		{
			asort($tabdisplayorder["{$this->instance[instanceid]}"]);
			foreach ($tabdisplayorder["{$this->instance[instanceid]}"] as $tabid => $tab)
			{
				// Add the tab
				$chatrooms .= $chattabs["$tabid"];
				unset($chattabs["$tabid"]);
			}
		}
		
		// Add new / unknown tabs
		$chatrooms .= implode(' ', $chattabs);
		
		// Register the chat rooms we are joined to
		$shoutbox->register('chatrooms', $chatrooms);		
		
		if ($this->registry->options['dbtech_vbshout_separate_activeusers'])
		{
			// Array of all active users
			$this->fetch_active_users(false);
			
			// Begin creating the template
			$templater = vB_Template::create('dbtech_vbshout_activeusers');
				$templater->register('activeusers', (count($this->activeusers) ? implode('<br />', $this->activeusers) : $vbphrase['dbtech_vbshout_no_active_users']));
				$templater->register('addedpx', 	$addedpx);			
			
			// We're using the separate Active Users block
			switch ($direction)
			{
				case 'left':
				case 'above':
				case 'below':
					// Register the active users frame
						$templater->register('direction', 	'left');
					$template_hook["dbtech_vbshout_activeusers_right"] .= $templater->render();
					break;
					
				case 'right':
					// Register the active users frame
						$templater->register('direction', 	'right');
					$template_hook["dbtech_vbshout_activeusers_left"] .= $templater->render();
					break;
			}
		}		
		
		// Register the header link variable
		$shoutbox->register('title', 		$title);
		$shoutbox->register('headerlink', 	$headerlink);
		
		// Register template variables
		if (!$this->registry->userinfo['userid'] OR !$this->permissions['canshout'])
		{
			// Set the CSS hack
			$shoutbox->register('csshack', $csshack);
			
			// We can't shout
			$show['canshout'] = false;
		}
		else
		{
			// We can shout
			$show['canshout'] = true;
			
			if (!$shoutbox->is_registered('shoutarea'))
			{
				// We haven't registered a shout area yet
				$templater = vB_Template::create('dbtech_vbshout_shoutbox_shoutarea_vertical');
					$templater->register('direction', 'left');
					$shoutbox->register('direction', 'left');
				$shoutarea = $templater->render();
				
				// Register the shout controls also
				$templater = vB_Template::create('dbtech_vbshout_shoutbox_shoutcontrols');
					$templater->register('permissions', $this->permissions);
					$templater->register('editortools', $editortools);
				$template_hook['dbtech_vbshout_shoutcontrols_below'] .= $templater->render();			
				
				// Register the shout area as being on the left
				$shoutbox->register('shoutarea', $shoutarea);
			}
		}
		
		// Register template hooks
		$shoutbox->register('template_hook', 	$template_hook);
		$shoutbox->register('show', 			$show);
		
		if ($this->registry->options['dbtech_vbshout_optimisation'])
		{
			// Set the time now
			$shoutbox->register('timenow', TIMENOW);
		}
		
		// Finally render the template
		return $shoutbox->render();
	}
	
	/**
	* Prints out the AJAX XML
	*/
	public function print_ajax_xml()
	{
		if (!$this->fetched['error'])
		{
			// Bugfix
			unset($this->fetched['error']);
		}
		
		// Initialise the XML object
		$xml = new vB_AJAX_XML_Builder($this->registry, 'text/xml');
		
		// Add a default group
		$xml->add_group('vbshout');
		
		$tags = array(
			'aoptime',
			'success',
			'sticky',
			'error',
			'clear',
			'editor',
			'content',
			'activeusers',
			'activereports',
			'activeusers2',
			'shoutid',
			'archive',
			'menucode',
			'pmuserid',
			'chatroomid',
			'roomname',
			'pmtime',
		);
		
		foreach ($tags as $tagname)
		{
			if (isset($this->fetched["$tagname"]))
			{
				// Include this tag
				$xml->add_tag("$tagname", $this->fetched["$tagname"]);
			}
		}
		
		if ($this->fetched['chatroomids'])
		{
			// Add the shouts group
			$xml->add_group('chatroomids');
			
			// Go through every shout
			foreach ($this->fetched['chatroomids'] as $chatroomid)
			{
				// Add the shout
				$xml->add_tag('chatroomid2', $chatroomid);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if ($this->fetched['roomnames'])
		{
			// Add the shouts group
			$xml->add_group('roomnames');
			
			// Go through every shout
			foreach ($this->fetched['roomnames'] as $roomname)
			{
				// Add the shout
				$xml->add_tag('roomname2', $roomname);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if ($this->fetched['usernames'])
		{
			// Add the shouts group
			$xml->add_group('usernames');
			
			// Go through every shout
			foreach ($this->fetched['usernames'] as $username)
			{
				// Add the shout
				$xml->add_tag('username2', $username);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if ($this->fetched['aoptimes'])
		{
			// Add the shouts group
			$xml->add_group('aoptimes');
			
			// Go through every shout
			foreach ($this->fetched['aoptimes'] as $aoptime)
			{
				// Add the shout
				$xml->add_tag('aoptime2', $aoptime);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if ($this->fetched['tabids'])
		{
			// Add the shouts group
			$xml->add_group('tabids');
			
			// Go through every shout
			foreach ($this->fetched['tabids'] as $tabid)
			{
				// Add the shout
				$xml->add_tag('tabid2', $tabid);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if ($this->fetched['shouts'])
		{
			// Add the shouts group
			$xml->add_group('shouts');
			
			// Go through every shout
			foreach ($this->fetched['shouts'] as $shoutarray)
			{
				$styleprops = array();
				
				if ($shoutarray['styleprops']['bold'])
				{
					// Set bold font
					$styleprops['bold'] = $shoutarray['styleprops']['bold'];
				}
				
				if ($shoutarray['styleprops']['italic'])
				{
					// Set italic font
					$styleprops['italic'] = $shoutarray['styleprops']['italic'];
				}
				
				if ($shoutarray['styleprops']['underline'])
				{
					// Set underline font
					$styleprops['underline'] = $shoutarray['styleprops']['underline'];
				}
				
				if ($shoutarray['styleprops']['color'])
				{
					// Set font color
					$styleprops['color'] = $shoutarray['styleprops']['color'];
				}
				
				if ($shoutarray['styleprops']['font'])
				{
					// Set font face
					$styleprops['font'] = $shoutarray['styleprops']['font'];
				}
				
				// Add the shout
				$xml->add_tag('shout', $shoutarray['template'], $styleprops);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		$xml->close_group();
		$xml->print_xml();
	}
	
	/**
	* Checks for a protected usergroup
	*
	* @param	array	Usergroup information
	* @param	boolean	(Optional) Whether we should just return boolean
	*/
	public function check_protected_usergroup($exists, $boolreturn = false)
	{
		global $vbphrase;
		
		// Ensure this is set
		$instancepermissions = @unserialize($this->instance['permissions']);
		$instancepermissions = (is_array($instancepermissions) ? $instancepermissions : array());
		
		// Fetch all our usergroup ids
		$usergroupids = array_merge(array($exists['usergroupid']), explode(',', $exists['membergroupids']));
		
		if (!function_exists('fetch_bitfield_definitions'))
		{
			// Ensure we can fetch bitfields
			require(DIR . '/includes/adminfunctions_options.php');
		}
		$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
		
		$permarray = array();
		foreach ($usergroupids as $usergroupid)
		{
			if (!$usergroupid)
			{
				// Just skip it
				continue;
			}
			
			foreach ((array)$permissions as $permname => $bit)
			{
				$yesno = (bool)($instancepermissions["$usergroupid"] & $bit);
				
				if (!isset($permarray["$permname"]))
				{
					// Default to false
					$permarray["$permname"] = false;
				}
				
				if (!$permarray["$permname"] AND $yesno)
				{
					// Override to true
					$permarray["$permname"] = true;
				}
			}			
		}

		if ($permarray['isprotected'])
		{
			if (!$boolreturn)
			{
				// Flag for clearance
				//$this->fetched['clear'] = 'editor';
				
				// Err0r
				$this->fetched['error'] = construct_phrase($vbphrase['dbtech_vbshout_x_is_protected'], $exists['username']);
			}
			return true;
		}
		
		/*
		if ($exists['permissions']['dbtech_vbshoutpermissions'] & $this->registry->bf_ugp_dbtech_vbshoutpermissions['ismanager'])
		{
			if (!$boolreturn)
			{
				// Flag for clearance
				//$this->fetched['clear'] = 'editor';
				
				// Err0r
				$this->fetched['error'] = construct_phrase($vbphrase['dbtech_vbshout_x_is_manager'], $exists['username']);
			}
			return true;
		}
		*/
		
		return false;
	}
	
	/**
	* Logs a specified command.
	*
	* @param	string	The executed command.
	* @param	mixed	(Optional) Additional comments.
	*/
	public function log_command($command, $comment = NULL)
	{
		$bit = 0;
		switch ($command)
		{
			case 'shoutedit':
			case 'shoutdelete':
				$bit = 8;
				break;
			
			case 'prune':
				$bit = 1;
				break;
			
			case 'setsticky':
			case 'removesticky':
				$bit = 2;
				break;
				
			case 'ban':
			case 'unban':
				$bit = 4;
				break;
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_log_process')) ? eval($hook) : false;
		
		if (!$bit OR !($this->registry->options['dbtech_vbshout_logging'] & $bit))
		{
			// We didn't have this option on
			return;
		}
		
		$this->registry->db->query_write("
			INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_log
				(userid, dateline, ipaddress, command, comment)
			VALUES (
				" . $this->registry->db->sql_prepare($this->registry->userinfo['userid']) . ",
				" . $this->registry->db->sql_prepare(TIMENOW) . ",
				" . $this->registry->db->sql_prepare(IPADDRESS) . ",
				" . $this->registry->db->sql_prepare($command) . ",
				" . $this->registry->db->sql_prepare($comment) . "
			)
		");
	}
	
	/**
	* Determines the replacement for the BBCode SIZE limiter.
	*
	* @param	integer	The attempted SIZE value.
	*
	* @return	string	The new SIZE BBCode.
	*/
	public function process_bbcode_size($size)
	{
		// Returns the prepared string
		return '[size=' . (intval($size) > $this->registry->options['dbtech_vbshout_maxsize'] ? $this->registry->options['dbtech_vbshout_maxsize'] : $size) . ']';
	}
	
	/**
	* Prints the query for fetching active users
	*
	* @return	string	The query code.
	*/
	private function print_activeusers_query()
	{
		return "
			SELECT COUNT(DISTINCT userid) AS numactiveusers
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
			WHERE dateline >= " . (TIMENOW - ($this->registry->options['dbtech_vbshout_idletimeout'] ? $this->registry->options['dbtech_vbshout_idletimeout'] : 600)) . "
					AND vbshout.instanceid = " . intval($this->instance['instanceid']) . "
				" . ($this->chatroom ? "AND vbshout.chatroomid = " . intval($this->chatroom['chatroomid']) : 'AND vbshout.chatroomid = 0') . "
				AND userid > 0
		";
	}
	
	/**
	* Fetch all currently active users.
	*/	
	private function fetch_active_users($domenu = false, $force = false, $chatroomid = false)
	{
		global $vbphrase;
		
		if (empty($this->activeusers) OR $force)
		{
			// Array of all active users
			$this->activeusers = array();
			
			// Query active users
			$activeusers_q = $this->registry->db->query_read_slave("
				SELECT
					DISTINCT user.userid,
					username,
					usergroupid,
					membergroupids,
					infractiongroupid,
					displaygroupid,
					user.dbtech_vbshout_settings AS shoutsettings
				FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
				LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
				WHERE vbshout.dateline >= " . (TIMENOW - ($this->registry->options['dbtech_vbshout_idletimeout'] ? $this->registry->options['dbtech_vbshout_idletimeout'] : 600)) . "
					AND vbshout.userid > 0	
					" . (!$this->chatroom ? "AND vbshout.instanceid = " . intval($this->instance['instanceid']) : '')  . "
					" . ($this->chatroom ? "AND vbshout.chatroomid = " . intval($this->chatroom['chatroomid']) : 'AND vbshout.chatroomid = 0') . "
				ORDER BY username ASC
			");
			while ($activeusers_r = $this->registry->db->fetch_array($activeusers_q))
			{
				// fetch the markup-enabled username
				fetch_musername($activeusers_r);
				
				if (!$domenu)
				{					
					// Fetch the SEO'd URL to a member's profile
					if (intval($this->registry->versionnumber) == 3)
					{
						$this->activeusers[] = '<a href="member.php?' . $this->registry->session->vars['sessionurl'] . 'u=' . $activeusers_r['userid'] . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
					}
					else
					{
						$this->activeusers[] = '<a href="' . fetch_seo_url('member', $activeusers_r) . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
					}
				}
				else
				{
					// Cache the permissions
					cache_permissions($activeusers_r, false);
					
					// Ensure this is set
					$activeusers_r['jsusername'] = addslashes($activeusers_r['username']);
					
					$canpm = ($activeusers_r['userid'] != $this->registry->userinfo['userid']);
					if (file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml'))
					{
						if (!($activeusers_r['shoutsettings'] & 128) OR !($this->registry->userinfo['dbtech_vbshout_settings'] & 128) OR !$this->registry->options['dbtech_vbshout_enablepms'])
						{
							// You plain can't pm this person or PMs are disabled globally
							$canpm = false;
						}
					}
					
					// Fetch the SEO'd URL to a member's profile
					$this->activeusers[] = $this->create_memberaction_dropdown(
						$activeusers_r['userid'],
						$activeusers_r['username'],
						$activeusers_r['jsusername'],
						$activeusers_r['musername'],
						$activeusers_r['usertitle'],
						$canpm,
						$this->check_protected_usergroup($activeusers_r, true)
					);
				}
			}
			
		}
	}
	
	/**
	* Creates the member action dropdown template.
	*
	* @param	integer	User ID
	* @param	string	User Name
	* @param	string	JS-Safe User Name
	* @param	string	Markup User Name
	* @param	string	User Title
	* @param	boolean	User ID
	* @param	boolean	User ID
	* 
	* @return	string	The rendered template
	*/	
	private function create_memberaction_dropdown($userid, $username, $jsusername, $musername, $usertitle, $canpm, $isprotected, $shoutid = 0)
	{
		$this->i++;
		
		// Temp array
		$memberinfo = array('userid' => $userid, 'username' => $username);
		
		// Get the dropdown template
		$templater = vB_Template::create('dbtech_vbshout_memberaction_dropdown');
			$templater->register('memberinfo', 	$memberinfo);
			$templater->register('userid', 		$userid);
			$templater->register('username', 	$username);
			$templater->register('jsusername', 	$jsusername);
			$templater->register('musername', 	$musername);
			$templater->register('usertitle', 	$canpm);
			$templater->register('canpm', 		$canpm);
			$templater->register('isprotected', $isprotected);
			$templater->register('i', 			$this->i);
			$templater->register('permissions', $this->permissions);
			$templater->register('chatroom', 	$this->chatroom);
			$templater->register('shoutid', 	$shoutid);
		$this->fetched['menucode'] .= $templater->render();
		
		// Get the dropdown template
		$templater = vB_Template::create('dbtech_vbshout_memberaction_dropdown_link');
			$templater->register('memberinfo', 	$memberinfo);
			$templater->register('userid', 		$userid);
			$templater->register('username', 	$username);
			$templater->register('jsusername', 	$jsusername);
			$templater->register('musername', 	$musername);
			$templater->register('usertitle', 	$canpm);
			$templater->register('canpm', 		$canpm);
			$templater->register('isprotected', $isprotected);
			$templater->register('i', 			$this->i);
		return $templater->render();
	}
	
	/**
	* Fetches all forumids we are allowed access to.
	* 
	* @return	array	The list of forumids we can access
	*/
	public function fetch_forumids()
	{
		$forumcache = $this->registry->forumcache;
		/*
		$excludelist = explode(',', $this->registry->options['dbtech_infopanels_forum_exclude']);
		foreach ($excludelist AS $key => $excludeid)
		{
			$excludeid = intval($excludeid);
			unset($forumcache["$excludeid"]);
		}
		*/
	
		$forumids = array_keys($forumcache);
		
		// get forum ids for all forums user is allowed to view
		foreach ($forumids AS $key => $forumid)
		{
			if (is_array($includearray) AND empty($includearray["$forumid"]))
			{
				unset($forumids["$key"]);
				continue;
			}
	
			$fperms =& $this->registry->userinfo['forumpermissions']["$forumid"];
			$forum =& $this->registry->forumcache["$forumid"];
	
			if (!($fperms & $this->registry->bf_ugp_forumpermissions['canview']) OR !($fperms & $this->registry->bf_ugp_forumpermissions['canviewthreads']) OR !verify_forum_password($forumid, $forum['password'], false))
			{
				unset($forumids["$key"]);
			}
		}
		
		// Those shouts with 0 as their forumid
		$forumids[] = 0;
		
		return $forumids;
	}
	
	/**
	* Class factory. This is used for instantiating the extended classes.
	*
	* @param	string			The type of the class to be called (user, forum etc.)
	* @param	vB_Registry		An instance of the vB_Registry object.
	* @param	integer			One of the ERRTYPE_x constants
	*
	* @return	vB_DataManager	An instance of the desired class
	*/
	public function &datamanager_init($classtype, &$registry, $errtype = ERRTYPE_STANDARD)
	{
		if (empty($this->called))
		{
			// include the abstract base class
			require_once(DIR . '/includes/class_dm.php');
			$this->called = true;
		}
	
		if (preg_match('#^\w+$#', $classtype))
		{
			require_once($this->dir . '/class_dm_' . strtolower($classtype) . '.php');
	
			$classname = 'vB_DataManager_' . $classtype;
			$object = new $classname($registry, $errtype);
	
			return $object;
		}
	}
	
	/**
	* Fetches a list of allowed BBCode tags
	*
	* @param	array	The complete list of BBCode tags
	*
	* @return	array	A list of the allowed tags
	*/
	public function fetch_tag_list($tag_list)
	{
		if (!($this->bbcodepermissions & ALLOW_BBCODE_QUOTE))
		{			
			// [QUOTE]
			unset($tag_list['no_option']['quote']);
	
			// [QUOTE=XXX]
			unset($tag_list['option']['quote']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_BASIC))
		{
			// [B]
			unset($tag_list['no_option']['b']);

			// [I]
			unset($tag_list['no_option']['i']);

			// [U]
			unset($tag_list['no_option']['u']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_COLOR))
		{
			// [COLOR=XXX]
			unset($tag_list['option']['color']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_SIZE))
		{
			// [SIZE=XXX]
			unset($tag_list['option']['size']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_FONT))
		{
			// [FONT=XXX]
			unset($tag_list['option']['font']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_ALIGN))
		{
			// [LEFT]
			unset($tag_list['no_option']['left']);

			// [CENTER]
			unset($tag_list['no_option']['center']);

			// [RIGHT]
			unset($tag_list['no_option']['right']);

			// [INDENT]
			unset($tag_list['no_option']['indent']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_LIST))
		{
			// [LIST]
			unset($tag_list['no_option']['list']);

			// [LIST=XXX]
			unset($tag_list['option']['list']);

			// [INDENT]
			unset($tag_list['no_option']['indent']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_URL))
		{
			// [EMAIL]
			unset($tag_list['no_option']['email']);

			// [EMAIL=XXX]
			unset($tag_list['option']['email']);

			// [URL]
			unset($tag_list['no_option']['url']);

			// [URL=XXX]
			unset($tag_list['option']['url']);

			// [THREAD]
			unset($tag_list['no_option']['thread']);

			// [THREAD=XXX]
			unset($tag_list['option']['thread']);

			// [POST]
			unset($tag_list['no_option']['post']);

			// [POST=XXX]
			unset($tag_list['option']['post']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_PHP))
		{
			// [PHP]
			unset($tag_list['no_option']['php']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_CODE))
		{
			//[CODE]
			unset($tag_list['no_option']['code']);
		}

		if (!($this->bbcodepermissions & ALLOW_BBCODE_HTML))
		{
			// [HTML]
			unset($tag_list['no_option']['html']);
		}
	
		return $tag_list;
	}
	
	/**
	* Rebuilds the shout counter for every user.
	*/
	public function build_shouts_counter()
	{
		// Begin shout counter
		$counters = array();
		
		// Grab all shouts
		$shouts_q = $this->registry->db->query_read_slave("
			SELECT userid, shoutid
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
		");
		while ($shouts_r = $this->registry->db->fetch_array($shouts_q))
		{
			// Build shout counters
			$counters["$shouts_r[userid]"]++;
			
		}
		$this->registry->db->free_result($shouts_q);
		unset($shouts_r);	
		
		$cases = array();
		foreach ($counters as $userid => $shouts)
		{
			// Set the case
			$cases[] = "WHEN $userid THEN $shouts";
		}
		
		if (count($cases))
		{
			// Finally update the user table
			$this->registry->db->query_write("
				UPDATE " . TABLE_PREFIX . "user
				SET dbtech_vbshout_shouts = CASE userid
				" . implode(' ', $cases) . "
				ELSE 0 END
			");
		}
	}
	
	/**
	* Leaves the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public function leave_chatroom(&$chatroom, $userid)
	{
		$SQL = '';
		if ($chatroom['creator'] == $userid)
		{
			// We're killing the AOP file
			//$this->kill_aop();
			
			// Set chatroom to inactive
			$this->registry->db->query_write("
				UPDATE " . TABLE_PREFIX . "dbtech_vbshout_chatroom
				SET active = 0, members = NULL
				WHERE chatroomid = " . $this->registry->db->sql_prepare($chatroom['chatroomid']) . "
			");
		}
		else
		{
			// We weren't the creator, only we should abandon ship
			$SQL = "AND userid = " . $this->registry->db->sql_prepare($userid);
		}
		
		
		// Leave the chat room
		$this->registry->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
			WHERE chatroomid = " . $this->registry->db->sql_prepare($chatroom['chatroomid']) . 
				$SQL
				. ($status ? " AND status = 0" : '')
		);
		
		if ($SQL)
		{
			unset($chatroom['members']["$userid"]);
			$this->registry->db->query_write("
				UPDATE " . TABLE_PREFIX . "dbtech_vbshout_chatroom
				SET members = " . $this->registry->db->sql_prepare(trim(serialize($chatroom['members']))) . "
				WHERE chatroomid = " . $this->registry->db->sql_prepare($chatroom['chatroomid']) . "
			");			
		}
		
		// Build the cache
		$this->build_cache('dbtech_vbshout_chatroom');
	}
	
	/**
	* Joins the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public function join_chatroom(&$chatroom, $userid)
	{
		// Join the chat room
		$this->registry->db->query_write("
			UPDATE " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
			SET status = 1
			WHERE chatroomid = " . $this->registry->db->sql_prepare($chatroom['chatroomid']) . "
				AND userid = " . $this->registry->db->sql_prepare($userid) . "
		");	
		
		// We're now fully joined
		$chatroom['members']["$userid"] = '1';
		
		// Update chatroom bit
		$this->registry->db->query_write("
			UPDATE " . TABLE_PREFIX . "dbtech_vbshout_chatroom
			SET members = " . $this->registry->db->sql_prepare(trim(serialize($chatroom['members']))) . "
			WHERE chatroomid = " . $this->registry->db->sql_prepare($chatroom['chatroomid']) . "
		");	
		
		// Build the cache
		$this->build_cache('dbtech_vbshout_chatroom');
	}
	
	/**
	* Creates the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public function invite_chatroom(&$chatroom, $userid, $invitedby)
	{
		// Invite to join the chat room
		$this->registry->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
				(chatroomid, userid, status, invitedby)
			VALUES (
				" . $this->registry->db->sql_prepare($chatroom['chatroomid']) . ",
				" . $this->registry->db->sql_prepare($userid) . ",
				0,
				" . $this->registry->db->sql_prepare($this->registry->userinfo['userid']) . "				
			)
		");
		
		if ($this->registry->db->affected_rows())
		{
			// We're now fully joined
			$chatroom['members']["$userid"] = '0';
			
			// Update chatroom bit
			$this->registry->db->query_write("
				UPDATE " . TABLE_PREFIX . "dbtech_vbshout_chatroom
				SET members = " . $this->registry->db->sql_prepare(trim(serialize($chatroom['members']))) . "
				WHERE chatroomid = " . $this->registry->db->sql_prepare($chatroom['chatroomid']) . "
			");	
			
			// Build the cache
			$this->build_cache('dbtech_vbshout_chatroom');
		}
	}
	
	/**
	* Fetches what chatrooms we're a member of
	*
	* @param	array	The user info we're checking membership of
	* @param	mixed	Whether we're checking a status or not
	* @param	mixed	Whether we're checking an instanceid or not
	*/
	public function fetch_chatroom_memberships($userinfo, $status = NULL, $instanceid = NULL)
	{
		$memberof = array();
		foreach ((array)$this->cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['active'])
			{
				// Inactive chatroom
				continue;
			}
			
			if ($instanceid !== NULL)
			{
				if ($chatroom['instanceid'] != $instanceid)
				{
					// Skip this instance id
					continue;
				}
			}
			
			if ($chatroom['membergroupids'])
			{
				if (is_member_of($userinfo, explode(',', $chatroom['membergroupids'])))
				{
					// Do join it
					$memberof[] = $chatroomid;
				}
			}
			else
			{
				if (!isset($chatroom['members']["{$userinfo[userid]}"]))
				{
					// We're not a part this
					continue;
				}
				
				if ($status !== NULL AND $chatroom['members']["{$userinfo[userid]}"] !== $status)
				{
					// Wrong status
					continue;
				}
				
				// We're a member
				$memberof[] = $chatroomid;
			}
		}
		
		return $memberof;
	}
	
	/**
	* Rebuilds the shout counter for every user.
	*
	* @param	string	The new sticky note.
	*/
	public function set_sticky($sticky)
	{
		if (file_exists(DIR . '/includes/xml/bitfield_vbshout_pro.xml'))
		{
			// Set for one instance
			$SQL = "WHERE instanceid = " . $this->registry->db->sql_prepare($this->instance['instanceid']);
		}
		
		// Store raw sticky
		$sticky_raw = $sticky;
		
		// Ensure we got BBCode Parser
		require_once(DIR . '/includes/class_bbcode.php');
		if (!function_exists('convert_url_to_bbcode'))
		{
			require_once(DIR . '/includes/functions_newpost.php');
		}
		
		// Initialise the parser (use proper BBCode)
		$parser = new vB_BbCodeParser($this->registry, fetch_tag_list());
		
		if ($this->registry->options['allowedbbcodes'] & 64)
		{
			// We can use the URL BBCode, so convert links
			$sticky = convert_url_to_bbcode($sticky);
		}	
		
		// BBCode parsing
		$sticky = $parser->parse($sticky, 'nonforum');		
		
		// Set shoutbox sticky
		$this->registry->db->query_write("
			UPDATE " . TABLE_PREFIX . "dbtech_vbshout_instance
			SET sticky = " . $this->registry->db->sql_prepare($sticky) . ",
				sticky_raw = " . $this->registry->db->sql_prepare($sticky_raw) . 
			$SQL
		);
		
		// Build the cache
		$this->build_cache('dbtech_vbshout_instance');
		$this->instance['sticky'] = $sticky;		
	}
	
	/**
	* Builds the cache in case the datastore has been cleaned out.
	*
	* @param	string	Database table we are working with
	* @param	string	(Optional) Any additional clauses to the query
	*/
	public function build_cache($dbtype, $clauses = '')
	{
		return $this->cacheclass->build_cache($dbtype, $clauses);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Created: 16:52, Sat Dec 26th 2009
|| # SVN: $ $Rev$ $ - $ $Date$ $
|| ####################################################################
\*======================================================================*/
?>