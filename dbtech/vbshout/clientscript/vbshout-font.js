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

vBShout_Fonts_Obj = function()
{
	// Object variables
	this.wrapper;		// ID of the editor's wrapper
	this.vbshout;
	this.instanceid;
	
	// #########################################################################
	// Initialiser for the functionality within
	this.init = function(wrapper, default_font, instanceid)
	{
		// Set the wrapper reference
		this.wrapper = wrapper;
		
		this.instanceid = instanceid;
		eval("this.vbshout = vBShout" + instanceid + ";");
		
		// Builds the font popup
		this.build_fontname_popup(YAHOO.util.Dom.get(this.wrapper + '_popup_fontname' + instanceid));
		
		// Set the default font
		YAHOO.util.Dom.get(this.wrapper + "_fontfield" + this.instanceid).innerHTML = default_font;
		
		// Update the style properties
		this.vbshout.update_style_property('font', default_font);
	}
	
	// #########################################################################
	// Builds the popup for the font dropdown
	this.build_fontname_popup = function(obj)
	{
		YAHOO.util.Event.on(obj, "mouseover", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mouseout", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mouseup", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mousedown", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		
		if (YAHOO.util.Dom.get(this.wrapper + "_fontfield" + this.instanceid))
		{
			this.fontoptions = {'' : YAHOO.util.Dom.get(this.wrapper + "_fontfield" + this.instanceid).innerHTML};
		}
		
		var fonts = YAHOO.util.Dom.getElementsByClassName('fontname', '', obj);
		for (i = 0; i < fonts.length; i++)
		{
			fonts[i].controlkey = obj.id;
			fonts[i].editorid = this.wrapper;
			
			YAHOO.util.Event.on(fonts[i], "mouseover", 	this.menuoption_onmouseevent, 	obj);
			YAHOO.util.Event.on(fonts[i], "mouseout", 	this.menuoption_onmouseevent, 	obj);
			YAHOO.util.Event.on(fonts[i], "mouseup", 	this.menuoption_onmouseevent, 	obj);
			YAHOO.util.Event.on(fonts[i], "mousedown", 	this.menuoption_onmouseevent, 	obj);
			YAHOO.util.Event.on(fonts[i], "click", 		this.process_formatting, 		fonts[i], this);
			
			var fontoption = fonts[i].firstChild.innerHTML;
			this.fontoptions[fontoption] = fontoption;
		}		
	}
	
	// #########################################################################
	// What happens when the mouse interacts with the menu
	this.menuoption_onmouseevent = function(e, obj)
	{
		e = do_an_e(e);
		vBShout_Text_Editor.prototype.button_context(this, e.type, 'menu');
	}
	
	// #########################################################################
	// Builds the popup for the font dropdown	
	this.process_formatting = function(e, obj)
	{
		var fontfield = YAHOO.util.Dom.get(this.wrapper + "_fontfield" + this.instanceid);
		
		if (fontfield.innerHTML != obj.firstChild.innerHTML)
		{
			// Change the text on the font dropdown
			fontfield.innerHTML = obj.firstChild.innerHTML;
			
			// Update the style properties
			this.vbshout.update_style_property('font', fontfield.innerHTML);
		}
		YAHOO.vBulletin.vBPopupMenu.close_all();
	}
}

/*======================================================================*\
|| #################################################################### ||
|| # Created: 21:48, Fri Jan 1st 2010								  # ||
|| # SVN: $Rev$							 							  # ||
|| #################################################################### ||
\*======================================================================*/