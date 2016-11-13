<?php

require_once(DIR . '/includes/class_dbalter.php');
$db_alter = new vB_Database_Alter_MySQL($db);

echo '<ul>';
if ($db_alter->fetch_table_info('dbtech_vbshout_chatroommember'))
{
	$db_alter->add_field(array(
		'name'       => 'invitedby',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_chatroommember</em></strong></li>');	
}

if ($db_alter->fetch_table_info('dbtech_vbshout_instance'))
{
	$db_alter->add_field(array(
		'name'       => 'sticky',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));
	$db_alter->add_field(array(
		'name'       => 'shoutsound',
		'type'       => 'varchar',
		'length'     => '50',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));
	$db_alter->add_field(array(
		'name'       => 'invitesound',
		'type'       => 'varchar',
		'length'     => '50',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));
	$db_alter->add_field(array(
		'name'       => 'pmsound',
		'type'       => 'varchar',
		'length'     => '50',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));
	
	$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_vbshout_instance
		CHANGE active active TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'
	");
	$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_vbshout_instance
		CHANGE autodisplay autodisplay TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'		
	");
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_instance</em></strong></li>');
}

if ($db_alter->fetch_table_info('user'))
{
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_pm',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'user</em></strong></li>');
}

$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_vbshout_shout
	CHANGE forumid forumid INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	CHANGE instanceid instanceid INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	CHANGE chatroomid chatroomid INT( 10 ) UNSIGNED NOT NULL DEFAULT '0'	
");
print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_shout</em></strong></li>');
echo '</ul>';
?>