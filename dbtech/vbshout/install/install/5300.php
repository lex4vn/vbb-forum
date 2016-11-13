<?php

require_once(DIR . '/includes/class_dbalter.php');
$db_alter = new vB_Database_Alter_MySQL($db);

echo '<ul>';
if ($db_alter->fetch_table_info('dbtech_vbshout_chatroom'))
{
	$db_alter->add_field(array(
		'name'       => 'members',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_chatroom</em></strong></li>');
}
if ($db_alter->fetch_table_info('dbtech_vbshout_instance'))
{
	$db_alter->add_field(array(
		'name'       => 'sticky_raw',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_instance</em></strong></li>');
}
if ($db_alter->fetch_table_info('dbtech_vbshout_shout'))
{
	$db_alter->add_field(array(
		'name'       => 'message_raw',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));	
	print_modification_message('<li>Altered Table: <strong><em>' . TABLE_PREFIX . 'dbtech_vbshout_shout</em></strong></li>');
}
echo '</ul>';

define('CP_REDIRECT', 'vbshout.php?do=finalise&version=5300');
define('DISABLE_PRODUCT_REDIRECT', true);
?>