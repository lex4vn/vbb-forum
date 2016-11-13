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

vBShout_FontStyle_Obj = function()
{
	// Object variables
	this.wrapper;		// ID of the editor's wrapper
	this.vbshout;
	this.instanceid;	
	
	// #########################################################################
	// Initialiser for the functionality within
	this.init = function(wrapper, boldStyle, italicStyle, underlineStyle, instanceid)
	{
		// Set the wrapper reference
		this.wrapper = wrapper;
		
		this.instanceid = instanceid;
		eval("this.vbshout = vBShout" + instanceid + ";");
		
		if (YAHOO.util.Dom.get(this.wrapper + '_cmd_bold' + instanceid))
		{
			// Bold button is enabled
			this.init_command_button(YAHOO.util.Dom.get(this.wrapper + '_cmd_bold' + instanceid), 'bold');
			
			// Update the style properties
			this.vbshout.update_style_property('bold', boldStyle);
		}
		
		if (YAHOO.util.Dom.get(this.wrapper + '_cmd_italic' + instanceid))
		{
			// Bold button is enabled
			this.init_command_button(YAHOO.util.Dom.get(this.wrapper + '_cmd_italic' + instanceid), 'italic');
			
			// Update the style properties
			this.vbshout.update_style_property('italic', italicStyle);
		}
		
		if (YAHOO.util.Dom.get(this.wrapper + '_cmd_underline' + instanceid))
		{
			// Bold button is enabled
			this.init_command_button(YAHOO.util.Dom.get(this.wrapper + '_cmd_underline' + instanceid), 'underline');
			
			// Update the style properties
			this.vbshout.update_style_property('underline', underlineStyle);
		}
	}
	
	
	this.init_command_button = function(obj, cmd)
	{
		obj.cmd = cmd;
		obj.vbshout = this.vbshout;
		obj.editorid = this.wrapper;
		
		obj.state = (this.vbshout.editor[obj.cmd] != '' ? true : false);
		vBShout_Text_Editor.prototype.set_control_style(obj, 'button', this.vbshout.editor[obj.cmd] != '' ? 'selected' : 'normal');
	
		// event handlers
		obj.onclick = obj.onmousedown = obj.onmouseover = obj.onmouseout = this.command_button_onmouseevent;
	}
	
	this.command_button_onmouseevent = function(e)
	{
		e = do_an_e(e);
		
		if (e.type == 'click' && !YAHOO.util.Dom.hasClass(this.wrapper + "_cmd_" + this.cmd, "imagebutton_disabled"))
		{
			// Set the state of the button
			this.state = this.state == true ? false : true;
			
			// Update the button's context before saving
			vBShout_Text_Editor.prototype.button_context(this, 'mouseover');
			
			// Update the style properties
			this.vbshout.update_style_property(this.cmd, this.vbshout.editor[this.cmd] != '' ? '' : this.cmd);
		}
		else
		{
			// Set the button's context
			vBShout_Text_Editor.prototype.button_context(this, e.type);
		}
	}
}