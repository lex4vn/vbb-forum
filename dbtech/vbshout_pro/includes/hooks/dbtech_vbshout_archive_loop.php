<?php

if (($shouts_r['userid'] == $vbulletin->userinfo['userid'] AND $vbshout->permissions['caneditown']) OR
	($shouts_r['userid'] != $vbulletin->userinfo['userid'] AND $vbshout->permissions['caneditothers']))
{
	// We got the perms, give it to us
	$shouts_r['shouttype'] .= '(<a href="javascript://" onclick="return vBShout' . $instance['instanceid'] . '.edit_archive_shout(\'' . $shouts_r['shoutid'] . '\');">' . $vbphrase['edit'] . '</a> &middot; 
		<a href="javascript://" onclick="return vBShout' . $instance['instanceid'] . '.delete_archive_shout(\'' . $shouts_r['shoutid'] . '\');">' . $vbphrase['delete'] . '</a>) ';
	
	if (intval($vbulletin->versionnumber) == 3)
	{
		$shouts_r['dbtech_vbshout_aftermessage'] .= '
			<td class="alt1" style="display:none;" id="dbtech_shoutbox_editor_' . $shouts_r['shoutid'] . '_' . $instance['instanceid'] . '">
				<input type="text" class="primary textbox" name="dbtech_shoutbox_editor_text_' . $shouts_r['shoutid'] . '_' . $instance['instanceid'] . '" id="dbtech_shoutbox_editor_text_' . $shouts_r['shoutid'] . '_' . $instance['instanceid'] . '" style="width:75%; ' . implode(' ', $styleprops) . '" />
				<input type="button" class="button" value="' . $vbphrase['cancel'] . '" onclick="javascript:vBShout' . $instance['instanceid'] . '.cancel_editing_archive_shout(' . $shouts_r['shoutid'] . ');" />
				<input type="button" class="button" value="' . $vbphrase['save'] . '" onclick="javascript:vBShout' . $instance['instanceid'] . '.save_archive_shout(' . $shouts_r['shoutid'] . ');" />
			</td>';
	}
	else
	{
		$shouts_r['dbtech_vbshout_aftermessage'] .= '<div class="blockrow floatcontainer" style="display:none;" id="dbtech_shoutbox_editor_' . $shouts_r['shoutid'] . '_' . $instance['instanceid'] . '">
			<input type="text" class="primary textbox" name="dbtech_shoutbox_editor_text_' . $shouts_r['shoutid'] . '_' . $instance['instanceid'] . '" id="dbtech_shoutbox_editor_text_' . $shouts_r['shoutid'] . '_' . $instance['instanceid'] . '" style="width:75%; ' . implode(' ', $styleprops) . '" />
			<input type="button" class="button" value="' . $vbphrase['cancel'] . '" onclick="javascript:vBShout' . $instance['instanceid'] . '.cancel_editing_archive_shout(' . $shouts_r['shoutid'] . ');" />
			<input type="button" class="button" value="' . $vbphrase['save'] . '" onclick="javascript:vBShout' . $instance['instanceid'] . '.save_archive_shout(' . $shouts_r['shoutid'] . ');" />
		</div>';
	}
}
?>