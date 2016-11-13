<?php
/**
|*  Ideal Chat Pro v1.3.0
|*  Created: July 30th, 2011
|*  Last Modified: Never
|*  Author: Ideal Web Technologies (www.idealwebtech.com)
|*
|*  Copyright (c) 2011 Ideal Web Technologies
|*  This file is only to be used with the consent of Ideal Web Technologies 
|*  and may not be redistributed in whole or significant part!  By using
|*  this file, you agree to the Ideal Web Technologies' Terms of Service
|*  at www.idealwebtech.com/documents/tos.html
**/

// ### SET PHP ENVIRONMENT ################################################
error_reporting(E_ALL & ~E_NOTICE);

// ### DEFINE IMPORTANT CONSTANTS #########################################
define('NO_CP_COPYRIGHT', 1);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();

// ### REQUIRE BACK-END ###################################################
require_once ('./global.php');

// ### GLOBAL SETUP #######################################################
print_cp_header('Ideal Chat Pro v1.3.0');

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'chatrooms';
}

// ########################################################################
// ### START MAIN SCRIPT ##################################################
// ########################################################################

// ### EDIT CHAT ROOMS ####################################################
if ($_REQUEST['do'] == 'chatrooms')
{
	// Check if chat rooms are disabled
	if (!$vbulletin->options['iwt_idealchat_chatroomsenabled'] && $_REQUEST['skip_msg'] != 'true')
	{
		print_cp_message($vbphrase['iwt_idealchat_cp_chatroomsdisabled'], 'iwt_idealchat.php?do=chatrooms&amp;skip_msg=true', 0, '', true);
	}

	// Output the page
	print_form_header('iwt_idealchat');
	print_table_header('<span style="float: right;">' . construct_link_code($vbphrase['iwt_idealchat_cp_addchatroom'], 'iwt_idealchat.php?do=addchatroom') . '</span>' . $vbphrase['iwt_idealchat_cp_currentchatrooms'], 3, false, '', 'left');

	// Grab the rooms
	$roomsquery = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "iwt_chatrooms");

	// Check if there are no rooms
	if ($vbulletin->db->num_rows($roomsquery) == 0)
	{
		print_description_row($vbphrase['iwt_idealchat_cp_nochatrooms'], false, 3);
	}
	else
	{
		print_cells_row(array($vbphrase['iwt_idealchat_cp_roomname'], $vbphrase['iwt_idealchat_cp_passwordprotected'], ''), true);

		while ($room = $vbulletin->db->fetch_array($roomsquery))
		{
			$cell[0] = $room['roomname'];
			$cell[1] = ($room['password'] ? $vbphrase['yes'] : $vbphrase['no']);

			$cell[2] = construct_link_code($vbphrase['edit'], 'iwt_idealchat.php?do=editchatroom&amp;roomid=' . $room['id']) .
					   construct_link_code($vbphrase['delete'], 'iwt_idealchat.php?do=deletechatroom&amp;roomid=' . $room['id']);

			print_cells_row($cell);
		}
	}

	print_table_footer();
}

// ### ADD CHAT ROOM ######################################################
else if ($_REQUEST['do'] == 'addchatroom')
{
	print_form_header('iwt_idealchat', 'addchatroom_process');
	print_table_header($vbphrase['iwt_idealchat_cp_addchatroom']);

	print_input_row($vbphrase['iwt_idealchat_cp_roomname'] . '*', 'roomname', '', true, '35', '100');
	print_input_row($vbphrase['iwt_idealchat_cp_password'], 'password', '', true, '35', '100');

	print_submit_row();
}

