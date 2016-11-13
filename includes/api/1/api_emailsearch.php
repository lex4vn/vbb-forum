<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Blog 4.1.9 - Free Licence
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

class vB_APIMethod_api_emailsearch extends vBI_APIMethod
{
	public function output()
	{
		global $vbulletin, $db;

		$vbulletin->input->clean_array_gpc('p', array('fragment' => TYPE_STR));

		$vbulletin->GPC['fragment'] = convert_urlencoded_unicode($vbulletin->GPC['fragment']);

		if ($vbulletin->GPC['fragment'] != '' AND strlen($vbulletin->GPC['fragment']) >= 3)
		{
			$fragment = htmlspecialchars_uni($vbulletin->GPC['fragment']);
		}
		else
		{
			$fragment = '';
		}
		$data = array();
		if ($fragment != '')
		{
			$users = $db->query_read_slave("
				SELECT user.userid, user.username,user.email FROM " . TABLE_PREFIX . "user
				AS user WHERE email = '" . $db->escape_string_like($fragment) . "'");
			$data = $db->fetch_row($users);
		}

		return $data;

	}
}

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 26995 $
|| ####################################################################
\*======================================================================*/