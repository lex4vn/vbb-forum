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

// ######################### REQUIRE BACK-END ############################
require_once(DIR . '/includes/functions_user.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// ############################### start display options ###############################
if ($_REQUEST['action'] == 'options')
{	
	// Navigation bits
	$navbits[''] = $vbphrase['dbtech_vbshout_shoutbox_settings'];
	
	// Grab all the bitfields we can
	require_once(DIR . '/includes/class_bitfield_builder.php');
	$bitfields = vB_Bitfield_Builder::return_data();
	
	// Begin the array of options
	$optionlist = array();
	
	foreach (array(
		'dbtech_vbshout_general_settings' 	=> $bitfields['nocache']['dbtech_vbshout_general_settings'],
		'dbtech_vbshout_editor_settings' 	=> $bitfields['nocache']['dbtech_vbshout_editor_settings']
	) as $settinggroup => $settings)
	{
		// Begin settings
		$optionlist["$settinggroup"] = array();
		
		foreach ($settings as $settingname => $bit)
		{
			$optionlist["$settinggroup"][] = array(
				'varname'		=> $settingname,
				'description' 	=> $vbphrase["{$settingname}_descr"],
				'checked'		=> ((intval($vbulletin->userinfo['dbtech_vbshout_settings']) & $bit) ? ' checked="checked"' : ''),
				'settingphrase'	=> $vbphrase["{$settingname}"],
				'phrase'		=> $vbphrase["{$settingname}_short"],
			);
		}
	}
	
	if (!in_array($vbulletin->userinfo['dbtech_vbshout_shoutarea'], array('default', 'left', 'right', 'above', 'below')))
	{
		// Ensure its the correct value
		$vbulletin->userinfo['dbtech_vbshout_shoutarea'] = 'default';
	}
	
	$tabdisplayorder = @unserialize($vbulletin->userinfo['dbtech_vbshout_displayorder']);
		
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		if (!$instance['active'])
		{
			// Inactive instance
			//continue;
		}
		
		$chatrooms = '';
		
		// Do join it
		$templater = vB_Template::create('dbtech_vbshout_options_chattab');
			$templater->register('title', $vbphrase['dbtech_vbshout_notifications']);
			$templater->register('displayorder', $tabdisplayorder["$instanceid"]['shoutnotifs']);
			$templater->register('tabid', 'shoutnotifs');
			$templater->register('instanceid', $instanceid);
		$chatrooms .= $templater->render();	
		
		// Do join it
		$templater = vB_Template::create('dbtech_vbshout_options_chattab');
			$templater->register('title', $vbphrase['dbtech_vbshout_system_messages']);
			$templater->register('displayorder', $tabdisplayorder["$instanceid"]['systemmsgs']);
			$templater->register('tabid', 'systemmsgs');
			$templater->register('instanceid', $instanceid);
		$chatrooms .= $templater->render();	
		
		// Do join it
		$templater = vB_Template::create('dbtech_vbshout_options_chattab');
			$templater->register('title', $vbphrase['dbtech_vbshout_unhandled_reports']);
			$templater->register('displayorder', $tabdisplayorder["$instanceid"]['shoutreports']);
			$templater->register('tabid', 'shoutreports');
			$templater->register('instanceid', $instanceid);
		$chatrooms .= $templater->render();	
		
		$memberof = VBSHOUT::fetch_chatroom_memberships($vbulletin->userinfo, '1', $instance['instanceid']);
		
		foreach ((array)VBSHOUT::$cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['active'])
			{
				// Inactive chat room
				continue;
			}
			
			if ($chatroom['instanceid'] != $instance['instanceid'] AND $chatroom['instanceid'] != 0)
			{
				// Wrong instance id
				continue;
			}
			
			if ($chatroom['membergroupids'])
			{
				if (is_member_of($vbulletin->userinfo, explode(',', $chatroom['membergroupids'])))
				{
					// Do join it
					$templater = vB_Template::create('dbtech_vbshout_options_chattab');
						$templater->register('title', $chatroom['title']);
						$templater->register('displayorder', $tabdisplayorder["$instanceid"]["chatroom_{$chatroomid}_"]);
						$templater->register('tabid', "chatroom_{$chatroomid}_");
						$templater->register('instanceid', $instanceid);
					$chatrooms .= $templater->render();	
				}
			}
			else
			{
				if (in_array($chatroomid, $memberof))
				{
					// Do join it
					$templater = vB_Template::create('dbtech_vbshout_options_chattab');
						$templater->register('title', $chatroom['title']);
						$templater->register('displayorder', $tabdisplayorder["$instanceid"]["chatroom_{$chatroomid}_"]);
						$templater->register('tabid', "chatroom_{$chatroomid}_");
						$templater->register('instanceid', $instanceid);
					$chatrooms .= $templater->render();	
				}
			}
		}
		
		if (!empty($chatrooms))
		{
			$templater = vB_Template::create('dbtech_vbshout_options_chatrooms');
				$templater->register('headerphrase', $instance['name']);
				$templater->register('chatrooms', $chatrooms);
			$tabbits .= $templater->render();	
		}
	}
	
	if (intval($vbulletin->versionnumber) == 3)
	{
		foreach ($optionlist as $headerphrase => $options)
		{
			$optionbits2 = '';
			foreach ($options as $option)
			{
				$templater = vB_Template::create('dbtech_vbshout_options_bit_bit');
					$templater->register('option', $option);
				$optionbits2 .= $templater->render();	
			}
			
			$templater = vB_Template::create('dbtech_vbshout_options_bit');
				$templater->register('headerphrase', $vbphrase["$headerphrase"]);
				$templater->register('optionbits2', $optionbits2);
			$optionbits .= $templater->render();	
		}
		
		// Include the page template
		$page_templater = vB_Template::create('dbtech_vbshout_options');
			$page_templater->register('tabbits', $tabbits);
			$page_templater->register('optionbits', $optionbits);
			$page_templater->register('checked', array($vbulletin->userinfo['dbtech_vbshout_shoutarea'] => ' selected="selected"'));		
	}
	else
	{
		// Include the page template
		$page_templater = vB_Template::create('dbtech_vbshout_options');
			$page_templater->register('tabbits', $tabbits);
			$page_templater->register('optionlist', $optionlist);
			$page_templater->register('checked', array($vbulletin->userinfo['dbtech_vbshout_shoutarea'] => ' selected="selected"'));		
	}
}

