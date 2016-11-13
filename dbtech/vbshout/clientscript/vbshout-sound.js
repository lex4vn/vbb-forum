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

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

vBShout_Sounds_Obj = function()
{
	// Object variables
	this.vbshout;
	this.instanceid;
	this.sounds = {
		'shout' 	: false,
		'invite' 	: false,
		'pm' 		: false
	};
	this.muted = new Array();
	
	// #########################################################################
	// Initialiser for the functionality within
	this.init = function(instanceid)
	{
		this.instanceid = instanceid;
		eval("this.vbshout = vBShout" + instanceid + ";");		
	}
	
	// #########################################################################
	// Play the sound
	this.play = function(type)
	{
		if (this.vbshout.idle && !this.vbshout.idlesounds)
		{
			// We're not playing sounds while idle
			return false;
		}
		
		try
		{
			// Set the player var
			var player = YAHOO.util.Dom.get('dbtech_vbshout_sound_' + type + '_' + this.instanceid);
			
			if (!this.muted[this.vbshout.tab])
			{
				// Set the track
				player.SetVolume(100);
				
				// Play the noise
				player.Play();
			}			
		}
		catch(e) { }
	}
	
	// #########################################################################
	// Basically a fancy way of setting the image
	this.init_mute = function()
	{
		var mute = false;
		if (this.muted[this.vbshout.tab])
		{
			// It's muted
			mute = true;
		}
		
		// Set the image
		YAHOO.util.Dom.get('dbtech_shoutbox_soundbutton' + this.instanceid).src = 
			YAHOO.util.Dom.get('dbtech_shoutbox_soundbutton' + this.instanceid).src.replace('sound_' + (!mute ? 'off' : 'on') + '.png', 'sound_' + (mute ? 'off' : 'on') + '.png');
	}
	
	// #########################################################################
	// Flag this tab as muted
	this.toggle_mute = function()
	{
		var mute = true;
		if (this.muted[this.vbshout.tab])
		{
			// It was already muted
			mute = false;
		}
		
		// Set the image
		YAHOO.util.Dom.get('dbtech_shoutbox_soundbutton' + this.instanceid).src = 
			YAHOO.util.Dom.get('dbtech_shoutbox_soundbutton' + this.instanceid).src.replace('sound_' + (!mute ? 'off' : 'on') + '.png', 'sound_' + (mute ? 'off' : 'on') + '.png');
		
		// Set the muted for this type
		this.muted[this.vbshout.tab] = mute;
		
		// Save tab states
		this.vbshout.save_muted_tabs();
	}
}

/*======================================================================*\
|| #################################################################### ||
|| # Created: 21:48, Fri Jan 1st 2010								  # ||
|| # SVN: $Rev$							 							  # ||
|| #################################################################### ||
\*======================================================================*/