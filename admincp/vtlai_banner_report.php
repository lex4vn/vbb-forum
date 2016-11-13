<?php
// ++ ============================================================= ++
//                vt.Lai VBB Ads Management 4.1
//				 Code by: Vũ Thanh Lai (vt.lai)
//					Shared at: sinhvienit.net
// ++ ============================================================= ++
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
 
$phrasegroups = array('style');
$specialtemplates = array('products');
 
require_once('./global.php');

$vbulletin->input->clean_array_gpc('p', array(
			'loc_name'	  		=> TYPE_STR,
			'loc_width'		  	=> TYPE_INT,
			'loc_height'		=> TYPE_INT,
			'loc_default_code'	=> TYPE_STR,
        ));
$vbulletin->input->clean_array_gpc('p', array(
			'banner_loc_id'	  		=> TYPE_INT,
			'banner_name'	  		=> TYPE_STR,
			'banner_src'	  		=> TYPE_STR,
			'banner_link'	  		=> TYPE_STR,
			'banner_use_code'		=> TYPE_INT,
			'banner_code'	  		=> TYPE_STR,
			'banner_start_time'		=> TYPE_ARRAY,
			'banner_stop_time'		=> TYPE_ARRAY,
        ));
include_once CWD.'/includes/vt.lai-ads_class.adsloc.php';
include_once CWD.'/includes/vt.lai-ads_class.banner.php';
// #######################################
// #### START MAIN SCRIPT   #####################
// #######################################
$do = $_REQUEST['do'];
$time=TIMENOW;
		
if ($do=='report' && $_POST['banner_id']) 
{
	$banner_id=intval($_POST['banner_id']);

	$banner=new VTLAI_BANNER($_POST['banner_id']);
	$banner->loadinfo();
	/*
	print_table_start();
	print_table_header($banner->banner_name, 1);
	print_cells_row(array('Impression'), 1, -1);
	print_cells_row(array("<img border='0' src='vtlai_ads_stat_img.php?banner_id=$banner_id&startday={$_POST['start_time']['day']}&startmonth={$_POST['start_time']['month']}&startyear={$_POST['start_time']['year']}&stopday={$_POST['stop_time']['day']}&stopmonth={$_POST['stop_time']['month']}&stopyear={$_POST['stop_time']['year']}&stat_type=impression'>"));
	print_cells_row(array('Click'), 1, -1);
	print_cells_row(array("<img border='0' src='vtlai_ads_stat_img.php?banner_id=$banner_id&startday={$_POST['start_time']['day']}&startmonth={$_POST['start_time']['month']}&startyear={$_POST['start_time']['year']}&stopday={$_POST['stop_time']['day']}&stopmonth={$_POST['stop_time']['month']}&stopyear={$_POST['stop_time']['year']}&stat_type=click'>"));

	print_table_footer();
	*/
	$start['day']=intval($_POST['start_time']['day']);
	$start['month']=intval($_POST['start_time']['month']);
	$start['year']=intval($_POST['start_time']['year']);
	
	$stop['day']=intval($_POST['stop_time']['day']);
	$stop['month']=intval($_POST['stop_time']['month']);
	$stop['year']=intval($_POST['stop_time']['year']);

	$startday="{$start['year']}-{$start['month']}-{$start['day']}";
	$stopday="{$stop['year']}-{$stop['month']}-{$stop['day']}";

	$rs=$vbulletin->db->query_read("SELECT SUM(click) AS clicks,SUM(view) AS impression FROM ".TABLE_PREFIX."vtlai_ads_stat WHERE banner_id='$banner_id' AND logdate>='$startday' AND logdate<='$stopday'");
	$sum=mysql_fetch_assoc($rs);
	mysql_free_result($rs);
	$sum['impression']=number_format($sum['impression']);
	$sum['clicks']=number_format($sum['clicks']);
	
	echo '<CENTER style="font-size:14pt;"><B>'.$vbulletin->options['vtlai_ads_management_reporttitle'].'</B><BR />
			BANNER '.mb_convert_case($banner->banner_name,MB_CASE_UPPER,'UTF-8').'</CENTER>';
	echo '<br><br><br><br>';
	echo "<b>Impression: {$sum['impression']}<br>";
	echo "<img border='0' src='vtlai_ads_stat_img.php?banner_id=$banner_id&startday={$_POST['start_time']['day']}&startmonth={$_POST['start_time']['month']}&startyear={$_POST['start_time']['year']}&stopday={$_POST['stop_time']['day']}&stopmonth={$_POST['stop_time']['month']}&stopyear={$_POST['stop_time']['year']}&stat_type=impression'>";
	echo '<br><br>';
	echo "<b>Click: {$sum['clicks']}<br>";
	echo "<img border='0' src='vtlai_ads_stat_img.php?banner_id=$banner_id&startday={$_POST['start_time']['day']}&startmonth={$_POST['start_time']['month']}&startyear={$_POST['start_time']['year']}&stopday={$_POST['stop_time']['day']}&stopmonth={$_POST['stop_time']['month']}&stopyear={$_POST['stop_time']['year']}&stat_type=click'>";
	echo '<br><br><br><Br><br><center style="font-size:14pt;">'.$vbphrase['vtlai_vbb_ads_detail'].'</center><br>';
	echo '<table border="1" cellpadding="3" width="99%" style="border-collapse:collapse">';
	echo '<tr style="font-weight:bold;"><td>Date</td><td>Impression</td><td>Click</td></tr>';
	$rs=$vbulletin->db->query_read("SELECT *,DATE_FORMAT(logdate,'%d-%c-%Y') AS `ldate` FROM ".TABLE_PREFIX."vtlai_ads_stat WHERE banner_id='$banner_id' AND logdate>='$startday' AND logdate<='$stopday'");
	while($vtlai=mysql_fetch_assoc($rs))
	{
		echo "<tr><td>{$vtlai['ldate']}</td><td>{$vtlai['view']}</td><td>{$vtlai['click']}</td></tr>";
	}
	echo "<tr style='font-weight:bold;'><td>{$vbphrase['vtlai_vbb_ads_total']}:</td><td>{$sum['impression']}</td><td>{$sum['clicks']}</td></tr>";
	mysql_free_result($rs);
	echo '</table>';
}