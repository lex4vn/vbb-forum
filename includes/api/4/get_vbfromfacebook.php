<?php

/**
 *
 * @author Jorge Tiznado
 */
class vB_APIMethod_get_vbfromfacebook extends vBI_APIMethod {

    public function output(){
        
        $data = array('response' => array('users' => $this->getvBUserWithForumList()));
        return $data;

    }

    private function getvBUserWithForumList(){

       global $vbulletin, $db;
       $arrayResponse = array();

       $vbulletin->input->clean_array_gpc('p', array('facebookidList' => TYPE_STR));

        $vbulletin->GPC['facebookidList'] = convert_urlencoded_unicode($vbulletin->GPC['facebookidList']);
        $facebookidList = $vbulletin->GPC['facebookidList'];

       $vBUserStringList = "";
       $separator = "";
       $vBUserlist = $db->query_read_slave("
		SELECT user.userid, user.username, user.fbuserid
		FROM " . TABLE_PREFIX . "user AS user
		WHERE fbuserid IN ($facebookidList)
	");
       //error_log("SELECT user.userid, user.username FROM " . TABLE_PREFIX . "user WHERE fbuserid IN ($facebookidList)\n", 3, "/var/www/html/facebook/error/error1.txt");

        while ($vBUser = $db->fetch_array($vBUserlist)) {
            $arrayResponse[] = array(
                'userid'   => $vBUser['userid'],
                'username' => $vBUser['username'],
                'fbuserid'   => $vBUser['fbuserid']
            );
            
        }
        if(count($arrayResponse) == 0){
            $arrayResponse['response']['errormessage'][0] = 'no_users_in_facebook';
        }
        return $arrayResponse;
    }
}
?>
