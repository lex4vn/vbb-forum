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
if(!in_array($vbulletin->userinfo['usergroupid'],explode(',',$vbulletin->options['vtlai_ads_management_manage_group'])))
	print_cp_message($vbphrase['vtlai_vbb_ads_nopermission']);

$vbulletin->input->clean_array_gpc('p', array(
			'loc_name'	  		=> TYPE_STR,
			'loc_painful'  		=> TYPE_UINT,
			'loc_width'		  	=> TYPE_INT,
			'loc_height'		=> TYPE_INT,
			'loc_default_code'	=> TYPE_STR,
        ));
$vbulletin->input->clean_array_gpc('p', array(
			'banner_loc_id'	  		=> TYPE_INT,
			'banner_name'	  		=> TYPE_STR,
			'banner_painful'  		=> TYPE_UINT,
			'banner_src'	  		=> TYPE_STR,
			'banner_link'	  		=> TYPE_STR,
			'banner_use_code'		=> TYPE_INT,
			'banner_code'	  		=> TYPE_STR,
			'banner_start_time'		=> TYPE_ARRAY,
			'banner_stop_time'		=> TYPE_ARRAY,
        ));
$vbulletin->input->clean_array_gpc('p', array(
			'banner_id'	  	=> TYPE_INT,
			'report_type'	  	=> TYPE_STR
        ));
		
include_once CWD.'/includes/vt.lai-ads_class.adsloc.php';
include_once CWD.'/includes/vt.lai-ads_class.banner.php';
// #######################################
// #### START MAIN SCRIPT   #####################
// #######################################
$do = $_REQUEST['do'];
$time=TIMENOW;

print_cp_header("vt.Lai Ads Location Management");

?>

<?php 
////////////////////////// ADS LOCATION  ////////////////////////////////
// #### DO ADD NEW ADS LOC  ##################
// ======================================================================
if ($_REQUEST['do'] == 'loc' && $_POST['type']=='add') 
{

	$loc=new VTLAI_ADSLOC();
	$loc->addnew($vbulletin->GPC['loc_name'],$vbulletin->GPC['loc_width'],$vbulletin->GPC['loc_height'],$vbulletin->GPC['loc_default_code'],$vbulletin->GPC['loc_painful']);
	if($loc->loc_id)				
		print_cp_message($vbphrase['vtlai_vbb_ads_add_loc_successful'], 'vtlai_ads_management.php?do=loc', 0);
	else
		print_cp_message($vbphrase['vtlai_vbb_ads_add_loc_failed'], 'vtlai_ads_management.php?do=loc', 2);
	exit();
		
}	
// #### DO EDIT ADS LOC  ##################
// ======================================================================
if ($_REQUEST['do'] == 'loc' && $_POST['type']=='edit' && $_POST['loc_id']) 
{
	
	$loc=new VTLAI_ADSLOC($_POST['loc_id']);
	$loc->update($vbulletin->GPC['loc_name'],$vbulletin->GPC['loc_width'],$vbulletin->GPC['loc_height'],$vbulletin->GPC['loc_default_code'],$vbulletin->GPC['loc_painful']);
	if($loc->loc_id)				
		print_cp_message($vbphrase['vtlai_vbb_ads_update_loc_successful'], 'vtlai_ads_management.php?do=loc', 0);
	else
		print_cp_message($vbphrase['vtlai_vbb_ads_update_loc_failed'], 'vtlai_ads_management.php?do=loc', 2);
	exit();
}

// #### DO DELETE ADS LOC ####################
// ======================================================================
if ($_REQUEST['do'] == 'loc' && $_GET['type']=='del' && $_GET['loc_id']) 
{
	$loc=new VTLAI_ADSLOC($_GET['loc_id']);
	if($loc->delete())				
		print_cp_message($vbphrase['vtlai_vbb_ads_delete_loc_successful'], 'vtlai_ads_management.php?do=loc', 0);
	else
		print_cp_message($vbphrase['vtlai_vbb_ads_delete_loc_failed'], 'vtlai_ads_management.php?do=loc', 2);
	exit();
}

