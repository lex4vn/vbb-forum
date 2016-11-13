<?php

require_once(DIR . '/includes/class_dbalter.php');
$db_alter = new vB_Database_Alter_MySQL($db);

echo '<ul>';
if ($db_alter->fetch_table_info('user'))
{
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shoutboxsize',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_shoutboxsize_detached',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	$db_alter->add_field(array(
		'name'       => 'dbtech_vbshout_displayorder',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'user</em></strong></li>');
}
echo '</ul>';
?>