<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @copyright 2009
 * @createdate 10/03/2010 10:51
 */

if ( ! defined( 'NV_IS_MOD_USER' ) )
{
    die( 'Stop!!!' );
}

if ( file_exists( NV_ROOTDIR . '/' . DIR_FORUM . '/includes/config.php' ) )
{
    $password_crypt = $crypt->hash( $nv_password );
    
    require_once ( NV_ROOTDIR . '/' . DIR_FORUM . '/nukeviet/function.php' );
    
    $error = $lang_global['loginincorrect'];
    include ( NV_ROOTDIR . '/' . DIR_FORUM . '/includes/config.php' );
    
    $tableprefix = $config['Database']['tableprefix'];
    $db->sql_query( "SET NAMES 'latin1'" );
    $user_info = $db->sql_fetchrow( $db->sql_query( "SELECT * FROM `" . $tableprefix . "user` WHERE `username`=" . $db->dbescape( $nv_username ) . "" ) );
    if ( $user_info['userid'] > 0 and $user_info['password'] == md5( md5( $nv_password ) . $user_info['salt'] ) )
    {
        if ( $remember )
        {
            $nv_Request->set_Cookie( 'bbuserid', $user_info['userid'], NV_LIVE_COOKIE_TIME, false );
            $nv_Request->set_Cookie( 'bbpassword', md5( $user_info['password'] ), NV_LIVE_COOKIE_TIME, false );
        }
        
        $cleaned = build_query_array( $user_info['userid'], $remember );
        $db->sql_query( "INSERT IGNORE INTO " . $tableprefix . "session (" . implode( ', ', array_keys( $cleaned ) ) . ") VALUES (" . implode( ', ', $cleaned ) . ")" );
        
        $user_info['active'] = 0;
        $usergroupid = intval( $user_info['usergroupid'] );
        if ( in_array( $usergroupid, $user_groupid_in_vbb ) )
        {
            $user_info['active'] = 1;
        }
        
        $birthday = 0;
        if ( $user_info['birthday'] != "" )
        {
            $arr_birthday = array_map( "intval", explode( "-", $user_info['birthday'] ) );
            if ( count( $arr_birthday ) == 3 )
            {
                $birthday = mktime( 0, 0, 0, $arr_birthday[0], $arr_birthday[1], $arr_birthday[2] );
            }
        }
        
        $user_info['userid'] = intval( $user_info['userid'] );
        $user_info['username'] = $user_info['username'];
        $user_info['email'] = $user_info['email'];
        $user_info['full_name'] = $user_info['username'];
        $user_info['birthday'] = $birthday;
        $user_info['regdate'] = intval( $user_info['joindate'] );
        
        $user_info['website'] = $user_info['homepage'];
        $user_info['location'] = "";
        $user_info['sig'] = "";
        $user_info['yim'] = $user_info['yahoo'];
        $user_info['view_mail'] = 0;
        
        $db->sql_query( "SET NAMES 'utf8'" );
        $sql = "SELECT * FROM `" . NV_USERS_GLOBALTABLE . "` WHERE `userid`=" . intval( $user_info['userid'] );
        $result = $db->sql_query( $sql );
        $numrows = $db->sql_numrows( $result );
        
        if ( $db->sql_numrows( $result ) > 0 )
        {
            $sql = "UPDATE `" . NV_USERS_GLOBALTABLE . "` SET 
                `username` = " . $db->dbescape( $user_info['username'] ) . ", 
                `md5username` = " . $db->dbescape( md5( $user_info['username'] ) ) . ", 
                `password` = " . $db->dbescape( $password_crypt ) . ", 
                `email` = " . $db->dbescape( $user_info['email'] ) . ", 
                `full_name` = " . $db->dbescape( $user_info['full_name'] ) . ", 
                `birthday`=" . $user_info['birthday'] . ", 
				`sig`=" . $db->dbescape( $user_info['sig'] ) . ", 
                `regdate`=" . $user_info['regdate'] . ", 
                `website`=" . $db->dbescape( $user_info['website'] ) . ", 
                `location`=" . $db->dbescape( $user_info['location'] ) . ", 
                `yim`=" . $db->dbescape( $user_info['yim'] ) . ", 
                `view_mail`=" . $user_info['view_mail'] . ",
                `active`=" . $user_info['active'] . ",
                `last_login`=" . NV_CURRENTTIME . ", 
                `last_ip`=" . $db->dbescape( $client_info['ip'] ) . ", 
                `last_agent`=" . $db->dbescape( $client_info['agent'] ) . "
                 WHERE `userid`=" . $user_info['userid'];
        }
        else
        {
            $sql = "INSERT INTO `" . NV_USERS_GLOBALTABLE . "` 
                (`userid`, `username`, `md5username`, `password`, `email`, `full_name`, `gender`, `photo`, `birthday`, `sig`, 
                `regdate`, `website`, `location`, `yim`, `telephone`, `fax`, `mobile`, `question`, `answer`, `passlostkey`, 
                `view_mail`, `remember`, `in_groups`, `active`, `checknum`, `last_login`, `last_ip`, `last_agent`, `last_openid`) VALUES 
                (
                " . intval( $user_info['userid'] ) . ", 
                " . $db->dbescape( $user_info['username'] ) . ", 
                " . $db->dbescape( md5( $user_info['username'] ) ) . ", 
                " . $db->dbescape( $password_crypt ) . ", 
                " . $db->dbescape( $user_info['email'] ) . ", 
                " . $db->dbescape( $user_info['full_name'] ) . ", 
                '', 
                '', 
                " . $user_info['birthday'] . ", 
				" . $db->dbescape( $user_info['sig'] ) . ", 
                " . $user_info['regdate'] . ", 
                " . $db->dbescape( $user_info['website'] ) . ", 
                " . $db->dbescape( $user_info['location'] ) . ", 
                " . $db->dbescape( $user_info['yim'] ) . ", 
                '', '', '', '', '', '', 
                " . $user_info['view_mail'] . ", 0, '', 
                " . $user_info['active'] . ", '', 
                " . NV_CURRENTTIME . ", 
                " . $db->dbescape( $client_info['ip'] ) . ", 
                " . $db->dbescape( $client_info['agent'] ) . ", 
                '' 
                )";
        }
        
        if ( $db->sql_query( $sql ) )
        {
            $error = "";
        }
        else
        {
            $error = $lang_module['error_update_users_info'];
        }
    }
    unset( $userid );
    $db->sql_query( "SET NAMES 'utf8'" );
}
else
{
    trigger_error( "Error no forum vbb", 256 );
}

?>