// #### EDIT FORM ####################
// ======================================================================
if ($_REQUEST['do'] == 'loc' && $_GET['type']=='edit' && $_GET['loc_id']) 
{
	$loc=new VTLAI_ADSLOC($_GET['loc_id']);
	$loc->loadinfo();
	print_form_header('vtlai_ads_management', 'loc', true);
	print_table_header($vbphrase['vtlai_vbb_ads_edit_loc'], 2);
	print_input_row($vbphrase['vtlai_vbb_ads_locname'], 'loc_name', $loc->loc_name, 30, 50);
	print_yes_no_row($vbphrase['vtlai_vbb_ads_ispainful'], 'loc_painful', $loc->loc_painful);
	print_input_row($vbphrase['vtlai_vbb_ads_width'], 'loc_width', $loc->loc_width, 30, 50);
	print_input_row($vbphrase['vtlai_vbb_ads_height'], 'loc_height', $loc->loc_height, 30, 50);
	print_textarea_row($vbphrase['vtlai_vbb_ads_defaultcode'], 'loc_default_code', $loc->loc_default_code, 10, 52);
	echo '<input type="hidden" name="type" value="edit">';
	echo '<input type="hidden" name="loc_id" value="'.$_GET['loc_id'].'">';
	print_submit_row();
	print_table_footer(2, '', '', 0);
	exit();
}



// ##########################################
// #### ADS LOCATION LIST & ADD NEW FORM#####
// ##########################################
if ($do=='loc') 
{
	
	// #### ADD NEW LOCATION FORM ####################
	// ===============================================
	print_form_header('vtlai_ads_management', 'loc', true);
	print_table_header($vbphrase['vtlai_vbb_ads_add_loc'], 2);
	print_input_row($vbphrase['vtlai_vbb_ads_locname'], 'loc_name', $vbulletin->GPC['loc_name'], 30, 50);
	print_yes_no_row($vbphrase['vtlai_vbb_ads_ispainful'], 'loc_painful', $vbulletin->GPC['loc_painful']);
	print_input_row($vbphrase['vtlai_vbb_ads_width'], 'loc_width', $vbulletin->GPC['loc_width'], 30, 50);
	print_input_row($vbphrase['vtlai_vbb_ads_height'], 'loc_height', $vbulletin->GPC['loc_height'], 30, 50);
	print_textarea_row($vbphrase['vtlai_vbb_ads_defaultcode'], 'loc_default_code', htmlspecialchars($vbulletin->GPC['loc_default_code']), 10, 52);
	echo '<input type="hidden" name="type" value="add">';
	print_submit_row();
	print_table_footer(2, '', '', 0);
	
	// #### ADS LOCATION LIST ########################
	// ===============================================
	$rs = $vbulletin->db->query_read("SELECT * FROM `" . TABLE_PREFIX . "vtlai_ads_loc`");
	if (mysql_num_rows($rs) > 0) 
	{					
		print_table_start();
		print_table_header($vbphrase['vtlai_vbb_ads_loc'], 9);
		
		print_cells_row(array('ID',$vbphrase['vtlai_vbb_ads_locname'],$vbphrase['vtlai_vbb_ads_width'],$vbphrase['vtlai_vbb_ads_height'],$vbphrase['vtlai_vbb_ads_sdefaultcode'],$vbphrase['vtlai_vbb_ads_sispainful'],$vbphrase['vtlai_vbb_ads_varname'],$vbphrase['vtlai_vbb_ads_action']), 1, -1);

		while ($info = $vbulletin->db->fetch_array($rs))
		{
			$cell[] = $info['loc_id'];
			$cell[] = $info['loc_name'];
			$cell[] = $info['loc_width'];
			$cell[] = $info['loc_height'];
			$cell[] = '<pre style="height:80px;width:100px;overflow:auto;text-align:left;border: #CCC 1px solid">'.htmlspecialchars($info['loc_default_code']).'</pre>';
			$cell[] = ($info['loc_painful']?'<font color="red">Yes</font>':'No');
			$cell[] = '{vtlai_ads_code_'.$info['loc_id'].'}';
			$cell[] = '<a href="vtlai_ads_management.php?do=loc&type=edit&loc_id='.$info['loc_id'].'">'.$vbphrase['vtlai_vbb_ads_edit'].'</a> | <a href="vtlai_ads_management.php?do=loc&type=del&loc_id='.$info['loc_id'].'" onclick="return confirm(\''.$vbphrase['vtlai_vbb_ads_areyousure'].'\');">'.$vbphrase['vtlai_vbb_ads_del'].'</a>';
			

			print_cells_row($cell);
			unset($cell);
		}
				  
		print_table_footer();
	}


}


//#########################################################################
//########################## BANNER #######################################
//#########################################################################

