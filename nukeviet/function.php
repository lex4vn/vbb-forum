<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 14/7/2010, 3:4
 */

if ( ! defined( 'NV_MAINFILE' ) ) die( 'Stop!!!' );

$remember = true;
$user_groupid_in_vbb = array( 
    2, 5, 6, 7 
);

define( 'TYPE_STR', 7 ); // force trimmed string
define( 'TYPE_INT', 2 ); // force integer


function fetch_substr_ip ( $ip, $length = null )
{
    if ( $length === null or $length > 3 )
    {
        $length = 1;
    }
    return implode( '.', array_slice( explode( '.', $ip ), 0, 4 - $length ) );
}

function fetch_sessionhash ( )
{
    return md5( uniqid( microtime(), true ) );
}

function build_query_array ( $userid, $permanent )
{
    global $nv_Request, $db, $db_config, $client_info;
    
    $return = array();
    
    $db_fields = array( 
        'sessionhash' => TYPE_STR, 'userid' => TYPE_INT, 'host' => TYPE_STR, 'idhash' => TYPE_STR, 'lastactivity' => TYPE_INT, 'location' => TYPE_STR, 'styleid' => TYPE_INT, 'languageid' => TYPE_INT, 'loggedin' => TYPE_INT, 'inforum' => TYPE_INT, 'inthread' => TYPE_INT, 'incalendar' => TYPE_INT, 'badlocation' => TYPE_INT, 'useragent' => TYPE_STR, 'bypass' => TYPE_INT, 'profileupdate' => TYPE_INT 
    );
    
    $sessionhash = fetch_sessionhash();
    $nv_Request->set_Cookie( 'lastactivity', 0, NV_LIVE_COOKIE_TIME, false );
    $nv_Request->set_Cookie( 'lastvisit', NV_CURRENTTIME, NV_LIVE_COOKIE_TIME, false );
    if ( $permanent )
    {
        $expire = NV_LIVE_COOKIE_TIME;
    }
    else
    {
        $expire = 0;
    }
    $nv_Request->set_Cookie( 'sessionhash', $sessionhash, $expire, false );
    $thisvars = array( 
        'sessionhash' => $sessionhash, 'dbsessionhash' => $sessionhash, 'userid' => intval( $userid ), 'host' => $client_info['ip'], 'idhash' => md5( $client_info['agent'] . fetch_substr_ip( $client_info['ip'] ) ), 'lastactivity' => NV_CURRENTTIME, 'location' => '', 'styleid' => 0, 'languageid' => 0, 'loggedin' => intval( $userid ) ? 1 : 0, 'inforum' => 0, 'inthread' => 0, 'incalendar' => 0, 'badlocation' => 0, 'profileupdate' => 0, 'useragent' => $client_info['agent'], 'bypass' => 0 
    );
    
    foreach ( $db_fields as $fieldname => $cleantype )
    {
        switch ( $cleantype )
        {
            case TYPE_INT:
                $cleaned = intval( $thisvars["$fieldname"] );
                break;
            case TYPE_STR:
            default:
                $cleaned = "'" . $thisvars["$fieldname"] . "'";
        }
        $return["$fieldname"] = $cleaned;
    }
    
    return $return;
}

?>