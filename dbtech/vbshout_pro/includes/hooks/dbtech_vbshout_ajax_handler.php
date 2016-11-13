<?php

switch ($do)
{	
	case 'dbtech_vbshout_delete':
		$source = $this->registry->input->clean_gpc($this->fetchtype, 'source', TYPE_STR);
		
		if (!$source == 'archive')
		{
			break;
		}
		
		// We dont need this shiet
		unset(
			$this->fetched['success'],
			$this->fetched['aoptime'],
			$this->fetched['shouts'],
			$this->fetched['activeusers'],
			$this->fetched['sticky'],
			$this->fetched['clear'],
			$this->fetched['activeusers2'],
			$this->fetched['menucode']
		);
		break;
	
	case 'dbtech_vbshout_save':
		$source = $this->registry->input->clean_gpc($this->fetchtype, 'source', TYPE_STR);
		
		if (!$source == 'archive')
		{
			break;
		}
		
		// We dont need this shiet
		unset(
			$this->fetched['success'],
			$this->fetched['aoptime'],
			$this->fetched['shouts'],
			$this->fetched['activeusers'],
			$this->fetched['sticky'],
			$this->fetched['clear'],
			$this->fetched['activeusers2'],
			$this->fetched['menucode']
		);
		
		if (!$exists = $this->registry->db->query_first("
			SELECT 
				vbshout.*,
				user.username, IF(user.displaygroupid = 0, user.usergroupid, user.displaygroupid) AS displaygroupid, infractiongroupid
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
			INNER JOIN " . TABLE_PREFIX . "user AS user USING(userid)				
			WHERE shoutid = " . $this->registry->db->sql_prepare($shoutid)
		))
		{
			// The shout doesn't exist
			break;
		}			
		
		// Initialise BBCode Permissions
		$this->init_bbcode_permissions($this->instance['bbcodepermissions']);
		
		// Store these settings
		$backup = array(
			'allowedbbcodes' 	=> $this->registry->options['allowedbbcodes'],
			'allowhtml' 		=> $this->registry->options['allowhtml'],
			'allowbbcode' 		=> $this->registry->options['allowbbcode'],
			'allowsmilies' 		=> $this->registry->options['allowsmilies'],
			'allowbbimagecode' 	=> $this->registry->options['allowbbimagecode']
		);
		
		// Override allowed bbcodes
		$this->registry->options['allowedbbcodes'] 	= $this->bbcodepermissions;
		
		// Ensure we got BBCode Parser
		require_once(DIR . '/includes/class_bbcode.php');
		
		if (!function_exists('convert_url_to_bbcode'))
		{
			require_once(DIR . '/includes/functions_newpost.php');
		}
		
		// Initialise the parser
		$parser = new vB_BbCodeParser($this->registry, fetch_tag_list());
		
		// Override the BBCode list
		$this->registry->options['allowhtml'] 			= false;
		$this->registry->options['allowbbcode'] 		= true;
		$this->registry->options['allowsmilies'] 		= $this->registry->options['dbtech_vbshout_allowsmilies'];
		$this->registry->options['allowbbimagecode'] 	= ($this->bbcodepermissions & 1024);
		
		if ($this->bbcodepermissions & 64)
		{
			// We can use the URL BBCode, so convert links
			$exists['message'] = convert_url_to_bbcode($exists['message']);
		}
		
		// Store the unparsed message also
		$exists['message'] = trim($exists['message']);
		
		// BBCode parsing
		$exists['message'] = $parser->parse($exists['message'], 'nonforum');
		
		$this->fetched['shoutid'] = $shoutid;
		$this->fetched['archive'] = $exists['message'];
	
		foreach ($backup as $vbopt => $val)
		{
			// Reset the settings
			$this->registry->options["$vbopt"] = $val;
		}
	
		if ($exists['userid'] == -1)
		{
			// This was the SYSTEM
			$exists['username'] = $vbphrase['dbtech_vbshout_system'];
		}
		else
		{
			if (intval($vbulletin->versionnumber) == 3)
			{
				$start = '<a href="member.php?' . $this->registry->session->vars['sessionurl'] . 'u=' . $exists['userid'] . '" target="_blank">' . $exists['musername'] . '</a>';
			}
			else
			{
				$start = '<a href="' . fetch_seo_url('member', $exists) . '" target="_blank">';
			}
			$end = '</a>';
		}
	
		if (in_array($exists['type'], array($this->shouttypes['me'], $this->shouttypes['notif'])))
		{
			// This needs to look like a slash me
			$this->fetched['archive'] = '*' . $start . $exists['username'] . $end . ' ' . $this->fetched['archive'] . '*';
		}
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
			case 'banunban':
				$shout->set('message', ($exists['dbtech_vbshout_banned'] ? '/unban ' : '/ban ') . $exists['username']);
				break;
				
			case 'silenceunsilence':
				$shout->set('message', ($exists['dbtech_vbshout_silenced'] ? '/unsilence ' : '/silence ') . $exists['username']);			
				break;
				
			case 'ignoreunignore':
				$isignored = $this->registry->db->query_first_slave("
					SELECT userid
					FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist
					WHERE userid = " . intval($this->registry->userinfo['userid']) . "
						AND ignoreuserid = " . $this->registry->db->sql_prepare($userid)
				);
				$shout->set('message', ($isignored ? '/unignore ' : '/ignore ') . $exists['username']);
				break;
				
			case 'pruneshouts':
				// Prune an user's posts			
				$shout->set('message', '/prune ' . $exists['username']);
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
?>