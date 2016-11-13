<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.9 - Free Licence
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

$VB_API_WHITELIST = array(
	'response' => array(
		'buddies',
		'offlineusers' => array(
			'*' => array(
				'buddy',
				'show' => array('highlightuser')
			)
		),
		'onlineusers' => array(
			'*' => array(
				'buddy',
				'show' => array('highlightuser')
			)
		)
	),
	'show' => array(
		'playsound'
	)
);

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/