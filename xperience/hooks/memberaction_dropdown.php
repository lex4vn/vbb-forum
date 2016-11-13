<?php	
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz / Phalynx. All Rights Reserved. # ||
|| #################################################################### ||
\*======================================================================*/

	if ($vbulletin->options['xperience_use_gap'])
	{
			$templaterma = vB_Template::create('xperience_gap_memberaction');
			$templaterma->register('memberinfo', $memberinfo);
			$template_hook['memberaction_dropdown_items'] .= $templaterma->render();
	}

?>