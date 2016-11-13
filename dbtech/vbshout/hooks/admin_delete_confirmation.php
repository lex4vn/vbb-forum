<?php
if (THIS_SCRIPT == 'vbshout')
{
	$idfield = substr($table, strlen('dbtech_vbshout_')) . 'id';
	$handled = true;
	
	$item = $vbulletin->db->query_first("
		SELECT $idfield, $titlename AS title
		FROM " . TABLE_PREFIX . "$table
		WHERE $idfield = '$itemid'
	");
}
?>