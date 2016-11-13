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

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'attachments' => array(
			'*' => array(
				'attachment' => array(
					'counter', 'filesize', 'attachmentextension', 'attachmentid',
					'dateline', 'filename'
				)
			)
		),
		'threadinfo' => $VB_API_WHITELIST_COMMON['threadinfo'],
		'totalattachments'
	),
	'show' => array()
);

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/