<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBExperience 4.1                                                 # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2006-2011 Marius Czyz / Phalynx. All Rights Reserved. # ||
|| #################################################################### ||
\*======================================================================*/

function DoDropColumns()
{
	global $vbulletin;
	
	$vbulletin->db->hide_errors();
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "xperience_stats");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "xperience_groups");	
	$vbulletin->db->show_errors();

	DropField("user", "xperience");
	DropField("user", "xperience_done");
	DropField("user", "xperience_level");
	DropField("user", "xperience_levelp");
	DropField("user", "xperience_next_level");
	DropField("user", "xperience_next_level_points");
	DropField("user", "xperience_ppd");
	DropField("user", "xperience_ppd7");
	DropField("user", "xperience_ppd30");
	DropField("user", "xperience_awardt");
	DropField("user", "xperience_achievements");
	DropField("user", "xperience_lastupdate");
	DropField("user", "xperience_awardcount");
	DropField("user", "xperience_promotioncount");
	DropField("user", "xperience_achievementcount");
	DropField("user", "xperience_awards");
	DropField("user", "xperience_points");
	DropField("user", "xperience_shopitems");
}

function DoIndex()
{
	global $vbulletin;
	
	$vbulletin->db->hide_errors();
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "xperience_achievements_fields
		ADD INDEX achievementid (achievementid)");
	
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "xperience_achievements_issues
		ADD INDEX achievementid (achievementid),
		ADD INDEX userid (userid),
		ADD INDEX dateline (dateline)
		");

	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "xperience_award_issues
		ADD INDEX dateline (dateline)
		");

	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "xperience_promotion_issues
		ADD INDEX dateline (dateline)
		");
	
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "xperience_stats_changes
		ADD INDEX userid (userid),
		ADD INDEX dateline (dateline)
		");
		
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "xperience_gap
		ADD INDEX userid (userid),
		ADD INDEX toid (toid)
		");
	$vbulletin->db->show_errors();
}

