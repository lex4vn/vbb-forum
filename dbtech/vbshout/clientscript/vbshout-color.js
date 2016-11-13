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

vBShout_Colors_Obj = function()
{
	// Object variables
	this.wrapper;		// ID of the editor's wrapper
	this.vbshout;
	this.instanceid;

	// #########################################################################
	// Initialiser for the functionality within
	this.init = function(wrapper, default_color, instanceid)
	{
		// Set the wrapper reference
		this.wrapper = wrapper;
		
		this.instanceid = instanceid;
		eval("this.vbshout = vBShout" + instanceid + ";");		
		this.editorid = 'dbtech_shoutbox_editor_wrapper';
		
		// Builds the color popup
		this.build_forecolor_popup(YAHOO.util.Dom.get(this.wrapper + '_popup_forecolor' + this.instanceid));
		
		// Set the default font
		YAHOO.util.Dom.get(this.wrapper + "_color_bar" + this.instanceid).style.backgroundColor = default_color;
		
		// Update the style properties
		this.vbshout.update_style_property('color', default_color);
	}
	
	// #########################################################################
	// Builds the popup for the font dropdown
	this.build_forecolor_popup = function(obj)
	{
		YAHOO.util.Event.on(obj, "mouseover", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mouseout", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mouseup", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		YAHOO.util.Event.on(obj, "mousedown", 	vBShout_Text_Editor.prototype.menu_context, obj, vBShout_Text_Editor.prototype);
		//YAHOO.util.Event.on(obj, "click", 	vBShout_Text_Editor_Events.prototype.colorout_onclick);
		var colors = YAHOO.util.Dom.getElementsByClassName('colorbutton', '', obj);
		if (colors.length)
		{
			for (var x = 0; x < colors.length; x++)
			{
				colors[x].editorid = this.editorid + '_' + this.instanceid;
				colors[x].controlkey = obj.id;
				colors[x].colorname = YAHOO.util.Dom.getStyle(colors[x].firstChild, "background-color");
				colors[x].id = this.editorid + '_color_' + this.translate_color_commandvalue(colors[x].colorname);
				
				YAHOO.util.Event.on(colors[x], "mouseover", this.menuoption_onmouseevent, 	obj);
				YAHOO.util.Event.on(colors[x], "mouseout", 	this.menuoption_onmouseevent, 	obj);
				YAHOO.util.Event.on(colors[x], "mouseup", 	this.menuoption_onmouseevent, 	obj);
				YAHOO.util.Event.on(colors[x], "mousedown", this.menuoption_onmouseevent, 	obj);
				YAHOO.util.Event.on(colors[x], "click", 	this.process_formatting,		colors[x], this);
			}
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
	// Try to determine the selected colour's name
	this.translate_color_commandvalue = function(forecolor)
	{
		if (is_moz)
		{
			if (forecolor == '' || forecolor == null)
			{
				forecolor = window.getComputedStyle(vBShout['frames'].editor.body, null).getPropertyValue('color');
			}
	
			if (forecolor.toLowerCase().indexOf('rgb') == 0)
			{
				var matches = forecolor.match(/^rgb\s*\(([0-9]+),\s*([0-9]+),\s*([0-9]+)\)$/);
				if (matches)
				{
					return vBShout_Text_Editor.prototype.translate_silly_hex((matches[1] & 0xFF).toString(16), (matches[2] & 0xFF).toString(16), (matches[3] & 0xFF).toString(16));
				}
				else
				{
					return this.translate_color_commandvalue(null);
				}
			}
			else
			{
				return forecolor;
			}
		}
		else
		{
			return vBShout_Text_Editor.prototype.translate_silly_hex((forecolor & 0xFF).toString(16), ((forecolor >> 8) & 0xFF).toString(16), ((forecolor >> 16) & 0xFF).toString(16));
		}
	}

	
	// #########################################################################
	// Builds the popup for the font dropdown	
	this.process_formatting = function(e, obj)
	{
		var colorfield = YAHOO.util.Dom.get(this.wrapper + "_color_bar" + this.instanceid);
		
		if (colorfield.style.backgroundColor != obj.colorname)
		{
			// Change the text on the font dropdown
			colorfield.style.backgroundColor = obj.colorname;
			
			// Update the style properties
			this.vbshout.update_style_property('color', obj.colorname);
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