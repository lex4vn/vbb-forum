<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.9 - Free Licence
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

$VB_API_WHITELIST = array(
	'response' => array(
		'HTML' => array(
			'inimaxattach', 'maxnote', 'preview', 'sigperms',
			'sigpicurl'
		)
	),
	'show' => array(
		'canbbcode', 'canbbcodebasic', 'canbbcodecolor', 'canbbcodesize',
		'canbbcodefont', 'canbbcodealign', 'canbbcodelist', 'canbbcodelink',
		'canbbcodecode', 'canbbcodephp', 'canbbcodehtml', 'canbbcodequote',
		'allowimg', 'allowsmilies', 'allowhtml', 'cansigpic', 'cananimatesigpic'
	)
);

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/