function DoInstall()
{
	global $vbulletin;
	

	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_gap (
		gapid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		userid BIGINT NOT NULL DEFAULT '0', 
		toid BIGINT NOT NULL DEFAULT '0', 
		field VARCHAR(250) DEFAULT '',
		amount BIGINT NOT NULL DEFAULT '0', 
		dateline INT(10) DEFAULT '0',
		PRIMARY KEY (gapid),
		INDEX (userid, toid)
	)"); 
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_achievements_categories (
		categoryid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		categoryidentifier VARCHAR(50) DEFAULT '',
		categorytitle VARCHAR(250) NOT NULL DEFAULT 'Category',
		categorydesc MEDIUMTEXT,
		categoryorder INT(10) DEFAULT '1',
		PRIMARY KEY (categoryid)
	)");

	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_achievements (
		achievementid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		identifier VARCHAR(50) DEFAULT '',
		title VARCHAR(250) DEFAULT 'Achievement',
		description MEDIUMTEXT, 
		sortorder INT(10) DEFAULT '1',
		imagesmall VARCHAR(250) DEFAULT '',
		imagebig VARCHAR(250) DEFAULT '',	
		categoryid SMALLINT(10) DEFAULT '1',
		canlose SMALLINT NOT NULL DEFAULT '0',
		canpurchase SMALLINT NOT NULL DEFAULT '0',
		issecret SMALLINT NOT NULL DEFAULT '0',
		replaceid INT(10) DEFAULT '1',
		PRIMARY KEY (achievementid)
	)"); 
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_achievements_fields (
		fieldid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		achievementid INT(10) DEFAULT '0',
		field VARCHAR(250) DEFAULT '',
		value BIGINT NOT NULL DEFAULT '0',
		compare SMALLINT(6) NOT NULL DEFAULT '1',
		PRIMARY KEY (fieldid),
		INDEX (achievementid)
	)"); 
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_achievements_issues (
		issueid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		achievementid INT(10) DEFAULT '0',
		userid INT(10) DEFAULT '0',
		dateline INT(10) DEFAULT '0',
		visible SMALLINT DEFAULT '1',
		PRIMARY KEY (issueid),
		INDEX (achievementid),
		INDEX (userid),
		INDEX (dateline)
	)");
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_achievements_log (
		logid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		achievementid INT(10) DEFAULT '0',
		userid INT(10) DEFAULT '0',
		dateline INT(10) DEFAULT '0',
		logtype INT(10) DEFAULT '0',
		PRIMARY KEY (logid)
	)");
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_promotion (
		promotionid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		field VARCHAR(250) DEFAULT '',
		value BIGINT NOT NULL DEFAULT '0', 
		compare SMALLINT(6) NOT NULL DEFAULT '1',
		from_ug INT(10) DEFAULT '0',
		to_ug INT(10) DEFAULT '0',
		promotiontype INT(10) DEFAULT '0',
		sortorder INT(10) DEFAULT '0',
		parentid INT(10) DEFAULT '0',
		comment VARCHAR(250) DEFAULT '',
		PRIMARY KEY (promotionid)
	)"); 
	
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "xperience_promotion_log");
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_promotion_issues (
		promotionlogid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		promotionid INT(10) DEFAULT '0',
		userid INT(10) DEFAULT '0',
		from_ug INT(10) DEFAULT '0',
		to_ug INT(10) DEFAULT '0',
		promotiontype INT(10) DEFAULT '0',
		comment VARCHAR(250) NOT NULL DEFAULT '-',
		dateline INT(10) DEFAULT '0',
		PRIMARY KEY (promotionlogid),
		KEY userid (userid),
		INDEX (dateline)
	)"); 
	
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_awards (
		awardid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		awardname VARCHAR(250) NOT NULL DEFAULT 'award',
		awardtitle VARCHAR(250) NOT NULL DEFAULT 'My Award',
		awarddesc MEDIUMTEXT, 
		awardurl VARCHAR(250) DEFAULT '',
		awardbigurl VARCHAR(250) DEFAULT '',
		awardstatus SMALLINT NOT NULL DEFAULT '1', 
		awardfields MEDIUMTEXT,
		awardexclusions VARCHAR(250) DEFAULT '',
		awardlimit SMALLINT(6) DEFAULT '0',
		awardcategory SMALLINT(10) DEFAULT '1',
		manualassign VARCHAR(250) NOT NULL DEFAULT '0',
		PRIMARY KEY (awardid)
	)"); 
		
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_award_issues (
		issueid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		awardid INT(10) DEFAULT '0',
		userid INT(10) DEFAULT '0',
		dateline INT(10) DEFAULT '0',
		dateline_in INT(10) DEFAULT '0',
		dateline_out INT(10) DEFAULT '0',
		PRIMARY KEY (issueid),
		INDEX (dateline)
	)");
		
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_award_categories (
		categoryid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		categoryidentifier VARCHAR(50) DEFAULT '',
		categorytitle VARCHAR(250) NOT NULL DEFAULT 'Category',
		categorydesc MEDIUMTEXT,
		categoryorder INT(10) DEFAULT '1',
		PRIMARY KEY (categoryid)
	)");
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_groups (
		groupid INT(10) DEFAULT '0',
		members INT(10) DEFAULT '0',
		points BIGINT DEFAULT '0',
		points_max BIGINT DEFAULT '0',
		points_min BIGINT DEFAULT '0'
	)"); 
		
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_custompoints (
		pointid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		userid INT(10) DEFAULT '0',
		points_misc_custom BIGINT DEFAULT '0',
		adminid INT(10) DEFAULT '0',
		category VARCHAR(250) NOT NULL DEFAULT 'Manually assigned',
		comment VARCHAR(250) NOT NULL DEFAULT '-',
		dateline INT(10) DEFAULT '0',
		PRIMARY KEY (pointid),
		KEY userid (userid)
	)"); 
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_stats_changes (
		statsid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		userid BIGINT NOT NULL DEFAULT '0', 
		field VARCHAR(250) DEFAULT '',
		oldvalue BIGINT NOT NULL DEFAULT '0', 
		difference BIGINT NOT NULL DEFAULT '0', 
		newvalue BIGINT NOT NULL DEFAULT '0', 
		ismajor SMALLINT(6) DEFAULT '0',
		dateline INT(10) DEFAULT '0',
		PRIMARY KEY (statsid),
		INDEX (userid),
		INDEX (dateline)
	)"); 
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_stats (
		userid INT(10) DEFAULT '0',
		points_xperience BIGINT DEFAULT '0',
		points_thread BIGINT DEFAULT '0',
		points_threads BIGINT DEFAULT '0',
		points_threads_sg BIGINT DEFAULT '0',
		points_thread_votes BIGINT DEFAULT '0',
		points_thread_replycount BIGINT DEFAULT '0',
		points_thread_views BIGINT DEFAULT '0',
		points_thread_stickies BIGINT DEFAULT '0',
		points_thread_tags BIGINT DEFAULT '0',
		points_thread_rate BIGINT DEFAULT '0',
		points_post BIGINT DEFAULT '0',
		points_posts BIGINT DEFAULT '0',
		points_posts_sg BIGINT DEFAULT '0',
		points_post_thanks BIGINT DEFAULT '0',
		points_post_thanks_use BIGINT DEFAULT '0',
		points_post_attachment BIGINT DEFAULT '0',
		points_post_attachment_views BIGINT DEFAULT '0',
		points_post_avg FLOAT DEFAULT '0',
		points_post_den BIGINT DEFAULT '0',
		points_user BIGINT DEFAULT '0',
		points_user_profile BIGINT DEFAULT '0',
		points_user_infractions BIGINT DEFAULT '0',
		points_user_reputation BIGINT DEFAULT '0',
		points_user_reputation_use BIGINT DEFAULT '0',
		points_user_online BIGINT DEFAULT '0',
		points_user_socialgroup BIGINT DEFAULT '0',
		points_user_visitormessages BIGINT DEFAULT '0',
		points_user_albumpictures BIGINT DEFAULT '0',
		points_user_referrals BIGINT DEFAULT '0',
		points_user_friends BIGINT DEFAULT '0',
		points_user_activity FLOAT DEFAULT '0',
		points_user_activity30 FLOAT DEFAULT '0',
		points_user_activity7 FLOAT DEFAULT '0',
		points_user_law BIGINT DEFAULT '0',
		points_misc BIGINT DEFAULT '0',
		points_misc_ldm BIGINT DEFAULT '0',
		points_misc_dl2 BIGINT DEFAULT '0',
		points_misc_ppd BIGINT DEFAULT '0',
		points_misc_vbblog BIGINT DEFAULT '0',
		points_misc_vbcms BIGINT DEFAULT '0',
		points_misc_custom BIGINT DEFAULT '0',
		points_misc_events BIGINT DEFAULT '0',
		points_shop decimal(12,2) UNSIGNED NOT NULL,
		points_level SMALLINT NOT NULL DEFAULT '0',
		dateline INT(10) DEFAULT '0',
		promoted SMALLINT NOT NULL DEFAULT '0',
		PRIMARY	KEY userid (userid)
	)"); 
	
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "xperience_level (
		usergroupid SMALLINT(6) DEFAULT '0',
		xperience_points FLOAT DEFAULT '0',
		xperience_level SMALLINT(6) DEFAULT '1',
		PRIMARY KEY	(xperience_level)
	)"); 
	
	CheckField("user", "xperience", "BIGINT DEFAULT '1'");
	CheckField("user", "xperience_done", "SMALLINT DEFAULT '0'");
	CheckField("user", "xperience_level", "SMALLINT(6) DEFAULT '1'");
	CheckField("user", "xperience_levelp", "SMALLINT(6) DEFAULT '1'");
	CheckField("user", "xperience_next_level", "BIGINT DEFAULT '0'");
	CheckField("user", "xperience_next_level_points", "BIGINT DEFAULT '1'");
	CheckField("user", "xperience_ppd", "FLOAT DEFAULT '0'");
	CheckField("user", "xperience_ppd7", "FLOAT DEFAULT '0'");
	CheckField("user", "xperience_ppd30", "FLOAT DEFAULT '0'");
	CheckField("user", "xperience_awardt", "MEDIUMTEXT");
	CheckField("user", "xperience_achievements", "MEDIUMTEXT");
	CheckField("user", "xperience_lastupdate", "INT(10) DEFAULT '1'");
	CheckField("user", "xperience_awardcount", "SMALLINT(6) DEFAULT '0'");
	CheckField("user", "xperience_promotioncount", "SMALLINT(6) DEFAULT '0'");
	CheckField("user", "xperience_achievementcount", "SMALLINT(6) DEFAULT '0'");
	DropField("user", "xperience_awards");
	
	
	CheckField("xperience_promotion", "comment", "VARCHAR(250)");
	CheckField("xperience_achievements", "canlose", " SMALLINT NOT NULL DEFAULT '0'");
	CheckField("xperience_achievements", "canpurchase", " SMALLINT NOT NULL DEFAULT '0'");
	CheckField("xperience_achievements", "identifier", " VARCHAR(50) DEFAULT ''");
	CheckField("xperience_achievements", "replaceid", " INT(10) DEFAULT '1'");
	CheckField("xperience_achievements", "issecret", " INT(10) DEFAULT '0'");
	
	CheckField("xperience_achievements_issues", "visible", " SMALLINT DEFAULT '1'");


	CheckField("xperience_awards", "awardlimit", "SMALLINT(6) DEFAULT '1'");
	CheckField("xperience_awards", "awardurl", "VARCHAR(250)");
	CheckField("xperience_awards", "awardbigurl", "VARCHAR(250)");
	CheckField("xperience_awards", "awardcategory", "SMALLINT(10) DEFAULT '1'");
	CheckField("xperience_awards", "manualassign", "VARCHAR(250)");
	
	CheckField("xperience_custompoints", "category", "VARCHAR(250) NOT NULL DEFAULT 'Manually assigned'");


	CheckStatsField('points_level', "SMALLINT DEFAULT '0'");
	CheckStatsField('points_shop', 'decimal(12,2) UNSIGNED NOT NULL');
	CheckStatsField('points_xperience');
	CheckStatsField('promoted');
	CheckStatsField('points_misc_custom');
	CheckStatsField('points_misc_vbblog');
	CheckStatsField('points_misc_vbcms');
	CheckStatsField('points_misc_ppd');
	CheckStatsField('points_misc_dl2');
	CheckStatsField('points_misc_ldm');
	CheckStatsField('points_misc');
	CheckStatsField('points_thread_tags');
	CheckStatsField('points_post_attachment');
	CheckStatsField('points_post_attachment_views');
	CheckStatsField('points_post_thanks_use');
	CheckStatsField('points_post_den');
	CheckStatsField('points_posts_sg');
	CheckStatsField('points_user_reputation_use');
	CheckStatsField('points_user_referrals');
	CheckStatsField('points_user_friends');
	CheckStatsField('points_user_activity');
	CheckStatsField('points_user_activity30');
	CheckStatsField('points_user_activity7');
	CheckStatsField('points_user_socialgroup');
	CheckStatsField('points_user_visitormessages');
	CheckStatsField('points_user_albumpictures');
	CheckStatsField('points_user_profile');
	CheckStatsField('points_threads_sg');
	CheckStatsField('points_thread_rate');
	CheckStatsField('points_posts_sg');
	CheckStatsField('points_post_avg');
	CheckStatsField('points_misc_events');
	CheckStatsField('points_user_law');


	if (vbexp_field_exists('xperience_stats', 'points_post_attachement')) $vbulletin->db->query_write("ALTER TABLE ". TABLE_PREFIX ."xperience_stats DROP points_post_attachement");
	if (vbexp_field_exists('xperience_stats', 'points_user_activity')) $vbulletin->db->query_write("ALTER TABLE ". TABLE_PREFIX ."xperience_stats MODIFY points_user_activity FLOAT DEFAULT '0'");
	if (vbexp_field_exists('xperience_stats', 'points_user_activity')) $vbulletin->db->query_write("ALTER TABLE ". TABLE_PREFIX ."xperience_stats MODIFY points_user_activity FLOAT DEFAULT '0'");
	if (vbexp_field_exists('xperience_stats', 'points_user_activity30')) $vbulletin->db->query_write("ALTER TABLE ". TABLE_PREFIX ."xperience_stats MODIFY points_user_activity30 FLOAT DEFAULT '0'");
	if (vbexp_field_exists('xperience_stats', 'points_shop')) $vbulletin->db->query_write("ALTER TABLE ". TABLE_PREFIX ."xperience_stats MODIFY points_shop decimal(12,2) UNSIGNED NOT NULL");
	if (vbexp_field_exists('xperience_awards', 'manualassign')) $vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "xperience_awards MODIFY manualassign VARCHAR(250) NOT NULL DEFAULT '0'");
	if (vbexp_field_exists('user', 'xperience_points')) $vbulletin->db->query_write("ALTER TABLE ". TABLE_PREFIX ."user DROP xperience_points");
	if (vbexp_field_exists('user', 'xperience_shopitems')) $vbulletin->db->query_write("ALTER TABLE ". TABLE_PREFIX ."user DROP xperience_shopitems");
	if (vbexp_field_exists('xperience_achievements', 'issecret')) $vbulletin->db->query_write("ALTER TABLE ". TABLE_PREFIX ."xperience_achievements MODIFY issecret INT(10) DEFAULT '0'");

	$istable = $vbulletin->db->query_read("SELECT awardid FROM " . TABLE_PREFIX . "xperience_awards");
	if ($vbulletin->db->num_rows($istable) == 0) 
	{
		CreateDefaultAwards();
	}
	
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_awards SET awardcategory=1 WHERE awardcategory=0");

	$istableq = $vbulletin->db->query_read("SELECT achievementid, imagesmall, imagebig FROM " . TABLE_PREFIX . "xperience_achievements");
	if ($vbulletin->db->num_rows($istableq) == 0) 
	{
		CreateDefaultAchievements();
	}



	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_achievements SET imagesmall=REPLACE(imagesmall, '".$vbulletin->options['bburl']."/xperience/icons/', '')");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_achievements SET imagebig=REPLACE(imagebig, '".$vbulletin->options['bburl']."/xperience/icons/', '')");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_awards SET awardurl=REPLACE(awardurl, '".$vbulletin->options['bburl']."/xperience/icons/', '')");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "xperience_awards SET awardbigurl=REPLACE(awardbigurl, '".$vbulletin->options['bburl']."/xperience/icons/', '')");


	$vbulletin->db->hide_errors();
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "xperience_stats ADD PRIMARY KEY (userid)");
	$vbulletin->db->show_errors();
	
}

