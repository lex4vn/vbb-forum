<?php
if (class_exists('VBSHOUT'))
{
	global $template_hook, $ad_location, $hook_location, $vbphrase, $show;
	
	if (!class_exists('vB_Template'))
	{
		// We need the template class
		require_once(DIR . '/dbtech/vbshout/includes/class_template.php');
	}
	
	$jsphrases = array(
		'dbtech_vbshout_idle' 							=> $vbphrase['dbtech_vbshout_idle'],
		'dbtech_vbshout_flagged_idle'					=> $vbphrase['dbtech_vbshout_flagged_idle'],
		'dbtech_vbshout_saving_shout' 					=> $vbphrase['dbtech_vbshout_saving_shout'],
		'dbtech_vbshout_editing_shout' 					=> $vbphrase['dbtech_vbshout_editing_shout'],
		'dbtech_vbshout_editing_sticky' 				=> $vbphrase['dbtech_vbshout_editing_sticky'],
		'dbtech_vbshout_deleting_shout' 				=> $vbphrase['dbtech_vbshout_deleting_shout'],
		'dbtech_vbshout_fetching_shouts' 				=> $vbphrase['dbtech_vbshout_fetching_shouts'],
		'dbtech_vbshout_fetching_shouts_in_x_seconds'	=> $vbphrase['dbtech_vbshout_fetching_shouts_in_x_seconds'],
		'dbtech_vbshout_no_active_users'				=> $vbphrase['dbtech_vbshout_no_active_users'],
		'dbtech_vbshout_saving_shout_styles'			=> $vbphrase['dbtech_vbshout_saving_shout_styles'],	
		'dbtech_vbshout_ajax_disabled'					=> $vbphrase['dbtech_vbshout_ajax_disabled'],
		'dbtech_vbshout_must_wait_x_seconds'			=> $vbphrase['dbtech_vbshout_must_wait_x_seconds'],
	);
	
	vbshout_js_escape_string($jsphrases);
	
	foreach ($jsphrases as $varname => $value)
	{
		// Replace phrases with safe values
		$vbphrase["$varname"] = $value;
	}
	
	if (!function_exists('fetch_tag_list'))
	{
		require_once(DIR . '/includes/class_bbcode.php');
	}
	
	// Store all possible BBCode tags
	//VBSHOUT::$tag_list = fetch_tag_list('', true);
	
	if ($vbulletin->options['dbtech_vbshout_active'])
	{
		foreach ((array)VBSHOUT::$cache['instance'] as $instance)
		{	
			if (!$instance['active'])
			{
				// Inactive instance
				continue;
			}
					
			if ($vbulletin->userinfo['dbtech_vbshout_banned'])
			{
				// Banz!
				continue;
			}		
			
			if (!$instance['permissions_parsed']['canviewshoutbox'])
			{
				// Can't view this instance
				continue;
			}
	
			if ((int)$vbulletin->userinfo['posts'] < $instance['options']['minposts'] AND !VBSHOUT::$permissions['ismanager'])
			{
				// Too few posts
				continue;
			}
			
			// ######################## Start Value Fallback #########################
			// Maximum Characters Per Shout
			$instance['options']['maxchars'] 		= ($instance['options']['maxchars'] > 0 	? $instance['options']['maxchars'] 	: $vbulletin->options['postmaxchars']);
			$instance['options']['maxchars'] 		= (VBSHOUT::$permissions['ismanager'] > 0 	? 0 								: $instance['options']['maxchars']);
			
			// Maximum Images Per Shout
			$instance['options']['maximages'] 		= ($instance['options']['maximages'] > 0 	? $instance['options']['maximages'] : $vbulletin->options['maximages']);
			
			// Flood check time
			$instance['options']['floodchecktime'] 	= (VBSHOUT::$permissions['ismanager'] > 0 	? 0 								: $instance['options']['floodchecktime']);
			
			// Render the shoutbox
			$rendered = VBSHOUT::render($instance);
				
			if (THIS_SCRIPT == 'vbshout')
			{
				// Don't need to do anything with this
				continue;
			}
			
			switch ($instance['autodisplay'])
			{
				case 1:
					if (THIS_SCRIPT == 'index')
					{
						// Below Navbar
						if (intval($vbulletin->versionnumber) != 3)
						{
							// vB4 Location
							$ad_location['global_below_navbar'] .= $rendered;
						}
						else
						{
							// vB3 code
							$ad_location['ad_navbar_below'] .= $rendered;
						}
					}
					break;
					
				case 2:
					if (THIS_SCRIPT == 'index')
					{
						// Above Footer
						$template_hook['forumhome_below_forums'] .= $rendered;
					}
					break;
					
				default:
					// Disabled
					$show["vbshout_{$instance[varname]}"] = $rendered;
					break;
			}
		}
	}
	
	foreach ((array)VBSHOUT::$cache['chatroom'] as $chatroomid => $chatroom)
	{
		if (!$chatroom['active'])
		{
			// Gtfo
			continue;
		}
		
		if (!$chatroom['members'])
		{
			// It was just pure empty
			VBSHOUT::$cache['chatroom']["$chatroomid"]['members'] = array();
		}
		
		if (empty($chatroom['members']) AND !$chatroom['membergroupids'])
		{
			// Grab members
			$members = array();
			
			// This should never happen, rebuild members list
			$members_q = $vbulletin->db->query_read_slave("
				SELECT userid, status
				FROM " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
				WHERE chatroomid = " . intval($chatroomid)
			);
			while ($members_r = $vbulletin->db->fetch_array($members_q))
			{
				// Set the member
				$members["$members_r[userid]"] = $members_r['status'];
			}
			unset($members_r);
			$vbulletin->db->free_result($members_q);
			
			// init data manager
			$dm =& VBSHOUT::datamanager_init('Chatroom', $vbulletin, ERRTYPE_SILENT);
				$dm->set_existing($chatroom);
				
			if (empty($members))
			{
				// No members
				$dm->set('active', 0);
			}
			else
			{
				// Save members
				$dm->set('members', $members);
			}
			$dm->save();
		}
	}
	
	if (intval($vbulletin->versionnumber) == 3)
	{
		// We need the template class
		//require_once(DIR . '/dbtech/vbshout/hooks/process_templates_complete.php');
	}
}
?>