<?php	
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz / Phalynx. All Rights Reserved. # ||
|| #################################################################### ||
\*======================================================================*/


global $vbulletin;
if ($vbulletin->options['xperience_enabled'])
{

	if ($vbulletin->options['xperience_postbitstyle'] > 0)
	{
		if ($post['xperience'] > 0)
		{
			$xperience['points'] = vb_number_format($post['xperience']);
			$xperience['level'] = vb_number_format($post['xperience_level']);
			$xperience['levelp'] = vb_number_format($post['xperience_levelp'], 1);
			$xperience['level_up'] = vb_number_format($post['xperience_next_level']);
			$xperience['level_up_points'] = vb_number_format($post['xperience_next_level_points']);
			$xperience['ppd'] = vb_number_format($post['xperience_ppd'], 1);
			
			$xperience['gfx_level'] = construct_phrase($vbphrase['xperience_gfx_level'], $xperience['points'], $xperience['level']); 
			$xperience['gfx_levelup'] = construct_phrase($vbphrase['xperience_gfx_levelup'], $xperience['level_up'], $xperience['level_up_points'], "<br/>"); 
			$xperience['gfx_levelup_nb'] = construct_phrase($vbphrase['xperience_gfx_levelup'], $xperience['level_up'], $xperience['level_up_points'], ""); 
			$xperience['gfx_activity'] = construct_phrase($vbphrase['xperience_gfx_activity'], $xperience['ppd']); 
						
						
			if ($vbulletin->options['xperience_points_below_postcount'])
			{
				$templater_pp = vB_Template::create('xperience_points_postbit');
				$templater_pp->register('xperience', $xperience);
				$template_hook['postbit_userinfo_right_after_posts'] .= $templater_pp->render();
			}		
			
			if ($vbulletin->options['xperience_level_below_postcount'])
			{
				$templater_pp = vB_Template::create('xperience_level_postbit');
				$templater_pp->register('xperience', $xperience);
				$template_hook['postbit_userinfo_right_after_posts'] .= $templater_pp->render();
			}		
				
						
			$templater_pb = vB_Template::create('xperience_gfx');
			$templater_pb->register('xperience', $xperience);
				
			switch ($vbulletin->options['xperience_postbitposition'])
			{
				case 1:
					$template_hook['postbit_userinfo_right_after_posts'] .= $templater_pb->render();
					break;
				case 2:
					$template_hook['postbit_userinfo_right'] .= $templater_pb->render();
					break;
				case 3:
					$template_hook['postbit_signature_start'] .= $templater_pb->render();
					break;
				case 4:
					$template_hook['postbit_signature_end'] .= $templater_pb->render();
					break;
				case 5:
					$template_hook['postbit_messagearea_start'] .= $templater_pb->render();
					break;
				case 6:
					$post['xpbr'] = $templater_pb->render();
					break;
				default:
					$template_hook['postbit_userinfo_left'] .= $templater_pb->render();
			}
		}
	}
	
	if ($vbulletin->options['xperience_achievements_postbit'] < 5 || $vbulletin->options['xperience_achievements_postbit'] == 6)
	{
		require_once(DIR . '/includes/functions_xperience.php');
		$achievements = GetAchievements($post['xperience_achievements']);
		if (strlen($achievements) > 1)
		{
			$templater_ach = vB_Template::create('xperience_achievements_showcase');
			$templater_ach->register('achievements', $achievements);
			
			switch ($vbulletin->options['xperience_achievements_postbit'])
			{
				case 1:
					$template_hook['postbit_userinfo_right_after_posts'] .= $templater_ach->render();
					break;
				case 2:
					$template_hook['postbit_userinfo_right'] .= $templater_ach->render();
					break;
				case 3:
					$template_hook['postbit_signature_start'] .= $templater_ach->render();
					break;
				case 4:
					$template_hook['postbit_signature_end'] .= $templater_ach->render();
					break;
				case 6:
					$post['xpac'] = $templater_ach->render();
					break;
				default:
					$template_hook['postbit_userinfo_left'] .= $templater_ach->render();
			}
		}
	}
	
	
	
		
	if ($vbulletin->options['xperience_award_postbit'] < 5 OR $vbulletin->options['xperience_award_postbit'] == 6)
	{
		require_once(DIR . '/includes/functions_xperience.php');
		$awards = GetAwards($post['xperience_awardt']);
		if (strlen($awards) > 1)
		{
			$templater_aw = vB_Template::create('xperience_award_showcase');
			$templater_aw->register('awards', $awards);
			switch ($vbulletin->options['xperience_award_postbit'])
			{
				case 1:
					$template_hook['postbit_userinfo_right_after_posts'] .= $templater_aw->render();
					break;
				case 2:
					$template_hook['postbit_userinfo_right'] .= $templater_aw->render();
					break;
				case 3:
					$template_hook['postbit_signature_start'] .= $templater_aw->render();
					break;
				case 4:
					$template_hook['postbit_signature_end'] .= $templater_aw->render();
					break;
				case 6:
					$post['xpaw'] = $templater_aw->render();
					break;
				default:
					$template_hook['postbit_userinfo_left'] .= $templater_aw->render();
			}
		}
	}

}

?>