function CheckStatsField($column, $definition="BIGINT DEFAULT '0'")
{
	//Shortcut
	CheckField("xperience_stats", $column, $definition);
}

function CheckField($table, $column, $definition)
{
	global $vbulletin;
	if (!vbexp_field_exists($table, $column))
	{
		$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . $table." ADD ".$column." ".$definition);	
	}
}

function DropField($table, $column)
{
	global $vbulletin;
	if (vbexp_field_exists($table, $column))
	{
		$vbulletin->db->hide_errors();
		$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . $table." DROP ".$column);	
		$vbulletin->db->show_errors();
	}
}

function CreateDefaultAchievements()
{
	$categoryid = AddCategory("Social Network", "Achievements around your Social Network", 1, "xperience_achievements_categories");
	$lastid = AddAchievement($categoryid, 0, 'Social', 'You have at least three confirmed Friends', 'people_dude4_16.png', 'people_dude4_32.png', 'points_user_friends', 9, 1);
	$lastid = AddAchievement($categoryid, 0, 'Your first Group', 'You have created your first group', 'people_users2_16.png', 'people_users2_32.png', 'points_user_socialgroup', 1, 1);
	$lastid = AddAchievement($categoryid, 0, 'Recommendation Second Class', '1 Referral', 'recommendation_second_16.png', 'recommendation_second_32.png', 'points_user_referrals', 25, 1);
	$lastid = AddAchievement($categoryid, $lastid, 'Recommendation First Class', '5 Referrals', 'recommendation_first_16.png', 'recommendation_first_32.png', 'points_user_referrals', 125, 1);

	$categoryid = AddCategory("User", "Related to user", 2, "xperience_achievements_categories");
	$lastid = AddAchievement($categoryid, 0, '7 days registered', 'Survived the first week.', 'calendar_newred_7_16.png', 'calendar_newred_7_32.png', 'points_user_online', 14, 1);
	$lastid = AddAchievement($categoryid, $lastid, '31 days registered', 'Survived the first month.', 'calendar_newred_31_16.png', 'calendar_newred_31_32.png', 'points_user_online', 60, 1);
	$lastid = AddAchievement($categoryid, $lastid, '3 months registered', '3 months here and still alive.', 'calendar_newred_3_16.png', 'calendar_newred_3_32.png', 'points_user_online', 180, 1);
	$lastid = AddAchievement($categoryid, $lastid, '1 year registered', 'Survived the first 365 days!', 'calendar_newred_16.png', 'calendar_newred_32.png', 'points_user_online', 719, 1);
	$lastid = AddAchievement($categoryid, $lastid, 'Veteran', 'Very long registered, at least 3 years.', 'people_pacific_islanders_16.png', 'people_pacific_islanders_32.png', 'points_user_online', 2160, 1);
	$lastid = AddAchievement($categoryid, 0, 'Overdrive', 'Hyperactive! High! Activity!', 'atom_16.png', 'atom_32.png', 'points_user_activity', 100, 0);
	
	$categoryid = AddCategory("Community", "Related to activities around the community", 3, "xperience_achievements_categories");
	$lastid = AddAchievement($categoryid, 0, 'Created Album pictures', 'Uploaded pictures.', 'pictures_16.png', 'pictures_32.png', 'points_user_albumpictures', 1, 1);
	$lastid = AddAchievement($categoryid, 0, 'Created Blog entry', 'For the first Blog entry.', 'news_16.png', 'news_32.png', 'points_misc_vbblog', 1, 1);
	$lastid = AddAchievement($categoryid, 0, 'Tagger Second Class', 'I can tag!', 'signal_flag_blue_16.png', 'signal_flag_blue_32.png', 'points_thread_tags', 1, 1);
	$lastid = AddAchievement($categoryid, $lastid, 'Tagger First Class', 'I can even more tag!', 'signal_flag_yellow_16.png', 'signal_flag_yellow_32.png', 'points_thread_tags', 25, 1);
	$lastid = AddAchievement($categoryid, 0, 'Small Donator', 'Donated money', 'money2_16.png', 'money2_32.png', 'points_misc_ppd', 5, 1, 0);
	$lastid = AddAchievement($categoryid, $lastid, 'Big Donator', 'Donated a considerable sum of money', 'money_16.png', 'money_32.png', 'points_misc_ppd', 50, 1, 0);


	$categoryid = AddCategory("Project Tools", "About Project Bugs and Improvements", 0, "xperience_achievements_categories");
	$lastid = AddAchievement($categoryid, 0, 'Bug Hunter Second Class', 'Did found a bug', 'bug_16.png', 'bug_32.png', 'points_misc_pj_bug', 9, 1, 0);
	$lastid = AddAchievement($categoryid, $lastid, 'Bug Hunter First Class', 'Bug finding is my passion', 'holmes_16.png', 'holmes_32.png', 'points_misc_pj_bug', 99, 1, 0);
	$lastid = AddAchievement($categoryid, 0, 'Innovator Second Class', 'I invented something useful', 'brain_16.png', 'brain_32.png', 'points_misc_pj_feature', 2, 1, 0);
	$lastid = AddAchievement($categoryid, $lastid, 'Innovator First Class', 'I invented the wheel!', 'people_genius_16.png', 'people_genius_32.png', 'points_misc_pj_feature', 29, 1, 0);


	$categoryid = AddCategory("Experience", "Achievements", 4, "xperience_achievements_categories");
	$lastid = AddAchievement($categoryid, 0, '100 Experience Points', 'Achieved as soon as you reach 100 Experience Points', 'spheres_clouds_16.png', 'spheres_clouds_32.png', 'points_xperience', 100, 1);
	$lastid = AddAchievement($categoryid, $lastid, '250 Experience Points', 'Achieved as soon as you reach 250 Experience Points', 'spheres_amber_16.png', 'spheres_amber_32.png', 'points_xperience', 250, 1);
	$lastid = AddAchievement($categoryid, $lastid, '500 Experience Points', 'Achieved as soon as you reach 500 Experience Points', 'spheres_grape_16.png', 'spheres_grape_32.png', 'points_xperience', 500, 1);
	$lastid = AddAchievement($categoryid, $lastid, '1000 Experience Points', 'Achieved as soon as you reach 1000 Experience Points', 'spheres_green_16.png', 'spheres_green_32.png', 'points_xperience', 1000, 1);
	$lastid = AddAchievement($categoryid, $lastid, '5000 Experience Points', 'Achieved as soon as you reach 5000 Experience Points', 'spheres_red_16.png', 'spheres_red_32.png', 'points_xperience', 5000, 1);
	$lastid = AddAchievement($categoryid, $lastid, '10000 Experience Points', 'Achieved as soon as you reach 10000 Experience Points', 'spheres_graphite_16.png', 'spheres_graphite_32.png', 'points_xperience', 10000, 1);
	$lastid = AddAchievement($categoryid, $lastid, '25000 Experience Points', 'Achieved as soon as you reach 25000 Experience Points', 'spheres_magenta_16.png', 'spheres_magenta_32.png', 'points_xperience', 25000, 1);
	$lastid = AddAchievement($categoryid, $lastid, '50000 Experience Points', 'Achieved as soon as you reach 50000 Experience Points', 'spheres_iridescent_16.png', 'spheres_iridescent_32.png', 'points_xperience', 50000, 1);


	$categoryid = AddCategory("Market", "Shopping Deluxe", iif($vbulletin->options['market_active'] == 1, 6, 0), "xperience_achievements_categories");
	$lastid = AddAchievement($categoryid, 0, 'Occasional Consumer', 'Purchased occasionally some stuff', 'shoppingbasket_full_16.png', 'shoppingbasket_full_32.png', 'points_shop', 50, 1, iif($vbulletin->options['market_active'] == 1, 1, 0));
	$lastid = AddAchievement($categoryid, $lastid, 'Shopper', 'Shopper in the Market', 'shopping_basket_16.png', 'shopping_basket_32.png', 'points_shop', 750, 1, iif($vbulletin->options['market_active'] == 1, 1, 0));
	$lastid = AddAchievement($categoryid, $lastid, 'Power Shopper', 'Shopping deluxe', 'shoppingcart_full_16.png', 'shoppingcart_full_32.png', 'points_shop', 2000, 1, iif($vbulletin->options['market_active'] == 1, 1, 0));
	$lastid = AddAchievement($categoryid, $lastid, 'Merchant', 'Has great plans', 'shopping_container_16.png', 'shopping_container_32.png', 'points_shop', 6000, 1, iif($vbulletin->options['market_active'] == 1, 1, 0));


}

