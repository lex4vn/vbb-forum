<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 14/7/2010, 2:55
 */

if ( ! defined( 'NV_IS_MOD_USER' ) )
{
    die( 'Stop!!!' );
}
$sessionhash = $nv_Request->get_string( 'sessionhash', 'cookie', '', false );
if ( preg_match( "/^[a-z0-9]+$/", $sessionhash ) )
{
    if ( file_exists( NV_ROOTDIR . '/' . DIR_FORUM . '/includes/config.php' ) )
    {
        include ( NV_ROOTDIR . '/' . DIR_FORUM . '/includes/config.php' );
        $tableprefix = $config['Database']['tableprefix'];
        $db->sql_query( "DELETE FROM " . $tableprefix . "session WHERE sessionhash = " . $db->dbescape( $sessionhash ) . "" );
    }
}
$nv_Request->unset_request( 'bbuserid', 'cookie' );
$nv_Request->unset_request( 'bbpassword', 'cookie' );
$nv_Request->unset_request( 'sessionhash', 'cookie' );

?>