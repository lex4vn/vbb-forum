<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 31/05/2010, 00:36
 */
if ( ! defined( 'NV_MAINFILE' ) ) die( 'Stop!!!' );

$sessionhash = $nv_Request->get_string( 'sessionhash', 'cookie', '', false );
if ( preg_match( "/^[a-z0-9]+$/", $sessionhash ) )
{
    require_once ( NV_ROOTDIR . '/' . DIR_FORUM . '/nukeviet/function.php' );
    $tableprefix = "";
    if ( file_exists( NV_ROOTDIR . '/' . DIR_FORUM . '/includes/config.php' ) )
    {
        include ( NV_ROOTDIR . '/' . DIR_FORUM . '/includes/config.php' );
        $tableprefix = $config['Database']['tableprefix'];
    }
    $user_info['userid'] = 0;
    $query = $db->sql_query( "SELECT userid, idhash FROM " . $tableprefix . "session WHERE userid > 0 AND sessionhash ='" . $sessionhash . "'" );
    while ( list( $userid, $idhash ) = $db->sql_fetchrow( $query ) )
    {
        if ( $idhash == md5( $client_info['agent'] . fetch_substr_ip( $client_info['ip'] ) ) )
        {
            $user_info['userid'] = $userid;
        }
    }
    if ( $user_info['userid'] == 0 )
    {
        $nv_Request->unset_request( 'bbuserid', 'cookie' );
        $nv_Request->unset_request( 'bbpassword', 'cookie' );
        $nv_Request->unset_request( 'sessionhash', 'cookie' );
        $user_info = array();
    }
}

?>