<?php
/*
 * Forum Runner
 *
 * Copyright (c) 2013 to Internet Brands, Inc
 *
 * This file may not be redistributed in whole or significant part.
 *
 * http://www.forumrunner.com
 */

if (!is_object($vbulletin->db)) {
    return;
}

require_once(DIR . '/forumrunner/support/Snoopy.class.php');
require_once(DIR . '/forumrunner/sitekey.php');
require_once(DIR . '/forumrunner/support/JSON.php');

// You must have your valid Forum Runner forum site key.  This can be
// obtained from http://www.forumrunner.com in the Forum Manager.
if (!$mykey || $mykey == '') {
    return;
}

// Check to see if our prompt is disabled.  If so, exit.
$promptres = $vbulletin->db->query_read_slave("
    SELECT value
    FROM " . TABLE_PREFIX . "setting
    WHERE varname = 'forumrunner_redirect_onoff'
");

$prompt = $vbulletin->db->fetch_array($promptres);
if (intval($prompt['value']) == 0) {
    return;
}

// We know we have a prompt enabled at this point.  Phone home for status.
$snoopy = new snoopy();
$snoopy->submit('http://www.forumrunner.com/forumrunner/request.php',
    array(
        'cmd' => 'checkstatus',
        'sitekey' => $mykey,
    )
);

$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
$out = $json->decode($snoopy->results);

if (!$out['success']) {
    // If request failed for any reason, do not change anything.
    return;
}

if ($out['data']['pub']) {
    // We are published and fine.
    return;
}

// We are unpublished.  Disable prompt.
$vbulletin->db->query_write("
    UPDATE " . TABLE_PREFIX . "setting
    SET value = 0
    WHERE varname = 'forumrunner_redirect_onoff'
");

?>
