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

vBShout_Smilies_Obj = function()
{
	// String variables
	this.smilie_window;
	this.vbshout;
	this.instanceid;	
	this.editorid;
	
	// #########################################################################
	// Initialiser for the functionality within
	this.init = function(editorid, instanceid)
	{
		// Set the editor id
		this.editorid = editorid + instanceid;
		this.instanceid = instanceid;
		eval("this.vbshout = vBShout" + instanceid + ";");		
	}
	
	// #########################################################################
	// Button press to open the smiley window
	this.open_smilie_window = function(width, height)
	{
		if (typeof this.smilie_window != 'undefined' && !this.smilie_window.closed)
		{
			// Swap focus to it
			this.smilie_window.focus();
		}
		else
		{
			// Open the smiley window
			this.smilie_window = openWindow('misc.php?' + SESSIONURL + 'do=getsmilies&editorid=' + this.editorid, width, height, 'smilie_window' + this.instanceid);
			
			// Schedule the window's destruction
			window.onunload = this.smiliewindow_onunload;
		}
	}
	
	// #########################################################################
	// What will happen when the Smilie window gets destroyed
	this.smiliewindow_onunload = function(e)
	{
		if (typeof this.smilie_window != 'undefined' && !this.smilie_window.closed)
		{
			// Close the window
			this.smilie_window.close();
		}
	}

	// #########################################################################
	// Initialise the smileys (make them clickable)
	this.init_smilies = function(smilie_container)
	{
		if (smilie_container != null)
		{
			var smilies = fetch_tags(smilie_container, 'img');
			for (var i = 0; i < smilies.length; i++)
			{
				if (smilies[i].id && smilies[i].id.indexOf('_smilie_') != false)
				{
					smilies[i].style.cursor = pointer_cursor;
					smilies[i].editorid 	= this.editorid;
					smilies[i].vbshout 		= this.vbshout;
					smilies[i].onclick 		= this.smilie_onclick;
					smilies[i].unselectable = 'on';
				}
			}
		}
	}
	
	// #########################################################################
	// What happens when we click the smilie window 
	this.smilie_onclick = function(e)
	{
		// Stuff that's needed
		var editdoc 	= this.vbshout.frames['editor'];
		var text 		= ' ' + this.alt;
		var movestart 	= text.length;
		var moveend		= 0;
		
		if (!editdoc.hasfocus || (is_moz && is_mac))
		{
			editdoc.focus();
			if (is_opera)
			{
				editdoc.focus();
			}
		}
		
		if (typeof(editdoc.selectionStart) != 'undefined')
		{
			var opn = editdoc.selectionStart + 0;
			var scrollpos = editdoc.scrollTop;

			editdoc.value = editdoc.value.substr(0, editdoc.selectionStart) + text + editdoc.value.substr(editdoc.selectionEnd);

			if (movestart === false)
			{
				// do nothing
			}
			else if (typeof movestart != 'undefined')
			{
				editdoc.selectionStart = opn + movestart;
				editdoc.selectionEnd = opn + text.vBlength() - moveend;
			}
			else
			{
				editdoc.selectionStart = opn;
				editdoc.selectionEnd = opn + text.vBlength();
			}
			editdoc.scrollTop = scrollpos;
		}
		else if (document.selection && document.selection.createRange)
		{
			var sel = document.selection.createRange();
			sel.text = text.replace(/\r?\n/g, '\r\n');

			if (movestart === false)
			{
				// do nothing
			}
			else if (typeof movestart != 'undefined')
			{
				if ((movestart - text.vBlength()) != 0)
				{
					sel.moveStart('character', movestart - text.vBlength());
					selection_changed = true;
				}
				if (moveend != 0)
				{
					sel.moveEnd('character', -moveend);
					selection_changed = true;
				}
			}
			else
			{
				sel.moveStart('character', -text.vBlength());
				selection_changed = true;
			}

			if (selection_changed)
			{
				sel.select();
			}
		}
		else
		{
			// failed - just stuff it at the end of the message
			editdoc.value += text;
		}
		
		// Update the Remaining Character
		this.vbshout.check_length();

		return false;		
	}
};

/*======================================================================*\
|| #################################################################### ||
|| # Created: 21:48, Fri Jan 1st 2010								  # ||
|| # SVN: $Rev$							 							  # ||
|| #################################################################### ||
\*======================================================================*/