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

vBShout_Sizes_Obj = function()
{
	// Object variables
	this.wrapper;		// ID of the editor's wrapper
	this.vbshout;
	this.instanceid;
	
	// #########################################################################
	// Initialiser for the functionality within
	this.init = function(wrapper, default_size, instanceid)
	{
		// Set the wrapper reference
		this.wrapper = wrapper;
		
		this.instanceid = instanceid;
		eval("this.vbshout = vBShout" + instanceid + ";");		
		
		// Builds the size popup
		this.build_sizename_popup(YAHOO.util.Dom.get(this.wrapper + '_popup_sizename' + this.instanceid));
		
		// Set the default size
		YAHOO.util.Dom.get(this.wrapper + "_sizefield" + this.instanceid).innerHTML = default_size;
		
		// Update the style properties
		this.vbshout.update_style_property('size', default_size);
	}
	
	// #########################################################################
	// Builds the popup for the size dropdown
	this.build_sizename_popup = function(obj)
	{
		YAHOO.util.Event.on(obj, "mouseover", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mouseout", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mouseup", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mousedown", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		
		if (YAHOO.util.Dom.get(this.wrapper + "_sizefield" + this.instanceid))
		{
			this.sizeoptions = {'' : YAHOO.util.Dom.get(this.wrapper + "_sizefield" + this.instanceid).innerHTML};
		}
		
		var sizes = YAHOO.util.Dom.getElementsByClassName('fontsize', '', obj);
		for (i = 0; i < sizes.length; i++)
		{
			sizes[i].controlkey = obj.id;
			sizes[i].editorid = this.wrapper;
			
			YAHOO.util.Event.on(sizes[i], "mouseover", 	this.menuoption_onmouseevent, 	obj);
			YAHOO.util.Event.on(sizes[i], "mouseout", 	this.menuoption_onmouseevent, 	obj);
			YAHOO.util.Event.on(sizes[i], "mouseup", 	this.menuoption_onmouseevent, 	obj);
			YAHOO.util.Event.on(sizes[i], "mousedown", 	this.menuoption_onmouseevent, 	obj);
			YAHOO.util.Event.on(sizes[i], "click", 		this.process_formatting, 		sizes[i], this);
			
			var sizeoption = sizes[i].firstChild.innerHTML;
			this.sizeoptions[sizeoption] = sizeoption;
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
	// Builds the popup for the size dropdown	
	this.process_formatting = function(e, obj)
	{
		var sizefield = YAHOO.util.Dom.get(this.wrapper + "_sizefield" + this.instanceid);
		
		if (sizefield.innerHTML != obj.firstChild.innerHTML)
		{
			// Change the text on the size dropdown
			sizefield.innerHTML = obj.firstChild.innerHTML;
			
			// Update the style properties
			this.vbshout.update_style_property('size', sizefield.innerHTML);
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