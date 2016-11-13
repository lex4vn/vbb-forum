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

// #############################################################################
// Setup the main object
vBShout_Obj = function()
{
	// Object variables
	this.ajax			= new Array();	// Array of AJAX objects
	this.frames			= new Array();	// A list of all the frames we interact with
	this.timers			= new Array();	// A list of all the timers we keep track of
	this.lookup			= new Array();	// A list of all the users we have PMed at some point (userid - username)
	this.lookup2		= new Array();	// A list of all the chat rooms we have talked in at some point (chatroomid - roomname)
	this.aoptime		= new Array();	// Set this to when we last fetched from the database	
	this.muted			= new Array();	// Wrapper for the sounds object mute
	this.editor 		= {				// A list of all editor styling we may apply
		'bold' 		: '',
		'italic'	: '',
		'underline'	: '',
		'color'		: '',
		'font'		: 'Tahoma',
		'size'		: '11px'
	};
	this.tabs			= {				// A list of all the tabs we are playing with
		'shouts'		: true,
		'activeusers'	: true
	};
	this.smilies;						// Captain Placeholder of the Smiley Popup Functions Brigade
	this.fonts;							// Captain Placeholder of the Font Dropdown Functions Brigade
	this.colors;						// Captain Placeholder of the Color Dropdown Functions Brigade
	this.fontstyles;					// Captain Placeholder of the Bold/Italic/Underline Button Functions Brigade
	this.sounds;						// The sounds object
	this.get;							// The sounds object
	
	// Integer variables
	this.instanceid		= 1;			// What instance we are working with
	this.floodcheck		= 3;			// How big a delay (in seconds) between shouts
	this.refreshtime	= 5;			// How often (in seconds) to auto-refresh
	this.refreshtime2	= 0;			// How often (in seconds) to auto-refresh
	this.idlerefreshtime= 0;			// How often (in minutes) to auto-refresh
	this.idlelimit		= 180;			// A counter for how long we've been idle
	this.idletime		= 0;			// A counter for how long we've been idle
	this.scrollpos		= 0;			// Helper to determine whether the scrollbar has moved
	this.maxlength		= 256;			// The maximum length of a shout
	this.shoutid		= 0;			// Set this to > 0 when editing a shout
	this.lastshout		= 0;			// Set this to when we last shouted
	this.pmuserid		= 0;			// Userid of the person we are PMing
	this.menuid			= 0;			// Open username dropdown menu
	this.pmtime			= 0;			// PM Time
	this.countdown		= 0;	
	this.shoutDelay		= 0;	
	
	// Boolean variables
	this.aop			= false;		// Whether we're using the optimisation protocol
	this.detached		= false;		// Whether we're viewing the shoutbox in detached mode
	this.idle			= false;		// Whether we're idle at all
	this.inAJAX			= false;
	this.pauseCountdown	= false;	
	this.hasFetched		= false;	
	
	// String variables
	this.shoutorder		= 'ASC';		// The direction in which we're shouting (DESC = Newest First - ASC = Oldest First)
	this.editorid		= 'dbtech_shoutbox_editor';		// ID of the editor
	this.tab			= 'shouts';		// Where we are by default
	this.shoutstore		= '';
	this.aopfile		= '';
	this.lastAjax		= '';
	this.templates		= '';


	// #########################################################################
	// Initialise the Shoutbox
	this.init = function()
	{
		if (!AJAX_Compatible)
		{
			// AJAX won't work, this is foar srs.
			this.set_message(vbphrase['dbtech_vbshout_ajax_disabled'], 'error');
			// REPLACESTRING
			
			return false;
		}
		
		// Ensure we have this in minutes
		this.idlerefreshtime 	= this.idlerefreshtime * 60;
		
		// Backup
		this.refreshtime2 		= this.refreshtime;
		
		eval("this.vbshout = vBShout" + this.instanceid + ";");
		
		// Set our frames
		this.frames['shoutframe'] 	= YAHOO.util.Dom.get('dbtech_shoutbox_frame' + this.instanceid);			// The surrounding master frame
		this.frames['content'] 		= YAHOO.util.Dom.get('dbtech_shoutbox_content' + this.instanceid);			// The main shoutbox area
		this.frames['editor'] 		= YAHOO.util.Dom.get(this.editorid + this.instanceid);						// The shoutbox editor
		this.frames['tabs'] 		= YAHOO.util.Dom.get('dbtech_shoutbox_tabs' + this.instanceid);				// The shoutbox tabs
		this.frames['activeusers']	= YAHOO.util.Dom.get('dbtech_shoutbox_activeusers' + this.instanceid);		// The Active Users count
		this.frames['charcount']	= YAHOO.util.Dom.get('dbtech_shoutbox_remainingchars' + this.instanceid);	// The Remaining Characters count
		
		// Initialise any enabled editor tools
		this.init_editor_tools();
		
		if (this.frames['editor'])
		{
			// Check the input length
			this.check_length();
		}
		
		try
		{
			this.templates = eval("(" + this.templates + ")");
			//this.templates = jQuery.parseJSON(this.templates);
		} catch (e) {}
		
		// Finally fetch the shouts
		//this.fetch(this.tab, true);
		
		/*
		if (this.idle)
		{
			// We should be flagged as idle
			this.flag_idle();
		}
		else
		{
			// Schedule idle check
			this.check_idle();
			
			// Schedule fetching shouts 
			this.schedule_refresh();
		}
		*/
		
		/*
		if (!this.hasFetched)
		{
			// Fetch shouts nao
			this.hasFetched = true;			
			this.fetch(this.tab, true);
		}
		*/		
		
		// Initial shouts fetcing
		this.get = setInterval("vBShout" + this.instanceid + ".timer();", 1000);	

		// Store the phrased variable
		var everyone 	= YAHOO.util.Dom.get('dbtech_shoutbox_everyone' + this.instanceid);
		if (everyone)
		{
			this.everyone 	= everyone.innerHTML;
		}
	}
	
	/*
	// #########################################################################	
	// Repeating function to check if we're idle
	this.check_idle = function()
	{
		if (this.idlelimit == 0 || this.idle)
		{
			// We're not even checking for idle or we're already idle
			return false;
		}
		
		// Log the error to the console
		//console.log(this.timestamp() + "Checking for idle... Have been idle for %s seconds. Limit: %s seconds.", this.idletime, this.idlelimit);		
		
		if (this.idletime >= this.idlelimit)
		{
			// Yep, we're idle
			this.idle = true;
			
			if (this.idlerefreshtime)
			{
				// We're refreshing while idle
				this.refreshtime = this.idlerefreshtime;
			}
			else
			{
				// Clear the shout refresh interval
				clearInterval(this.timers['tab']);			
			}
			
			// We're idle
			this.flag_idle();
			
			// No need to increment it further
			return false;
		}
		
		// Check again in 1 sec
		setTimeout("vBShout" + this.instanceid + ".check_idle()", 1000);
				
		// Increment the time we've been idle
		this.idletime++;
	}
	*/
	
	// #########################################################################	
	// Shorthand function to flag as idle
	this.flag_idle = function()
	{
		this.set_message(vbphrase['dbtech_vbshout_flagged_idle']
			.replace('%link%', 'return vBShout' + this.instanceid + '.unIdle(true);'),
			'notice'
		);
	}
	
	/*
	// #########################################################################
	// We're no longer idle
	this.unidle = function()
	{
		// Log the error to the console
		console.log(this.timestamp() + 'Removing idle...');		
		
		if (this.idlerefreshtime)
		{
			// We're refreshing while idle
			this.refreshtime = this.refreshtime2;
		}
		
		// We're no longer idle
		this.idletime 	= 1;
		this.idle 		= false;
		
		// Hide the error message
		this.hide_message('notice');
		
		// Begin the timer again
		setTimeout("vBShout" + this.instanceid + ".check_idle()", 1000);
		
		// We're no longer idle, instantly fetch shouts unless we're posting
		this.fetch(this.tab);
		
		// Begin scheduling refreshes of the current tab
		this.schedule_refresh();
	}
	*/
	
	
	
	/**
	 * Countdown function for refresh time.
	 */
	this.timer = function()
	{
		if (this.shoutDelay > 0)
		{
			// We've shouted x seconds ago
			this.shoutDelay--;
		}
		
		if (this.pauseCountdown)
		{
			// We're not doing anything atm
			return;
		}
		
		this.idletime = parseInt(this.idletime);
		this.idlelimit = parseInt(this.idlelimit);
		
		// Increment idle time
		this.idletime++;
		
		if ((this.idletime >= this.idlelimit && this.idlelimit > 0) || this.idle)
		{
			// We're pausing the countdown
			this.pauseCountdown = true;
			
			// Yep, we're idle
			this.idle = true;
			
			/*
			if (this.idlerefreshtime)
			{
				// We're refreshing while idle
				this.refreshtime = this.idlerefreshtime;
			}
			*/
			
			// We're idle
			this.flag_idle();			
			
			if (!this.hasFetched)
			{
				// Fetch shouts nao
				this.fetch(this.tab, true);
				this.hasFetched = true;
			}
			
			return;
		}		
		
		if (--this.countdown > 0)
		{
			// Still not fetching :(
			console.log(this.timestamp() + vbphrase['dbtech_vbshout_fetching_shouts_in_x_seconds']
				.replace('%seconds%', this.countdown)
			);
		}
		else
		{
			// Fetch shouts nao
			this.fetch();
		}			
	},
	
	/**
	 * Resets the idle timer
	 * 
	 * @param boolean Whether we're un-pausing
	 */
	this.unIdle = function(unPause)
	{
		// Hide the error message
		this.hide_message('notice');
		
		// Reset idle timer
		this.idletime = 0;
		
		// Reset idle flag
		this.idle = false;		
		
		if (unPause)
		{
			// Unpause
			this.fetch();
			this.pauseCountdown = false;
			this.countdown = this.refreshtime;
		}
	},
	
	
	
	
	
	
	// #########################################################################	
	// Shorthand to schedule a refresh of the shouts
	this.schedule_refresh = function()
	{
		// Log a debug message to the console
		console.log(this.timestamp() + "Scheduling fetching current tab every %s seconds", this.refreshtime);
		
		// Clear the shout refresh interval
		clearInterval(this.timers['tab']);
		
		// Setup the schedule
		this.timers['tab'] = setInterval("vBShout" + this.instanceid + ".fetch();", (this.refreshtime * 1000));		
	}
	
	// #########################################################################	
	// Initialises various editor tools
	this.init_editor_tools = function()
	{
		if (typeof vBShout_Smilies_Obj != 'undefined')
		{
			// Initialise the smilies reference
			this.smilies = new vBShout_Smilies_Obj();
			
			// Set the editor id
			this.smilies.init(this.editorid, this.instanceid);
		}
		
		if (typeof vBShout_Fonts_Obj != 'undefined')
		{
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_fontfield' + this.instanceid))
			{			
				// Intialise the fonts reference
				this.fonts = new vBShout_Fonts_Obj();
				
				// Begin hacking the fonts dropdown
				this.fonts.init('dbtech_shoutbox_editor_wrapper', this.editor['font'], this.instanceid);
			}
		}
		else
		{
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_fontfield' + this.instanceid))
			{
				var fontssel = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_fontfield' + this.instanceid);
				if (this.editor['font'] != '')
				{
					for (var i = 0; i < fontssel.options.length; i++)
					{
						if (fontssel.options[i].value == this.editor['font'])
						{
							fontssel.selectedIndex = i;
							break;
						}
					}
				}
			}			
		}
		
		if (typeof vBShout_Sizes_Obj != 'undefined')
		{
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_popup_sizename' + this.instanceid))
			{			
				// Intialise the fonts reference
				this.sizes = new vBShout_Sizes_Obj();
				
				// Begin hacking the sizes dropdown
				this.sizes.init('dbtech_shoutbox_editor_wrapper', this.editor['size'], this.instanceid);
			}
		}
		else
		{
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_size_bar' + this.instanceid))
			{
				var sizessel = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_size_bar' + this.instanceid);
				if (this.editor['size'] != '')
				{
					for (var i = 0; i < sizessel.options.length; i++)
					{
						if (sizessel.options[i].value == this.editor['size'])
						{
							sizessel.selectedIndex = i;
							break;
						}
					}
				}
			}
		}
		
		if (typeof vBShout_Colors_Obj != 'undefined')
		{
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_color_bar' + this.instanceid))
			{						
				// Intialise the colors reference
				this.colors = new vBShout_Colors_Obj();
				
				// Begin hacking the colors dropdown
				this.colors.init('dbtech_shoutbox_editor_wrapper', this.editor['color'], this.instanceid);
			}
		}
		else
		{
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_color_bar' + this.instanceid))
			{			
				var colorssel = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_color_bar' + this.instanceid);
				if (this.editor['color'] != '')
				{
					for (var i = 0; i < colorssel.options.length; i++)
					{
						if (colorssel.options[i].value == this.editor['color'])
						{
							colorssel.selectedIndex = i;
							break;
						}
					}
				}
			}
		}
		
		if (typeof vBShout_FontStyle_Obj != 'undefined')
		{
			// Intialise the font styles reference
			this.fontstyles = new vBShout_FontStyle_Obj();
			
			// Begin hacking the font styles buttons
			this.fontstyles.init('dbtech_shoutbox_editor_wrapper', this.editor['bold'], this.editor['italic'], this.editor['underline'], this.instanceid);
		}
		else
		{
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_bold' + this.instanceid))
			{
				var boldbtn = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_bold' + this.instanceid);
				if (this.editor['bold'] != '')
				{
					boldbtn.value = 'B [*]';
				}
			}			
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_italic' + this.instanceid))
			{
				var italicbtn = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_italic' + this.instanceid);
				if (this.editor['italic'] != '')
				{
					italicbtn.value = 'I [*]';
				}
			}			
			if (YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_underline' + this.instanceid))
			{
				var underlinebtn = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_underline' + this.instanceid);
				if (this.editor['underline'] != '')
				{
					underlinebtn.value = 'U [*]';
				}
			}			
		}
		
		if (typeof vBShout_Sounds_Obj != 'undefined')
		{
			// Intialise the sounds reference
			this.sounds = new vBShout_Sounds_Obj();
			
			// Init the sounds
			this.sounds.init(this.instanceid);
		}
	}
	
	// #########################################################################	
	// Init mutes
	this.init_mute = function()
	{
		if (typeof vBShout_Sounds_Obj != 'undefined' && YAHOO.util.Dom.get('dbtech_shoutbox_soundbutton' + this.instanceid))
		{
			// Copy over muted object
			this.sounds.muted = this.muted;
			
			// Init the mute
			this.sounds.init_mute();
		}
	}
	
	// #########################################################################	
	// Toggles the bold button (3.8 onry)
	this.toggle_bold = function()
	{
		var boldbtn = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_bold' + this.instanceid);
		if (this.editor['bold'] == '')
		{
			this.update_style_property('bold', 'bold');			
			boldbtn.value = 'B [*]';
		}
		else
		{
			this.update_style_property('bold', '');			
			boldbtn.value = 'B';
		}
	}
	
	// #########################################################################	
	// Toggles the italic button (3.8 onry)
	this.toggle_italic = function()
	{
		var italicbtn = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_italic' + this.instanceid);
		if (this.editor['italic'] == '')
		{
			this.update_style_property('italic', 'italic');			
			italicbtn.value = 'I [*]';
		}
		else
		{
			this.update_style_property('italic', '');			
			italicbtn.value = 'I';
		}
	}
	
	// #########################################################################	
	// Toggles the underline button (3.8 onry)
	this.toggle_underline = function()
	{
		var underlinebtn = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_cmd_underline' + this.instanceid);
		if (this.editor['underline'] == '')
		{
			this.update_style_property('underline', 'underline');			
			underlinebtn.value = 'U [*]';
		}
		else
		{
			this.update_style_property('underline', '');			
			underlinebtn.value = 'U';
		}
	}
	
	// #########################################################################	
	// Toggles the fontstyle (3.8 onry)
	this.toggle_fontstyle = function()
	{
		var fontssel = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_fontfield' + this.instanceid);
		
		this.update_style_property('font', fontssel.value);			
	}
	
	// #########################################################################	
	// Toggles the fontcolor (3.8 onry)
	this.toggle_fontcolor = function()
	{
		var colorssel = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_color_bar' + this.instanceid);
		
		this.update_style_property('color', colorssel.value);			
	}
	
	// #########################################################################	
	// Toggles the size (3.8 onry)
	this.toggle_size = function()
	{
		var sizessel = YAHOO.util.Dom.get('dbtech_shoutbox_editor_wrapper_size_bar' + this.instanceid);
		
		this.update_style_property('size', sizessel.value);			
	}
	
	// #########################################################################	
	// Updates the style properties based on the Editor Tools selections
	this.update_style_property = function(type, value, noupdate)
	{
		// Default to the same property as the style type
		var property 	= type;
		var setValue	= value;
		switch (type)
		{
			case 'bold':
				property 	= 'fontWeight';
				setValue = (value ? type : '');
				break;
				
			case 'italic':
				property 	= 'fontStyle';
				setValue = (value ? type : '');
				break;
				
			case 'underline':
				property 	= 'textDecoration';
				setValue = (value ? type : '');
				break;
				
			case 'font':
				property 	= 'fontFamily';
				break;
				
			case 'size':
				property 	= 'fontSize';
				break;
		}
		
		// Log a debug message to the console
		console.log(this.timestamp() + "Style property %s set. Value: %s.", type, setValue);
		
		if (type == 'size')
		{
			// Set the editor style
			eval("this.frames['shoutframe'].style." + property + " = '" + setValue + "';");
		}
		else
		{
			// Set the editor style
			eval("this.frames['editor'].style." + property + " = '" + setValue + "';");
		}
		
		if (this.editor[type] != value && !noupdate)
		{
			// Log a debug message to the console
			console.log(this.timestamp() + "Style property %s changed. Old value: %s - New value: %s", type, this.editor[type], value);
			
			// Change the editor type
			this.editor[type] = value;
			
			// Save the style properties
			this.save_editor_styles();
		}
	}
	
	// #########################################################################	
	// Clear the contents of the editor
	this.clear = function()
	{
		// Log a debug message to the console
		console.log(this.timestamp() + "Removing contents of the editor.");
		
		// Clear the contents of the editor
		this.frames['editor'].value = '';
		this.check_length();
	}
	
	// #########################################################################	
	// Inserts a command
	this.set_command = function(cmd)
	{
		// Clear the contents of the editor
		this.frames['editor'].value = cmd + ' ';
		this.frames['editor'].focus();
		this.check_length();
		
		return false;
	}
	
	// #########################################################################	
	// Shorthand for command
	this.chat_invite = function()
	{
		// Set the invite command
		this.set_command('/invite');
		
		return false;
	}
	
	// #########################################################################	
	// Function to show the shout reports page
	this.show_reports = function()
	{
		window.location.href = 'vbshout.php?' + SESSIONURL + 'do=reportlist&instanceid=' + this.instanceid;
	}

	// #########################################################################	
	// Function to begin PMing an user
	this.create_pm = function(userid, username)
	{
		if (!this.lookup[userid])
		{
			// Add the username to the lookup
			this.lookup[userid] = username;
		}
		
		// Creates a new PM to a given user
		this.add_tab('pm_' + userid + '_', username, true, "show_pm_tab('" + userid + "')", "close_pm_tab(this, '" + userid + "')");
		
		// Shows the PM tab
		this.show_pm_tab(userid);
		
		if (this.menuid)
		{
			// Close any open menus we have
			this.toggle_menu(this.menuid);
		}
		
		return false;
	}
	
	// #########################################################################	
	// Function to display a PM tab
	this.show_pm_tab = function(userid)
	{
		if (this.tab == 'pm_' + userid + '_')
		{
			// We're already viewing this PM tab.
			return false;
		}
		
		// Blank this out just in case we have no PMs
		this.frames['content'].innerHTML = '';
		
		// Show the tab
		this.show_tab('pm_' + userid + '_', true);
		
		// Set new PM User
		this.pmuserid = userid;
		
		// Update who we're shouting to
		this.set_shout_target(this.lookup[userid]);
		
		return false;
	}
	
	// #########################################################################	
	// Function to close a PM tab	
	this.close_pm_tab = function(tabobj, userid)
	{
		if (this.tab == 'pm_' + userid + '_')
		{
			// We were viewing this PM tab.
			this.pmuserid = 0;
			this.set_shout_target(false);
		}
		
		// Close the tab
		this.close_tab(tabobj, 'pm_' + userid + '_');
		
		return false;
	}
	
	// #########################################################################	
	// Function to create a chat room tab
	this.create_chatroom_tab = function(chatroomid, roomname, canclose)
	{
		// Creates a new PM to a given user
		this.add_tab('chatroom_' + chatroomid + '_', roomname, canclose, "show_chatroom_tab('" + chatroomid + "')", "close_chatroom_tab(this, '" + chatroomid + "')");
		
		if (this.menuid)
		{
			// Close any open menus we have
			this.toggle_menu(this.menuid);
		}
		
		return false;
	}
	
	// #########################################################################	
	// Function to display a PM tab
	this.show_chatroom_tab = function(chatroomid)
	{
		if (this.tab == 'chatroom_' + chatroomid + '_' + this.instanceid)
		{
			// We're already viewing this chatroom tab.
			return false;
		}
		
		// Blank this out just in case we have no PMs
		this.frames['content'].innerHTML = '';
		
		// Show the tab
		this.show_tab('chatroom_' + chatroomid + '_', true);
		
		// Set new PM User
		this.chatroomid = chatroomid;
		
		// Update who we're shouting to
		//this.set_shout_target(this.lookup[userid]);
		
		return false;
	}
	
	// #########################################################################	
	// Function to close a chatroom tab	
	this.close_chatroom_tab = function(tabobj, chatroomid)
	{
		if (this.tab == 'chatroom_' + chatroomid + '_')
		{
			// We were viewing this PM tab.
			this.chatroomid = 0;
			//this.set_shout_target(false);
		}
		
		if (confirm('Are you sure you wish to leave this chat room?'))
		{
			// Leave the chatroom with shit and stuff
			this.leave_chatroom(chatroomid);
		
			// Close the tab
			this.close_tab(tabobj, 'chatroom_' + chatroomid + '_');
		}
		
		return false;
	}
	
	// #########################################################################	
	// Adds a tab
	this.add_tab = function(tabid, text, canclose, showfunc, closefunc)
	{
		// Ensure these are not nil
		showfunc 	= showfunc 	? showfunc 	: "show_tab('" + tabid + "')";
		closefunc 	= closefunc ? closefunc : "close_tab(this, '" + tabid + "')";
		
		if (this.tabs[tabid])
		{
			// Show this tab instead
			eval('this.' + showfunc + ';');
			return false;
		}
		
		// This tab now exists
		this.tabs[tabid] = true;
		
		var html = '';
		if (canclose)
		{
			// We can close the tab
			html += ' [<a href="javascript://" onclick="return vBShout' + this.instanceid + '.' + closefunc + ';">X</a>]';
		}
		
		// Create the tab
		cellposition = this.frames['tabs'].rows[0].cells.length - 1;
		newtab = this.frames['tabs'].rows[0].insertCell(cellposition);
		newtab.className = 'dbtech_vbshout_tabcontainer';
		newtab.innerHTML = '<div id="' + tabid + this.instanceid + '" class="dbtech_vbshout_tabs alt2"><a href="javascript://" onclick="return vBShout' + this.instanceid + '.' + showfunc + ';" id="tabobj_' + tabid + this.instanceid + '">' + text + '</a>' + html + '</div>';
	}
	
	// #########################################################################	
	// Displays the contents of a tab
	this.show_tab = function(tabid, force)
	{
		if (this.tab != tabid)
		{
			// Log a debug message to the console
			console.log(this.timestamp() + "Switching from %s to %s.", this.tab, tabid);		
		
			if (this.tab.indexOf('pm', 0) > -1)
			{
				// Reset PM info
				this.pmuserid = 0;
				this.set_shout_target(false);
			}
			
			if (this.tab.indexOf('chatroom', 0) > -1)
			{
				// Reset chatroom info
				this.chatroomid = 0;
			}
			
			// Set tab styles
			YAHOO.util.Dom.get(this.tab + this.instanceid).className = 'dbtech_vbshout_tabs alt2';
			YAHOO.util.Dom.get(tabid + this.instanceid).className = 'dbtech_vbshout_tabs alt';
			YAHOO.util.Dom.removeClass(YAHOO.util.Dom.get(tabid + this.instanceid), 'dbtech_vbshout_highlight');
			
			// Set the new content
			this.tab = tabid;
			
			if (typeof vBShout_Sounds_Obj != 'undefined' && YAHOO.util.Dom.get('dbtech_shoutbox_soundbutton' + this.instanceid) != null)
			{
				// Refresh muting
				this.sounds.init_mute();
			}
			
			
			// We're changing shit, so force
			force = true;
		}
		
		//if (force)
		//{
			// We're needing new content
			this.fetch(tabid, true);
		//6}
		
		return false;
	}
	
	// #########################################################################	
	// Closes a tab
	this.close_tab = function(tabobj, tabid)
	{
		// This tab no longer exists
		this.tabs[tabid] = false;
		
		try
		{
			// Removes the tab object
			tabobj.parentNode.parentNode.parentNode.removeChild(tabobj.parentNode.parentNode);
		}
		catch (e)
		{
			// No idea why this is happening
			this.tab = tabid;
		}
		
		if (this.tab == tabid)
		{
			// We revert to fetching shouts
			this.tab = 'shouts';
			
			// Set tab styles
			YAHOO.util.Dom.get(this.tab + this.instanceid).className = 'dbtech_vbshout_tabs alt';
			
			 // Re-fetches shouts
			this.fetch('shouts', true);
		}
		
		// Log a debug message to the console
		console.log(this.timestamp() + "Switching from %s to %s.", tabid + this.instanceid, this.tab + this.instanceid);		
		
		return false;
	}
	
	// #########################################################################	
	// Sets the shout target
	this.set_shout_target = function(username)
	{
		// Fetch the two objects
		var everyone 	= YAHOO.util.Dom.get('dbtech_shoutbox_everyone' + this.instanceid);
		var user 		= YAHOO.util.Dom.get('dbtech_shoutbox_pmuser' + this.instanceid);
		
		if (everyone)
		{
			if (!username)
			{
				// Reverting to everyone
				everyone.innerHTML		= this.everyone;
			}
			else
			{
				// Setting an username
				everyone.innerHTML 		= PHP.trim(username);
			}
		}
	}
	
	// #########################################################################	
	// Resets the shout target
	this.reset_shout_target = function()
	{
		// Clear out this shit
		this.pmuserid = 0;
		this.set_shout_target(false);
		this.show_tab('shouts');
		
		return false;
	}
	
	
	// #######################################################################
	// ######################## AJAX FUNCTIONS ###############################
	// #######################################################################
	
	// #########################################################################
	// Fetches a given type of content
	this.fetch = function(type, force, includeaoptime)
	{
		// Reset the countdown
		this.pauseCountdown = true;
		
		if (!type)
		{
			// Avoid refreshing problem
			type = this.tab;
		}
		
		if ((!this.ajax['save'] && !this.ajax['fetch'] && !this.idle) || force == true)
		//if ((!this.inAJAX && !this.idle) || force == true)
		{
			// Initialise this
			var extraparams = '';
			
			/*
			if (this.idle)
			{
				// We're in idle
				extraparams += '&idle=1';
				force = false;
			}
			*/
			
			if (this.pmuserid)
			{
				// Set who we're PMing
				extraparams += '&pmuserid=' + this.pmuserid;
			}
			
			// Log a debug message to the console
			console.log(this.timestamp() + 'Fetching ' + type + this.instanceid + '...');			
			
			if (typeof this.aoptime[this.tab] == 'undefined')
			{
				force = true;
			}
			
			if (this.aop && !force)
			{
				includeaoptime = true;
			}
			
			if (includeaoptime)
			{
				extraparams += '&aoptime=' + this.aoptime[this.tab];
				console.log(this.timestamp() + 'AOP Time ' + this.aoptime[this.tab] + '...');				
			}
			
			switch (type)
			{
				case 'shout':
					extraparams += '&shoutid=' + this.shoutid;
					break;
			}
			
			if (this.detached)
			{
				// We're in detached mode
				extraparams += '&detached=1';
			}
			
			for (var i in this.tabs)
			{
				if (this.tabs[i])
				{
					// This is an active tab
					extraparams += '&tabs[' + i + ']=1';
				}
			}
			
			if (this.chatroomid)
			{
				// Set who we're PMing
				extraparams += '&chatroomid=' + this.chatroomid;
			}			
			
			// Set shout order
			extraparams += '&shoutorder=' + this.shoutorder;
			
			// Set tab id
			extraparams += '&pmtime=' + this.pmtime;		
			
			// Set tab id
			extraparams += '&tabid=' + this.tab;		
			
			// Set type id
			extraparams += '&type=' +  PHP.urlencode(type);		
			
			if ((this.aop && !force) && type != 'activeusers')
			{
				if (type == 'shouts' || type == 'aop')
				{
					// Fetch shouts
					type = 'shouts' + this.instanceid;
				}
				
				if (type == 'shoutnotifs' || type == 'systemmsgs')
				{
					// Fetch shouts
					type = type + this.instanceid;
				}
				
				if (type.substring(type.length - 1, type.length) == '_')
				{
					// Global chatroom
					type = type + '0';
				}
				
				this.aopfile = 'dbtech/vbshout/aop/' + type + '.txt';
				this.filemtime();
				//alert('ere');
			}
			else
			{
				// Execute AJAX
				this.ajax_call('fetch', extraparams);
			}
		}
	}
	
	// #########################################################################	
	// Function to create a chat room
	this.create_chatroom = function()
	{
		// Initialise this
		var extraparams = '';
		var roomname = YAHOO.util.Dom.get('roomname' + this.instanceid).value;

		// Set title
		extraparams += '&title=' + PHP.urlencode(roomname);
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);		
		
		// Execute AJAX
		this.ajax_call('createchat', extraparams);
		
		return false;
	}
	
	// #########################################################################	
	// Leaving a chat room
	this.leave_chatroom = function(chatroomid, is_popup)
	{
		if (this.ajax['leavechat'] || this.ajax['joinchat'])
		{
			// We're not doing anything else yet
			return false;
		}
		
		// Initialise this
		var extraparams = '';
		
		// Set chatroom id
		extraparams += '&chatroomid=' + chatroomid;
		
		if (is_popup)
		{
			// Set status id
			extraparams += '&status=1';
		}
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);		
		
		// Execute AJAX
		this.ajax_call('leavechat', extraparams);
	}
	
	// #########################################################################	
	// Joining a chat room
	this.join_chatroom = function(chatroomid)
	{
		if (this.ajax['leavechat'] || this.ajax['joinchat'])
		{
			// We're not doing anything else yet
			//return false;
		}
		
		// Initialise this
		var extraparams = '';
		
		// Set chatroom id
		extraparams += '&chatroomid=' + chatroomid;
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);		
		
		// Execute AJAX
		this.ajax_call('joinchat', extraparams);
	}
		
	// #########################################################################	
	// User Management actions
	this.usermanage = function(action, userid)
	{
		// Initialise this
		var extraparams = '';
		
		var message = '';
		switch (action)
		{
			case 'banunban':
				message = 'Are you sure you wish to ban / unban this user?';
				break;
			
			case 'silenceunsilence':
				message = 'Are you sure you wish to silence / unsilence this user?';
				break;
			
			case 'ignoreunignore':
				message = 'Are you sure you wish to ignore / unignore this user?';
				break;
			
			case 'pruneshouts':
				message = 'Are you sure you wish to prune all shouts from this user?';
				break;
			
			case 'chatremove':
				message = 'Are you sure you wish to remove this user from this Chat Room?';
				break;
		}
		
		if (confirm(message))
		{
			// We're editing a shout
			console.log(this.timestamp() + 'Attempting to perform action %s on userid %s...', action, userid);			
			
			if (this.chatroomid)
			{
				// Set who we're PMing
				extraparams += '&type=chatroom_' + this.chatroomid + '_&chatroomid=' + this.chatroomid;
			}
			
			if (this.pmuserid)
			{
				// Set who we're PMing
				extraparams += '&type=pm_' + this.pmuserid + '_&pmuserid=' + this.pmuserid;
			}			
			
			// Set action
			extraparams += '&manageaction=' + action;
			
			// Set userid
			extraparams += '&userid=' + userid;
			
			// Reset the countdown
			this.pauseCountdown = true;
	
			// Remove any idle flag we might have
			this.unIdle(false);			
			
			// Execute AJAX
			this.ajax_call('usermanage', extraparams);
		}
		
		if (this.menuid)
		{
			// Close any open menus we have
			this.toggle_menu(this.menuid);
		}
		
		return false;
	}
	
	// #########################################################################	
	// Fetches the sticky note to edit
	this.edit_sticky = function()
	{
		// Initialise this
		var extraparams = '';
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);		
		
		// Execute AJAX
		this.ajax_call('fetchsticky', extraparams);
	}
		
	// #########################################################################	
	// Submit a new shout or save the editing shout
	this.save = function()
	{
		if (this.frames['editor'].value.length == 0)
		{
			// We don't care about empty shouts
			return false;
		}
		
		if (this.shoutDelay > 0)
		{
			// We've shouted x seconds ago
			this.set_message(vbphrase['dbtech_vbshout_must_wait_x_seconds']
				.replace('%time%', this.floodcheck)
				.replace('%time2%', (this.floodcheck - this.shoutDelay)),
				'error'
			);
			
			return false;
		}		
		
		// Initialise this
		var extraparams = '';
		
		/*
		// Set the current time
		var timenow = new Date().getTime();
		
		if ((timenow - this.lastshout) < (this.floodcheck * 1000) && this.lastshout > 0)
		{
			// Flood check
			this.set_message(vbphrase['dbtech_vbshout_must_wait_x_seconds']
				.replace('%time%', this.floodcheck)
				.replace('%time2%', parseInt((timenow - this.lastshout) / 1000)),
				'error'
			);
			return false;
		}
		// Set our last shout time
		this.lastshout = timenow;
		*/

 		if (this.shoutid > 0)
		{
			// We're editing a shout
			console.log(this.timestamp() + 'Attempting to save shout: %s...', this.shoutid);			
			
			// Add some extra parameters
			extraparams += '&shoutid=' + parseInt(this.shoutid);
			
			if (this.pmuserid)
			{
				// Set who we're PMing
				extraparams += '&type=pm_' + this.pmuserid + '_&pmuserid=' + this.pmuserid;
			}			
			
			if (this.chatroomid)
			{
				// Set who we're PMing
				extraparams += '&type=chatroom_' + this.chatroomid + '_&chatroomid=' + this.chatroomid;
			}			
		}
		else
		{
			// We're submitting a new shout
			console.log(this.timestamp() + 'Attempting to insert shout...');				
			
			if (this.pmuserid)
			{
				// We're submitting a new PM
				console.log(this.timestamp() + 'Attempting to PM user: %s', this.lookup[this.pmuserid]);				
				
				// Set who we're PMing
				extraparams += '&type=pm_' + this.pmuserid + '_&pmuserid=' + this.pmuserid;
			}
			
			if (this.chatroomid)
			{
				// We're submitting a new PM
				console.log(this.timestamp() + 'Attempting to chat in room: %s', this.chatroomid);				
				
				// Set who we're PMing
				extraparams += '&type=chatroom_' + this.chatroomid + '_&chatroomid=' + this.chatroomid;
			}
		}
		
		if (this.detached)
		{
			// We're in detached mode
			extraparams += '&detached=1';
		}
		
		// Set tab id
		extraparams += '&tabid=' + this.tab;		
	
		// Set message
		extraparams += '&message=' + PHP.urlencode(PHP.trim(this.frames['editor'].value));
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);	
		
		// Set shout delay
		this.shoutDelay = this.floodcheck;		
		
		// Execute AJAX
		this.ajax_call('save', extraparams);
		
		// Temporary storage
		this.frames['editor'].value = '';
		
		/*
		if (this.idle)
		{
			// We're obviously no longer idle, but init AJAX first so that we don't double fetch shouts
			this.unidle();
		}

		// Reset idle time
		this.idletime = 0;
		*/
		
		if (this.shoutid)
		{
			// We're no longer editing a shout
			this.cancel_shout_editing();			
		}
		
		return false;
	}
	
	// #########################################################################	
	// Begins editing a shout
	this.edit_shout = function(shoutid)
	{
		// Reset the countdown
		this.pauseCountdown = true;
		
		// Set the shout id
		this.shoutid = shoutid;			
		YAHOO.util.Dom.get('dbtech_shoutbox_deletebutton' + this.instanceid).style.display = 'inline';
		
		// Help text change
		YAHOO.util.Dom.get('dbtech_shoutbox_target' + this.instanceid).style.display = 'none';
		YAHOO.util.Dom.get('dbtech_shoutbox_editing' + this.instanceid).style.display = 'inline';
		
		// Set the editor contents
		this.frames['editor'].value = PHP.unhtmlspecialchars(YAHOO.util.Dom.get('dbtech_shoutbox_shout' + shoutid + '_message' + this.instanceid).value);			
		
		// Fetch the shout
		//this.fetch('shout', true);		
		
		// Just to be safe
		this.check_length();
	}	

	// #########################################################################	
	// Cancels editing a shout
	this.cancel_shout_editing = function()
	{
		// Reset the countdown
		this.pauseCountdown = false;
		
		// Remove all evidence of editing a shout
		this.shoutid = 0;
		YAHOO.util.Dom.get('dbtech_shoutbox_deletebutton' + this.instanceid).style.display = 'none';
		
		// Help text change
		YAHOO.util.Dom.get('dbtech_shoutbox_target' + this.instanceid).style.display = 'inline';
		YAHOO.util.Dom.get('dbtech_shoutbox_editing' + this.instanceid).style.display = 'none';
		
		// Clear the editor
		this.clear();
	}	
	
	// #########################################################################	
	// Begins editing a shout
	this.edit_archive_shout = function(shoutid)
	{
		// We're editing a shout
		console.log(this.timestamp() + 'Attempting to edit Archive shout: %s...', shoutid);			
			
		var editor 	= YAHOO.util.Dom.get('dbtech_shoutbox_editor_' + shoutid + '_' + this.instanceid);
		var message = YAHOO.util.Dom.get('dbtech_shoutbox_message_' + shoutid + '_' + this.instanceid);
		
		editor.style.display 	= 'block';
		message.style.display 	= 'none';
		
		YAHOO.util.Dom.get('dbtech_shoutbox_editor_text_' + shoutid + '_' + this.instanceid).value = PHP.unhtmlspecialchars(YAHOO.util.Dom.get('dbtech_shoutbox_message_raw_' + shoutid + '_' + this.instanceid).innerHTML);
	}
	
	// #########################################################################	
	// Begins editing a shout
	this.cancel_editing_archive_shout = function(shoutid)
	{
		// We're editing a shout
		console.log(this.timestamp() + 'Cancelling editing Archive shout: %s...', shoutid);			
				
		var editor 	= YAHOO.util.Dom.get('dbtech_shoutbox_editor_' + shoutid + '_' + this.instanceid);
		var message = YAHOO.util.Dom.get('dbtech_shoutbox_message_' + shoutid + '_' + this.instanceid);
		
		editor.style.display 	= 'none';
		message.style.display 	= 'block';
		
		YAHOO.util.Dom.get('dbtech_shoutbox_editor_text_' + shoutid + '_' + this.instanceid).value = PHP.unhtmlspecialchars(YAHOO.util.Dom.get('dbtech_shoutbox_message_raw_' + shoutid + '_' + this.instanceid).innerHTML);
	}
	
	// #########################################################################
	// Save the editor styles
	this.delete_archive_shout = function(shoutid)
	{
		var extraparams = '';
		
		if (confirm('Are you sure you wish to delete this shout?'))
		{
			// Remove the shout
			YAHOO.util.Dom.get('dbtech_shoutbox_shout' + shoutid + '_' + this.instanceid).style.display = 'none';
			
			// We're editing a shout
			console.log(this.timestamp() + 'Attempting to delete Archive shout: %s...', shoutid);			
			
			// Set tab id
			extraparams += '&tabid=' + this.tab;		
			
			// Set shout id
			extraparams += '&shoutid=' + shoutid;
			
			// Set source
			extraparams += '&source=archive';
			
			// Reset the countdown
			this.pauseCountdown = true;
	
			// Remove any idle flag we might have
			this.unIdle(false);			
			
			// Execute AJAX
			this.ajax_call('delete', extraparams);
		}
		
		return false;
	}	
	
	// #########################################################################
	// Save the editor styles
	this.save_archive_shout = function(shoutid)
	{
		var extraparams = '';
		
		// We're editing a shout
		console.log(this.timestamp() + 'Attempting to save Archive shout: %s...', shoutid);			
		
		// Set the raw text
		YAHOO.util.Dom.get('dbtech_shoutbox_message_raw_' + shoutid + '_' + this.instanceid).innerHTML = YAHOO.util.Dom.get('dbtech_shoutbox_editor_text_' + shoutid + '_' + this.instanceid).value;
		
		var editor 	= YAHOO.util.Dom.get('dbtech_shoutbox_editor_' + shoutid + '_' + this.instanceid);
		var message = YAHOO.util.Dom.get('dbtech_shoutbox_message_' + shoutid + '_' + this.instanceid);
		
		editor.style.display 	= 'none';
		message.style.display 	= 'block';
		
		// Set tab id
		extraparams += '&tabid=' + this.tab;		
		
		// Set shout id
		extraparams += '&shoutid=' + shoutid;
		
		// Set message
		extraparams += '&message=' + PHP.urlencode(PHP.trim(YAHOO.util.Dom.get('dbtech_shoutbox_editor_text_' + shoutid + '_' + this.instanceid).value));
		
		// Set source
		extraparams += '&source=archive';
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);		
		
		// Execute AJAX
		this.ajax_call('save', extraparams);
	}	
	
	// #########################################################################
	// Save the editor styles
	this.save_editor_styles = function()
	{
		var styleproperties = new Array(
			'editor[bold]=' + (this.editor['bold'] ? 1 : 0),
			'editor[italic]=' + (this.editor['italic'] ? 1 : 0),
			'editor[underline]=' + (this.editor['underline'] ? 1 : 0),
			'editor[color]=' + this.editor['color'],
			'editor[font]=' + this.editor['font'],
			'editor[size]=' + this.editor['size']
		);
		
		var extraparams = '';
		
		if (this.detached)
		{
			// We're in detached mode
			extraparams += '&detached=1';
		}		
		
		// Set tab id
		extraparams += '&tabid=' + this.tab;		
		
		// Set styleprops
		extraparams += '&' + styleproperties.join('&');
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);		
		
		// Execute AJAX
		this.ajax_call('styleprops', extraparams);
		
		/*
		if (this.idle)
		{
			// We're obviously no longer idle, but init AJAX first so that we don't double fetch shouts
			this.unidle();
		}
		*/		
	}
	
	// #########################################################################
	// Save the tabs
	this.save_muted_tabs = function()
	{
		if (!this.sounds)
		{
			// Sounds aint enabled
			return false;
		}
		
		// begin params
		var extraparams = '';
		
		if (this.detached)
		{
			// We're in detached mode
			extraparams += '&detached=1';
		}		
		
		for (var i in this.sounds.muted)
		{
			// This is an active tab
			extraparams += '&tabs[' + i + ']=' + this.sounds.muted[i];
		}
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);		
		
		// Execute AJAX
		this.ajax_call('sounds', extraparams);
		
		/*
		if (this.idle)
		{
			// We're obviously no longer idle, but init AJAX first so that we don't double fetch shouts
			this.unidle();
		}
		*/		
	}
	
	// #########################################################################
	// Save the editor styles
	this.delete_shout = function()
	{
		// Initialise this
		var extraparams = '';
		
 		if (this.shoutid == 0)
		{
			// This should never happen but just in case
			return false;
		}
		
		// We're editing a shout
		console.log(this.timestamp() + 'Attempting to delete shout: %s...', this.shoutid);			
		
		if (this.pmuserid)
		{
			// Set who we're PMing
			extraparams += '&type=pm_' + this.pmuserid + '_&pmuserid=' + this.pmuserid;
		}	
		
		if (this.chatroomid)
		{
			// Set who we're PMing
			extraparams += '&type=chatroom_' + this.chatroomid + '_&chatroomid=' + this.chatroomid;
		}	
		
		if (this.detached)
		{
			// We're in detached mode
			extraparams += '&detached=1';
		}		
		
		// Set tab id
		extraparams += '&tabid=' + this.tab;		
		
		// Set shout id
		extraparams += '&shoutid=' + this.shoutid;
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);		
		
		// Execute AJAX
		this.ajax_call('delete', extraparams);
		
		// We're no longer editing a shout
		this.cancel_shout_editing();
		
		/*
		if (this.idle)
		{
			// We're obviously no longer idle, but init AJAX first so that we don't double fetch shouts
			this.unidle();
		}
		*/		
	}
	
	// #########################################################################
	// Fetches the userid for a specific username
	this.lookup_username = function()
	{
		// Initialise this
		var extraparams = '';
		var username = YAHOO.util.Dom.get('username' + this.instanceid).value;
		
 		if (username == '')
		{
			// This should never happen but just in case
			return false;
		}
		
		// We're editing a shout
		console.log(this.timestamp() + 'Attempting to lookup username: %s...', username);			
		
		if (this.detached)
		{
			// We're in detached mode
			extraparams += '&detached=1';
		}		
		
		// Set username
		extraparams += '&username=' + PHP.urlencode(username);
		
		// Reset the countdown
		this.pauseCountdown = true;

		// Remove any idle flag we might have
		this.unIdle(false);	
		
		// Execute AJAX
		this.ajax_call('lookup', extraparams);
		
		/*
		if (this.idle)
		{
			// We're obviously no longer idle, but init AJAX first so that we don't double fetch shouts
			this.unidle();
		}	
		*/	
	}
	
	// #########################################################################
	// Finalise fetching of content
	this.ajax_completed = function(ajax)
	{
		// Also reset the countdown here
		this.pauseCountdown = false;
		this.countdown = this.refreshtime;
		
		//this.ajax[ajax.argument] = false;
		//this.inAJAX = false;

		if (!ajax.responseXML)
		{
			// Empty response
			this.set_message('Invalid response from server: ' + ajax.responseText, 'error');
			this.restore_shout_message();			
			return false;
		}
		
		// check for error first
		var error = ajax.responseXML.getElementsByTagName('error');
		var clear = ajax.responseXML.getElementsByTagName('clear');
			
		if (clear.length)
		{
			// We need to clear an editor
			this.frames[clear[0].firstChild.nodeValue].value = '';
			this.check_length(); // Just to be safe
		}
			
		if (error.length)
		{
			var err = error[0].firstChild.nodeValue;
			var chatroomid = parseInt(err.split('_')[1]);
			
			if (chatroomid)
			{
				if (this.chatroomid == chatroomid)
				{
					// We need to reset chatroom id
					this.chatroomid = 0;
				}
				
				// Close the tab
				this.close_tab(YAHOO.util.Dom.get('tabobj_chatroom_' + chatroomid + '_' + this.instanceid), 'chatroom_' + chatroomid + '_');
				return false;
			}
			
			// Throw the error returned
			this.throw_ajax_error(err);
			this.restore_shout_message();
		}
		else
		{
			// All possible tags
			var tags = [
				'activeusers', 'activereports', 'activeusers2', 'aoptime', 'shout', 'success', 'sticky', 'editor', 'content', 'shoutid',
				'archive', 'menucode', 'pmuserid', 'chatroomid', 'roomname', 'chatroomid2', 'roomname2', 'username2',
				'aoptime2', 'tabid2', 'pmtime'
			];
			for (var t = 0; t < tags.length; t++)
			{
				eval('var ' + tags[t] + ' = ajax.responseXML.getElementsByTagName("' + tags[t] + '");');
			}
			
			if (success.length)
			{
				// Print success message
				this.set_message(success[0].firstChild.nodeValue, 'success');
			}
			
			if (sticky.length)
			{
				if (!sticky[0].firstChild)
				{
					// We're removing the sticky note
					this.hide_message('sticky');
				}
				else
				{
					// Print sticky message
					this.set_message(sticky[0].firstChild.nodeValue, 'sticky');
				}
			}
			
			if (activeusers.length)
			{
				// Set the active users
				this.frames['activeusers'].innerHTML = activeusers[0].firstChild.nodeValue;
			}
			
			if (activereports.length)
			{
				// Set the active users
				YAHOO.util.Dom.get('dbtech_shoutbox_shoutreports' + this.instanceid).innerHTML = activereports[0].firstChild.nodeValue;
			}
			
			if (activeusers2.length)
			{
				// Set the active users
				YAHOO.util.Dom.get('dbtech_vbshout_sidebar' + this.instanceid).innerHTML = activeusers2[0].firstChild.nodeValue;
			}
			
			if (aoptime2.length)
			{
				for (var i = 0; i < aoptime2.length; i++)
				{
					// Shorthand
					var aoptimes 	= aoptime2[i];
					var aoptime3 	= aoptimes.firstChild.nodeValue;
					var tabids 		= tabid2[i];
					var tabid 		= tabids.firstChild.nodeValue;
					
					if (tabid == this.tab)
					{
						// We're not alerting our own tab
						//continue;
					}					
					
					if (!this.aoptime[tabid])
					{
						// This is an unvisited tab
						this.aoptime[tabid] = aoptime3;
						continue;
					}
					
					if (this.aoptime[tabid] >= aoptime3)
					{
						// The AOP time is higher or equal, already seen it
						continue;
					}
					
					// Set the new AOP time
					this.aoptime[tabid] = aoptime3;
					
					// We're editing a shout
					console.log(this.timestamp() + "Tab: %s\nAOP: %s", tabid, aoptime3);			
					
					if (this.sounds)
					{
						// Ensure we only try this if we can
						this.sounds.play('shout');
					}
					
					if (tabid != this.tab)
					{					
						// Get the object
						YAHOO.util.Dom.addClass(YAHOO.util.Dom.get(tabid + this.instanceid), 'dbtech_vbshout_highlight');
					}
				}
			}
			
			if (aoptime.length)
			{
				// We had an AOP tag
				if (this.aoptime[this.tab] < aoptime[0].firstChild.nodeValue || !this.aoptime[this.tab])
				{
					// Overwrite the AOP time
					this.aoptime[this.tab] = aoptime[0].firstChild.nodeValue;
				}
			}
			
			if (pmtime.length)
			{
				var newpm = pmtime[0].firstChild.nodeValue;
				
				if (!this.pmtime || this.pmtime < newpm)
				{
					if (this.pmtime < newpm)
					{
						if (this.sounds)
						{
							// Ensure we only try this if we can
							this.sounds.play('pm');
						}
					}
					
					// Set PM time
					this.pmtime = newpm;
				}
			}
			
			if (editor.length)
			{
				// We're setting the editor contents
				this.frames['editor'].value = editor[0].firstChild.nodeValue;
				this.check_length();				
			}
			
			if (content.length)
			{
				// We're setting shout frame contents
				this.frames['content'].innerHTML = content[0].firstChild.nodeValue;
			}
			
			if (archive.length && shoutid.length)
			{
				// We're setting shout frame contents
				YAHOO.util.Dom.get('dbtech_shoutbox_message_' + shoutid[0].firstChild.nodeValue + '_' + this.instanceid).innerHTML = archive[0].firstChild.nodeValue;
			}
			
			if (menucode.length && menucode[0].firstChild)
			{
				// Set the active users
				YAHOO.util.Dom.get('dbtech_shoutbox_menucode' + this.instanceid).innerHTML = menucode[0].firstChild.nodeValue;
				this.menuid = 0;
			}
			
			if (pmuserid.length)
			{
				// Create PM for this user
				this.create_pm(pmuserid[0].firstChild.nodeValue, YAHOO.util.Dom.get('username' + this.instanceid).value);
			}
			
			if (chatroomid.length && roomname.length)
			{
				// Create chat room tab
				this.create_chatroom_tab(chatroomid[0].firstChild.nodeValue, roomname[0].firstChild.nodeValue, true);
				this.show_chatroom_tab(chatroomid[0].firstChild.nodeValue);
			}
			
			if (chatroomid2.length)
			{
				for (var i = 0; i < chatroomid2.length; i++)
				{
					// Shorthand
					var chatroomids = chatroomid2[i];
					var chatroomid 	= chatroomids.firstChild.nodeValue;
					var roomnames 	= roomname2[i];
					var roomname 	= roomnames.firstChild.nodeValue;
					var usernames 	= username2[i];
					var username 	= usernames.firstChild.nodeValue;
					
					if (!this.tabs['chatroom_' + chatroomid + '_'])
					{
						if (this.sounds)
						{
							// Ensure we only try this if we can
							this.sounds.play('invite');
						}
						
						if (confirm('Do you want to join the Chat Room: ' + roomname + '? You were invited by ' + username + '.'))
						{
							// Ensure we only create uncreated tabs
							this.create_chatroom_tab(chatroomid, roomname, true);
							this.join_chatroom(chatroomid);
							this.show_chatroom_tab(chatroomid);
						}
						else
						{
							// Nah, fuck this shit
							this.leave_chatroom(chatroomid, true);
						}
					}
				}
			}
			
			if (shout.length)
			{
				// Begin storing the output
				var html = new Array();
				
				for (var i = 0; i < shout.length; i++)
				{
					// Shorthand
					var _shout 		= shout[i];
					var message 	= PHP.trim(_shout.firstChild.nodeValue);
					var shoutObj 	= null;
					try 
					{
						// Attempt to parse JSON
						//shoutObj = jQuery.parseJSON(message);
						eval("shoutObj = (" + message + ")");
					}
					catch (e) {};
					
					if (shoutObj != null)
					{
						if (!this.templates[shoutObj.template])
						{
							continue;
						}
						
						html[i] = this.templates[shoutObj.template]
							.replace(/%shoutid%/igm, 				shoutObj.shoutid)
							.replace(/%instanceid%/igm, 			shoutObj.instanceid)
							.replace(/%message_raw%/igm, 			shoutObj.message_raw)
							.replace(/%canedit%/igm, 				shoutObj.canedit)
							.replace(/%time%/igm, 					shoutObj.time)
							.replace(/%memberaction_dropdown%/igm, 	shoutObj.memberaction_dropdown)
							.replace(/%styleprops%/igm, 			shoutObj.styleprops)
							.replace(/%message%/igm, 				shoutObj.message)				
							.replace(/%pmuser%/igm, 				shoutObj.pmuser)				
							.replace(/%musername%/igm, 				shoutObj.musername)				
							.replace(/%altclass%/igm, 				shoutObj.altclass)				
					}
				}
				
				if (html.length)
				{
					// Set the content if we had any
					this.frames['content'].innerHTML = html.join('');
				}
				
				if (this.shoutorder == 'ASC' && this.frames['shoutframe'].scrollTop < this.frames['shoutframe'].scrollHeight)
				{
					// Make it snap the scrollbar to the bottom
					if (typeof(HTMLElement) != 'undefined')
					{
						this.frames['shoutframe'].scrollBottom();
					}
					else
					{
						this.frames['shoutframe'].scrollTop = this.frames['shoutframe'].scrollHeight;
					}
				}
			}
		}
	}
	
	// #########################################################################
	// Shorthand for an ajax call
	this.ajax_call = function(varname, extraparams)
	{
		//if (varname != 'fetch')
		//{
			//this.inAJAX = true;
		//}
		// We're in an AJAX request
		//this.ajax[varname] = true;
		//this.lastAjax = varname;
		
		YAHOO.util.Connect.asyncRequest('POST', 'vbshout.php', {
			success: this.ajax_completed,
			failure: this.handle_ajax_error,
			timeout: vB_Default_Timeout,
			scope: this,
			argument: varname
		}, SESSIONURL + 'securitytoken=' + SECURITYTOKEN + '&do=ajax&action=' + varname + extraparams + '&instanceid=' + this.instanceid);		
	}
	
	
	// #######################################################################
	// ######################## ERROR HANDLING ###############################
	// #######################################################################
	
	// #########################################################################
	// Function for manually throwing an AJAX error
	this.throw_ajax_error = function(errormsg, errtype)
	{
		if (YAHOO.util.Dom.get('dbtech_shoutbox_error_message' + this.instanceid))
		{
			// Throw the error returned
			this.set_message(errormsg, 'error');
		}
		else
		{
			//alert(errormsg);
		}
		
		// Log the error to the console
		console.error(this.timestamp() + "AJAX Error: %s", errormsg);		
	}
	
	// #########################################################################
	// This should never happen.
	this.handle_ajax_error = function(ajax)
	{
		// Also reset the countdown here
		this.pauseCountdown = false;
		this.countdown = this.refreshtime;
		
		//this.ajax[ajax.argument] = false;
		//this.inAJAX = false;
		
		if (YAHOO.util.Dom.get('dbtech_shoutbox_error_message' + this.instanceid))
		{
			try
			{
				if (ajax.statusText == 'communication failure' || ajax.statusText == 'transaction aborted')
				{
					// Ignore this error
					return false;
				}
				
				// Restore shout message
				this.restore_shout_message();
				
				// Throw the error returned
				this.set_message(ajax.statusText, 'error');
				
				// Log the error to the console
				console.error(this.timestamp() + "AJAX Error: Status = %s: %s", ajax.status, ajax.statusText);
			}
			catch (e)
			{
				// Log the error to the console
				console.error(this.timestamp() + "AJAX Error: %s", ajax.responseText);
			}
		}
		else
		{
			try
			{
				// Just pop it up
				alert(ajax.statusText);
			}
			catch (e)
			{
				alert(ajax.responseText);
			}
		}
	}
	
	// #########################################################################
	// This should never happen.
	this.handle_ajax_error2 = function()
	{
		this.fetch(this.tab, true, true);
	}
	
	// #########################################################################
	// Function for displaying a thrown error
	this.set_message = function(msg, type)
	{
		// Log the error to the console
		console.log(this.timestamp() + "Setting %s: %s", type.charAt(0).toUpperCase() + '' + type.substr(1), msg);
		
		if (YAHOO.util.Dom.get('dbtech_shoutbox_' + type + '_message' + this.instanceid))
		{
			if (type != 'sticky' && type != 'notice')
			{
				// Clear any timeouts we may have already
				clearTimeout(this.timers[type]);
				
				// Create a new timer for hiding the frame
				this.timers[type] = setTimeout("vBShout" + this.instanceid + ".hide_message('" + type + "')", 5000);
			}
			
			// Display the message
			YAHOO.util.Dom.get('dbtech_shoutbox_' + type + '_message' + this.instanceid).innerHTML 	= msg;
			YAHOO.util.Dom.get('dbtech_shoutbox_' + type + '_frame' + this.instanceid).style.display 	= 'block';
			
			if (this.shoutorder == 'ASC' && this.frames['shoutframe'].scrollTop == this.scrollpos)
			{
				if (typeof(HTMLElement) != 'undefined')
				{
					// Scroller hasn't moved, snap to bottom
					this.frames['shoutframe'].scrollBottom();
				}
			}
		}
	}
	
	// #########################################################################
	// Function for displaying a thrown error
	this.hide_message = function(type)
	{
		// Log the error to the console
		console.log(this.timestamp() + "Hiding %s...", type.charAt(0).toUpperCase() + '' + type.substr(1));
		
		if (YAHOO.util.Dom.get('dbtech_shoutbox_' + type + '_message' + this.instanceid))
		{
			// Hide the frame
			YAHOO.util.Dom.get('dbtech_shoutbox_' + type + '_message' + this.instanceid).innerHTML 	= 'N/A';
			YAHOO.util.Dom.get('dbtech_shoutbox_' + type + '_frame' + this.instanceid).style.display 	= 'none';
			
			if (this.shoutorder == 'ASC')
			{
				// Store the position of the scroller
				this.scrollpos = this.frames['shoutframe'].scrollTop;
			}			
		}
	}
	
	// #########################################################################
	// Restores our attempted shout post
	this.restore_shout_message = function()
	{
		if (this.frames['editor'])
		{
			// There was an error of some description
			this.frames['editor'].value = this.shoutstore;
		}
	}
	
	// #########################################################################
	// Toggles the menu
	this.toggle_menu = function(menuid, uniqueid)
	{
		var divObj = YAHOO.util.Dom.get('menu' + menuid + '_' + this.instanceid);
		var aObj = YAHOO.util.Dom.get('click' + uniqueid + '_' + this.instanceid);
		
		if (!divObj)
		{
			// Just to prevent errors
			return false;
		}
		
		if (divObj.style.display == 'none' && menuid != this.menuid)
		{
			if (this.menuid)
			{
				// Turn off the previous menu
				this.toggle_menu(this.menuid);
			}
			
			// Set the new menu id
			this.menuid = menuid;
			
			// Hack the menu to show			
			divObj.style.display = 'inline';
			divObj.style.position = 'absolute';
			divObj.style.top = (YAHOO.util.Dom.getY(aObj) + parseInt(aObj.offsetHeight)) + 'px';
			divObj.style.left = YAHOO.util.Dom.getX(aObj) + 'px';

			right = (parseInt(divObj.style.left) + divObj.offsetWidth);

			if (right >= YAHOO.util.Dom.getViewportWidth())
			{
				divObj.style.left = (YAHOO.util.Dom.getViewportWidth() - divObj.offsetWidth) + 'px';
			}
		}
		else
		{
			// Hide the open menu
			divObj.style.display = 'none';
			this.menuid = 0;
		}
		
		return false;		
	}
	
	
	// #######################################################################
	// ######################## GENERIC FUNCTIONALITY ########################
	// #######################################################################

	// #########################################################################
	// Process keystroke
	this.keystroke = function(e)
	{
		// What key we pressed
		var keynum = 0;
		
		if (window.event)
		{
			// IE
			keynum = e.keyCode;
		}
		else if (e.which)
		{
			// Netscape / Firefox / Opera
			keynum = e.which;
		}
		
		if (keynum == 27 && this.shoutid)
		{
			// We're editing a shout, cancel editing
			this.cancel_shout_editing();
		}
		
		if (keynum == 13)
		{
			// This was saving the form
			return this.save();
		}
	}
	
	// #########################################################################
	// Check the length of the input
	this.check_length = function()
	{
		if (this.maxlength == 0)
		{
			// We're not checking length
		
			if (this.frames['editor'].value != '')
			{
				// Store this
				this.shoutstore = this.frames['editor'].value;
			}
			return true;
		}
		
		if (this.frames['editor'].value.length > this.maxlength)
		{
			// Strip characters that go beyond the limit
			this.frames['editor'].value = this.frames['editor'].value.substring(0, this.maxlength);
		}
		
		if (this.frames['editor'].value != '')
		{
			// Store this
			this.shoutstore = this.frames['editor'].value;
		}
		
		// Set the Remaining Characters count
		this.frames['charcount'].innerHTML = (this.maxlength - this.frames['editor'].value.length);		
	}	
	
	// #########################################################################
	// Debugging function, generates a timestamp of when something occurred
	this.timestamp = function()
	{
		var d = new Date();
		
		return '[' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds() + '] ';
	}
	

	// #############################################################################
	// Emulates PHP's filemtime
	this.filemtime = function()
	{
		YAHOO.util.Connect.asyncRequest('GET', this.aopfile + '?v=' + Math.random() * 99999999999999, {
		//YAHOO.util.Connect.asyncRequest('HEAD', this.aopfile, {
			//success: this.get_filemtime,
			success: this.get_filemtime2,
			failure: this.handle_ajax_error2,
			timeout: vB_Default_Timeout,
			scope: this
		});		
	}
	
	// #############################################################################
	// Grabs the actual filemtime
	this.get_filemtime = function(ajax)
	{
		//console.log(this.timestamp() + ajax.getAllResponseHeaders);
		var d = new Date();
		var dateline = Date.parse(ajax.getResponseHeader['Last-Modified']) / 1000;
		
		if (isNaN(dateline))
		{
			console.log(this.timestamp() + "\nHEAD request was NaN.");
			
			YAHOO.util.Connect.asyncRequest('GET', this.aopfile + '?v=' + Math.random() * 99999999999999, {
				success: this.get_filemtime2,
				failure: this.handle_ajax_error2,
				timeout: vB_Default_Timeout,
				scope: this
			});
			return false;
		}
		
		// Fucking AOP -.-
		dateline = dateline - 1;
		
		// Shorthand
		var timenow = parseInt(d.getTime() / 1000);
		
		if (dateline > this.aoptime[this.tab])
		{
			console.log(this.timestamp() + this.tab + " AOP file returned new shouts: \n" + dateline + "\n" + this.aoptime[this.tab]);
		
			// Force an update
			this.fetch(this.tab, true, true);
			
			return false;
		}
		
		if (dateline == 0)
		{
			console.log(this.timestamp() + "AOP file returned 0");
			
			// Force an update
			this.fetch(this.tab, true, true);
			
			return false;
		}
		
		if ((timenow - dateline) > 60)
		{
			console.log(this.timestamp() + "AOP file hasn't been modified for 60 seconds: " + (timenow - dateline));
			
			this.aoptime[this.tab] = (timenow + 5);
			
			// Force an update
			this.fetch(this.tab, true, true);
			
			return false;
		}
		else
		{
			// Also reset the countdown here
			this.pauseCountdown = false;
			this.countdown = this.refreshtime;			
		}
	}
	
	// #############################################################################
	// Grabs the actual filemtime
	this.get_filemtime2 = function(ajax)
	{
		//console.log(this.timestamp() + ajax.getAllResponseHeaders);
		var d = new Date();
		var dateline = ajax.responseText;
		var timenow = parseInt(d.getTime() / 1000);
		
		if (dateline > this.aoptime[this.tab])
		{
			console.log(this.timestamp() + this.tab + " AOP file returned new shouts: \n" + dateline + "\n" + this.aoptime[this.tab]);
		
			// Force an update
			this.fetch(this.tab, true, true);
			
			return false;
		}
		
		if (dateline == 0)
		{
			console.log(this.timestamp() + "AOP file returned 0");
			
			// Force an update
			this.fetch(this.tab, true, true);
			
			return false;
		}
		
		if ((timenow - dateline) > 60)
		{
			console.log(this.timestamp() + "AOP file hasn't been modified for 60 seconds: " + (timenow - dateline));
			
			this.aoptime[this.tab] = (timenow + 5);
			
			// Force an update
			this.fetch(this.tab, true, true);
			
			return false;
		}
		else
		{
			// Also reset the countdown here
			this.pauseCountdown = false;
			this.countdown = this.refreshtime;			
		}
	}
	
	// #############################################################################
	// Filters elements from the array via the callback.
	this.array_filter = function(arr, func)
	{
		var retObj = {}, k;
		
		for (k in arr)
		{
			if (func(arr[k]))
			{
				retObj[k] = arr[k];
			}
		}
		
		return retObj;
	}
};