function AddCategory($categorytitle, $categorydesc, $categoryorder, $table)
{
	global $vbulletin;
	
	$istable = $vbulletin->db->query_read("SELECT categoryid FROM " . TABLE_PREFIX . $table . " WHERE categorytitle='".$categorytitle."'");
	if ($vbulletin->db->num_rows($istable) > 0) 
	{
		$category = $vbulletin->db->fetch_array($istable);
		
		return $category['categoryid'];
	}
	
	
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . $table."
		(categorytitle, categorydesc, categoryorder) VALUES ('".$categorytitle."', '".$categorydesc."', ".$categoryorder.")");

	return $vbulletin->db->insert_id();
}



function AddAchievement($categoryid, $replaceid, $title, $description, $imagesmall, $imagebig, $field, $value, $compare, $sortorder = 1)
{
	global $vbulletin;
	
	
	$identifier = addslashes($field.$value.$compare.$title);
	if (strlen($identifier)>50)
	{
		$identifier = substr($identifier, 0, 50);
	}
	
	$istable = $vbulletin->db->query_read("SELECT achievementid FROM " . TABLE_PREFIX . "xperience_achievements WHERE identifier='".$identifier."'");
	if ($vbulletin->db->num_rows($istable) > 0) 
	{
		$achievement = $vbulletin->db->fetch_array($istable);
		
		return $achievement['achievementid'];
	}
	
	
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_achievements 
	(sortorder, replaceid, identifier, categoryid, title, description,  imagesmall, imagebig) 
	VALUES 
	(".$sortorder.", ".$replaceid.", '".$identifier."', ".$categoryid.", '".$title."', '".$description."', '".$imagesmall."', '".$imagebig."')
	");
	
	$achievementid = $vbulletin->db->insert_id();
	
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_achievements_fields 
	(achievementid, field, value, compare) 
	VALUES 
	(".$achievementid.", '".$field."', '".$value."', ".$compare.")
	");
	
	return $achievementid;
}



