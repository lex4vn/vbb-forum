<?php
/*======================================================================*\
|| #################################################################### ||
|| # Ideal Web Technologies - Time Spent Online - Functions           # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright © 2010 Ideal Web Technologies  (www.idealwebtech.com)  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| #################################################################### ||
\*======================================================================*/

// Set the install time if not already set
if (!$vbulletin->options['iwt_timespentonline_install_timestamp'])
{
	$vbulletin->options['iwt_timespentonline_install_timestamp'] = TIMENOW;
	build_datastore('options', serialize($vbulletin->options), 1); 

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->options['iwt_timespentonline_install_timestamp'] . "' WHERE varname = 'iwt_timespentonline_install_timestamp'");
}

// Check and see if the system is active
if ($vbulletin->options['iwt_timespentonline_active'])
{
	// Check that the user is not a guest if he is then we dont need to run this code
	if ($vbulletin->userinfo['userid'])
	{
		// Unset the shutdown query so we can reset it up and add in our extra code to be run (query saving)
		unset($db->shutdownqueries['lastvisit']);

		$timespentonline = $vbulletin->userinfo['timespentonline'] + (TIMENOW - $vbulletin->userinfo['lastactivity']);

		if (TIMENOW - $vbulletin->userinfo['lastactivity'] > $vbulletin->options['cookietimeout'])
		{	
			//Dont update the time on the site as the user has been idle to long
			$db->shutdown_query("
				UPDATE " . TABLE_PREFIX . "user
				SET lastvisit = lastactivity,
				lastactivity = " . TIMENOW . "
				WHERE userid = " . $vbulletin->userinfo['userid'] . " AND " . TIMENOW . " > lastactivity
			", 'lastvisit');
		}
		else
		{
			//Update the time on the site and the users lastactivity time
			$db->shutdown_query("
				UPDATE " . TABLE_PREFIX . "user
				SET lastactivity = " . TIMENOW . ",
				timespentonline = $timespentonline
				WHERE userid = " . $vbulletin->userinfo['userid'] . " AND " . TIMENOW . " > lastactivity
			", 'lastvisit');
		}
	}
}

//Setup the micro time processing function
function calc_micro_timespent($time)
{
	global $vbphrase,$vbulletin;

	if ($time < 60 && $processedtime == '')
	{
		$processedtime = $vbphrase['iwt_timespentonline_na'];
	}
	else if ($time < 3600) //Lets see how many minutes we have
	{
		$minutes = floor($time/60);
		$processedtime = $minutes . ' ' . $vbphrase['iwt_timespentonline_minutes_micro'];
	}
	else if ($time < 86400) // Lets see how many hours we have
	{
		$hours = floor($time/3600);
		$processedtime = $hours . ' ' . $vbphrase['iwt_timespentonline_hours_micro'] . ' ' . calc_micro_timespent($time%3600);
	}  
	else // Lets see how many days we have
	{
		$days = floor($time/86400);
		$processedtime = $days . ' ' . $vbphrase['iwt_timespentonline_days_micro'] . ' ' . calc_micro_timespent($time%86400);
	}

	return $processedtime;
}

//Setup the time processing function
function calc_timespent($time, $bypasssanity = false)
{
	global $vbphrase,$vbulletin;

	$phraseext = '';

	if ($vbulletin->options['iwt_timespentonline_micropostbit'] && (THIS_SCRIPT=='private' || THIS_SCRIPT=='showthread' ||THIS_SCRIPT=='showpost'))
	{
		return calc_micro_timespent($time);
	}
	else if ($vbulletin->options['iwt_timespentonline_shortphrases'])
	{
		$phraseext = '_short';
	}

	if ($time < 1)
	{
		if(!$bypasssanity)
		{
			//Lets setup a default for users who havent been online since this hack was installed so that we show something at least
			$processedtime = $vbphrase['iwt_timespentonline_na'];
		}
		else
		{
			$processtimed = '';
		}
	}
	else if ($time == 1) // Lets see how many seconds we have
	{
		$processedtime = $time . " " . $vbphrase['iwt_timespentonline_second'.$phraseext];
	}
	else if ($time < 60) // Lets see how many seconds we have
	{
		$processedtime = floor($time) . " " . $vbphrase['iwt_timespentonline_seconds'.$phraseext];
	}
	else if ($time < 3600) //Lets see how many minutes we have
	{
		$minutes = floor($time/60);
		if ($minutes == 1) //We only have one so lets use singular phrase
		{
			$processedtime =  $minutes . ' ' . $vbphrase['iwt_timespentonline_minute'.$phraseext] . ' ' . calc_timespent($time%60,true);        
		}
		else
		{
			$processedtime = $minutes . ' ' . $vbphrase['iwt_timespentonline_minutes'.$phraseext] . ' ' . calc_timespent($time%60,true);        
		}
	}
	else if ($time < 86400) // Lets see how many hours we have
	{
		$hours = floor($time/3600);
		if ($hours == 1) //We only have one so lets use singular phrase
		{
			$processedtime = $hours . ' ' . $vbphrase['iwt_timespentonline_hour'.$phraseext] . ' ' . calc_timespent($time%3600,true);       
		}
		else
		{
			$processedtime = $hours . ' ' . $vbphrase['iwt_timespentonline_hours'.$phraseext] . ' ' . calc_timespent($time%3600,true);        
		}
	}  
	else if ($time < 604800) // Lets see how many days we have
	{
		$days = floor($time/86400);
		if ($days == 1) //We only have one so lets use singular phrase
		{
			$processedtime = $days . ' ' . $vbphrase['iwt_timespentonline_day'] . ' ' . calc_timespent($time%86400,true);        
		}
		else
		{
			$processedtime = $days . ' ' . $vbphrase['iwt_timespentonline_days'] . ' ' . calc_timespent($time%86400,true);        
		}
	}  
	else if ($time < 2592000) // Lets see how many weeks we have
	{
		$weeks = floor($time/604800);
		if ($weeks == 1) //We only have one so lets use singular phrase
		{
			$processedtime = $weeks . ' ' . $vbphrase['iwt_timespentonline_week'.$phraseext] . ' ' . calc_timespent($time%604800,true);       
		}
		else
		{
			$processedtime = $weeks . ' ' . $vbphrase['iwt_timespentonline_weeks'.$phraseext] . ' ' . calc_timespent($time%604800,true);        
		}
	}  
	else if ($time < 31536000) // Lets see how many months we have
	{
		$months = floor($time/2592000);
		if ($months == 1) //We only have one so lets use singular phrase
		{
			$processedtime = $months . ' ' . $vbphrase['iwt_timespentonline_month'.$phraseext] . ' ' . calc_timespent($time%2592000,true);        
		}
		else
		{
			$processedtime = $months . ' ' . $vbphrase['iwt_timespentonline_months'.$phraseext] . ' ' . calc_timespent($time%2592000,true);       
		}
	}
	else  
	{
		//Okay so we must have over a years worth of time if we made it this far so lets process years
		$years = floor($time/31536000);
		if ($years == 1) //We only have one so lets use singular phrase
		{
			$processedtime = $years . ' ' . $vbphrase['iwt_timespentonline_year'.$phraseext] . ' ' . calc_timespent($time%31536000,true);        
		}
		else
		{
			$processedtime = $years . ' ' . $vbphrase['iwt_timespentonline_years'.$phraseext] . ' ' . calc_timespent($time%31536000,true);        
		}
	}

	return $processedtime;
}

//Setup the time-per-day calculating function
function calc_timeperday($time,$joindate)
{
	global $vbphrase;
	global $vbulletin;

	if ($vbulletin->options['iwt_timespentonline_install_timestamp'] > $joindate)
	{
		$days = $vbulletin->options['iwt_timespentonline_install_timestamp'];
	}
	else
	{
		$days = $joindate;
	}

	$days = (TIMENOW - $days) / 86400;

	if ($days != '0')
	{
		$average = $time / $days;
	}
	else
	{
		$average = '0';
	}

	if ($average > 86400) // Lets throw in some safety for incase the user somehow had more than 24 hours per day
	{
		$average = 86400;
	}

	return calc_timespent($average);
}

/*======================================================================*\
|| ####################################################################
|| # Thats All Folks!!!
|| ####################################################################
\*======================================================================*/