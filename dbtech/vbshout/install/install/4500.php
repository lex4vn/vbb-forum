<?php

require_once(DIR . '/includes/class_dbalter.php');
$db_alter = new vB_Database_Alter_MySQL($db);

if ($vbulletin->products['dbtech_vbshout_pro'])
{
	// We have the pro version installed, remove it without erasing data
	if (!function_exists('delete_product'))
	{
		require(DIR . '/includes/adminfunctions_plugin.php');
	}
	delete_product('dbtech_vbshout_pro');
}
else
{
	// We're lacking the Pro database
	require($installpath . '/install/pro.php');
}

echo '<ul>';
$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_chatroom` (
	  `chatroomid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `title` VARCHAR( 50 ) NOT NULL ,
	  `membergroupids` CHAR( 250 ) NOT NULL ,
	  `instanceid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	  `active` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1',
	  `creator` INT( 10 ) UNSIGNED NOT NULL DEFAULT '1',
	  PRIMARY KEY (`chatroomid`) ,	  
	  INDEX ( `instanceid` )
	)
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_chatroom</em></strong></li>');

$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_chatroommember` (
	  `userid` INT( 10 ) UNSIGNED NOT NULL ,
	  `chatroomid` INT( 10 ) UNSIGNED NOT NULL ,
	  `status` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
	  PRIMARY KEY (`userid`, `chatroomid`)   
	)
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_chatroommember</em></strong></li>');

$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_instance` (
	  `instanceid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `name` VARCHAR( 50 ) NOT NULL ,
	  `description` MEDIUMTEXT NULL DEFAULT NULL ,
	  `active` TINYINT( 1 ) UNSIGNED NOT NULL ,
	  `autodisplay` TINYINT( 1 ) UNSIGNED NOT NULL ,
	  `deployment` MEDIUMTEXT NULL DEFAULT NULL ,
	  `templates` MEDIUMTEXT NULL DEFAULT NULL ,
	  `permissions` MEDIUMTEXT NULL DEFAULT NULL ,
	  PRIMARY KEY (`instanceid`)
	)
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_instance</em></strong></li>');


if ($db_alter->fetch_table_info('dbtech_vbshout_shout'))
{
	$db_alter->add_field(array(
		'name'       => 'forumid',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	$db_alter->add_field(array(
		'name'       => 'chatroomid',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	$db_alter->add_field(array(
		'name'       => 'instanceid',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_shout</em></strong></li>');	
}

echo '</ul><ul>';

if ($vbulletin->products['dbtech_vbshout'])
{
	$permarray = array();
	foreach ((array)$vbulletin->usergroupcache as $usergroupid => $usergroup)
	{
		// Just stuff all the perms in there
		$permarray["$usergroupid"] = $usergroup['dbtech_vbshoutpermissions'];
	}
}
else
{
	$permarray = unserialize('a:8:{i:2;i:2108;i:6;i:4092;i:4;i:12;i:8;i:0;i:7;i:3964;i:5;i:4092;i:1;i:4;i:3;i:12;}');
}

$db->query_write("
INSERT INTO `" . TABLE_PREFIX . "dbtech_vbshout_instance`
	(`name`, `description`, `active`, `autodisplay`, `deployment`, `templates`, `permissions`)
VALUES (
	'Shoutbox',
	'This is the default Shoutbox.\r\nYou can change this description by clicking [Edit].',
	1,
	" . (isset($vbulletin->options['dbtech_vbshout_autoadd']) ? $vbulletin->options['dbtech_vbshout_autoadd'] : 1) . ",
	'" . (isset($vbulletin->options['dbtech_vbshout_includescripts']) ? $vbulletin->options['dbtech_vbshout_includescripts'] : 'index') . "',
	'" . (isset($vbulletin->options['dbtech_vbshout_includetemplates']) ? $vbulletin->options['dbtech_vbshout_includetemplates'] : '') . "',
	'" . $db->escape_string(trim(serialize($permarray))) . "'
)");
print_modification_message('<li>Updated Instance: <strong><em>1</em></strong></li>');

// Populate the shout table
$db->query_write("UPDATE " . TABLE_PREFIX . "dbtech_vbshout_shout SET instanceid = (SELECT instanceid FROM " . TABLE_PREFIX . "dbtech_vbshout_instance ORDER BY instanceid DESC LIMIT 1)");
print_modification_message('<li>Updated Shouts: <strong><em>Instance ID</em></strong></li>');

$db->query_write("UPDATE " . TABLE_PREFIX . "dbtech_vbshout_shout SET type = 32 WHERE userid = -1");
print_modification_message('<li>Updated Shouts: <strong><em>SYSTEM</em></strong></li>');

$db->query_write("UPDATE " . TABLE_PREFIX . "user SET dbtech_vbshout_shoutstyle = NULL");
print_modification_message('<li>Updated Shout Styles: <strong><em>Reset</em></strong></li>');

/*
// Populate the shout table
$db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = value + 256, defaultvalue = defaultvalue + 256 WHERE varname = 'dbtech_vbshout_editors'");
print_modification_message('<li>Updated Setting: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_editors</em></strong></li>');

$db->query_write("UPDATE " . TABLE_PREFIX . "user SET dbtech_vbshout_settings = dbtech_vbshout_settings + 4096");
print_modification_message('<li>Updated Setting: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_settings</em></strong></li>');
*/

echo '</ul>';
?>