// ############################### start save options ##################################
if ($_POST['action'] == 'updateoptions')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'options'        		=> TYPE_ARRAY_BOOL,
		'set_options'    		=> TYPE_ARRAY_BOOL,
		'shoutarea'		 		=> TYPE_STR,
		'shoutsize'		 		=> TYPE_UINT,
		'shoutsize_detached' 	=> TYPE_UINT,
		'displayorder' 			=> TYPE_ARRAY,
	));
	
	// Grab all the bitfields we can
	require_once(DIR . '/includes/class_bitfield_builder.php');
	$bitfields = vB_Bitfield_Builder::return_data();
	
	$userdata =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
	$userdata->set_existing($vbulletin->userinfo);
	
	// Add to userdata
	$userdata->bitfields['dbtech_vbshout_settings'] = array_merge($bitfields['nocache']['dbtech_vbshout_editor_settings'], $bitfields['nocache']['dbtech_vbshout_general_settings']);
	
	// options bitfield
	foreach ($userdata->bitfields['dbtech_vbshout_settings'] AS $key => $val)
	{
		if (isset($vbulletin->GPC['options']["$key"]) OR isset($vbulletin->GPC['set_options']["$key"]))
		{
			$value = $vbulletin->GPC['options']["$key"];
			$userdata->set_bitfield('dbtech_vbshout_settings', $key, $value);
		}
	}
	
	if (!in_array($vbulletin->GPC['shoutarea'], array('default', 'left', 'right', 'above', 'below')))
	{
		// Ensure its the correct value
		$vbulletin->GPC['shoutarea'] = 'default';
	}
	
	// Set the shout area
	$userdata->set('dbtech_vbshout_shoutarea', $vbulletin->GPC['shoutarea']);
	$userdata->set('dbtech_vbshout_shoutboxsize', $vbulletin->GPC['shoutsize']);
	$userdata->set('dbtech_vbshout_shoutboxsize_detached', $vbulletin->GPC['shoutsize_detached']);
	$userdata->set('dbtech_vbshout_displayorder', trim(serialize($vbulletin->GPC['displayorder'])));
	
	// Save the userdata
	$userdata->save();	

	$vbulletin->url = 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=profile&action=options';
	eval(print_standard_redirect('redirect_updatethanks'));
}