function CreateDefaultAwards()
{
	global $vbulletin;
	
	$categoryid = AddCategory("Postings", "Awards", 1, "xperience_award_categories");
	AddAward($categoryid, 'Posting Award', 'Zealousness posting and tagging', '0', 'people_robot_16.png', 'people_robot_32.png', 'points_threads+points_thread_votes+points_thread_replycount+points_thread_views+points_thread_stickies+points_thread_tags+points_posts+points_post_attachment');
	AddAward($categoryid, 'Frequent Poster', 'User likes to post', '0', 'spam_16.png', 'spam_32.png', 'points_post_avg');
	AddAward($categoryid, 'Discussion Ender', 'I killed most discussions!', '0', 'discussion_ender_nail_16.png', 'discussion_ender_nail_32.png', 'points_post_den');
	AddAward($categoryid, 'Master Tagger', '<tag>Me</tag>', '0', 'signal_flag_red_16.png', 'signal_flag_red_32.png', 'points_thread_tags');

	$categoryid = AddCategory("Social Network", "Awards around your Social Network", 2, "xperience_award_categories");
	AddAward($categoryid, 'Community Award', 'Social engaged', '0', 'flower_blue_16.png', 'flower_blue_32.png',  'points_user_visitormessages+points_user_socialgroup+points_user_friends+points_user_reputation');
	AddAward($categoryid, 'Most Popular', 'Favourite Person', '0', 'favorites_16.png', 'favorites_32.png', 'points_user_reputation');
	AddAward($categoryid, 'User with most referrers', 'User has the most referrers', '0', 'news_16.png', 'news_32.png', 'points_user_referrals');
			
	$categoryid = AddCategory("Miscellaneous", "Awards", 3, "xperience_award_categories");
	AddAward($categoryid, 'Calendar Award', 'Engaged with events', '0', 'calendar_red_empty_16.png', 'calendar_red_empty_32.png', 'points_misc_events');
	AddAward($categoryid, 'Activity Award', 'Very Active', '0', 'symbol_configuration_1_16.png', 'symbol_configuration_1_32.png', 'points_user_activity');
	AddAward($categoryid, 'Downloads', 'Downloads for all!', '0', 'harddisk_16.png', 'harddisk_32.png', 'points_post_attachment+points_misc_ldm+points_misc_dl2');
	AddAward($categoryid, 'Arm of Law', 'Most given infractions!', '0', 'people_police_us_16.png', 'people_police_us_32.png', 'points_user_law');
	AddAward($categoryid, 'King of Publishing', 'Most published content with vBCMS and vBBlog!', '0', 'newspaper_16.png', 'newspaper_32.png', 'points_misc_vbblog+points_misc_vbcms');
}

function AddAward($awardcategory, $awardtitle, $awarddesc, $awardlimit, $awardurl, $awardbigurl, $awardfields)
{
	global $vbulletin;
	
	$identifier = addslashes($awardfields.$awardtitle);
	
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "xperience_awards 
	(awardcategory, awardname, awardlimit, awardurl, awardbigurl, awardtitle, awarddesc, awardfields) 
	VALUES 
	(".$awardcategory.", '".$identifier."', ".$awardlimit.", '".$awardurl."', '".$awardbigurl."', '".$awardtitle."', '".$awarddesc."', '".$awardfields."')
	");

}


function vbexp_field_exists($table, $field)
{
	global $vbulletin;
	return ($vbulletin->db->num_rows($vbulletin->db->query_read("SHOW COLUMNS FROM `" . TABLE_PREFIX .$table."` LIKE '".$field."'")) > 0);
}


?>