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

foreach ($VB_API_WHITELIST['response'] as $k => $v)
{
	if ($v == 'similarthreads')
	{
		unset($VB_API_WHITELIST['response'][$k]);
		break;
	}
}
$VB_API_WHITELIST['response']['similarthreads'] = array(
	'similarthreadbits' => array(
		'*' => array(
			'simthread' => array(
				'threadid', 'forumid', 'title', 'prefixid', 'taglist', 'postusername',
				'postuserid', 'replycount', 'preview', 'lastreplytime', 'prefix_plain_html',
				'prefix_rich'
			)
		)
	)
);

function api_result_prerender_2($t, &$r)
{
	switch ($t)
	{
		case 'showthread_similarthreadbit':
			$r['simthread']['lastreplytime'] = $r['simthread']['lastpost'];
			break;
	}
}

vB_APICallback::instance()->add('result_prerender', 'api_result_prerender_2', 2);

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/