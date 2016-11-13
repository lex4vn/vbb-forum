<?php

global $vbulletin, $template_hook, $ad_location, $hook_location, $vbphrase, $vbshout;
		
// Global code
require_once(DIR . '/dbtech/vbshout/includes/global.php');

if (intval($vbulletin->versionnumber) == 4)
{
	if ($vbulletin->options['dbtech_vbshout_active'] AND $vbulletin->userinfo['posts'] >= $vbulletin->options['dbtech_vbshout_minposts'])
	{
		foreach ((array)$vbshout->cache['instance'] as $instance)
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
			
			// Determine what scripts to load
			//$vbshout_scriptload = (in_array(THIS_SCRIPT, explode(',', str_replace(' ', '', $vbulletin->options['dbtech_vbshout_includescripts']))) OR strlen($vbulletin->options['dbtech_vbshout_includescripts']) == 0);
			$vbshout_scriptload = (in_array(THIS_SCRIPT, explode(',', str_replace(' ', '', $instance['deployment']))) OR strlen($instance['deployment']) == 0);
						
			if (!$vbshout_scriptload)
			{
				// Not loading here
				continue;
			}
			
			// Setup the permissions
			$vbshout->init_permissions($instance['permissions']);
			
			if (!$vbshout->permissions['canviewshoutbox'])
			{
				// Can't view this instance
				continue;
			}
			
			// ######################## Start Value Fallback #########################
			// Maximum Characters Per Shout
			$vbulletin->options['dbtech_vbshout_maxchars'] = ($vbulletin->options['dbtech_vbshout_maxchars'] > 0 ? $vbulletin->options['dbtech_vbshout_maxchars'] : $vbulletin->options['postmaxchars']);
			$vbulletin->options['dbtech_vbshout_maxchars'] = ($vbshout->permissions['ismanager'] > 0 ? 0 : $vbulletin->options['dbtech_vbshout_maxchars']);
			
			// Maximum Images Per Shout
			$vbulletin->options['dbtech_vbshout_maximages'] = ($vbulletin->options['dbtech_vbshout_maximages'] > 0 ? $vbulletin->options['dbtech_vbshout_maximages'] : $vbulletin->options['maximages']);
			
			// Flood checks
			$vbulletin->options['dbtech_vbshout_floodchecktime'] = ($vbshout->permissions['ismanager'] ? 0 : $vbulletin->options['dbtech_vbshout_floodchecktime']);
			
			// Auto-idle
			$vbulletin->options['dbtech_vbshout_autoidle'] = ($vbshout->permissions['autoidle'] ? 1 : 0);
			
			// Render the shoutbox
			$shoutbox['vbshout'] = $vbshout->render($instance);
				
			if (THIS_SCRIPT != 'vbshout')
			{
				switch ($instance['autodisplay'])
				{
					case 1:
						// Below Navbar
						if (intval($vbulletin->versionnumber) != 3)
						{
							// vB4 Location
							$ad_location['global_below_navbar'] .= $shoutbox['vbshout'];
						}
						else
						{
							// vB3 code
							$ad_location['ad_navbar_below'] .= $shoutbox['vbshout'];
						}
						break;
						
					case 2:
						// Above Footer
						$template_hook['forumhome_below_forums'] .= $shoutbox['vbshout'];
						break;
						
					default:
						// Do nothing, maybe hook here?
						break;
				}
			}
			
			if ($instance['autodisplay'])
			{
				// We've already displayed it foo
				continue;
			}
			
			foreach ((array)explode(',', str_replace(' ', '', $instance['templates'])) as $templatename)
			{
				if (intval($vbulletin->versionnumber) != 3)
				{
					// Preregister the vBShout template
					vB_Template::preRegister($templatename, $shoutbox);
				}
				else
				{
					// vB3 code
					$GLOBALS['vbshout'] = $shoutbox['vbshout'];
				}
			}
		}
	}
}
?>