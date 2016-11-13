<?php

require_once(DIR . '/includes/class_dbalter.php');
$db_alter = new vB_Database_Alter_MySQL($db);

echo '<ul>';
$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_vbshout_report`
	(
		`reportid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`reportuserid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`userid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`shoutid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`instanceid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`shout` MEDIUMTEXT NULL DEFAULT NULL,
		`reportreason` MEDIUMTEXT NULL DEFAULT NULL,
		`modnotes` MEDIUMTEXT NULL DEFAULT NULL,
		`handled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'		
	)
");
print_modification_message('<li>Created Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_report</em></strong></li>');

echo '</ul><ul>';
if ($db_alter->fetch_table_info('dbtech_vbshout_instance'))
{
	$db_alter->add_field(array(
		'name'       => 'bbcodepermissions',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));
	$db_alter->add_field(array(
		'name'       => 'notices',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));
	$db->query_write("UPDATE " . TABLE_PREFIX . "dbtech_vbshout_instance SET bbcodepermissions = 'a:4:{i:6;i:95;i:5;i:95;i:2;i:95;i:7;i:95;}'");
	
	global $vbshout;
	if (method_exists($vbshout, 'build_cache'))
	{
		$vbshout->build_cache('dbtech_vbshout_instance');
	}
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_instance</em></strong></li>');
}

if ($db_alter->fetch_table_info('user'))
{
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_soundsettings',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'user</em></strong></li>');
}
echo '</ul>';
?>