// #### DO EDIT BANNER SAVE ####################
// ======================================================================
if ($do=='banner' && $_POST['type']=='edit' && $_POST['banner_id']) 
{
	$vbulletin->input->clean_array_gpc('f', array(
		'bannerfile'        => TYPE_FILE,
	));
	
	//--If have file upload
	if ($vbulletin->GPC['bannerfile']['error']==0 && file_exists($vbulletin->GPC['bannerfile']['tmp_name']))
	{
		$fname=rand(1,9999).'-'.basename($vbulletin->GPC['bannerfile']['name']);
		if(@move_uploaded_file($vbulletin->GPC['bannerfile']['tmp_name'],$vbulletin->options['vtlai_ads_management_uploadpath'].'/'.$fname))
			$vbulletin->GPC['banner_src']=$vbulletin->options['vtlai_ads_management_exportpath'].'/'.$fname;
		else
			print_cp_message($vbphrase['vtlai_vbb_ads_cantmoveuploaded'].'<br>'.$vbulletin->options['vtlai_ads_management_uploadpath']);
	}
	
	$banner_start_time=mktime($vbulletin->GPC['banner_start_time']['hour'],$vbulletin->GPC['banner_start_time']['minute'],0,$vbulletin->GPC['banner_start_time']['month'],$vbulletin->GPC['banner_start_time']['day'],$vbulletin->GPC['banner_start_time']['year']);
	$banner_stop_time=mktime($vbulletin->GPC['banner_stop_time']['hour'],$vbulletin->GPC['banner_stop_time']['minute'],0,$vbulletin->GPC['banner_stop_time']['month'],$vbulletin->GPC['banner_stop_time']['day'],$vbulletin->GPC['banner_stop_time']['year']);

	$banner=new VTLAI_BANNER($_POST['banner_id']);
	$banner->update($vbulletin->GPC['banner_name'],$vbulletin->GPC['banner_loc_id'],$vbulletin->GPC['banner_code'],$vbulletin->GPC['banner_src'],$vbulletin->GPC['banner_link'],$banner_start_time,$banner_stop_time,$vbulletin->GPC['banner_use_code']);
	print_cp_message($vbphrase['vtlai_vbb_ads_update_banner_successful'], 'vtlai_ads_management.php?do=banner', 0);
	exit();
}


// #### EDIT BANNER FORM ####################
// ======================================================================
if ($do=='banner' && $_GET['type']=='edit' && $_GET['banner_id']) 
{

	//----GET ALL LOCATION
	$array=array();
	$rs = $vbulletin->db->query_read("SELECT loc_id,loc_name FROM `" . TABLE_PREFIX . "vtlai_ads_loc`");
	if (mysql_num_rows($rs) > 0) 
	{	
		while($loc=mysql_fetch_assoc($rs))
		{
			$array[$loc['loc_id']]=$loc['loc_name'];
		}
	}
	mysql_free_result($rs);
	

	
	//---Load Banner Info
	$banner=new VTLAI_BANNER($_GET['banner_id']);
	$banner->loadinfo();
	
	print_form_header('vtlai_ads_management', 'banner', true);
	print_table_header('Banner: '.$banner->banner_name, 2);
	print_select_row($vbphrase['vtlai_vbb_ads_banner_loc'], 'banner_loc_id', $array, $banner->banner_loc_id, true);
	print_input_row($vbphrase['vtlai_vbb_ads_banner_name'], 'banner_name', $banner->banner_name, 30, 50);
	print_input_row($vbphrase['vtlai_vbb_ads_banner_src'], 'banner_src', $banner->banner_src, 30, 50);
	print_upload_row($vbphrase['vtlai_vbb_ads_upload_banner'], 'bannerfile', 999999999);
	print_input_row($vbphrase['vtlai_vbb_ads_banner_link_to'], 'banner_link', $banner->banner_link, 30, 50);
	print_time_row($vbphrase['vtlai_vbb_ads_banner_starttime'], 'banner_start_time',$banner->banner_start_time);
	print_time_row($vbphrase['vtlai_vbb_ads_banner_stoptime'], 'banner_stop_time',$banner->banner_stop_time);
	print_yes_no_row($vbphrase['vtlai_vbb_ads_banner_custom'], 'banner_use_code', $banner->banner_use_code);
	print_textarea_row($vbphrase['vtlai_vbb_ads_banner_customcode'], 'banner_code', $banner->banner_code, 10, 52);
	echo '<input type="hidden" name="type" value="edit">';
	echo '<input type="hidden" name="banner_id" value="'.$_GET['banner_id'].'">';
	print_submit_row();
	print_table_footer(2, '', '', 0);
	exit();
}