else if ($_REQUEST['do'] == 'addchatroom_process')
{
    $vbulletin->input->clean_array_gpc('p', array(
		'roomname' => TYPE_STR,
		'password' => TYPE_STR
	));

    $roomname = $vbulletin->GPC['roomname'];
    $password = $vbulletin->GPC['password'];

    if (!$roomname)
    {
        print_stop_message('iwt_idealchat_cp_needroomname');
    }

	if ($password)
	{
		$password = md5($password);
	}

	$vbulletin->db->query_write("
		INSERT INTO " . TABLE_PREFIX . "iwt_chatrooms (roomname, password, usercreated)
		VALUES ('" . $vbulletin->db->escape_string($roomname) . "', '" . $vbulletin->db->escape_string($password) . "', 0)
	");

	print_cp_message($vbphrase['iwt_idealchat_cp_chatroomadded'], 'iwt_idealchat.php?do=chatrooms&amp;skip_msg=true');
}

// ### EDIT CHAT ROOM #####################################################
else if ($_REQUEST['do'] == 'editchatroom')
{
	$roomid = $vbulletin->input->clean_gpc('r', 'roomid', TYPE_UINT);

	if (!$roomid)
	{
        print_stop_message('iwt_idealchat_cp_invalidroomid');
	}

	// Grab the room
	$room = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "iwt_chatrooms WHERE id=$roomid");

	if (!$room['id'])
	{
        print_stop_message('iwt_idealchat_cp_invalidroomid');
	}

	// Print out the form
	print_form_header('iwt_idealchat', 'editchatroom_process');
	print_table_header($vbphrase['iwt_idealchat_cp_addchatroom']);

	print_input_row($vbphrase['iwt_idealchat_cp_roomname'] . '*', 'roomname', $room['roomname'], true, '35', '100');
	print_input_row($vbphrase['iwt_idealchat_cp_password'], 'password', '', true, '35', '100');

	if ($room['password'])
	{
		print_yes_no_row($vbphrase['iwt_idealchat_cp_removepassword'], 'removepassword', '0');
	}

	construct_hidden_code('roomid', $room['id']);
	print_hidden_fields();
	print_submit_row();
}

else if ($_REQUEST['do'] == 'editchatroom_process')
{
    $vbulletin->input->clean_array_gpc('p', array(
		'roomid' => TYPE_UINT,
		'roomname' => TYPE_STR,
		'password' => TYPE_STR,
		'removepassword' => TYPE_BOOL
	));

	$roomid = $vbulletin->GPC['roomid'];
    $roomname = $vbulletin->GPC['roomname'];
    $password = $vbulletin->GPC['password'];
    $removepassword = $vbulletin->GPC['removepassword'];

	if (!$roomid)
	{
        print_stop_message('iwt_idealchat_cp_invalidroomid');
	}

	// Grab the room
	$room = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "iwt_chatrooms WHERE id=$roomid");

	if (!$room['id'])
	{
        print_stop_message('iwt_idealchat_cp_invalidroomid');
	}

	// Check for errors
    if (!$roomname)
    {
        print_stop_message('iwt_idealchat_cp_needroomname');
    }

	if ($password)
	{
		$password = md5($password);
	}
	else
	{
		if ($removepassword)
		{
			$password = '';
		}
		else
		{
			$password = $room['password'];
		}
	}

	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "iwt_chatrooms
		SET roomname='" . $vbulletin->db->escape_string($roomname) . "', password='" . $vbulletin->db->escape_string($password) . "'
		WHERE id=$roomid
	");

	print_cp_message($vbphrase['iwt_idealchat_cp_chatroomupdated'], 'iwt_idealchat.php?do=chatrooms&amp;skip_msg=true');
}

// ### DELETE CHAT ROOM ###################################################
else if ($_REQUEST['do'] == 'deletechatroom')
{
	$roomid = $vbulletin->input->clean_gpc('r', 'roomid', TYPE_UINT);

	if (!$roomid)
	{
        print_stop_message('iwt_idealchat_cp_invalidroomid');
	}

	print_confirmation($vbphrase['iwt_idealchat_cp_chatroomdeletedconf'], 'iwt_idealchat', 'deletechatroom_process', array('roomid' => $roomid));
}

else if ($_REQUEST['do'] == 'deletechatroom_process')
{
	$roomid = $vbulletin->input->clean_gpc('p', 'roomid', TYPE_UINT);

	if (!$roomid)
	{
        print_stop_message('iwt_idealchat_cp_invalidroomid');
	}

	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "iwt_chatrooms WHERE id=$roomid");
	print_cp_message($vbphrase['iwt_idealchat_cp_chatroomdeleted'], 'iwt_idealchat.php?do=chatrooms&amp;skip_msg=true');
}

// ### GLOBAL SHUTDOWN ####################################################
echo '<p align="center"><a class="copyright" target="_blank" href="http://www.idealwebtech.com/">Powered by Ideal Chat Pro v1.3.0, Copyright &copy; 2011 Ideal Web Technologies</a></p>';

print_cp_footer();

?>