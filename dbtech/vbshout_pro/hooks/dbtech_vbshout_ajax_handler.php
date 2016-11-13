<?php
switch ($do)
{	
	case 'delete':
		$source = self::$vbulletin->input->clean_gpc(self::$fetchtype, 'source', TYPE_STR);
		
		if (!$source == 'archive')
		{
			break;
		}
		
		// We dont need this shiet
		unset(
			self::$fetched['success'],
			self::$fetched['aoptime'],
			self::$fetched['shouts'],
			self::$fetched['activeusers'],
			self::$fetched['sticky'],
			self::$fetched['clear'],
			self::$fetched['activeusers2'],
			self::$fetched['menucode']
		);
		break;
	
	case 'save':
		$source = self::$vbulletin->input->clean_gpc(self::$fetchtype, 'source', TYPE_STR);
		
		if (!$source == 'archive')
		{
			break;
		}
		
		// We dont need this shiet
		unset(
			self::$fetched['success'],
			self::$fetched['aoptime'],
			self::$fetched['shouts'],
			self::$fetched['activeusers'],
			self::$fetched['sticky'],
			self::$fetched['clear'],
			self::$fetched['activeusers2'],
			self::$fetched['menucode']
		);
		
		if (!$exists = self::$vbulletin->db->query_first("
			SELECT 
				vbshout.*,
				user.username, IF(user.displaygroupid = 0, user.usergroupid, user.displaygroupid) AS displaygroupid, infractiongroupid
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
			INNER JOIN " . TABLE_PREFIX . "user AS user USING(userid)				
			WHERE shoutid = " . self::$vbulletin->db->sql_prepare($shoutid)
		))
		{
			// The shout doesn't exist
			break;
		}			
		
		// Store these settings
		$backup = array(
			'allowedbbcodes' 	=> self::$vbulletin->options['allowedbbcodes'],
			'allowhtml' 		=> self::$vbulletin->options['allowhtml'],
			'allowbbcode' 		=> self::$vbulletin->options['allowbbcode'],
			'allowsmilies' 		=> self::$vbulletin->options['allowsmilies'],
			'allowbbimagecode' 	=> self::$vbulletin->options['allowbbimagecode']
		);
		
		// Override allowed bbcodes
		self::$vbulletin->options['allowedbbcodes'] 	= self::$instance['bbcodepermissions_parsed']['bit'];
		
		// Ensure we got BBCode Parser
		require_once(DIR . '/includes/class_bbcode.php');
		
		if (!function_exists('convert_url_to_bbcode'))
		{
			require_once(DIR . '/includes/functions_newpost.php');
		}
		
		// Initialise the parser
		$parser = new vB_BbCodeParser(self::$vbulletin, fetch_tag_list());
		
		// Override the BBCode list
		self::$vbulletin->options['allowhtml'] 			= false;
		self::$vbulletin->options['allowbbcode'] 		= true;
		self::$vbulletin->options['allowsmilies'] 		= VBSHOUT::$instance['options']['allowsmilies'];
		self::$vbulletin->options['allowbbimagecode'] 	= (self::$instance['bbcodepermissions_parsed']['bit'] & 1024);
		
		if (self::$instance['bbcodepermissions_parsed']['bit'] & 64)
		{
			// We can use the URL BBCode, so convert links
			$exists['message'] = convert_url_to_bbcode($exists['message']);
		}
		
		// Store the unparsed message also
		$exists['message'] = trim($exists['message']);
		
		// BBCode parsing
		$exists['message'] = $parser->parse($exists['message'], 'nonforum');
		
		self::$fetched['shoutid'] = $shoutid;
		self::$fetched['archive'] = $exists['message'];
	
		foreach ($backup as $vbopt => $val)
		{
			// Reset the settings
			self::$vbulletin->options["$vbopt"] = $val;
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
				$start = '<a href="member.php?' . self::$vbulletin->session->vars['sessionurl'] . 'u=' . $exists['userid'] . '" target="_blank">' . $exists['musername'] . '</a>';
			}
			else
			{
				$start = '<a href="' . fetch_seo_url('member', $exists) . '" target="_blank">';
			}
			$end = '</a>';
		}
	
		if (in_array($exists['type'], array(self::$shouttypes['me'], self::$shouttypes['notif'])))
		{
			// This needs to look like a slash me
			self::$fetched['archive'] = '*' . $start . $exists['username'] . $end . ' ' . self::$fetched['archive'] . '*';
		}
		break;
	
	case 'usermanage':
		
		if (!$exists)
		{
			break;
		}
		
		// Init the Shout DM
		$shout = self::datamanager_init('Shout', self::$vbulletin, ERRTYPE_ARRAY);
		$shout->set('instanceid', self::$instance['instanceid']);
		$shout->set('chatroomid', self::$chatroom['chatroomid']);
		
		$skip = false;
		switch ($action)
		{
			case 'banunban':
				$shout->set('message', ($exists['dbtech_vbshout_banned'] ? '/unban ' : '/ban ') . $exists['username']);
				break;
				
			case 'silenceunsilence':
				$shout->set('message', ($exists['dbtech_vbshout_silenced'] ? '/unsilence ' : '/silence ') . $exists['username']);			
				break;
				
			case 'pruneshouts':
				// Prune an user's posts			
				$shout->set('message', '/prune ' . $exists['username']);
				break;
				
			default:
				$skip = true;
				break;
		}
		
		if (!$skip)
		{
			// Now save it
			$shout->save();
			
			if (self::$fetched['error'])
			{
				// We haz error
				break;
			}
			
			// Shout fetching args
			$args = array();					
			if ($type == 'pm')
			{
				// Fetch only PMs
				$args['types'] 		= self::$shouttypes['pm'];
				$args['onlyuser'] 	= $userid;
			}
			
			// Fetch only from this chatroom
			$args['chatroomid'] = self::$chatroom['chatroomid'];
			
			// We want to fetch shouts
			self::fetch_shouts($args);
		}
		unset($shout);
		
		break;
}
?>