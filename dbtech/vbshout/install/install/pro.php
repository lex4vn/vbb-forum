<?php

echo '<ul>';
// New tables
$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_command` (
	  `commandid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `userid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	  `command` VARCHAR( 50 ) NOT NULL DEFAULT '',
	  `useinput` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
	  `output` MEDIUMTEXT NULL DEFAULT NULL ,
	  INDEX ( `userid` ) ,
	  PRIMARY KEY (`commandid`)
	)
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_command</em></strong></li>');

$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_deeplog` (
	  `deeplogid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `shoutid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ,
	  `userid` INT( 10 ) NOT NULL DEFAULT '0' ,
	  `username` VARCHAR( 100 ) NOT NULL DEFAULT '' ,
	  `dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ,
	  `message` MEDIUMTEXT NULL ,
	  `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' ,
	  `id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ,
	  `notification` ENUM( '', 'thread', 'reply' ) NOT NULL DEFAULT '' ,
	  PRIMARY KEY (`deeplogid`)
	)
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_deeplog</em></strong></li>');


echo '</ul><ul>';

if ($db_alter->fetch_table_info('user'))
{
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_silenced',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shoutarea',
		'type'       => 'varchar',
		'length'     => '15',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => 'default'
	));
	
	// Populate the shout table
	$db->query_write("UPDATE " . TABLE_PREFIX . "user SET dbtech_vbshout_settings = dbtech_vbshout_settings + 4096");
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'user</em></strong></li>');	
}

echo '</ul>';
?>