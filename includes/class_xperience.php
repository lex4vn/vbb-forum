<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz / Phalynx. All Rights Reserved. # ||
|| #################################################################### ||
\*======================================================================*/

class xPerience
{

function CalculateXP ($user, $reallynothing) 
{
	global $vbulletin, $profilefields;
	
	require_once('./includes/functions_xperience.php');

	$datecut = "315532800"; //Tue, 01 Jan 1980 00:00:00 GMT
	if (!$vbulletin->options['xperience_points_datecut']==0)
	{
		$checkdatecut = strtotime($vbulletin->options['xperience_points_datecut']);	
		if ($checkdatecut > 0)
		{
			$datecut = $checkdatecut;
			
		}
	}
	
		
		
	$IgnorePluginForums = array();

	($hook = vBulletinHook::fetch_hook('xperience_calcdata_begin')) ? eval($hook) : false;
		
	if (count($profilefields) == 0)
	{
		$profilefields = GetProfileFields();
	}
				
	$DoDebug = 0;
	
	if (!$vbulletin->options['xperience_enabled'])
	{
		return;
	}

	if (strlen($vbulletin->options['xperience_ignore_forums']) > 0)
	{
		$IgnoreSettingForums = explode(",", $vbulletin->options['xperience_ignore_forums']);
	}
	else
	{
		$IgnoreSettingForums = array();
	}
	
	$IgnoreForums = array_merge($IgnoreSettingForums, $IgnorePluginForums);	
	
	if (count($IgnoreForums) > 0)
	{
		
		$IgnoreForum = " AND forumid NOT IN(";
	
		$IgnoreForum .= implode(",", $IgnoreForums); 
	
		$IgnoreForum .= ")"; 
	}

	if (strlen($vbulletin->options['xperience_ignore_usergroupsids']) > 0)
	{
		$usergroups = explode(",", $vbulletin->options['xperience_ignore_usergroupsids']);
		for ($i = 0; $i < count($usergroups); $i ++) 
		{
			if (is_member_of($user, $usergroups[$i]))
			{
				if ($DoDebug==1) echo "<br/>User via usergroup disabled";
				$this->DisableUser($user['userid']);
				return "";
			}
		}
	}
	
	if (strlen($vbulletin->options['xperience_ignore_users']) > 0)
	{
		$users=explode(",", $vbulletin->options['xperience_ignore_users']);
		for ($i = 0; $i < count($users); $i ++) 
		{
			if ($user['userid'] == $users[$i])
			{
				if ($DoDebug==1) echo "<br/>User via ignore user disabled";
				$this->DisableUser($user['userid']);
				return "";
			}
		}
	}
	
	if ($vbulletin->options['xperience_calcactive'])
	{
		$ppd_dateframe=mktime(date("H"), date("i"), 0, date("m"), date("d")-$vbulletin->options['xperience_ppd_days'], date("Y"));
		if ($user['lastactivity'] < $ppd_dateframe)
		{	
			if ($DoDebug==1) echo "<br/>User via activity disabled";
			$this->DisableUser($user['userid']);
			return "";
		}
	}
	
	$xperience_old = fetch_xperience($user['userid']);
	
	
	$current_timestamp = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y"));
	
	if ($DoDebug==1) echo "<br/>Threads";
	
	//Threads
	$xperience['count_threads'] = 0;
	$xperience['count_thread_votes'] = 0;
	$xperience['count_thread_replycount'] = 0;
	$xperience['count_thread_views'] = 0;
	$xperience['count_thread_stickies'] = 0;
	
	$thread_counts = $vbulletin->db->query_read("SELECT
			COUNT(*) AS count_threads,
			SUM(votetotal/votenum) AS count_votes,
			SUM(replycount) AS count_replycount,
			SUM(views) AS count_views,
			SUM(sticky) AS count_stickies
			FROM " . TABLE_PREFIX . "thread 
			WHERE visible=1 $IgnoreForum
			AND postuserid=".$user['userid']."
			AND dateline > ".$datecut
			);

	if ($vbulletin->db->num_rows($thread_counts) > 0)
	{
		$thread_count = $vbulletin->db->fetch_array($thread_counts);
		if ($vbulletin->options['xperience_points_pt'] > 0) $xperience['count_threads'] = $thread_count['count_threads']*$vbulletin->options['xperience_points_pt'];
		if ($vbulletin->options['xperience_points_po'] > 0) $xperience['count_thread_votes'] = $thread_count['count_votes']*$vbulletin->options['xperience_points_po'];
		if ($vbulletin->options['xperience_points_pr'] > 0) $xperience['count_thread_replycount'] = $thread_count['count_replycount']*$vbulletin->options['xperience_points_pr'];
		if ($vbulletin->options['xperience_points_pv'] > 0) $xperience['count_thread_views'] = $thread_count['count_views']*$vbulletin->options['xperience_points_pv'];
		if ($vbulletin->options['xperience_points_ps'] > 0) $xperience['count_thread_stickies'] = $thread_count['count_stickies']*$vbulletin->options['xperience_points_ps'];
	}



	//Posts
	if ($DoDebug==1) echo "<br/>Posts";
	$xperience['count_posts'] = 0;
	if ($vbulletin->options['xperience_points_pp'] > 0) 
	{
		$posts = $vbulletin->db->query_read("SELECT
		COUNT(*) AS count_posts
		FROM " . TABLE_PREFIX . "post AS p
		INNER JOIN " . TABLE_PREFIX . "thread as t ON p.threadid=t.threadid
		WHERE p.visible=1".$IgnoreForum."
		AND p.userid=".$user['userid']." 
		AND t.dateline > ".$datecut." 
		AND p.dateline > ".$datecut
		);

		if ($vbulletin->db->num_rows($posts) > 0)
		{
			$post = $vbulletin->db->fetch_array($posts);
			$xperience['count_posts'] = $post['count_posts']*$vbulletin->options['xperience_points_pp'];
			$xperience['postcount'] = $post['count_posts'];
		} 
	}



	//Days online
	$calcjoindate = $user['joindate'];	
	if ($calcjoindate < $datecut)
	{
		$calcjoindate = $datecut;
	}
	
	$xperience['user_daysonline'] = (TIMENOW - $calcjoindate) / 86400;
	
	$xperience['count_post_avg'] = 0;
	if ($xperience['user_daysonline'] > 7)
	{
		//Posts Per Day	
		if ($DoDebug==1) echo "<br/>Posts Per Day";
		
		if ($xperience['user_daysonline'] < 1)
		{
			$xperience['count_post_avg'] = $xperience['postcount'];
		}
		else
		{
			$ppdavg = $vbulletin->db->fetch_array($ppdavgq);
			$xperience['count_post_avg'] = $xperience['postcount'] / $xperience['user_daysonline'];
		}
	}

	//Attachments
	if ($DoDebug==1) echo "<br/>Attachments";
	$xperience['count_post_attachment'] = 0;
	if ($vbulletin->options['xperience_points_pa'] > 0) 
	{
		$attq =$vbulletin->db->query_read("SELECT 
		SUM(counter) as sum_att_views
		FROM " . TABLE_PREFIX . "attachment as a
		WHERE a.userid=".$user['userid']." 
		AND a.dateline > ".$datecut
		);
		
		if ($vbulletin->db->num_rows($attq) > 0)
		{
			$att = $vbulletin->db->fetch_array($attq);
			$xperience['count_post_attachment'] = $att['sum_att_views']*$vbulletin->options['xperience_points_pa'];
		} 
	}

	//Calendar Events
	if ($DoDebug==1) echo "<br/>Calendar Events";
	$xperience['count_misc_events'] = 0;
	if ($vbulletin->options['xperience_points_events'] > 0) 
	{
		$eventsq = $vbulletin->db->query_read("SELECT
			COUNT(*) AS count_events
			FROM " . TABLE_PREFIX . "event
			WHERE visible=1
			AND userid=".$user['userid']." 
			AND dateline > ".$datecut
			);

		if ($vbulletin->db->num_rows($eventsq) > 0)
		{
			$events = $vbulletin->db->fetch_array($eventsq);
			$xperience['count_misc_events'] = $events['count_events']*$vbulletin->options['xperience_points_events'];
		} 
	}

	//Social Group Discussions
	if ($DoDebug==1) echo "<br/>Social Group Discussions";
	$xperience['count_threads_sg'] = 0;
	$xperience['count_posts_sg'] = 0;	
	if ($vbulletin->options['xperience_points_psgt'] > 0 OR $vbulletin->options['xperience_points_psgp'] > 0 )
	{
		
		if (DoesTableExists("discussion"))
		{
			if ($vbulletin->options['xperience_points_psgt'] > 0)
			{
				$downloadsq =$vbulletin->db->query_read("SELECT d.discussionid FROM " . TABLE_PREFIX . "discussion AS d
					INNER JOIN " . TABLE_PREFIX . "groupmessage AS g ON g.discussionid=d.discussionid
					WHERE postuserid=".$user['userid']." AND visible>0 AND deleted=0
					AND g.dateline > ".$datecut." 
					GROUP BY d.discussionid
				");
		
				if ($vbulletin->db->num_rows($downloadsq) > 0)
				{
					$xperience['count_threads_sg'] = $vbulletin->db->num_rows($downloadsq)*$vbulletin->options['xperience_points_psgt'];
				} 
			}
			
			if ($vbulletin->options['xperience_points_psgp'] > 0)
			{
				$downloadsq =$vbulletin->db->query_read("SELECT 
					COUNT(gmid) as sum_messages
					FROM " . TABLE_PREFIX . "groupmessage
					WHERE state='visible'
					AND dateline > ".$datecut." 
					AND postuserid=".$user['userid']."
					");
		
				if ($vbulletin->db->num_rows($downloadsq) > 0)
				{
					$downloads = $vbulletin->db->fetch_array($downloadsq);
					$xperience['count_posts_sg'] = $downloads['sum_messages']*$vbulletin->options['xperience_points_psgp'];
				} 
			}
			
		}
	}	
		

	
	//Hack "Who Downloaded This Attachment?"
	if ($DoDebug==1) echo "<br/>Who Downloaded This Attachment";
	$xperience['count_post_attachment_views'] = 0;
	if ($vbulletin->options['xperience_points_pav'] > 0)
	{	
		if (DoesTableExists("attachmentviewslog"))
		{
			$downloadsq =$vbulletin->db->query_read("SELECT 
			COUNT(attachmentid) as sum_views
			FROM " . TABLE_PREFIX . "attachmentviewslog
			WHERE userid=".$user['userid']."
			AND dateline > ".$datecut." 
			");
	
			if ($vbulletin->db->num_rows($downloadsq) > 0)
			{
				$downloads = $vbulletin->db->fetch_array($downloadsq);
				$xperience['count_post_attachment_views'] = $downloads['sum_views']*$vbulletin->options['xperience_points_pav'];
			} 
		}
	}

	//Fill Profile
	if ($DoDebug==1) echo "<br/>Fill Profile";
	$xperience['count_user_profile']=0;
	if ($vbulletin->options['xperience_points_upr'] > 0) 
	{
		if (count($profilefields) > 0)
		{
			$userprofileq =$vbulletin->db->query_read("SELECT 
				*
				FROM " . TABLE_PREFIX . "userfield
				WHERE userid=".$user['userid']."
				");
			if ($vbulletin->db->num_rows($userprofileq) > 0) 
			{
				$userprofile = $vbulletin->db->fetch_array($userprofileq);
				foreach($profilefields AS $profilekey => $profileid)
				{
					if (strlen($userprofile['field'.$profileid]) > 0)
					{
						$xperience['count_user_profile'] += $vbulletin->options['xperience_points_upr'];
					}
				}
			}
		}
	}
	
	//Discussion Ender
	if ($DoDebug==1) echo "<br/>Discussion Ender";
	$xperience['count_post_den'] = 0;
	if ($vbulletin->options['xperience_points_den'] > 0)
	{
		$editsq = $vbulletin->db->query_read("SELECT
			P.userid, COUNT(userid) AS times
			FROM " . TABLE_PREFIX . "thread AS T
			INNER JOIN " . TABLE_PREFIX . "post AS P ON T.lastpostid=P.postid
			WHERE P.userid=".$user['userid']." 
			AND T.dateline > ".$datecut." 
			AND P.dateline > ".$datecut." 
			GROUP BY P.userid
			ORDER BY P.dateline DESC, P.userid"
			);
	
				
		if ($vbulletin->db->num_rows($editsq) > 0)
		{
			$edit = $vbulletin->db->fetch_array($editsq);
			$xperience['count_post_den'] = $edit['times'] * $vbulletin->options['xperience_points_den'];
		}
	}

	//Arm of Law
	if ($DoDebug==1) echo "<br/>Arm of Law";
	$xperience['count_user_law'] = 0;
	if ($vbulletin->options['xperience_points_law'] > 0)
	{
		$edits = $vbulletin->db->query_read("SELECT
			COUNT(*) AS cnt_edits
			FROM " . TABLE_PREFIX . "infraction
			WHERE whoadded=".$user['userid']." 
			AND dateline > ".$datecut." 
			GROUP BY whoadded"
			);
	
				
		if ($vbulletin->db->num_rows($edits) > 0)
		{
			$edit = $vbulletin->db->fetch_array($edits);
			$xperience['count_user_law'] = $edit['cnt_edits'] * $vbulletin->options['xperience_points_law'];
		}
	}


	//Infractions
	if ($DoDebug==1) echo "<br/>Infractions";
	$xperience['count_user_infractions']=0;
	if ($vbulletin->options['xperience_points_pi'] > 0) 
	{
	
		if ($vbulletin->options['xperience_pi_mode']) 
		{
			
			$infractionq = $vbulletin->db->query_read("SELECT
				SUM(points) AS sum_points
				FROM " . TABLE_PREFIX . "infraction
				WHERE action<>2 AND userid=".$user['userid']." 
				AND dateline > ".$datecut
			);
	
			if ($vbulletin->db->num_rows($infractionq) > 0)
			{
				$infraction = $vbulletin->db->fetch_array($infractionq);
				$xperience['count_user_infractions'] = $infraction['sum_points']*$vbulletin->options['xperience_points_pi'];
			}
		
			
		} else {
			$xperience['count_user_infractions'] = $vbulletin->options['xperience_points_pi']*$user['ipoints'];
		}
	}

	//Reputation
	if ($DoDebug==1) echo "<br/>Reputation";
	$xperience['count_user_reputation']=0;
	$xperience['count_user_reputation_use']=0;
	if ($vbulletin->options['reputationenable']) 
	{
	
		if ($vbulletin->options['xperience_points_pu'] > 0) $xperience['count_user_reputation'] = $vbulletin->options['xperience_points_pu']*$user['reputation'];
	
		if ($vbulletin->options['xperience_points_puu'] > 0) 
		{
			$repuseq = $vbulletin->db->query_read("SELECT
				COUNT(whoadded) AS count_reputation_use
				FROM " . TABLE_PREFIX . "reputation
				WHERE whoadded=".$user['userid']." 
				AND dateline > ".$datecut
			);
	
			if ($vbulletin->db->num_rows($repuseq) > 0)
			{
				$repuse = $vbulletin->db->fetch_array($repuseq);
				$xperience['count_user_reputation_use'] = $repuse['count_reputation_use']*$vbulletin->options['xperience_points_puu'];
			}
		}
	}
	
	
	

	$xperience['count_user_online']=0;
	if ($vbulletin->options['xperience_points_pd'] > 0) $xperience['count_user_online'] = $xperience['user_daysonline']*$vbulletin->options['xperience_points_pd'];

	//Referrals
	if ($DoDebug==1) echo "<br/>Referrals";
	$xperience['count_user_referrals'] = 0;
	if ($vbulletin->options['xperience_points_pf'] > 0) 
	{
		$refs = $vbulletin->db->query_read("SELECT
		COUNT(referrerid) AS count_referrer
		FROM " . TABLE_PREFIX . "user AS u
		WHERE u.referrerid=".$user['userid']."
		GROUP BY referrerid"		
		);

		if ($vbulletin->db->num_rows($refs) > 0)
		{
			$ref = $vbulletin->db->fetch_array($refs);
			$xperience['count_user_referrals'] = $ref['count_referrer']*$vbulletin->options['xperience_points_pf'];
		} 
	}

	//Custom Points
	if ($DoDebug==1) echo "<br/>Custom Points";
	$xperience['count_misc_custom'] = 0;
	$customq = $vbulletin->db->query_read("SELECT
		SUM(points_misc_custom) AS count_custom
		FROM " . TABLE_PREFIX . "xperience_custompoints AS c
		WHERE c.userid=".$user['userid']."
		GROUP BY c.userid"		
	);

	if ($vbulletin->db->num_rows($customq) > 0)
	{
		$custom = $vbulletin->db->fetch_array($customq);
		$xperience['count_misc_custom'] = $custom['count_custom'];
	} 


	//Social groups
	if ($DoDebug==1) echo "<br/>Social Groups";
	$xperience['count_user_socialgroup'] = 0;
	if ($vbulletin->options['xperience_points_pg'] > 0) 
	{
		$sgmembersq =$vbulletin->db->query_read("SELECT 
		SUM(members) as sum_members
		FROM " . TABLE_PREFIX . "socialgroup
		WHERE creatoruserid=".$user['userid']);

		if ($vbulletin->db->num_rows($sgmembersq) > 0)
		{
			$sgmembers = $vbulletin->db->fetch_array($sgmembersq);
			$xperience['count_user_socialgroup'] = $sgmembers['sum_members']*$vbulletin->options['xperience_points_pg'];
		} 
	}

	if ($DoDebug==1) echo "<br/>Tags";
	//Tags
	$xperience['count_thread_tags'] = 0;
	if ($vbulletin->options['xperience_points_pb'] > 0) 
	{
	$tagq =$vbulletin->db->query_read("SELECT 
		COUNT(*) as count_tags
		FROM " . TABLE_PREFIX . "tagcontent
		WHERE userid=".$user['userid']." 
		AND dateline > ".$datecut		
		);

		if ($vbulletin->db->num_rows($tagq) > 0)
		{
			$tag = $vbulletin->db->fetch_array($tagq);
			$xperience['count_thread_tags'] = $tag['count_tags']*$vbulletin->options['xperience_points_pb'];
		} 
	}


	if ($DoDebug==1) echo "<br/>Threadrate";
	//Threadrate
	$xperience['count_thread_rate'] = 0;
	if ($vbulletin->options['xperience_points_tr'] > 0) 
	{
	$tagq =$vbulletin->db->query_read("SELECT 
		COUNT(*) as count_tr
		FROM " . TABLE_PREFIX . "threadrate
		WHERE userid=".$user['userid']);

		if ($vbulletin->db->num_rows($tagq) > 0)
		{
			$tag = $vbulletin->db->fetch_array($tagq);
			$xperience['count_thread_rate'] = $tag['count_tr']*$vbulletin->options['xperience_points_tr'];
		} 
	}
	
	//Friends
	if ($DoDebug==1) echo "<br/>Friends";
	$xperience['count_user_friends'] = 0;
	if ($vbulletin->options['xperience_points_pe'] > 0) 
	{	
		$friendsq =$vbulletin->db->query_read("SELECT 
		friendcount
		FROM " . TABLE_PREFIX . "user
		WHERE userid=".$user['userid']);

		if ($vbulletin->db->num_rows($friendsq) > 0)
		{
			$friends = $vbulletin->db->fetch_array($friendsq);
			$xperience['count_user_friends'] = $friends['friendcount']*$vbulletin->options['xperience_points_pe'];
		}
	}
	

	//Visitormessages
	if ($DoDebug==1) echo "<br/>Visitor Messages";
	$xperience['count_user_visitormessages'] = 0;
	if ($vbulletin->options['xperience_points_pm'] > 0) 
	{	
		$vmessagesq =$vbulletin->db->query_read("SELECT 
		COUNT(*) as count_visitormessages
		FROM " . TABLE_PREFIX . "visitormessage
		WHERE state='visible' AND postuserid=".$user['userid']."
		AND dateline > ".$datecut
		);

		if ($vbulletin->db->num_rows($vmessagesq) > 0)
		{
			$vmessages = $vbulletin->db->fetch_array($vmessagesq);
			$xperience['count_user_visitormessages'] = $vmessages['count_visitormessages']*$vbulletin->options['xperience_points_pm'];
		}
	}

	//Albumpictures
	if ($DoDebug==1) echo "<br/>Albumpictures";
	$xperience['count_user_albumpictures'] = 0;
	if ($vbulletin->options['xperience_points_pc'] > 0) 
	{	
		$albumfield="visible";
		if (DoesColumnExists('album', 'picturecount'))
		{
			$albumfield="picturecount";
		}
		$vmessagesq =$vbulletin->db->query_read("SELECT 
		SUM($albumfield) AS cnt_pictures
		FROM " . TABLE_PREFIX . "album
		WHERE userid=".$user['userid']."
		AND createdate > ".$datecut
		);

		if ($vbulletin->db->num_rows($vmessagesq) > 0)
		{
			$vmessages = $vbulletin->db->fetch_array($vmessagesq);
			$xperience['count_user_albumpictures'] = $vmessages['cnt_pictures']*$vbulletin->options['xperience_points_pc'];
		} 
	}

	
	

	
	//Hack "Links and Downloads"
	if ($DoDebug==1) echo "<br/>LDM";
	$xperience['count_misc_ldm'] = 0;
	if ($vbulletin->options['xperience_points_pw'] > 0) 
	{	
		if (DoesTableExists("local_linkslink"))
		{
			$downloadsq =$vbulletin->db->query_read("SELECT 
			SUM(linkhits) as sum_linkhits
			FROM " . TABLE_PREFIX . "local_linkslink
			WHERE linkuserid=".$user['userid']."
			AND linkdate > ".$datecut." 
			GROUP BY linkuserid		
			");
	
			if ($vbulletin->db->num_rows($downloadsq) > 0)
			{
				$downloads = $vbulletin->db->fetch_array($downloadsq);
				$xperience['count_misc_ldm'] = $downloads['sum_linkhits']*$vbulletin->options['xperience_points_pw'];
			} 
		}
	}
	
	//Hack "DownloadsII"
	if ($DoDebug==1) echo "<br/>DL2";
	$xperience['count_misc_dl2'] = 0;
	if ($vbulletin->options['xperience_points_pw'] > 0) 
	{	
		if (DoesTableExists("dl2_files"))
		{
			$downloadsq =$vbulletin->db->query_read("SELECT 
			SUM(totaldownloads) as sum_downloads
			FROM " . TABLE_PREFIX . "dl2_files
			WHERE uploaderid=".$user['userid']."
			AND dateadded > ".$datecut."
			GROUP BY uploaderid		
			");
	
			if ($vbulletin->db->num_rows($downloadsq) > 0)
			{
				$downloads = $vbulletin->db->fetch_array($downloadsq);
				$xperience['count_misc_dl2'] = $downloads['sum_downloads']*$vbulletin->options['xperience_points_pw'];
			}
		} 
	}


	if ($DoDebug==1) echo "<br/>vBulletin Blog";
	
	//vBBlog
	$xperience['count_misc_vbblog'] = 0;
	if ($vbulletin->options['xperience_points_pn'] > 0) 
	{
		if (DoesTableExists("blog"))
		{	
			$thread_counts = $vbulletin->db->query_read("SELECT
					COUNT(*) AS count_blogentries,
					SUM(ratingtotal/ratingtotal) AS count_votes,
					SUM(comments_visible) AS count_replycount,
					SUM(views) AS count_views
					FROM " . TABLE_PREFIX . "blog 
					WHERE state='visible'
					AND userid=".$user['userid']."
					AND dateline > ".$datecut
					);
		
			if ($vbulletin->db->num_rows($thread_counts) > 0) 
			{
				$thread_count = $vbulletin->db->fetch_array($thread_counts);
				if ($vbulletin->options['xperience_points_po'] > 0) $xperience['misc_vbblog_votes'] = $thread_count['count_votes']*$vbulletin->options['xperience_points_po'];
				if ($vbulletin->options['xperience_points_pr'] > 0) $xperience['misc_vbblog_replycount'] = $thread_count['count_replycount']*$vbulletin->options['xperience_points_pr'];
				if ($vbulletin->options['xperience_points_pv'] > 0) $xperience['misc_vbblog_views'] = $thread_count['count_views']*$vbulletin->options['xperience_points_pv'];
				
					$xperience['count_misc_vbblog'] = $thread_count['count_blogentries']*$vbulletin->options['xperience_points_pn'];
					$xperience['count_misc_vbblog']+=$xperience['misc_vbblog_votes'];
					$xperience['count_misc_vbblog']+=$xperience['misc_vbblog_replycount'];
					$xperience['count_misc_vbblog']+=$xperience['misc_vbblog_views'];
			}
		}
	}
	
	if ($DoDebug==1) echo "<br/>vBulletin CMS";
	
	//vBCMS
	$xperience['count_misc_vbcms'] = 0;
	if ($vbulletin->options['xperience_points_cms'] > 0) 
	{
		if (DoesTableExists("cms_node"))
		{	
			$thread_counts = $vbulletin->db->query_read("SELECT
					COUNT(*) AS count_cms
					FROM " . TABLE_PREFIX . "cms_node 
					WHERE setpublish=1
					AND userid=".$user['userid']."
					AND publishdate > ".$datecut
					);
		
			if ($vbulletin->db->num_rows($thread_counts) > 0) 
			{
				$thread_count = $vbulletin->db->fetch_array($thread_counts);
				$xperience['count_misc_vbcms'] = $thread_count['count_cms']*$vbulletin->options['xperience_points_cms'];
			}
		}
	}
	
	

//Hack "Cyb - PayPal Donate"
if ($DoDebug==1) echo "<br/>Paypal";
	$xperience['count_misc_ppd'] = 0;
	if ($vbulletin->options['xperience_points_py_DISABLED'] > 0) 
	{
		if (DoesTableExists("cybppdonate"))
		{
			$ppdonateq =$vbulletin->db->query_read("SELECT 
			SUM(amount) as sum_amount
			FROM " . TABLE_PREFIX . "cybppdonate
			WHERE confirmed = '1' AND userid=".$user['userid']."
			AND dateline > ".$datecut." 
			GROUP BY userid		
			");
	
			if ($vbulletin->db->num_rows($ppdonateq) > 0)
			{
				$ppdonate = $vbulletin->db->fetch_array($ppdonateq);
				$xperience['count_misc_ppd'] = $ppdonate['sum_amount']*$vbulletin->options['xperience_points_py'];
			}
		}
	}
	
	
	$xperience['count_misc'] = 0;
	$xperience['count_thread'] = 0;
	$xperience['count_post'] = 0;
	$xperience['count_user'] = 0;
	$xperience['count_misc'] = 0;
	$xperience['count_xperience'] = 0;

	$xperience['count_shop'] = 0;
	
	$xperience_new['points_shop'] = intval($xperience_old['points_shop']);
	$xperience["count_shop"] = $xperience_new['points_shop'];
	
	
	if ($vbulletin->options['xperience_use_gap']) 
	{
		if ($DoDebug==1) echo "<br/>Give Away Points";
		
		$gap_f_q =$vbulletin->db->query_read("SELECT
			amount, field
			FROM " . TABLE_PREFIX . "xperience_gap
			WHERE
			userid = ".$user['userid']);
			
		if ($vbulletin->db->num_rows($gap_f_q) > 0)
		{	
			while ($gap_f = $vbulletin->db->fetch_array($gap_f_q))
			{
				$field = $gap_f['field'];
				$field = str_replace("points", "count", $field);
				$xperience["$field"] -= $gap_f['amount'];
			}
		}
		
		$gap_t_q =$vbulletin->db->query_read("SELECT
			amount, field
			FROM " . TABLE_PREFIX . "xperience_gap
			WHERE
			toid = ".$user['userid']);
			
		if ($vbulletin->db->num_rows($gap_t_q) > 0)
		{
			while ($gap_t = $vbulletin->db->fetch_array($gap_t_q))
			{
				$field = $gap_t['field'];
				$field = str_replace("points", "count", $field);
				$xperience["$field"] += $gap_t['amount'];
			}
		}
		
	}


	($hook = vBulletinHook::fetch_hook('xperience_calcdata_summaries')) ? eval($hook) : false;
	
	if ($DoDebug==1) echo "<br/>Summaries";
	//Summary Thread
	$xperience['count_thread'] += $xperience['count_threads'];
	$xperience['count_thread'] += $xperience['count_threads_sg'];
	$xperience['count_thread'] += $xperience['count_thread_votes'];
	$xperience['count_thread'] += $xperience['count_thread_replycount'];
	$xperience['count_thread'] += $xperience['count_thread_views'];
	$xperience['count_thread'] += $xperience['count_thread_stickies'];
	$xperience['count_thread'] += $xperience['count_thread_tags'];
	$xperience['count_thread'] += $xperience['count_thread_rate'];
	($hook = vBulletinHook::fetch_hook('xperience_calcdata_thread')) ? eval($hook) : false;
	
	//Summary Post
	$xperience['count_post'] += $xperience['count_posts'];
	$xperience['count_post'] += $xperience['count_posts_den'];
	$xperience['count_post'] += $xperience['count_posts_sg'];
	$xperience['count_post'] += $xperience['count_post_thanks'];
	$xperience['count_post'] += $xperience['count_post_thanks_use'];
	$xperience['count_post'] += $xperience['count_post_attachment'];
	$xperience['count_post'] += $xperience['count_post_attachment_views'];
	($hook = vBulletinHook::fetch_hook('xperience_calcdata_post')) ? eval($hook) : false;
	
	//Summary User
	$xperience['count_user'] += $xperience['count_user_socialgroup'];
	$xperience['count_user'] += $xperience['count_user_friends'];
	$xperience['count_user'] += $xperience['count_user_reputation'];
	$xperience['count_user'] += $xperience['count_user_reputation_use'];
	$xperience['count_user'] += $xperience['count_user_visitormessages'];
	$xperience['count_user'] += $xperience['count_user_online'];
	$xperience['count_user'] += $xperience['count_user_albumpictures'];
	$xperience['count_user'] += $xperience['count_user_referrals'];	
	$xperience['count_user'] += $xperience['count_user_profile'];
	$xperience['count_user'] += $xperience['count_user_law'];
	$xperience['count_user'] -= $xperience['count_user_infractions'];
	($hook = vBulletinHook::fetch_hook('xperience_calcdata_user')) ? eval($hook) : false;
	
	//Summary Misc
	$xperience['count_misc'] += $xperience['count_misc_ldm'];
	$xperience['count_misc'] += $xperience['count_misc_dl2'];
	$xperience['count_misc'] += $xperience['count_misc_ppd'];
	$xperience['count_misc'] += $xperience['count_misc_vbblog'];
	$xperience['count_misc'] += $xperience['count_misc_vbcms'];
	$xperience['count_misc'] += $xperience['count_misc_custom'];
	$xperience['count_misc'] += $xperience['count_misc_events'];
	$xperience['count_misc'] -= $xperience_new['points_shop'];
	($hook = vBulletinHook::fetch_hook('xperience_calcdata_misc')) ? eval($hook) : false;
	
	($hook = vBulletinHook::fetch_hook('xperience_calcdata')) ? eval($hook) : false;
	
	
	
		if ($DoDebug==1) echo "<br/>Summary ".$xperience['count_xperience'];
	//Summary All
	$xperience['count_xperience'] += $xperience['count_thread'];
	$xperience['count_xperience'] += $xperience['count_post'];
	$xperience['count_xperience'] += $xperience['count_user'];
	$xperience['count_xperience'] += $xperience['count_misc'];
	$xperience['count_xperience'] = floor($xperience['count_xperience']);
	
	if ($DoDebug==1) echo "<br/>Summary Misc: ".$xperience['count_misc'];
	if ($DoDebug==1) echo "<br/>Summary Post: ".$xperience['count_post'];
	if ($DoDebug==1) echo "<br/>Summary Thread: ".$xperience['count_thread'];
	if ($DoDebug==1) echo "<br/>Summary User: ".$xperience['count_user'];
	if ($DoDebug==1) echo "<br/>Summary: ".$xperience['count_xperience'];
	
	
	if ($DoDebug==1) echo "<br/>Level";
	$level_current_q = $vbulletin->db->query_read("SELECT *
		FROM " . TABLE_PREFIX . "xperience_level
		WHERE xperience_points<".$xperience['count_xperience']."
		ORDER BY xperience_points DESC LIMIT 0,1"); 
		
	if ($vbulletin->db->num_rows($level_current_q) > 0)
	{
		$level_current = $vbulletin->db->fetch_array($level_current_q);
	}
	else
	{
		$level_current['xperience_level']=1;
		$level_current['xperience_points']=1;
		$level_current['usergroupid']=0;
	}
	if ($level_current['xperience_level'] > $vbulletin->options['xperience_maxlevel'])
	{
		$level_current['xperience_level'] = $vbulletin->options['xperience_maxlevel'];
	}
	
	if ($level_current['xperience_level'] < 1)
	{
		$level_current['xperience_level'] = 1;
	}
	
	$level_up_q = $vbulletin->db->query_read("SELECT xperience_level, xperience_points
		FROM " . TABLE_PREFIX . "xperience_level
		WHERE xperience_points>".$xperience['count_xperience']."
		ORDER BY xperience_points ASC LIMIT 0,1"); 
		
	if ($vbulletin->db->num_rows($level_up_q) > 0)
	{
		$level_up = $vbulletin->db->fetch_array($level_up_q);
	}
	else
	{
		$level_up['xperience_level']=2;
		$level_up['xperience_points']=2;
	}
	
	$xperience_difference_level = $level_up['xperience_points'] - $level_current['xperience_points'];
	$xperience_difference_user = $level_up['xperience_points'] - $xperience['count_xperience'];

	$xperience_next = 100-floor(100*($xperience_difference_user/$xperience_difference_level));
	if ($xperience_next > 99)
	{
		$xperience_next = 99;
	}
	if ($xperience_next < 0)
	{
		$xperience_next = 0;
	}

	
	
	

	
//	if ($vbulletin->options['xperience_use_achievements']) 
//	{
//		if ($DoDebug==1) echo "<br/>Achievements";
//		DoAchievements($user, $xperience);
//	}
		
	

	if ($DoDebug==1) echo "<br/>Stats";
 
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_stats WHERE userid=".$user['userid']);
	$isstatsq = $vbulletin->db->query_read("SELECT userid from " . TABLE_PREFIX . "xperience_stats WHERE userid=".$user['userid']);
	if ($vbulletin->db->num_rows($isstatsq)==0)	$vbulletin->db->query_write("REPLACE INTO " . TABLE_PREFIX . "xperience_stats (userid) VALUES (".$user['userid'].")");

	$xperience['count_user_activity'] = $this->GetPPD(7, $user, $vbulletin->options['xperience_avgppd7']);
	$xperience['count_user_activity30'] = $this->GetPPD(30, $user, $vbulletin->options['xperience_avgppd30']);
	$xperience['count_user_activity7'] = $this->GetPPD($vbulletin->options['xperience_ppd_days'], $user, $vbulletin->options['xperience_avgppd']);

	$xperience['count_level'] = $level_current['xperience_level'];

	$xperience['promoted'] = 0;
	if ($vbulletin->options['xperience_use_promotions']) 
	{
		if ($DoDebug==1) echo "<br/>Promotions";
		DoPromotions($user, $xperience);
		
	}

	

	if ($DoDebug==1) echo "<br/>Update<br/>";

	$points_needed = $level_up['xperience_points']-$xperience['count_xperience'];
	if ($points_needed<0)
	{
		$points_needed=0;
	}
	
	if ($vbulletin->options['xperience_maxlevel']<>100) 
	{
		$level_current['xperience_levelp'].=floor(100*($level_current['xperience_level']/$vbulletin->options['xperience_maxlevel']));
	}
	else
	{
		$level_current['xperience_levelp'] = $level_current['xperience_level'];
	}
		


	$UpdateQuery = "UPDATE " . TABLE_PREFIX . "xperience_stats 
		SET
		points_xperience=".$xperience['count_xperience'].",";
		
	foreach($xperience AS $qfield => $qvalue)
	{

		if (stristr($qfield, 'count_') == true)
		{
			$newfield = str_replace("count_", "points_", $qfield);
			$UpdateQuery .= $newfield."=".(int)$xperience[$qfield].",";
		}
	}
			
	($hook = vBulletinHook::fetch_hook('xperience_calcdata_query')) ? eval($hook) : false;


	$UpdateQuery .="promoted=".$xperience['promoted'].",
	dateline=".$current_timestamp."	
	WHERE userid=".$user['userid'];
	

	$vbulletin->db->query_write($UpdateQuery);


	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
		SET
		xperience_done=1, 
		xperience=".(int)$xperience['count_xperience'].",
		xperience_level=".(int)$level_current['xperience_level'].",
		xperience_levelp=".(int)$level_current['xperience_levelp'].",
		xperience_next_level=".(int)$xperience_next.",
		xperience_next_level_points=".(int)$points_needed.",
		xperience_lastupdate=".(int)$current_timestamp.",
		xperience_ppd=".(int)$xperience['count_user_activity'].",
		xperience_ppd30=".(int)$xperience['count_user_activity30'].",	
		xperience_ppd7=".(int)$xperience['count_user_activity7']."	
		WHERE userid=".(int)$user['userid']);
	
	ValidateActivity();
	
	 
	

	
	if ($vbulletin->options['xperience_use_activities']) 
	{
		$xperience_new = fetch_xperience($user['userid']);
		
		$fields_notallowed = array("dateline", "points_user_activity", "points_user_activity30", "points_user_activity7", "points_post_avg", "points_user_online");
		$fields_major_low = array("points_thread",  "points_post", "points_user", "points_misc");
		$fields_major_high = array("points_xperience");
		
		($hook = vBulletinHook::fetch_hook('xperience_calcdata_stats')) ? eval($hook) : false;
	
		if (count($xperience_old) > 0 AND count($xperience_new) > 0)
		{
			foreach($xperience_old AS $fieldk => $fielde)
			{
				if (!in_array($fieldk, $fields_notallowed))
				{
					if ($xperience_new[$fieldk] > $fielde OR $xperience_new[$fieldk] < $fielde)
					{
						$difference = $xperience_new[$fieldk] - $fielde;
						
						if ($DoDebug==1) echo "<br/>Changes: ".$fieldk.", ".$difference;
						
						$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_stats_changes
						(userid, field, oldvalue, difference, newvalue, ismajor, dateline)
						VALUES (
						".$user['userid'].",
						'".$fieldk."',
						".(int)$fielde.",
						".(int)$difference.",
						".(int)$xperience_new[$fieldk].",
						".iif(in_array($fieldk, $fields_major_low), 1, iif(in_array($fieldk, $fields_major_high), 2, 0)).",
						".$current_timestamp.")");
					}
				}
			}
		}
	}


	($hook = vBulletinHook::fetch_hook('xperience_calcdata_end')) ? eval($hook) : false;
}

function CalculateGroupXP($group)
{
	global $vbulletin;
	
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_groups WHERE groupid=".$group['groupid']);
	
	$pointsq =$vbulletin->db->query_read("SELECT
		SUM(points_xperience) AS sum_points, COUNT(*) AS count_members, MAX(points_xperience) AS max_points, MIN(points_xperience) AS min_points
		FROM " . TABLE_PREFIX . "socialgroupmember
		INNER JOIN " . TABLE_PREFIX . "xperience_stats on " . TABLE_PREFIX . "xperience_stats.userid=" . TABLE_PREFIX . "socialgroupmember.userid
		WHERE type='member' AND groupid=".$group['groupid']);
	
	if ($vbulletin->db->num_rows($pointsq) > 0) 
	{
		$points = $vbulletin->db->fetch_array($pointsq);
		if ($points['sum_points'] > 0 AND $points['count_members'] > 0 AND $points['count_members'] >= $vbulletin->options['xperience_groups_minmembers'])
		{
			$sum_points = floor($points['sum_points']/$points['count_members']);
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_groups (groupid, members, points, points_max, points_min)
				VALUES (
				".$group['groupid'].",
				".$points['count_members'].",
				".$sum_points.",
				".$points['max_points'].",
				".$points['min_points'].")");	
		}
	}
}

function DisableUser($UserID) 
{
	global $vbulletin;

	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "xperience_stats WHERE userid=".$UserID);

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
		SET
	
		xperience_awardt='', 
		xperience_done=1, 
		xperience=0,
		xperience_level=0,
		xperience_next_level=0,
		xperience_next_level_points=0,
		xperience_ppd=0
		WHERE userid=".$UserID);
}

function GetPPD($days, $user, $avgppd)
{
	global $vbulletin;

	$ppd_dateframe = mktime(date("H"), date("i"), 0, date("m"), date("d")-$days, date("Y"));
	
	$ppdq = $vbulletin->db->query_read("SELECT
		COUNT(*) AS mycount
		FROM " . TABLE_PREFIX . "xperience_stats_changes
		WHERE dateline>".$ppd_dateframe." AND ismajor=0
		AND userid=".$user['userid']
	);
	
	if ($vbulletin->db->num_rows($ppdq) > 0)
	{
		$ppda = $vbulletin->db->fetch_array($ppdq);
		$ownppdv = $ppda['mycount'];
	}
	else
	{
		$ownppdv = 0;
	}
			
			
	if ((($avgppd == 0) AND ($ownppdv == 0)) OR (($avgppd > 0) AND ($ownppdv == 0)))
	{
		$posts_per_day = 0;
	}
	
	if (($avgppd == 0) AND ($ownppdv > 0))
	{
		$posts_per_day = 100;
	}

	if (($avgppd > 0) AND ($ownppdv > 0))
	{
		$posts_per_day = 100 * ($ownppdv / $avgppd);
	}
	
	if ($posts_per_day > 100)
	{
		$posts_per_day = 100;
	}
	
	if ($posts_per_day < 0)
	{
		$posts_per_day = 0;
	}
			
	return $posts_per_day;
	
}


function GetAVGPPD($days)
{
	global $vbulletin;
	
	$ppd_dateframe = mktime(date("H"), date("i"), 0, date("m"), date("d")-$days, date("Y"));
	
	$ppdq = $vbulletin->db->query_read("SELECT
		COUNT(*) AS mycount
		FROM " . TABLE_PREFIX . "xperience_stats_changes
		WHERE dateline>".$ppd_dateframe." AND ismajor=0
		GROUP BY userid
		ORDER BY mycount DESC
		LIMIT 0,10
	");
	
	if ($vbulletin->db->num_rows($ppdq) > 0)
	{
		while ($ppda = $vbulletin->db->fetch_array($ppdq))
		{
			$ppdv += $ppda['mycount'];	
			$ppdc ++;
		}
		
	}
	else
	{
		$ppdv = 0;
	}
			
	if ($ppdv > 1)
	{
		$ppdv = $ppdv / $ppdc;
	}
	
	
return $ppdv;
	
}

function GetMaxLevel() 
{
	global $vbulletin;
	
	$maxlevelq =$vbulletin->db->query_read("SELECT
		xperience_level
		FROM " . TABLE_PREFIX . "xperience_level
		ORDER BY xperience_level DESC
		LIMIT 0,1");
	
	if ($vbulletin->db->num_rows($maxlevelq) > 0) 
	{
		$maxlevel = $vbulletin->db->fetch_array($maxlevelq);
		return $maxlevel['xperience_level'];
	} else {
		return 100;
	}
}


function CalculateAwards() 
{ 
    global $vbulletin; 
     // thanks goes out to Abe1, who helped me integrating "ignore groups" and "limit awards" to this
     
    $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user 
        SET 
        xperience_awardt=''"); 

    if (!$vbulletin->options['xperience_use_awards']) 
    { 
        return; 
    } 

    require_once('./includes/functions_xperience.php');     

    $awardsq =$vbulletin->db->query_read("SELECT 
        awardid AS id,
        awardtitle AS title, 
        awardurl AS small,
        awardbigurl AS big,
        awardfields,
        manualassign,
        awardlimit
        FROM " . TABLE_PREFIX . "xperience_awards 
        WHERE awardstatus > 0 
        ORDER BY awardcategory, awardlimit, awardstatus      
        "); 
         
        if ($vbulletin->db->num_rows($awardsq) > 0) 
        {
            while ($awards = $vbulletin->db->fetch_array($awardsq)) 
            { 

		    	      $awardfields = explode("+", $awards['awardfields']); 
		            foreach ($awardfields AS $awardfield) 
		            { 
		            	if (strlen($awardfield) > 0) 
		              {
										$awardfieldq =$vbulletin->db->query_read("SHOW COLUMNS FROM " . TABLE_PREFIX . "xperience_stats WHERE Field='".$awardfield."'");
										if ($vbulletin->db->num_rows($awardfieldq) == 0) 
										{
										 	continue 2;
										}
        					
									}
		            } 

                if ($awards['manualassign'] <> 0) 
                {
                    if (!strstr($awards['manualassign'], ",")) 
                    { 
                        if (verify_id("user", $awards['manualassign'], false) <> 0) 
                        { 
                            WriteAward($awards, $awards['manualassign'], true); 
                        } 
                    } else { 
                        $manualawards=explode(",", $awards['manualassign']); 
                         
                        for ($i = 0; $i < count($manualawards); $i ++) 
                        { 
                            if (verify_id("user", $manualawards[$i], false) <> 0) 
                            { 
                                WriteAward($awards, $manualawards[$i], true); 
                            } 
                        } 
                    } 
                } else { 
                    $wherenot = '';  
                    if (strlen($vbulletin->options['xperience_ignore_users']) > 0)  
                    {  
                        $IgnoreUser=" AND u.userid NOT IN(".$vbulletin->options['xperience_ignore_users'].")";  
                    } 

                    $usergroups = explode(",", $vbulletin->options['xperience_award_exclude']); 
                    foreach ($usergroups AS $usergroupid) 
                    { 
                    	if ($usergroupid>0) 
                      {
												$wherenot .= " OR u.usergroupid = '$usergroupid' OR FIND_IN_SET($usergroupid, u.membergroupids)"; 
											}
                    } 
                    
                    if ($vbulletin->options['xperience_award_max'])  
                    {  
                        if (is_array($awardsgiven)) 
                        { 
                            foreach ($awardsgiven AS $awardsgivenuserid => $awardsgivenawardfields) 
                            { 
                                if (count($awardsgivenawardfields) >= $vbulletin->options['xperience_award_max'] && !in_array($awards['awardfields'], $awardsgivenawardfields)) 
                                { 
                                    $wherenot .= " OR u.userid = '$awardsgivenuserid'"; 
                                } 
                            } 
                        } 
                    }  

                    $genawardq = $vbulletin->db->query_read("SELECT 
                        u.userid, 
                        ".$awards['awardfields']." 
                        AS get_count 
                        FROM " . TABLE_PREFIX . "xperience_stats as s 
                        INNER JOIN " . TABLE_PREFIX . "user as u ON u.userid = s.userid 
                        WHERE 1 = 1 $IgnoreUser AND NOT (1=2 $wherenot) 
                        ORDER BY get_count DESC, points_xperience DESC 
                        LIMIT ".$awards['awardlimit'].",1"); 
                                         
                    if ($vbulletin->db->num_rows($genawardq) > 0) 
                    { 
                        $genaward = $vbulletin->db->fetch_array($genawardq); 
                         
                        if ($genaward['get_count'] <> 0) 
                        { 
                            WriteAward($awards, $genaward['userid']); 
                            $awardsgiven[$genaward['userid']][] = $awards['awardfields'];  
                        } 
                         
                    } 
                } 
            } 
        } 
	}  



}
?>