// #######################################################################
// ######################## VBULLETIN 4.1.4 FIXES ########################
// #######################################################################
function vBShout_Text_Editor()
{
}

/**
* Set Control Style
*
* @param	object	The object to be styled
* @param	string	Control type - 'button' or 'menu'
* @param	string	The mode to use, corresponding to the istyles array
*/
vBShout_Text_Editor.prototype.set_control_style = function(obj, controltype, mode)
{
	if (obj.mode != mode)
	{
		obj.mode = mode;

		YAHOO.util.Dom.removeClass(obj, "imagebutton_selected");
		YAHOO.util.Dom.removeClass(obj, "imagebutton_hover");
		YAHOO.util.Dom.removeClass(obj, "imagebutton_down");

		switch(obj.mode)
		{
			case "down":
				YAHOO.util.Dom.addClass(obj, "imagebutton_down");
				break;
			case "selected":
				YAHOO.util.Dom.addClass(obj, "imagebutton_selected");
				break;
			case "hover":
				YAHOO.util.Dom.addClass(obj, "imagebutton_hover");
				break;
			case "normal":
				break;
		}

		return;
	}
};

/**
* Button Context
*
* @param	object	The button object
* @param	string	Incoming event type
* @param	string	Control type - 'button' or 'menu'
*/
vBShout_Text_Editor.prototype.button_context = function(obj, state, controltype)
{
	if (this.disabled)
	{
		return;
	}

	if (typeof controltype == 'undefined')
	{
		controltype = 'button';
	}

	if (YAHOO.util.Dom.hasClass(obj, "imagebutton_disabled"))
	{
		return;
	}

	switch (obj.state)
	{
		case true: // selected button
		{
			switch (state)
			{
				case 'mouseover':
				case 'mousedown':
				case 'mouseup':
				{
					this.set_control_style(obj, controltype, 'down');
					break;
				}
				case 'mouseout':
				{
					this.set_control_style(obj, controltype, 'selected');
					break;
				}
			}
			break;
		}

		default: // not selected
		{
			switch (state)
			{
				case 'mouseover':
				case 'mouseup':
				{
					this.set_control_style(obj, controltype, 'hover');
					break;
				}
				case 'mousedown':
				{
					this.set_control_style(obj, controltype, 'down');
					break;
				}
				case 'mouseout':
				{
					this.set_control_style(obj, controltype, 'normal');
					break;
				}
			}
			break;
		}
	}
};

