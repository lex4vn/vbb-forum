<?php

// Put the two create tabs here for the sysmsg and notif
$chattabs['shoutnotifs'] = (($this->registry->options['dbtech_vbshout_shoutboxtabs'] & 1) ? "vBShout" . $this->instance['instanceid'] . ".add_tab('shoutnotifs', '" . $vbphrase['dbtech_vbshout_notifications'] . "', false);" : '');
$chattabs['systemmsgs'] = (($this->registry->options['dbtech_vbshout_shoutboxtabs'] & 2) ? "vBShout" . $this->instance['instanceid'] . ".add_tab('systemmsgs', '" . $vbphrase['dbtech_vbshout_system_messages'] . "', false);" : '');

$soundsettings = @unserialize($this->registry->userinfo['dbtech_vbshout_soundsettings']);
if (is_array($soundsettings["{$this->instance[instanceid]}"]))
{
	foreach ($soundsettings["{$this->instance[instanceid]}"] as $tabid => $bool)
	{
		$chatrooms .= "vBShout" . $this->instance['instanceid'] . ".muted['" . $tabid . "'] = " . intval($bool) . ';';
	}
	$chatrooms .= "vBShout" . $this->instance['instanceid'] . ".init_mute();";
}

if ($this->registry->options['dbtech_vbshout_archive_link'] AND $this->permissions['canviewarchive'])
{
	// Reshape the archive link somewhat
	$headerlink = ' [' . $start . $vbphrase['archive'] . $end . ']';
}

// Set the shout area location
if ($this->registry->userinfo['dbtech_vbshout_shoutarea'] AND $this->registry->userinfo['dbtech_vbshout_shoutarea'] != 'default')
{
	// User chose to override the location
	$direction = $this->registry->userinfo['dbtech_vbshout_shoutarea'];
}
else
{
	// User chose default location
	$direction = $this->registry->options['dbtech_vbshout_shoutarea'];
}

// Just default to this
$addedpx = 0;

if ($this->registry->userinfo['userid'] AND $this->permissions['canshout'])
{	
	switch ($direction)
	{
		case 'left':
		case 'right':
			// Either left or right big editor position
			$templater = vB_Template::create('dbtech_vbshout_shoutbox_shoutarea_vertical');
				$templater->register('direction', $direction);
			$shoutarea = $templater->render();
			
			// Register the shout area as being on the left or right
			$shoutbox->register('shoutarea', 	$shoutarea);
			$shoutbox->register('direction', 	$direction);
			$shoutbox->unregister('editortools');
			
			// Register the shout controls also			
			$templater = vB_Template::create('dbtech_vbshout_shoutbox_shoutcontrols');
				$templater->register('permissions', $this->permissions);
				$templater->register('editortools', $editortools);				
			$template_hook['dbtech_vbshout_shoutcontrols_below'] .= $templater->render();			
			break;
		
		case 'above':
		case 'below':
			// Padding
			$addedpx = 60;
			
			// Slim shout area above or below
			$templater = vB_Template::create('dbtech_vbshout_shoutbox_shoutarea_horizontal');
				$templater->register('direction', ($direction == 'above' ? 'bottom' : 'top'));
			$template_hook["dbtech_vbshout_shoutarea_{$direction}"] .= $templater->render();
			
			// Register the shout controls also			
			$templater = vB_Template::create('dbtech_vbshout_shoutbox_shoutcontrols');
				$templater->register('permissions', $this->permissions);
				$templater->register('editortools', $editortools);
			if (intval($this->registry->versionnumber) == 3)
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

if ($this->registry->userinfo['dbtech_vbshout_shoutboxsize'])
{
	$this->registry->options['dbtech_vbshout_height'] = $this->registry->userinfo['dbtech_vbshout_shoutboxsize'];	
}
?>