// ################### DO ADD NEW BANNER ################################
// ======================================================================
if ($do=='banner' && $_POST['type']=='add') 
{
	$vbulletin->input->clean_array_gpc('f', array(
		'bannerfile'        => TYPE_FILE,
	));
	
	//--If have file upload
	if ($vbulletin->GPC['bannerfile']['error']==0 && file_exists($vbulletin->GPC['bannerfile']['tmp_name']))
	{
		$fname=rand(1,9999).'-'.basename($vbulletin->GPC['bannerfile']['name']);
		if(@move_uploaded_file($vbulletin->GPC['bannerfile']['tmp_name'],$vbulletin->options['vtlai_ads_management_uploadpath'].'/'.$fname))
			$vbulletin->GPC['banner_src']=$vbulletin->options['vtlai_ads_management_exportpath'].'/'.$fname;
		else
			print_cp_message($vbphrase['vtlai_vbb_ads_cantmoveuploaded'].'<br>'.$vbulletin->options['vtlai_ads_management_uploadpath']);
	}
	
	$banner_start_time=mktime($vbulletin->GPC['banner_start_time']['hour'],$vbulletin->GPC['banner_start_time']['minute'],0,$vbulletin->GPC['banner_start_time']['month'],$vbulletin->GPC['banner_start_time']['day'],$vbulletin->GPC['banner_start_time']['year']);
	$banner_stop_time=mktime($vbulletin->GPC['banner_stop_time']['hour'],$vbulletin->GPC['banner_stop_time']['minute'],0,$vbulletin->GPC['banner_stop_time']['month'],$vbulletin->GPC['banner_stop_time']['day'],$vbulletin->GPC['banner_stop_time']['year']);

	if(!$vbulletin->GPC['banner_name'])
		print_cp_message('Bạn chưa nhập tên banner');
	
	$banner=new VTLAI_BANNER();
	$banner->addnew($vbulletin->GPC['banner_name'],$vbulletin->GPC['banner_loc_id'],$vbulletin->GPC['banner_code'],$vbulletin->GPC['banner_src'],$vbulletin->GPC['banner_link'],$banner_start_time,$banner_stop_time,$vbulletin->GPC['banner_use_code']);
	if($banner->banner_id)				
	{
		print_cp_message($vbphrase['vtlai_vbb_ads_banner_successful'], 'vtlai_ads_management.php?do=banner', 0);
		exit();
	}
	else
	{
		print_cp_message($vbphrase['vtlai_vbb_ads_banner_failed'], 'vtlai_ads_management.php?do=banner', 2);
		exit();
	}
	
}
// #################### DO DELETE BANNER ################################
// ======================================================================
if ($do=='banner' && $_GET['type']=='del' && $_GET['banner_id']) 
{
	$banner=new VTLAI_BANNER($_GET['banner_id']);

	if($banner->delete())
		print_cp_message($vbphrase['vtlai_vbb_ads_banner_delsuccessful'], 'vtlai_ads_management.php?do=banner', 0);
	else
		print_cp_message($vbphrase['vtlai_vbb_ads_banner_delfailed'], 'vtlai_ads_management.php?do=banner', 2);
	exit();
}

// #### ADD NEW BANNER FORM ####################
// ======================================================================
if ($do=='banner') 
{
	//----GET ALL LOCATION
	$array=array();
	$rs = $vbulletin->db->query_read("SELECT loc_id,loc_name FROM `" . TABLE_PREFIX . "vtlai_ads_loc`");
	if (mysql_num_rows($rs) > 0) 
	{	
		while($loc=mysql_fetch_assoc($rs))
		{
			$array[$loc['loc_id']]=$loc['loc_name'];
		}
	}
	mysql_free_result($rs);

	
	print_form_header('vtlai_ads_management', 'banner', true);
	print_table_header($vbphrase['vtlai_vbb_ads_add_banner'], 2);
	print_select_row($vbphrase['vtlai_vbb_ads_banner_loc'], 'banner_loc_id', $array, $vbulletin->GPC['banner_loc_id'], true);
	print_input_row($vbphrase['vtlai_vbb_ads_banner_name'], 'banner_name', $banner->banner_name, 30, 50);
	print_input_row($vbphrase['vtlai_vbb_ads_banner_src'], 'banner_src', $vbulletin->GPC['banner_src'], 30, 50);
	print_upload_row($vbphrase['vtlai_vbb_ads_upload_banner'], 'bannerfile', 999999999);
	print_input_row($vbphrase['vtlai_vbb_ads_banner_link_to'], 'banner_link', $vbulletin->GPC['banner_link'], 30, 50);
	print_time_row($vbphrase['vtlai_vbb_ads_banner_starttime'], 'banner_start_time',$time);
	print_time_row($vbphrase['vtlai_vbb_ads_banner_stoptime'], 'banner_stop_time',$time);
	print_yes_no_row($vbphrase['vtlai_vbb_ads_banner_custom'], 'banner_use_code', $vbulletin->GPC['banner_use_code']);
	print_textarea_row($vbphrase['vtlai_vbb_ads_banner_customcode'], 'banner_code', htmlspecialchars($vbulletin->GPC['banner_code']), 10, 52);
	echo '<input type="hidden" name="type" value="add">';
	print_submit_row();
	print_table_footer(2, '', '', 0);
}