/**
* Menu Context
*
* @param	object	The menu container object
* @param	string	The state of the control
*/
vBShout_Text_Editor.prototype.menu_context = function(e, obj)
{
	if (this.disabled)
	{
		return;
	}

//		YAHOO.util.Dom.removeClass(obj, "imagebutton_selected");
//		YAHOO.util.Dom.removeClass(obj, "imagebutton_hover");
//		YAHOO.util.Dom.removeClass(obj, "imagebutton_down");

	var children = YAHOO.util.Dom.getElementsByClassName('popupctrl', 'div', obj);

	switch (e.type)
	{
		case 'mouseout':
		{
			if (!YAHOO.util.Dom.hasClass(children[0], "imagebutton_down"))
			{
				this.set_control_style(children[0], 'button', 'normal');
			}
			break;
		}
		case 'mousedown':
		{
			if (YAHOO.util.Dom.hasClass(children[0], "imagebutton_down"))
			{
				this.set_control_style(children[0], 'button', 'hover');
			}
			else
			{
				this.set_control_style(children[0], 'popup', 'down');
			}
			break;
		}
		case 'mouseup':
		case 'mouseover':
		{
			this.set_control_style(children[0], 'button', 'hover');
			break;
		}
	}
};

/**
* Function to translate a hex like F AB 9 to #0FAB09 and then to coloroptions['#0FAB09']
*
* @param	string	Red value
* @param	string	Green value
* @param	string	Blue value
*
* @return	string	Option from coloroptions array
*/
vBShout_Text_Editor.prototype.translate_silly_hex = function(r, g, b)
{
	return "#" + (PHP.str_pad(r, 2, 0) + PHP.str_pad(g, 2, 0) + PHP.str_pad(b, 2, 0));
};



// #######################################################################
// ######################## LANGUAGE EXTENSIONS ##########################
// #######################################################################

// #############################################################################
// Code to snap a scrollbar to the bottom

if (typeof(HTMLElement) != 'undefined')
{
	HTMLElement.prototype.scrollBottom = function()
	{
		// Finally set the scrollTop attribute
		this.scrollTop = this.scrollHeight;
	}
}

/*======================================================================*\
|| #################################################################### ||
|| # Created: 23:33, Mon Dec 28th 2009								  # ||
|| # SVN: $Rev$							 							  # ||
|| #################################################################### ||
\*======================================================================*/