// ############################### start display options ###############################
if ($_REQUEST['action'] == 'ignorelist')
{	
	// Navigation bits
	$navbits[''] = $vbphrase['dbtech_vbshout_ignore_list'];
	
	// The finished array of all ignored users
	$ignorelist = array();
	
	// Query all users we're ignoring
	$ignorelist_q = $db->query_read_slave("
		SELECT 
			user.userid,
			ignoreuserid,
			username,
			usergroupid,
			infractiongroupid,
			displaygroupid
			" . ($vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . "
		FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist AS ignorelist
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = ignorelist.ignoreuserid)
		WHERE ignorelist.userid = " . intval($vbulletin->userinfo['userid']) . "
		ORDER BY username ASC
	");
	
	while ($ignorelist_r = $db->fetch_array($ignorelist_q))
	{
		// fetch the markup-enabled username
		fetch_musername($ignorelist_r);
		
		$ignorelist["$ignorelist_r[userid]"] = $ignorelist_r;
	}
	
	if (intval($vbulletin->versionnumber) == 3)
	{
		foreach ($ignorelist as $userid => $user)
		{
			$templater = vB_Template::create('dbtech_vbshout_ignorelist_bit');
				$templater->register('userid', $userid);
				$templater->register('user', $user);
			$ignorebits .= $templater->render();	
		}
		// Include the page template
		$page_templater = vB_Template::create('dbtech_vbshout_ignorelist');
			$page_templater->register('ignorebits', $ignorebits);
	}
	else
	{
		// Include the page template
		$page_templater = vB_Template::create('dbtech_vbshout_ignorelist');
		
		if (count($ignorelist))
		{
			// So the if condition works as intended
			$page_templater->register('ignorelist', $ignorelist);
		}
	}
}

// ############################### start save options ##################################
if ($_POST['action'] == 'updateignorelist')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'unignore' 		=> TYPE_ARRAY_BOOL,
		'set_unignore' 	=> TYPE_ARRAY_BOOL,
		'username' 		=> TYPE_ARRAY_STR
	));
	
	// Who to unignore
	$SQL = array();
	
	foreach ($vbulletin->GPC['unignore'] as $userid => $checked)
	{
		// Unignore this user
		$SQL[] = $userid;
	}
	
	if (count($SQL))
	{
		$db->query_write("
			DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist
			WHERE ignoreuserid IN(" . implode(',', $SQL) . ")
				AND userid = " . $vbulletin->userinfo['userid']
		);
	}
	
	// Who to ignore
	$SQL = array();
		
	foreach ($vbulletin->GPC['username'] as $username)
	{
		if (!$username)
		{
			// This field didn't contain anything
			continue;
		}
		
		if (!$exists = $db->query_first_slave("
			SELECT userid, usergroupid, membergroupids
			FROM " . TABLE_PREFIX . "user
			WHERE username = " . $db->sql_prepare($username)
		))
		{
			// Invalid user
			continue;
		}
		
		if ($exists['userid'] == $vbulletin->userinfo['userid'])
		{
			// Ourselves, duh
			continue;
		}
					
		// Get our usergroup permissions
		cache_permissions($exists, false);
					
		if ($exists['permissions']['dbtech_vbshoutpermissions'] & $vbulletin->bf_ugp_dbtech_vbshoutpermissions['isprotected'])
		{
			// Can't ignore protected
			continue;
		}
		
		// Add to ignore list
		$SQL[] = "(" . $vbulletin->userinfo['userid'] . ", " . intval($exists['userid']) . ")";
	}
	
	if (count($SQL))
	{
		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "dbtech_vbshout_ignorelist
				(userid, ignoreuserid)
			VALUES
				" . implode(',', $SQL)
		);
	}
	
	$vbulletin->url = 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=profile&action=ignorelist';
	eval(print_standard_redirect('redirect_updatethanks'));
}

