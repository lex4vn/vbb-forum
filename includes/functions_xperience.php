<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz / Phalynx. All Rights Reserved. # ||
|| #################################################################### ||
\*======================================================================*/

function GetFiles($filter)
{
	$files = array();
	if ($handle = opendir('./xperience/icons/'))
	{
	  while (false !== ($file = readdir($handle)))
	  {
			if (strpos($file, $filter) > 0)
			{
				$files[] = $file;
    	}
    }
    sort($files);
		closedir($handle);
	}
	 
return $files;
}


function GetPromotionEntry($promotion, $BFBo, $data, $oldusergrouparray, $IsLastGroup)
{
	global $vbulletin, $vbphrase;
	
	$promotion_overview = array();
	
	if ($vbulletin->options['xperience_promotions_show_benefits'] AND count($oldusergrouparray) > 0)
	{
		$promotion_overview = FetchBenefits($oldusergrouparray, $promotion, $BFBo);
		$promotion_overview['promotion_benefits'] = 1;
	}
	
	$promotion_overview['ingroup'] = 0;
	if (is_member_of($vbulletin->userinfo, $promotion['usergroupid']))
	{
		$promotion_overview['ingroup'] = 1;
	}

	$groupcountsq = $vbulletin->db->query_read("SELECT
		COUNT(userid) AS total
		FROM " . TABLE_PREFIX . "user AS user
		WHERE usergroupid = ".$promotion['usergroupid']."
	");
	
	if ($vbulletin->db->num_rows($groupcountsq) > 0)
	{	
		$groupcounts = $vbulletin->db->fetch_array($groupcountsq);
	}

	$promotion_overview['count_users'] = vb_number_format($groupcounts['total']);



	if ($vbulletin->options['xperience_promotions_show_jumps'])
	{

		$promotionsubq = $vbulletin->db->query_read("SELECT
			p.*,
			u_to.*,
			u_to.title as to_title,
			u_fr.title as fr_title
			FROM " . TABLE_PREFIX . "xperience_promotion AS p
			LEFT JOIN " . TABLE_PREFIX . "usergroup AS u_to ON u_to.usergroupid=p.to_ug
			LEFT JOIN " . TABLE_PREFIX . "usergroup AS u_fr ON u_fr.usergroupid=p.from_ug
			WHERE from_ug=".$promotion['usergroupid']."
			ORDER BY u_fr.title, p.sortorder, p.field		
			LIMIT 0,50
		");
		
		if ($vbulletin->db->num_rows($promotionsubq) > 0)
		{	
			while ($promotionsub = $vbulletin->db->fetch_array($promotionsubq))
			{

				$promotionentry['field'] = $vbphrase['xperience_'.$promotionsub[field]]; 
				if (strlen($promotionentry['field']) < 1)
				{
					$promotionentry['field'] = $promotionsub["field"];
				}
				
				$promotionentry['value'] = vb_number_format($promotionsub['value']);
			
				$promotionentry['compare'] = $vbphrase['xperience_achievements_'.$promotionsub['compare']];
				
				$promotionentry['yours'] = vb_number_format($data[$promotionsub[field]]);
								
				$promotionentry['state_image'] = "icon_state_no_12.png";
				switch ($promotionsub['compare'])
				{
					case "-1":
						if ($data[$promotionsub[field]] < $promotionsub['value'])
						{
							$promotionentry['state_image'] = "icon_state_yes_12.png";
						}
						break;
					case "1":
						if ($data[$promotionsub[field]] > $promotionsub['value'])
						{
							$promotionentry['state_image'] = "icon_state_yes_12.png";
						}
						break;
					default:
						if ($data[$promotionsub[field]] == $promotionsub['value'])
						{
							$promotionentry['state_image'] = "icon_state_yes_12.png";
						}
						break;
				}
				$templater = vB_Template::create('xperience_promotion_overview_entry_header');
		 		$templater->register('promotionsub', $promotionsub);
				$promotion_overview['promotion_conditions'] .= $templater->render();  
				
				$templater = vB_Template::create('xperience_promotion_overview_entry');
		 		$templater->register('promotionentry', $promotionentry);
				$promotion_overview['promotion_conditions'] .= $templater->render(); 

			}
		}
	}

	$templater = vB_Template::create('xperience_promotion_overview');
	$templater->register('promotion', $promotion);
	$templater->register('promotion_overview', $promotion_overview);
	$xperience['promotions'] .= $templater->render();
			
		
	return $xperience['promotions'];

}



function FetchBenefits($oldusergrouparray, $promotion, $BFBo)
{

	global $vbulletin, $vbphrase;
	
	$promotion_overview = array();
	
	$promotion_benefits_exclusion = array("usergroupid", "title", "description", "usertitle", "forumpermissions");
	
	$ug_bitfield_from = array();

	$promotion_overview['promotion_benefits'] = 0;
	foreach($vbulletin->bf_ugp AS $permissiongroup => $fields)
	{
		$ug_bitfield_from["$permissiongroup"] = convert_bits_to_array($oldusergrouparray["$permissiongroup"], $fields);
	}

	$ug_bitfield_to = array();
	foreach($vbulletin->bf_ugp AS $permissiongroup => $fields)
	{
		$ug_bitfield_to["$permissiongroup"] = convert_bits_to_array($promotion["$permissiongroup"], $fields);
	}
	

	$included_permission_names = array();
	foreach ($ug_bitfield_from AS $settingname => $newvalue)
	{ 

		$oldvalue = $ug_bitfield_to[$settingname];

		foreach ($oldvalue AS $myname => $myvalue)
		{ 
			
			if ($myvalue <> $newvalue["$myname"])
			{
				$included_permission_names[] = $settingname;
				
				$promotion['translated'] = $vbphrase[$BFBo->data['ugp'][$settingname][$myname]['phrase']];
				$promotion['translated'] = preg_replace("/<dfn(.|\s)*?dfn>/", "", $promotion['translated']);
				
				
				$promotion_overview['promotion_benefits'] .= "<li><i>".$promotion['translated']."</i> set from <b>".$newvalue["$myname"]."</b> to <b>".$myvalue."</b>";

				if ($myvalue == 1)
				{
					$templater = vB_Template::create('xperience_promotion_overview_benefits_per');
					$templater->register('promotion', $promotion);
					$promotion_overview['promotion_benefits_assigned'] .= $templater->render();
				}
				else
				{
					$templater = vB_Template::create('xperience_promotion_overview_benefits_per');
					$templater->register('promotion', $promotion);
					$promotion_overview['promotion_benefits_revoked'] .= $templater->render();
				}

				$promotion_overview['promotion_benefits'] = 1;
			}
		}
	}
	$uug = $promotion['usergroupid'];
	$oldusergroupid = $oldusergrouparray['usergroupid'];

	foreach ($vbulletin->usergroupcache["$oldusergroupid"] AS $settingname => $oldvalue)
	{ 
		if (!in_array($settingname, $promotion_benefits_exclusion))
		{
			if ($vbulletin->usergroupcache["$uug"][$settingname] <> $oldvalue)
			{
				if (!in_array($settingname, $included_permission_names))
				{
					$newvalue = $vbulletin->usergroupcache["$uug"][$settingname];

					foreach ($BFBo->data['ugp'] AS $grouptitle => $perms)
					{
						foreach ($perms AS $permtitle => $permvalue)
						{ 
							if ($permtitle == $settingname)
							{
								$promotion['translated'] = $vbphrase[$BFBo->data['ugp'][$grouptitle][$settingname]['phrase']];
								$promotion['translated'] = preg_replace("/<dfn(.|\s)*?dfn>/", "", $promotion['translated']);

								break 2;
							}
						}
					}
					$promotion['oldvalue'] = vb_number_format($oldvalue, 3);
					$promotion['newvalue'] = vb_number_format($newvalue, 3);
					
					$templater = vB_Template::create('xperience_promotion_overview_benefits_set');
					$templater->register('promotion', $promotion);
					$promotion_overview['promotion_benefits_allowances'] .= $templater->render();
					$promotion_overview['promotion_benefits'] = 1;
				}
			}
		}
	}

return $promotion_overview;

}


function DoAchievements($limit = 100)
{
	
	global $vbulletin;
	
	$processed = 0;
	
	$achievementsq = $vbulletin->db->query_read("SELECT a.achievementid, a.canlose, a.replaceid
		FROM " . TABLE_PREFIX . "xperience_achievements AS a
		WHERE sortorder > 0
	");

	if ($vbulletin->db->num_rows($achievementsq) > 0)
	{
		
		
		while ($achievement = $vbulletin->db->fetch_array($achievementsq))
		{
			echo "Processing achievement ".$achievement['achievementid']."<br/>";
			$conditionq = $vbulletin->db->query_read("SELECT * 
				FROM " . TABLE_PREFIX . "xperience_achievements_fields
				WHERE achievementid=".$achievement['achievementid']."
			");
			
			if ($vbulletin->db->num_rows($conditionq) == 0)
			{
				continue;
			}
		
			$conditionstring = " ";
			$conditionstringa = " ";
			while ($condition = $vbulletin->db->fetch_array($conditionq))
			{
		
				switch ($condition['compare'])
				{
					case "-1":
						$compare = "<";
						$comparea= ">";
						break;
					case "1":
						$compare = ">";
						$comparea = "<";
						break;
					default:
						$compare = "=";
						$comparea = "<>";
						break;
				}
				$conditionstring .=$condition['field']." ".$compare." ".$condition['value']." AND ";
				$conditionstringa .=$condition['field']." ".$comparea." ".$condition['value']." OR ";
			}
			
			if ($limit > 0)
			{
				$limitsql = "LIMIT 0,".$limit;
			}

			
			$achievementissueq = $vbulletin->db->query_read("SELECT userid
				FROM " . TABLE_PREFIX . "xperience_stats
				WHERE ".$conditionstring."
				userid NOT IN(
					SELECT userid FROM " . TABLE_PREFIX . "xperience_achievements_issues WHERE achievementid=".$achievement['achievementid']."
				)
				".$limitsql);
				
			if ($vbulletin->db->num_rows($achievementissueq) > 0)
			{

				while ($achievementissue = $vbulletin->db->fetch_array($achievementissueq))
				{
					$current_timestamp = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));
					$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_achievements_issues (achievementid, userid, dateline) VALUES (".$achievement['achievementid'].", ".$achievementissue['userid'].", '".$current_timestamp."')"); 
					if ($achievement['replaceid'] > 0)
					{
						$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_achievements_issues SET visible=0 WHERE achievementid=".$achievement['replaceid']." AND userid=".$achievementissue['userid']); 
					}
					AddNotification("xperience_achievementcount", $achievementissue['userid']);
					WriteAchievementItems($achievementissue['userid']);
					$processed++;
					if ($processed > $limit)
					{
						return $processed;
					}
				}
			}
			
			if ($achievement['canlose'] == 1)
			{
				$conditionstringa = substr($conditionstringa, 0, strlen($conditionstringa)-3);
				$achievementissueq = $vbulletin->db->query_read("SELECT userid
				FROM " . TABLE_PREFIX . "xperience_stats
				WHERE (".$conditionstringa.")
				AND userid IN(
					SELECT userid FROM " . TABLE_PREFIX . "xperience_achievements_issues WHERE achievementid=".$achievement['achievementid']."
				)
				".$limitsql);
				
				if ($vbulletin->db->num_rows($achievementissueq) > 0)
				{
					while ($achievementissue = $vbulletin->db->fetch_array($achievementissueq))
					{
						$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_achievements_issues WHERE achievementid=".$achievement['achievementid']." AND userid=".$achievementissue['userid']); 
						WriteAchievementItems($achievementissue['userid']);
						$processed++;
						if ($processed > $limit)
						{
							return $processed;
						}
					}
				}
			}
		}
	}
	return $processed;
	
}


function DoPromotions($user, $xperience)
{
	global $vbulletin;

	if (!$vbulletin->options['xperience_use_promotions']) 
	{
		return "";
	}
		
	$promotionsfieldsq =$vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_promotion AS p
		LEFT JOIN " . TABLE_PREFIX . "usergroup AS u ON u.usergroupid=p.from_ug
		WHERE from_ug=".$user['usergroupid']."
		AND sortorder>0
		ORDER BY sortorder
	");
	
	
	if ($vbulletin->db->num_rows($promotionsfieldsq) > 0)
	{
		while ($promotionsfields = $vbulletin->db->fetch_array($promotionsfieldsq)) 
		{
		
			$promoted = 0;
			$demoted = 0;
			
			if (!array_key_exists($promotionsfields['field'], $xperience))
			{
				if (array_key_exists(str_replace("points", "count", $promotionsfields['field']), $xperience))
				{
					$promotionsfields['field'] = str_replace("points", "count", $promotionsfields['field']);					
				}
			}
						
			$fielduser = $promotionsfields['field'];
			switch ($promotionsfields['compare'])
			{
				case "-1":
					if ($xperience[$fielduser] < $promotionsfields['value'])
					{
						$promoted = 1;
					}
					if ($xperience[$fielduser] > $promotionsfields['value'])
					{
						$demoted = 1;
					}
					break;
				case "1":
					if ($xperience[$fielduser] > $promotionsfields['value'])
					{
						$promoted = 1;
					}
					if ($xperience[$fielduser] < $promotionsfields['value'])
					{
						$demoted = 1;
					}
					break;
				default:
					if ($xperience[$fielduser] == $promotionsfields['value'])
					{
						$promoted = 1;
					}
					if ($xperience[$fielduser] !== $promotionsfields['value'])
					{
						$demoted = 1;
					}
					break;
			}
			
			 //DEMOTE NOT IMPLEMENTED AND $demoted == 0 
			 		 
			if ($promoted == 0)
			{
				break;
			}
			$allconditionsmet = 1;
			if ($allconditionsmet == 1)
			{
				PromoteUser($user, $promotionsfields['from_ug'], $promotionsfields['to_ug'], $promotionsfields['promotionid'], IIF($promoted==1, 1, 2));
				return 1;
			}
		}
	}
}

function PromoteUser($user, $from_ug, $to_ug, $promotionid, $ispromote = 1)
{
	global $vbulletin;
	//build_forum_permissions();

	$userdata =& datamanager_init('User', $vbulletin, ERRTYPE_SILENT);
	$userdata->set_existing($user);
	$userdata->set('usergroupid', $to_ug);
	
	$userdata->set_usertitle(
			'',
			true,
			$vbulletin->usergroupcache["$to_ug"],
			false,
			false
		);
 
	
	
	
	$userdata->save();
	
	if ($vbulletin->options['xperience_use_activities']) 
	{
		$current_timestamp = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_promotion_issues (
			promotionid,
			userid,
			from_ug,
			to_ug,
			promotiontype,
			comment,
			dateline
			) VALUES (
			".$promotionid.",
			".$user['userid'].",
			".$from_ug.",
			".$to_ug.",
			".IIF($ispromote==1, 1, 2).",
			'',
			".$current_timestamp."		
			)
		");
	}
	AddNotification("xperience_promotioncount", $user['userid']);
}

function GetAchievementUsersCount($achievementid)
{
	global $vbulletin;
	$users = 0;
	$achievementcountq =$vbulletin->db->query_read("SELECT COUNT(*) AS usercount
		FROM " . TABLE_PREFIX . "xperience_achievements_issues WHERE achievementid=".$achievementid);
	
	if ($vbulletin->db->num_rows($achievementcountq) > 0)
	{
		$achievementcount = $vbulletin->db->fetch_array($achievementcountq);
		$users = $achievementcount['usercount'];
	}
	return $users;
}


function print_select($title, $name, $array, $selected = '', $htmlise = false, $size = 0, $multiple = false)
{
	global $vbulletin;

	require_once(DIR . '/includes/adminfunctions.php');

	$uniqueid = fetch_uniqueid_counter();

	$select = "<div id=\"ctrl_$name\"><select name=\"$name\" id=\"sel_{$name}_$uniqueid\" tabindex=\"1\" class=\"bginput\"" . iif($size, " size=\"$size\"") . iif($multiple, ' multiple="multiple"') . iif($vbulletin->debug, " title=\"name=&quot;$name&quot;\"") . ">\n";
	$select .= construct_select_options($array, $selected, $htmlise);
	$select .= "</select></div>\n";

	return $select;
} 

function GetActivityGAP($date_start, $date_end, $userid = 0, $limit = 50)
{
	$actives = array();
	
	global $vbulletin, $vbphrase;
	
	if (!$vbulletin->options['xperience_use_gap'])
	{
		return $actives;
	}
	
	if ($userid !== 0)
	{
		$usersql = "AND g.userid=".$userid;		
	}

	if ($vbulletin->options['xperience_use_gap']) 
	{
		
		$gapq = $vbulletin->db->query_read("SELECT
			u.username AS uname, uto.username AS utoname, g.*
			FROM " . TABLE_PREFIX . "xperience_gap AS g
			INNER JOIN " . TABLE_PREFIX . "user AS u ON g.userid=u.userid
			INNER JOIN " . TABLE_PREFIX . "user AS uto ON g.toid=uto.userid
			WHERE g.dateline>".$date_start." AND g.dateline<".$date_end."
			".$usersql." 
			ORDER BY g.dateline DESC, gapid DESC
			LIMIT ".$limit);
			
		if ($vbulletin->db->num_rows($gapq) > 0)
		{
			while ($gap = $vbulletin->db->fetch_array($gapq))
			{
				$gap['fieldname'] = $vbphrase['xperience_'.$gap[field]];
				$gap['amount'] = vb_number_format($gap['amount']);
				$gap['date'] = vbdate($vbulletin->options['dateformat'], $gap['dateline'])." ".vbdate($vbulletin->options['timeformat'], $gap['dateline']);
				$gap['detaildate'] = vbdate($vbulletin->options['timeformat'], $gap['dateline']);

				$gap['userid1'] = $gap['userid'];
				$gap['username1'] = $gap['uname'];
				
				$gap['userid2'] = $gap['toid'];
				$gap['username2'] = $gap['utoname'];

				$gap['phrase'] = construct_phrase($vbphrase['xperience_gap_x'], $gap['userid1'], $gap['username1'], $gap['amount'], $gap['fieldname'], $gap['userid2'], $gap['username2']); 
	
				$templater = vB_Template::create('xperience_gap_entry');
			 	$templater->register('gap', $gap);
				$gap_log = $templater->render();  
				$actives[] = array($gap['dateline'], $gap_log);	
			}
			
		} 
	}
	return $actives;
}

function GetActivityAll($date_start, $date_end, $do, $limit, $userid = 0, $isprofile = 0, $delnotification = 1)
{
	global $vbulletin, $vbphrase, $activity, $name;

	$all = array();

	$do_promotions = false;
	$do_points = false;
	$do_achievements = false;
	$do_awards = false;
	$isoverview = false;
	
switch ($do)
{
	case "points":
		$name = $vbphrase['xperience_activities_points'];				
		$do_points = true;
		$level = 0;
		break;
	case "achievements":
		$name = $vbphrase['xperience_achievements'];
		$do_achievements = true;
		if ($delnotification==1)
		{
			DeleteNotification('xperience_achievementcount', $userid);
		}
		break;
	case "promotions":
		$name = $vbphrase['xperience_promotions'];
		$do_promotions = true;
		if ($delnotification==1)
		{
			DeleteNotification('xperience_promotioncount', $userid);
		}
		break;
	case "awards":
		$name = $vbphrase['xperience_awards'];
		$do_awards = true;
		if ($delnotification==1)
		{
			DeleteNotification('xperience_awardcount', $userid);
		}
		break;
	default:
		$name = $vbphrase['xperience_activities_overview'];
		$do_promotions = true;
		$do_points = true;
		$do_achievements = true;
		$do_awards = true;
		$isoverview = true;
		$level = 1;
		if ($delnotification==1)
		{
			DeleteNotification('xperience_promotioncount', $userid);
			DeleteNotification('xperience_awardcount', $userid);
			DeleteNotification('xperience_achievementcount', $userid);
		}
}



	if ($do_promotions)
	{
		$arr_promotions = array();
		$arr_promotions = GetActivityPromotions($date_start, $date_end, $userid, $limit);
		if (count($arr_promotions) > 0)
		{
			$all = array_merge($all, $arr_promotions);
		}
	}
	
	if ($do_achievements)
	{
		$arr_achievements = array();
		$arr_achievements = GetActivityAchievements($date_start, $date_end, $userid, $limit);
		if (count($arr_achievements) > 0)
		{
			$all = array_merge($all, $arr_achievements);
		}
	}
	
	if ($do_points)
	{
		$arr_points = array();
		$arr_points = GetActivityActivities($date_start, $date_end, $level, $userid, $limit);
		if (count($arr_points) > 0)
		{
			$all = array_merge($all, $arr_points);
		}
		
		$arr_points_gap = array();
		$arr_points_gap = GetActivityGAP($date_start, $date_end, $userid, $limit);
		if (count($arr_points_gap) > 0)
		{
			$all = array_merge($all, $arr_points_gap);
		}
	}


	if ($do_awards)
	{
		$arr_awards = array();
		$arr_awards = GetActivityAwards($date_start, $date_end, $userid, $limit);
		if (count($arr_awards) > 0)
		{
			$all = array_merge($all, $arr_awards);
		}
	}
	
	$currentdate = "";
	if (count($all) > 0)
	{
		
		arsort($all); 
		
		$i=0;
		foreach ($all AS $key => $item)
		{			
			$i++;
			if ($i > $limit)
			{
				if (strlen($do) > 0)
				{
					$dotext = "&do=".$do;
				}
				$activity .= construct_phrase($vbphrase['xperience_gap_more'], $vbulletin->options['bburl']."/xperience.php?go=activities".$dotext."&year=".date("Y", $item[0])."&month=".date("n", $item[0])."&day=".date("j", $item[0]));
				$activity .= "<br/>";
				break;
			}
			else
			{
				
				
				$activities['date'] = vbdate($vbulletin->options['dateformat'], $item[0]);
				if ($currentdate <> $activities['date'])
				{
					if ($currentdate <> "")
					{
						$activity .="<br/>";
					}
					$templater = vB_Template::create('xperience_activities_dateentry');
		 			$templater->register('activities', $activities);
					$activity .= $templater->render();  
		
				}
				$currentdate = $activities['date'];

				$activity .= $item[1];
			}
		}
		$activity .= "<br/>";

		if ($isprofile AND !$isoverview)
		{
			$activity = construct_phrase($vbphrase['xperience_activities_for'], $name, $vbulletin->options['bburl']."/xperience.php?go=activities<br/><br/>").$activity;
		}

	}

	
}

function GetActivityAchievements($date_start, $date_end, $userid = 0, $limit = 50)
{
	$actives = array();
	
	global $vbulletin, $vbphrase;
	
	if (!$vbulletin->options['xperience_use_achievements'])
	{
		return $actives;
	}
	
	if ($userid !== 0)
	{
		$usersql = "AND xi.userid=".$userid;
	}
	$logq =$vbulletin->db->query_read("SELECT
		xi.*,
		xa.title,
		xa.imagesmall,
		us.username
		FROM " . TABLE_PREFIX . "xperience_achievements_issues AS xi
		INNER JOIN " . TABLE_PREFIX . "user AS us ON us.userid=xi.userid
		INNER JOIN " . TABLE_PREFIX . "xperience_achievements AS xa ON xa.achievementid=xi.achievementid
		WHERE xi.dateline>".$date_start." AND xi.dateline<".$date_end." 
		".$usersql."
		ORDER BY xi.dateline DESC
		LIMIT ".$limit);

	if ($vbulletin->db->num_rows($logq) > 0)
	{
		while ($log = $vbulletin->db->fetch_array($logq))
		{
			
			$log['image'] = "icon_achievements_got.png";

			if (strlen($log['imagesmall']) > 0)
			{
				$log['achievementimage'] = $vbulletin->options['bburl']."/xperience/icons/".$log['imagesmall'];
			}
			else
			{
				$log['achievementimage'] = $vbulletin->options['bburl']."/xperience/images/icon_achievements_default.png";
			}

			$log['phrase'] = construct_phrase($vbphrase['xperience_achievements_g'], $log['userid'], $log['username'], $log['achievementimage'], $log['title']); 

			$log['detaildate'] = vbdate($vbulletin->options['timeformat'], $log['dateline']);			
			$templater = vB_Template::create('xperience_achievements_logbit');
		 	$templater->register('log', $log);
			$logbits = $templater->render();  
			$actives[] = array($log['dateline'], $logbits);	
		}
		
	}

		return $actives;

}



function GetActivityAwards($date_start, $date_end, $userid = 0, $limit = 50)
{
	$actives = array();
	
	global $vbulletin, $vbphrase;
	
	if (!$vbulletin->options['xperience_use_awards'])
	{
		return $actives;
	}
	
	if ($userid !== 0)
	{
		$usersql = "AND xi.userid=".$userid;
	}
	$awlogs =$vbulletin->db->query_read("SELECT
		xi.*,
		xa.awardtitle,
		xa.awardname,
		xa.awardurl,
		us.username
		FROM " . TABLE_PREFIX . "xperience_award_issues AS xi
		INNER JOIN " . TABLE_PREFIX . "user AS us ON us.userid=xi.userid
		INNER JOIN " . TABLE_PREFIX . "xperience_awards AS xa ON xa.awardid=xi.awardid
		WHERE xi.dateline>".$date_start." AND xi.dateline<".$date_end." 
		".$usersql."
		ORDER BY xi.dateline DESC
		LIMIT ".$limit);

	if ($vbulletin->db->num_rows($awlogs) > 0)
	{
		while ($awlog = $vbulletin->db->fetch_array($awlogs))
		{
			if ($awlog['dateline_out'] > 0)
			{
				$awlog['awphrase'] = construct_phrase($vbphrase['xperience_award_r'], $awlog['userid'], $awlog['username'], $vbulletin->options['bburl']."/xperience/icons/".$awlog['awardurl'], $awlog['awardtitle']); 
				$awlog['dateline'] -= 1;
				$awlog['awimage'] = "icon_award_returned.png";
			}
			else
			{
				$awlog['awphrase'] = construct_phrase($vbphrase['xperience_award_g'], $awlog['userid'], $awlog['username'], $vbulletin->options['bburl']."/xperience/icons/".$awlog['awardurl'], $awlog['awardtitle']); 
				$awlog['awimage'] = "icon_award_got.png";
			}

			if (strlen($awlog['awardurl']) > 0)
			{
				$awlog['image'] = $vbulletin->options['bburl']."/xperience/icons/".$awlog['awardurl'];
			}
			else
			{
				$awlog['image'] = $vbulletin->options['bburl']."/xperience/icons/default_16.png";
			}

			$awlog['detaildate'] = vbdate($vbulletin->options['timeformat'], $awlog['dateline']);			


			$templater = vB_Template::create('xperience_awards_logbit');
		 	$templater->register('awlog', $awlog);
			$awlogbits = $templater->render();  
			$actives[] = array($awlog['dateline'], $awlogbits);	
		}
		
	}

		return $actives;

}



function GetActivityPromotions($date_start, $date_end, $userid = 0, $limit = 50)
{
	$actives = array();
	
	global $vbulletin, $vbphrase;

	if (!$vbulletin->options['xperience_use_promotions'])
	{
		return $actives;
	}
	
	if ($userid !== 0)
	{
		$usersql = "AND l.userid=".$userid;
	}

	$promotionq = $vbulletin->db->query_read("SELECT
		g.usertitle, g.title, u.username, l.*
		FROM " . TABLE_PREFIX . "xperience_promotion_issues AS l
		INNER JOIN " . TABLE_PREFIX . "user AS u ON u.userid=l.userid
		INNER JOIN " . TABLE_PREFIX . "usergroup AS g ON g.usergroupid=l.to_ug
		WHERE l.dateline>".$date_start." AND l.dateline<".$date_end."
		".$usersql."
		ORDER BY dateline DESC, promotionid DESC
		LIMIT ".$limit);

	if ($vbulletin->db->num_rows($promotionq) > 0)
	{	
		$currentdate = "";
		while ($promotion = $vbulletin->db->fetch_array($promotionq))
		{
			if (strlen($promotion['usertitle'])>1)
			{
				$promotion['usergroup'] = $promotion['usertitle'];
			} else {
				$promotion['usergroup'] = $promotion['title'];
			}
			$promotion['detaildate'] = vbdate($vbulletin->options['timeformat'], $promotion['dateline']);
			$promotion['date'] = vbdate($vbulletin->options['dateformat'], $promotion['dateline']);

			if ($promotion['promotiontype'] == 2)
			{
				$promotion['phrase'] = construct_phrase($vbphrase['xperience_demoted_x'], $promotion['userid'], $promotion['username'], $promotion['detaildate'], $promotion['usergroup']); 
				$promotion['icon'] = "icon_demoted.png";
			} else {
				$promotion['phrase'] = construct_phrase($vbphrase['xperience_promoted_x'], $promotion['userid'], $promotion['username'], $promotion['detaildate'], $promotion['usergroup']); 
				$promotion['icon'] = "icon_promoted.png";				
			}
			

			$templater = vB_Template::create('xperience_promotion_entry');
		 	$templater->register('promotion', $promotion);
			$activityentry = $templater->render();  

			$actives[] = array($promotion['dateline'], $activityentry);			
		}
		return $actives;
	}
	
}


function GetActivityActivities($date_start, $date_end, $level, $userid = 0, $limit = 50)
{
	$actives = array();
	
	global $vbulletin, $vbphrase;
	
	if ($userid !== 0)
	{
		$usersql = "AND s.userid=".$userid;
	}
	
	$activitiesq = $vbulletin->db->query_read("SELECT
		u.username, s.* 
		FROM " . TABLE_PREFIX . "xperience_stats_changes AS s
		INNER JOIN " . TABLE_PREFIX . "user AS u ON u.userid=s.userid
		WHERE s.ismajor = ".$level."
		AND s.dateline>".$date_start." AND s.dateline<".$date_end."
		$usersql
		ORDER BY s.dateline DESC
		LIMIT ".$limit);

	if ($vbulletin->db->num_rows($activitiesq) > 0)
	{
		while ($activities = $vbulletin->db->fetch_array($activitiesq))
		{

			$earn['detaildate'] = vbdate($vbulletin->options['timeformat'], $activities['dateline']);
			$activities['date'] = vbdate($vbulletin->options['dateformat'], $activities['dateline']);
			$activities['fieldname'] = $vbphrase['xperience_'.$activities[field]];
			if (strlen($activities['fieldname']) == 0)
			{
				$activities['fieldname'] = $activities['field'];
			} 
			$activities['points'] = vb_number_format($activities['difference']);

			$earn['points'] = construct_phrase($vbphrase['xperience_activity_points'], $activities['difference'], $activities['fieldname']); 
			
			
			if ($activities['difference'] > 0)
			{
 				$earn['phrase'] = construct_phrase($vbphrase['xperience_activity_x_earn'], $activities['userid'], $activities['username'], $earn['points'] );
				$earn['image'] = "icon_points_earn.png";
			}
			else
			{
				$earn['phrase'] = construct_phrase($vbphrase['xperience_activity_x_lose'], $activities['userid'], $activities['username'], $earn['points'] );
				$earn['image'] = "icon_points_lose.png";
			}
			
			$templater = vB_Template::create('xperience_activities_entry');
		 	$templater->register('earn', $earn);
			$activityentry = $templater->render();  
			
							
			$actives[] = array($activities['dateline'], $activityentry);
	
			
		}
			return $actives;
	}
	
}


function GetProfileFields()
{
	global $vbulletin;
	
	$profilefieldsq =$vbulletin->db->query_read("SELECT 
		profilefieldid
		FROM " . TABLE_PREFIX . "profilefield
		WHERE (editable=1 OR editable=2) AND def=0
		");
	
	$profilefields = array();
	if ($vbulletin->db->num_rows($profilefieldsq) > 0) 
	{
		while ($profilefield = $vbulletin->db->fetch_array($profilefieldsq))
		{
			$profilefields[] = $profilefield['profilefieldid'];
		}
	} 

	return $profilefields;

}

function DeleteNotification($column, $userid)
{
	global $vbulletin;
	if ($userid == $vbulletin->userinfo['userid'])
	{
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET ".$column."=0 WHERE userid=".$userid);
	}
}

function AddNotification($column, $userid)
{
	global $vbulletin;
	
	if ($vbulletin->options['xperience_notifications']) 
	{
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET ".$column."=+1 WHERE userid=".$userid);
	}
}


function ValidateActivity()
{
	global $vbulletin;
	

	$valactq = $vbulletin->db->query_read("SELECT
		userid
		FROM " . TABLE_PREFIX . "xperience_stats
		WHERE points_user_activity > 99
		ORDER BY points_post_avg DESC
		");
		
	if ($vbulletin->db->num_rows($valactq) > 1)
	{
		$firstrow = $vbulletin->db->fetch_array($valactq);
		
		$impact = 99.9;		
		while ($valact = $vbulletin->db->fetch_array($valactq))
		{
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats 
				SET
				points_user_activity=".$impact."
				WHERE userid=".$valact['userid']);
			
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
				SET
				xperience_ppd=".$impact."
				WHERE userid=".$valact['userid']);
				$impact -= 0.1;
				if ($impact < 99)
				{
					$impact = 99;
				}
		}
	}
	
		$valactq = $vbulletin->db->query_read("SELECT
		userid
		FROM " . TABLE_PREFIX . "xperience_stats
		WHERE points_user_activity30 > 99
		ORDER BY points_post_avg DESC
		");
		
	if ($vbulletin->db->num_rows($valactq) > 1)
	{
		$firstrow = $vbulletin->db->fetch_array($valactq);
		
		$impact = 99.9;		
		while ($valact = $vbulletin->db->fetch_array($valactq))
		{
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats 
				SET
				points_user_activity30=".$impact."
				WHERE userid=".$valact['userid']);
			
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
				SET
				xperience_ppd30=".$impact."
				WHERE userid=".$valact['userid']);
				$impact -= 0.1;
				if ($impact < 99)
				{
					$impact = 99;
				}
		}
	}
	
		$valactq = $vbulletin->db->query_read("SELECT
		userid
		FROM " . TABLE_PREFIX . "xperience_stats
		WHERE points_user_activity7 > 99
		ORDER BY points_post_avg DESC
		");
		
	if ($vbulletin->db->num_rows($valactq) > 1)
	{
		$firstrow = $vbulletin->db->fetch_array($valactq);
		
		$impact = 99.9;		
		while ($valact = $vbulletin->db->fetch_array($valactq))
		{
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_stats 
				SET
				points_user_activity7=".$impact."
				WHERE userid=".$valact['userid']);
			
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
				SET
				xperience_ppd7=".$impact."
				WHERE userid=".$valact['userid']);
				$impact -= 0.1;
				if ($impact < 99)
				{
					$impact = 99;
				}
		}
	}
}

function GetDayDiff($date1, $date2)
{
	$dateDiff = $date1 - $date2;
	$days = floor($dateDiff/(60*60*24));
	return $days;
}


function DoesColumnExists($table, $field)
{
	global $vbulletin;
	return ($vbulletin->db->num_rows($vbulletin->db->query_read("SHOW COLUMNS FROM `" . TABLE_PREFIX .$table."` LIKE '".$field."'"))> 0);
}

function DoesTableExists($tablename)
{
	global $vbulletin;
	
	$istable = $vbulletin->db->query_read("SHOW TABLES LIKE '" . TABLE_PREFIX . $tablename."'");
	if ($vbulletin->db->num_rows($istable) > 0) 
	{	
		return 1;
	}
	return 0;
}



function ResolveAssociation($userxp, $option_name, $tablecheck = "", $column_name = "")
{
	global $vbulletin, $settingphrase;
		
	if ($vbulletin->options["$option_name"] == 0) 
	{
		return "";
	}

	if (strlen($tablecheck) > 0)
	{
		$istable = $vbulletin->db->query_read("SHOW TABLES LIKE '" . TABLE_PREFIX . $tablecheck."'");
		if ($vbulletin->db->num_rows($istable) == 0)
		{
			return "";
		}
	}

	
	$phrase = "setting_".$option_name."_title";
	$xperience['name'] = $settingphrase["$phrase"];
	
	$descphrase = "setting_".$option_name."_desc";
	$xperience['description']= $settingphrase["$descphrase"];
	
	$xperience['value'] = $vbulletin->options["$option_name"];
	
	$xperience['yours'] = "-";
	

	if (strlen($column_name) > 0)
	{

		$averageq = $vbulletin->db->query_read("SELECT AVG(".$column_name.") AS A FROM " . TABLE_PREFIX . "xperience_stats WHERE ".$column_name." > 0");
		if ($vbulletin->db->num_rows($averageq) > 0)
		{
			$average = $vbulletin->db->fetch_array($averageq);
			$xperience['average'] = vb_number_format($average['A']);
		}
		
		if ($vbulletin->userinfo['userid'] > 0)
		{
			$xperience['yours'] = vb_number_format($userxp[$column_name]);
		}
				
		if ($average['A'] == (int)$userxp[$column_name])
		{
			$xperience['trend'] = "arrow_right_blue_16.png";
		}
		elseif ($average['A'] > (int)$userxp[$column_name])
		{
			$xperience['trend'] = "arrow_down_red_16.png";
		}
		else
		{
			$xperience['trend'] = "arrow_up_green_16.png";
		}
		
	}

	$templater = vB_Template::create('xperience_earn_entry');
 	$templater->register('xperience', $xperience);
 	$xperience_earn_entry = $templater->render();  
	return $xperience_earn_entry;
}


function GetBestGroup()
{
	
	global $vbulletin;	
	
	if (!$vbulletin->options['xperience_use_groups'])
	{
		return "";
	}
	
		
	$groupq = $vbulletin->db->query_read("SELECT 
		x.*,
		g.name
		" . ($vbulletin->options['sg_enablesocialgroupicons'] ? ', socialgroupicon.dateline AS icondateline, socialgroupicon.width AS iconwidth, socialgroupicon.height AS iconheight, socialgroupicon.thumbnail_width AS iconthumb_width, socialgroupicon.thumbnail_height AS iconthumb_height' : '') . " 
		FROM " . TABLE_PREFIX . "xperience_groups AS x
		INNER JOIN " . TABLE_PREFIX . "socialgroup AS g ON g.groupid=x.groupid
		".($vbulletin->options['sg_enablesocialgroupicons'] ?
			"LEFT JOIN " . TABLE_PREFIX . "socialgroupicon AS socialgroupicon ON
				(socialgroupicon.groupid = g.groupid)" : '')." 
		ORDER BY points DESC
		LIMIT 1");

	if ($vbulletin->db->num_rows($groupq) > 0)
	{
		require_once('./includes/functions_socialgroup.php');	
		$groupinfo = $vbulletin->db->fetch_array($groupq);
		$groupinfo['groupiconurl'] = fetch_socialgroupicon_url($groupinfo, true);
		$groupinfo['members'] = vb_number_format($groupinfo['members']);
		$groupinfo['points'] = vb_number_format($groupinfo['points']);

		
		$templater = vB_Template::create('xperience_user_block_sg');
		$templater->register('groupinfo', $groupinfo);
		$xperience = $templater->render(); 
		
	}
	
	return $xperience;
	
}

function GetMostAchievements($sortorder, $phrase)
{
	global $vbulletin;	
	
	if (!$vbulletin->options['xperience_use_achievements'])
	{
		return "";
	}
	
	$achievementcountq =$vbulletin->db->query_read("SELECT
		COUNT(*) AS cnt_ach,
		i.achievementid,
		a.title,
		a.description,
		a.imagebig
		FROM " . TABLE_PREFIX . "xperience_achievements_issues AS i
		INNER JOIN " . TABLE_PREFIX . "xperience_achievements AS a ON a.achievementid=i.achievementid
		GROUP BY achievementid
		ORDER BY COUNT(*) ".$sortorder."
		LIMIT 1
	");
	
	if ($vbulletin->db->num_rows($achievementcountq) > 0)
	{
		
		// get total members
		$totalmembers = $vbulletin->db->query_first("
			SELECT
			COUNT(*) AS users,
			MAX(userid) AS maxid
			FROM " . TABLE_PREFIX . "user
		");
		
		
		$achievement = $vbulletin->db->fetch_array($achievementcountq);
		$achievement['userspercent'] = vb_number_format(($achievement['cnt_ach']/$totalmembers['users'])*100);
		$achievement['users'] = vb_number_format($achievement['cnt_ach']);
		$achievement['name'] = $phrase;
		
		$templater = vB_Template::create('xperience_user_block_maa');
		$templater->register('achievement', $achievement);
		$templater->register('userinfo', $userinfo);
		$xperience = $templater->render(); 
		
	}
	
	return $xperience;
	
}



function WriteAward($award, $userid, $ismanually = false)
{
	global $vbulletin;
	
	$timestamp = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));

	if (!verify_id("user", $userid, false, false))
	{
		return "";
	}
	
	if (!is_array($award))
	{
		return "";
	}

	$awardissues = $vbulletin->db->query_read("SELECT
		*
		FROM " . TABLE_PREFIX . "xperience_award_issues
		WHERE dateline_out=0 AND userid=".$userid." AND awardid=".$award['id']);  
	
	if ($vbulletin->db->num_rows($awardissues) == 0)
	{
		if (!$ismanually)
		{
			$oldawardissues = $vbulletin->db->query_read("SELECT
				*
				FROM " . TABLE_PREFIX . "xperience_award_issues
				WHERE dateline_out=0 AND awardid=".$award['id']." ORDER BY dateline_in DESC, issueid DESC");
				
			if ($vbulletin->db->num_rows($oldawardissues) > 0)
			{
				while ($oldawardissue = $vbulletin->db->fetch_array($oldawardissues))
				{
					$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_award_issues SET dateline='".$timestamp."', dateline_out='".$timestamp."' WHERE issueid=".$oldawardissue['issueid']."");
					AddNotification("xperience_awardcount", $oldawardissue['userid']);
				}
			}
		}
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_award_issues (awardid, userid, dateline, dateline_in) VALUES (".$award['id'].", ".$userid.", '".$timestamp."', '".$timestamp."')"); 
		AddNotification("xperience_awardcount", $userid);
	}

	WriteAwardItems($userid);

}

function GetAchievementsFull($userid, $withtitle = false)
{
		global $vbulletin;
		
		if (intval($userid) == 0)
		{
			return "";
		}
		
		$achievementq = $vbulletin->db->query_read("SELECT
		a.title,
		a.imagebig as big
		FROM " . TABLE_PREFIX . "xperience_achievements_issues AS i
		INNER JOIN " . TABLE_PREFIX . "xperience_achievements AS a ON a.achievementid=i.achievementid
		WHERE i.visible>0 AND i.userid=".$userid."
		ORDER BY a.categoryid, a.achievementid
	");

	if ($vbulletin->db->num_rows($achievementq) > 0)
	{
		while ($xpicon = $vbulletin->db->fetch_array($achievementq))
		{
		
			if ($withtitle)
			{
				$xpicon['description'] = $xpicon['title'];
			}
		
			$templater = vB_Template::create('xperience_xpicon_big');
			$templater->register('xpicon', $xpicon);
			$achievements .= $templater->render(); 

		}			
	}	
	return $achievements;
}

function WriteAwardItems($userid)
{
	global $vbulletin;
	
	if ($vbulletin->options['xperience_award_postbit'] == 5)
	{
		return;
	}
	
	$awards = array();
	$awardsq =$vbulletin->db->query_read("SELECT
		a.awardid AS id,
		a.awardtitle AS title,
		a.awardurl AS small,
		a.awardbigurl AS big
		FROM " . TABLE_PREFIX . "xperience_award_issues AS i
		INNER JOIN " . TABLE_PREFIX . "xperience_awards AS a ON a.awardid=i.awardid
		WHERE i.userid=".$userid." AND i.dateline_out=0
		ORDER BY i.dateline
		LIMIT 0,".$vbulletin->options['xperience_award_max']."
	");

	if ($vbulletin->db->num_rows($awardsq) > 0)
	{
		while ($awardsarr = $vbulletin->db->fetch_array($awardsq))
		{
			$awardsarr['awardtitle'] = addslashes($awardsarr['awardtitle']);
			$awards[] = $awardsarr;
		}
	}	

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
		SET
		xperience_awardt='".serialize($awards)."'
		WHERE userid=".$userid);
	
}


function WriteAchievementItems($userid)
{
	global $vbulletin;
	
	if ($vbulletin->options['xperience_achievements_postbit'] == 5)
	{
		return;
	}
	
	$achievements = array();
	$achievementsq =$vbulletin->db->query_read("SELECT
		a.achievementid AS id,
		a.title,
		a.imagesmall AS small,
		a.imagebig AS big
		FROM " . TABLE_PREFIX . "xperience_achievements_issues AS i
		INNER JOIN " . TABLE_PREFIX . "xperience_achievements AS a ON a.achievementid=i.achievementid
		WHERE i.visible>0 AND i.userid=".$userid."
		ORDER BY i.dateline
		LIMIT 0,".$vbulletin->options['xperience_achievements_items']."
	");

	if ($vbulletin->db->num_rows($achievementsq) > 0)
	{
		while ($achievementsarr = $vbulletin->db->fetch_array($achievementsq))
		{
			$achievementsarr['title'] = addslashes($achievementsarr['title']);
			$achievements[] = $achievementsarr;
		}
	}	

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
		SET
		xperience_achievements='".serialize($achievements)."'
		WHERE userid=".$userid);
	
}

function GetXPIcon($XPIconString, $withtitle = false, $usebig = false) 
{
	global $vbulletin, $stylevar, $vbphrase;

	$XPIconArray = unserialize($XPIconString);

	if (!is_array($XPIconArray))
	{
		return "";
	}

	foreach($XPIconArray AS $xpicon)
	{

		if ($usebig)
		{
			$templater = vB_Template::create('xperience_xpicon_big');
		}
		else
		{
			$templater = vB_Template::create('xperience_xpicon_small');
		}

		$xpicon['title'] = stripslashes($xpicon['title']);

		if ($withtitle)
		{
			$xpicon['description'] .= $xpicon['title'];
		}
			
		$templater->register('xpicon', $xpicon);
		$output .= trim($templater->render());
	}

	return $output;
}




function GetAwards($MyAwards, $withtitle = false, $usebig = false) 
{
	return GetXPIcon($MyAwards, $withtitle, $usebig);
}

function GetAchievements($MyAchievements, $withtitle = false, $usebig = false) 
{
	return GetXPIcon($MyAchievements, $withtitle, $usebig);
}

function GetAvatar($userinfo)
{
	global $vbulletin;
	
	$avatarurl = $userinfo['avatarurl'];
	
	if ($userinfo['avatarid']) {
		$avatarurl = $userinfo['avatarpath'];
	} else 	{
		if ($userinfo['hascustomavatar'] AND $vbulletin->options['avatarenabled']) 	{
			if ($vbulletin->options['usefileavatar']) 	{
				$avatarurl = $vbulletin->options['avatarurl'] . "/avatar$userinfo[userid]_$userinfo[avatarrevision].gif";
			} else 	{
				$avatarurl = 'image.php?' . $vbulletin->session->vars['sessionurl'] . "u=$userinfo[userid]&amp;dateline=$userinfo[avatardateline]";
			}
		} else {
			if (!empty($userinfo['avatarurl'])) {
				$avatarurl = $userinfo['avatarurl'];
			} else {
				$avatarurl = '';
			}
		}
	}

return $avatarurl;
} 



function fetch_xperience($userid)
{
	global $vbulletin;
	
	$userstats = array();
	$userq = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "xperience_stats WHERE userid=".$userid);
	if ($vbulletin->db->num_rows($userq) > 0)
	{
		$userstats = $vbulletin->db->fetch_array($userq);
	}
	else
	{
		$userstats['userid'] = $userid;
	}
	
	return $userstats;
}


function fetch_statistics($fetchthis)
{
	global $vbulletin, $vbphrase;
	
	$optionsq =$vbulletin->db->query_read("SHOW COLUMNS
		FROM " . TABLE_PREFIX . "xperience_stats LIKE 'points_".$fetchthis."%'");

	if ($vbulletin->db->num_rows($optionsq) > 0)
	{

		
		while ($options = $vbulletin->db->fetch_array($optionsq)) 
		{
			$statsname = $vbphrase['xperience_'.$options[Field]];

			
			$getstatsq =$vbulletin->db->query_read("SELECT
				u.userid, u.username, s.".$options['Field']." AS cnt_count
				FROM " . TABLE_PREFIX . "xperience_stats AS s
				INNER JOIN " . TABLE_PREFIX . "user AS u ON u.userid = s.userid
				WHERE s.".$options['Field']." <> 0
				ORDER BY s.".$options['Field']." DESC, s.points_xperience DESC
				LIMIT ".$vbulletin->options['xperience_stats_maxtop']);

				
			if ($vbulletin->db->num_rows($getstatsq) > 0)
			{

				$statscontent = '';
				$entrycounter = 0;

				while ($getstats = $vbulletin->db->fetch_array($getstatsq)) 
				{
					$entrycounter++;
					$getstats['cnt_count'] = vb_number_format($getstats['cnt_count']);
					$templater = vB_Template::create('xperience_stats_entry');
 					$templater->register('getstats', $getstats);
 					$templater->register('entrycounter', $entrycounter);
 					$statscontent .= $templater->render();  
				}
				
				if ($entrycounter < $vbulletin->options['xperience_stats_maxtop']+1 )
				{
					for($i=$entrycounter; $i <= $vbulletin->options['xperience_stats_maxtop']-1; $i++)
					{
					$templater = vB_Template::create('xperience_stats_entry_empty');
					$templater->register('entrycounter', $i+1);
 					$statscontent .= $templater->render(); 
					}
				}

				$templater = vB_Template::create('xperience_stats_entries');
				$templater->register('statscontent', $statscontent);
				$templater->register('statsname', $statsname);
				$stats .= $templater->render(); 

			}
		}
	}


	return $stats;
}
?>