// #### ADS BANNER LIST ####################
// ======================================================================
if ($do=='banner') 
{
	$rs = $vbulletin->db->query_read("SELECT * FROM `" . TABLE_PREFIX . "vtlai_ads_loc`");
	if (mysql_num_rows($rs) > 0) 
	{
		while ($loc = $vbulletin->db->fetch_array($rs))	
		{
			print_table_start();
			print_table_header($loc['loc_name']." (ID: {$loc['loc_id']})", 8);
			
			print_cells_row(array('Banner ID',$vbphrase['vtlai_vbb_ads_banner_name'],$vbphrase['vtlai_vbb_ads_banner_src2'],$vbphrase['vtlai_vbb_ads_banner_link_to2'],$vbphrase['vtlai_vbb_ads_banner_starttime'],$vbphrase['vtlai_vbb_ads_banner_stoptime'],$vbphrase['vtlai_vbb_ads_action']), 1, -1);
			
			$rs2 = $vbulletin->db->query_read("SELECT * FROM `" . TABLE_PREFIX . "vtlai_ads_banner` WHERE banner_loc_id='{$loc['loc_id']}' ORDER BY banner_id DESC");
			while ($banner = $vbulletin->db->fetch_array($rs2))
			{
				$cell[] = $banner['banner_id'];
				$cell[] = $banner['banner_name'];
				$cell[] = '<input type="text" size="30" onfocus="this.select()" value="'.$banner['banner_src'].'">';
				$cell[] = '<input type="text" size="30" onfocus="this.select()"  value="'.$banner['banner_link'].'">';
				$cell[] = date('d-m-Y',$banner['banner_start_time']);
				$cell[] = date('d-m-Y',$banner['banner_stop_time']);
				$cell[] = '<a href="vtlai_ads_management.php?do=banner&type=edit&banner_id='.$banner['banner_id'].'">'.$vbphrase['vtlai_vbb_ads_edit'].'</a> | <a href="vtlai_ads_management.php?do=banner&type=del&banner_id='.$banner['banner_id'].'" onclick="return confirm(\''.$vbphrase['vtlai_vbb_ads_areyousure'].'\');">'.$vbphrase['vtlai_vbb_ads_del'].'</a>';
				
				print_cells_row($cell);
				unset($cell);
				$banner=new VTLAI_BANNER($banner['banner_id']);
				echo '<tr><td colspan="8">'.$banner->show().'</td></tr>';
			}
					  
			print_table_footer();
		}
	}
}

// #### ADS BANNER REPORT ####################
// ======================================================================
if ($do=='report' && !$_POST['banner_id']) 
{
	$vtlai=array();
	//---Get Banner list
	$rs1 = $vbulletin->db->query_read("SELECT loc_id,loc_name FROM `" . TABLE_PREFIX . "vtlai_ads_loc`");
	while($loc=mysql_fetch_assoc($rs1))
	{
		$rs2 = $vbulletin->db->query_read("SELECT banner_id,banner_name FROM `" . TABLE_PREFIX . "vtlai_ads_banner` WHERE banner_loc_id='{$loc['loc_id']}' ORDER BY banner_id DESC");
		while($banner=mysql_fetch_assoc($rs2))
		{
			$vtlai[$loc['loc_name']][$banner['banner_id']]="{$banner['banner_id']}-{$banner['banner_name']}";
		}
		mysql_free_result($rs2);
	}
	mysql_free_result($rs1);
	print_form_header('vtlai_banner_report', 'report', true);
	print_table_header($vbphrase['vtlai_vbb_ads_view_report'], 2);
	print_select_row($vbphrase['vtlai_vbb_ads_view_banner_report'], 'banner_id', $vtlai, $vbulletin->GPC['banner_id'], true);
	print_time_row($vbphrase['vtlai_vbb_ads_view_report_starttime'], 'start_time',$time);
	print_time_row($vbphrase['vtlai_vbb_ads_view_report_stoptime'], 'stop_time',$time);
	print_submit_row($vbphrase['vtlai_vbb_ads_view_report']);
	print_table_footer(2, '', '', 0);

}

print_cp_footer();

?>