// ############################### start display options ###############################
if ($_REQUEST['action'] == 'customcommands')
{
	// Navigation bits
	$navbits[''] = $vbphrase['dbtech_vbshout_custom_commands'];
	
	// The finished array of all ignored users
	$commandlist = array();
	
	// Query all users we're ignoring
	$commandlist_q = $db->query_read_slave("
		SELECT *
		FROM " . TABLE_PREFIX . "dbtech_vbshout_command
		WHERE userid = " . intval($vbulletin->userinfo['userid']) . "
		ORDER BY command ASC
	");
	
	$showlabel = true;
	while ($commandlist_r = $db->fetch_array($commandlist_q))
	{
		// Show label for the first only
		$commandlist_r['showlabel'] = $showlabel;
		
		// Set checked
		$commandlist_r['useinput'] = ($commandlist_r['useinput'] ? ' checked="checked"' : '');
		
		// Grab the list of all our current commands
		$commandlist["$commandlist_r[commandid]"] = $commandlist_r;
		
		// No longer show label
		$showlabel = false;
	}
	
	if (intval($vbulletin->versionnumber) == 3)
	{
		foreach ($commandlist as $commandid => $command)
		{
			$templater = vB_Template::create('dbtech_vbshout_customcommands_bit');
				$templater->register('commandid', $commandid);
				$templater->register('command', $command);
			$commandbits .= $templater->render();	
		}
		// Include the page template
		$page_templater = vB_Template::create('dbtech_vbshout_customcommands');
			$page_templater->register('commandbits', $commandbits);
	}
	else
	{
		// Include the page template
		$page_templater = vB_Template::create('dbtech_vbshout_customcommands');
		
		if (count($commandlist))
		{
			// So the if condition works as intended
			$page_templater->register('commandlist', $commandlist);
		}
	}
}

// ############################### start save options ##################################
if ($_POST['action'] == 'updatecustomcommands')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'command' 		=> TYPE_ARRAY,
		'newcommand' 	=> TYPE_ARRAY
	));
	
	// The finished array of all ignored users
	$commandlist = array();
	$commandlist2 = array();
	
	// Query all users we're ignoring
	$commandlist_q = $db->query_read_slave("
		SELECT *
		FROM " . TABLE_PREFIX . "dbtech_vbshout_command
		WHERE userid = " . intval($vbulletin->userinfo['userid']) . "
		ORDER BY command ASC
	");
	
	while ($commandlist_r = $db->fetch_array($commandlist_q))
	{
		// Grab the list of all our current commands
		$commandlist["$commandlist_r[command]"] = $commandlist_r;
		$commandlist2["$commandlist_r[commandid]"] = $commandlist_r;
	}
	
	// List of all new commands to insert
	$SQL = array();
	
	foreach ($vbulletin->GPC['newcommand'] as $command)
	{
		if (strlen($command['input'][0]) <= 0)
		{
			// Empty command
			continue;
		}
		
		if ($command['input'][0] != '/')
		{
			// Ensure it starts with a slash
			$command['input'] = '/' . $command['input'];
		}
		
		if (in_array($command['input'], array('/pm', '/prune', '/editsticky', '/removenotice', '/removesticky', '/sticky', 
		  '/me', '/ignore', '/unignore', '/silence', '/unsilence', '/notice', '/setnotice', '/setsticky', '/ban', '/unban', '/createchat'
		)))
		{
			// Protected command
			continue;
		}
		
		if ($commandlist["$command[input]"])
		{
			// Already exists
			continue;
		}
		
		// This now exists
		$commandlist["$command[input]"] = true;
		
		// Schedule for database insertion
		$SQL[] = "(
			" . $vbulletin->userinfo['userid'] . ",
			" . $db->sql_prepare($command['input']) . ",
			" . ($command['useinput'] ? 1 : 0) . ",
			" . $db->sql_prepare($command['output']) . "
		)";
	}
	
	if (count($SQL))
	{
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_command
				(userid, command, useinput, output)
			VALUES
				" . implode(',', $SQL)
		);
	}
	
	foreach ($vbulletin->GPC['command'] as $commandid => $command)
	{
		if (!is_array($commandlist2["$commandid"]))
		{
			// Non-existant command
			continue;
		}
		
		if ($command['input'] == $commandlist2["$commandid"]['command']
			AND $command['useinput'] == $commandlist2["$commandid"]['useinput']
			AND $command['output'] == $commandlist2["$commandid"]['output']
		)
		{
			// Not changed
			continue;
		}
		
		if (!$command['input'] OR $command['input'] == '/')
		{
			// Delete
			$db->query_write("
				DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_command
				WHERE commandid = " . $commandid . "
					AND userid = " . $vbulletin->userinfo['userid'] . "
			");
		}
		else
		{
			// Update
			$db->query_write("
				UPDATE " . TABLE_PREFIX . "dbtech_vbshout_command
				SET 
					command = " . $db->sql_prepare($command['input']) . ",
					useinput = " . ($command['useinput'] ? 1 : 0) . ",
					output = " . $db->sql_prepare($command['output']) . "
				WHERE commandid = " . $commandid . "
					AND userid = " . $vbulletin->userinfo['userid'] . "
			");
		}
	}

	$vbulletin->url = 'vbshout.php?' . $vbulletin->session->vars['sessionurl'] . 'do=profile&action=customcommands';
	eval(print_standard_redirect('redirect_updatethanks'));
}

// #######################################################################
if (intval($vbulletin->versionnumber) == 3)
{
	// Create navbits
	$navbits = construct_navbits($navbits);	
	eval('$navbar = "' . fetch_template('navbar') . '";');
}
else
{
	$navbar = render_navbar_template(construct_navbits($navbits));	
}
construct_usercp_nav('dbtech_vbshout_' . $_REQUEST['action']);

$templater = vB_Template::create('USERCP_SHELL');
	$templater->register_page_templates();
	$templater->register('cpnav', $cpnav);
	if (method_exists($page_templater, 'render'))
	{
		// Only run this if there's anything to render
		$templater->register('HTML', $page_templater->render());
	}
	$templater->register('clientscripts', $clientscripts);
	$templater->register('navbar', $navbar);
	$templater->register('navclass', $navclass);
	$templater->register('onload', $onload);
	$templater->register('pagetitle', $pagetitle);
	$templater->register('template_hook', $template_hook);
print_output($templater->render());

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: button.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>