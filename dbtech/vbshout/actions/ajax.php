<?php
$fetchtype = (VBSHOUT::$fetchtype == 'r' ? $_REQUEST : $_POST);

if (!$vbulletin->userinfo['dbtech_vbshout_banned'])
{
	// Handle ajax request
	VBSHOUT::ajax_handler($fetchtype['action']);
}
else
{
	// Initialise the XML object
	$xml = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');
	
	// Add a default group
	$xml->add_group('vbshout');
	
	// Add the error
	$xml->add_tag('error', $vbphrase['dbtech_vbshout_banned']);
	
	// Finish off the XML
	$xml->close_group();
	$xml->print_xml();
}
?>