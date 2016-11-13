<?php

require_once(DIR . '/includes/class_dbalter.php');
$db_alter = new vB_Database_Alter_MySQL($db);


echo '<ul>';
// New Tables

$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_ignorelist` (
	  `userid` INT( 10 ) UNSIGNED NOT NULL ,
	  `ignoreuserid` INT( 10 ) UNSIGNED NOT NULL ,
	  PRIMARY KEY ( `userid` , `ignoreuserid` )
	)
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_ignorelist</em></strong></li>');

$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_log` (
	  `logid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `userid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	  `username` VARCHAR( 100 ) NOT NULL ,	  
	  `dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	  `ipaddress` CHAR( 15 ) NOT NULL ,
	  `command` VARCHAR( 50 ) NOT NULL ,
	  `comment` MEDIUMTEXT NULL DEFAULT NULL ,
	  PRIMARY KEY (`logid`)
	)				 
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_log</em></strong></li>');

$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_shout` (
	  `shoutid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `userid` INT( 10 ) NOT NULL DEFAULT '0' ,
	  `dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ,
	  `message` MEDIUMTEXT NULL ,
	  `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' ,
	  `id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ,
	  `notification` ENUM( '', 'thread', 'reply' ) NOT NULL DEFAULT '' ,
	  PRIMARY KEY (`shoutid`),
	  KEY `type` (`type`,`id`),
	  KEY `userid` (`userid`),
	  KEY `dateline` (`dateline`)
	)
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_shout</em></strong></li>');


echo '</ul><ul>';
// Altered Tables

// Add the administrator field
if ($db_alter->fetch_table_info('administrator'))
{
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshoutadminperms',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'administrator</em></strong></li>');	
}

// Add the usergroup field
if ($db_alter->fetch_table_info('forum'))
{
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_newthread',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_newreply',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'forum</em></strong></li>');	
}

// Add the usergroup field
if ($db_alter->fetch_table_info('user'))
{
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_banned',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_settings',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '4095'
	));
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shouts',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shoutstyle',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
	));	
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'user</em></strong></li>');	
}

if ($db_alter->fetch_table_info('usergroup'))
{
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshoutpermissions',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'usergroup</em></strong></li>');	
}


echo '</ul><ul>';
// Populated Tables

// Populate the datastore table
$db->query_write("
	REPLACE INTO " . TABLE_PREFIX . "datastore
		(title, data)
	VALUES 
		('dbtech_vbshout_aoptime', 0),
		('dbtech_vbshout_sticky', 'Welcome to DragonByte Tech: vBShout!')
");
print_modification_message('<li>Populated Table: <strong><em>' . TABLE_PREFIX . 'datastore</em></strong></li>');

$db->query_write("
	INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_shout
		(userid, dateline, message)
	VALUES 
		('-1', " . TIMENOW . ", 'Congratulations on your installation of DragonByte Tech: vBShout! We have taken the liberty of setting some default options for you, but you should enter the AdminCP and familiarise yourself with the various settings. Use the /prune command to get rid of this message, or double-click it and click the Delete button. Enjoy your new Shoutbox!')
");
print_modification_message('<li>Populated Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_shout</em></strong></li>');


echo '</ul>';
?>