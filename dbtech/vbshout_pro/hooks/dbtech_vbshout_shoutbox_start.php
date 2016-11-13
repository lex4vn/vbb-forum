<?php
$suffix = ((THIS_SCRIPT == 'vbshout' AND $_REQUEST['do'] == 'detach') ? '_detached' : '');

if (self::$vbulletin->userinfo['dbtech_vbshout_shoutboxsize' . $suffix])
{
	$instance['options']['height'] = self::$vbulletin->userinfo['dbtech_vbshout_shoutboxsize' . $suffix];
	
	foreach (array(
		'dbtech_vbshout_activeusers',
		'dbtech_vbshout_editortools_pro',
		'dbtech_vbshout_memberaction_dropdown',
		'dbtech_vbshout_memberaction_dropdown_link',
		'dbtech_vbshout_shoutbox',
		'dbtech_vbshout_editortools',
		'dbtech_vbshout_shoutarea_horizontal',
		'dbtech_vbshout_shoutarea_vertical',			
		'dbtech_vbshout_shoutcontrols',			
		'dbtech_vbshout_shouttype_me',
		'dbtech_vbshout_shouttype_pm',
		'dbtech_vbshout_shouttype_shout',
		'dbtech_vbshout_shouttype_system'
	) AS $templatename)
	{
		if (intval(self::$vbulletin->versionnumber) > 3)
		{
			// Register the instance variable on all these
			vB_Template::preRegister($templatename, array('instance' => $instance));
		}
	}	
}

// Put the two create tabs here for the sysmsg and notif
$chattabs['shoutnotifs'] = (($instance['options']['shoutboxtabs'] & 1) ? "vBShout" . $instance['instanceid'] . ".add_tab('shoutnotifs', '" . $vbphrase['dbtech_vbshout_notifications'] . "', false);" : '');
$chattabs['systemmsgs'] = (($instance['options']['shoutboxtabs'] & 2) ? "vBShout" . $instance['instanceid'] . ".add_tab('systemmsgs', '" . $vbphrase['dbtech_vbshout_system_messages'] . "', false);" : '');

if (!is_array(self::$vbulletin->userinfo['dbtech_vbshout_soundsettings']))
{
	// Only unserialize if it's not already an array
	self::$vbulletin->userinfo['dbtech_vbshout_soundsettings'] = @unserialize(self::$vbulletin->userinfo['dbtech_vbshout_soundsettings']);
}

$soundsettings = self::$vbulletin->userinfo['dbtech_vbshout_soundsettings'];
if (is_array($soundsettings["$instance[instanceid]"]))
{
	foreach ($soundsettings["$instance[instanceid]"] as $tabid => $bool)
	{
		$chatrooms .= "vBShout" . $instance['instanceid'] . ".muted['" . $tabid . "'] = " . intval($bool) . ';';
	}
	$chatrooms .= "vBShout" . $instance['instanceid'] . ".init_mute();";
}

if ($instance['options']['archive_link'] AND $instance['permissions_parsed']['canviewarchive'])
{
	// Reshape the archive link somewhat
	$headerlink = ' [' . $start . $vbphrase['archive'] . $end . ']';
}

// Set the shout area location
if (self::$vbulletin->userinfo['dbtech_vbshout_shoutarea'] AND self::$vbulletin->userinfo['dbtech_vbshout_shoutarea'] != 'default')
{
	// User chose to override the location
	$direction = self::$vbulletin->userinfo['dbtech_vbshout_shoutarea'];
}
else
{
	// User chose default location
	$direction = $instance['options']['shoutarea'];
}

// Just default to this
$addedpx = 0;

if (self::$vbulletin->userinfo['userid'] AND $instance['permissions_parsed']['canshout'])
{	
	switch ($direction)
	{
		case 'left':
		case 'right':
			// Either left or right big editor position
			$templater = vB_Template::create('dbtech_vbshout_shoutarea_vertical');
				$templater->register('direction', $direction);
			$shoutarea = $templater->render();
			
			// Register the shout area as being on the left or right
			$shoutbox->register('shoutarea', 	$shoutarea);
			$shoutbox->register('direction', 	$direction);
			$shoutbox->unregister('editortools');
			
			// Register the shout controls also			
			$templater = vB_Template::create('dbtech_vbshout_shoutcontrols');
				$templater->register('permissions', $instance['permissions_parsed']);
				$templater->register('editortools', $editortools);				
			$template_hook['dbtech_vbshout_shoutcontrols_below'] = $templater->render();			
			break;
		
		case 'above':
		case 'below':
			// Padding
			$addedpx = 60;
			
			// Slim shout area above or below
			$templater = vB_Template::create('dbtech_vbshout_shoutarea_horizontal');
				$templater->register('direction', ($direction == 'above' ? 'bottom' : 'top'));
			$template_hook["dbtech_vbshout_shoutarea_{$direction}"] = $templater->render();
			
			// Register the shout controls also			
			$templater = vB_Template::create('dbtech_vbshout_shoutcontrols');
				$templater->register('permissions', $instance['permissions_parsed']);
				$templater->register('editortools', $editortools);
			if (intval(self::$vbulletin->versionnumber) == 3)
			{
				$template_hook["dbtech_vbshout_shoutarea_{$direction}"] .= '<div style="padding-' . ($direction == 'above' ? 'bottom' : 'top') . ':6px;">' . $templater->render() . '</div>';
			}
			else
			{
				$template_hook["dbtech_vbshout_shoutarea_{$direction}"] .= '<li style="padding-' . ($direction == 'above' ? 'bottom' : 'top') . ':6px;">' . $templater->render() . '</li>';
			}

			// Register the shout area as being above or below
			$shoutbox->register('direction', 	'left');
			$shoutbox->register('csshack', 		$csshack);
			$shoutbox->register('shoutarea', 	'');
			$shoutbox->unregister('editortools');
			break;
	}
}
$domenu = true;
?>