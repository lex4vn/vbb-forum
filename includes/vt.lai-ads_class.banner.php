<?php
// ++ ============================================================= ++
//                vt.Lai VBB Ads Management 4.1
//				 Code by: Vũ Thanh Lai (vt.lai)
//					Shared at: sinhvienit.net
// ++ ============================================================= ++
class VTLAI_BANNER
{
	protected $info=array();
	
	public function __construct($id=0)
	{
		$this->banner_id=intval($id);
	}
	
	public function addnew($banner_name,$banner_loc_id,$banner_code,$banner_src,$banner_link,$banner_start_time,$banner_stop_time,$banner_use_code=0)
	{
		global $vbulletin;
		
		$this->banner_loc_id=$banner_loc_id;
		$this->banner_code=$banner_code;
		$this->banner_src=$banner_src;
		$this->banner_link=$banner_link;
		$this->banner_start_time=$banner_start_time;
		$this->banner_stop_time=$banner_stop_time;
		$this->banner_name=$banner_name;
		$this->banner_use_code=$banner_use_code;
		
		$insert_key=array();
		$insert_value=array();
		foreach($this->info as $key=>&$value)
		{
			if($key!='banner_id')
			{
				$insert_key[]=$key;
				$insert_value[]=addslashes($value);
			}
		}
		$insert1=implode(',',$insert_key);
		$insert2=implode("','",$insert_value);
		
		$vbulletin->db->query_write("INSERT INTO ".TABLE_PREFIX."vtlai_ads_banner($insert1) VALUES('$insert2')");
		return $this->banner_id=mysql_insert_id();
	}
	
	public function update($banner_name,$banner_loc_id,$banner_code,$banner_src,$banner_link,$banner_start_time,$banner_stop_time,$banner_use_code=false)
	{
		global $vbulletin;
		$this->banner_loc_id=$banner_loc_id;
		$this->banner_code=$banner_code;
		$this->banner_src=$banner_src;
		$this->banner_link=$banner_link;
		$this->banner_start_time=$banner_start_time;
		$this->banner_stop_time=$banner_stop_time;
		$this->banner_name=$banner_name;
		$this->banner_use_code=$banner_use_code;
		
		foreach($this->info as $key=>&$value)
		{
			if($key!='banner_id')
				$qr[]="$key='".addslashes($value)."'";
		}
		$updateqr=implode(',',$qr);
		//exit("UPDATE ".TABLE_PREFIX."vtlai_ads_banner SET $updateqr WHERE banner_id='{$this->banner_id}'");
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX."vtlai_ads_banner SET $updateqr WHERE banner_id='{$this->banner_id}'");
		return mysql_affected_rows();
	}
	
	public function delete()
	{
		global $vbulletin;
		$vbulletin->db->query_write("DELETE FROM ".TABLE_PREFIX."vtlai_ads_stat WHERE  banner_id='{$this->banner_id}'");
		$vbulletin->db->query_write("DELETE FROM ".TABLE_PREFIX."vtlai_ads_click WHERE  banner_id='{$this->banner_id}'");
		$vbulletin->db->query_write("DELETE FROM ".TABLE_PREFIX."vtlai_ads_banner WHERE banner_id='{$this->banner_id}'");
		return mysql_affected_rows();
	}
	
	public function loadinfo($loadsize=false)
	{
		global $vbulletin;
		$rs=$vbulletin->db->query_read("SELECT * FROM ".TABLE_PREFIX."vtlai_ads_banner WHERE banner_id='{$this->banner_id}'");
		$this->info=mysql_fetch_assoc($rs);
		mysql_free_result($rs);
		if($loadsize)
		{
			$rs=$vbulletin->db->query_read("SELECT loc_width,loc_height FROM ".TABLE_PREFIX."vtlai_ads_loc WHERE loc_id='{$this->banner_loc_id}'");
			$info=mysql_fetch_assoc($rs);
			$this->banner_width=$info['loc_width'];
			$this->banner_height=$info['loc_height'];
			mysql_free_result($rs);
		}
	}
	
	public function show()
	{
		global $vbulletin;
		$rs=$vbulletin->db->query_read("SELECT * FROM ".TABLE_PREFIX."vtlai_ads_banner WHERE banner_id='{$this->banner_id}'");
		if(mysql_num_rows($rs))
		{
			$info=mysql_fetch_assoc($rs);
			$loc=new VTLAI_ADSLOC($info['banner_loc_id']);
			$loc->loadinfo();
			$info['banner_width']=$loc->loc_width;
			$info['banner_height']=$loc->loc_height;
			if($info['banner_use_code'])
				return $info['banner_code'];
			elseif(eregi('\.swf$',$info['banner_src']))
			{
				if(!defined('IN_CONTROL_PANEL'))
					$l='vtlai_adiframe.php';
				else
					$l='../vtlai_adiframe.php';
				return '<iframe src="'.$l.'?banner_id='.$this->banner_id.'" width="'.$info['banner_width'].'" height="'.$info['banner_height'].'" frameborder="0" scrolling="no" name="banner_'.$this->banner_id.'" marginheight="0" marginwidth="0">
				  <p>Trình duyệt của bạn ko hỗ trợ iframes.</p>
				</iframe> ';
			}
			else
			{
				if(!defined('IN_CONTROL_PANEL'))
					return '<a href="vtlai_goads.php?banner_id='.$info['banner_id'].'" target="_blank"><img border="0" src="'.$info['banner_src'].'" width="'.$info['banner_width'].'" height="'.$info['banner_height'].'"></a>';
				else	
					return '<a href="../vtlai_goads.php?banner_id='.$info['banner_id'].'" target="_blank"><img border="0" src="'.$info['banner_src'].'" width="'.$info['banner_width'].'" height="'.$info['banner_height'].'"></a>';
			}
		}
		return '';
	}
	
	public function click()
	{
		global $vbulletin;
		if($this->banner_id)
		{
			$sql="INSERT INTO ".TABLE_PREFIX."vtlai_ads_click (
			`banner_id` ,
			`timeline` ,
			`logdate` ,
			`ipaddress`
			)
			VALUES (
			'{$this->banner_id}', UNIX_TIMESTAMP() , CURDATE( ) , '{$_SERVER['REMOTE_ADDR']}'
			);";
			$vbulletin->db->query_write($sql);
			//--Update Click Count
			$vbulletin->db->query_write("INSERT INTO ".TABLE_PREFIX."vtlai_ads_stat (`banner_id` ,`logdate` ,`click`)	VALUES ('{$this->banner_id}', CURDATE( ) , '1') ON DUPLICATE KEY UPDATE click = click +1");
			
		}
	}
	
	public function go()
	{
		global $vbulletin;
		if(!$this->banner_id)
		{
			header('Location: http://sinhvienit.net/@forum/');
			exit();
		}
		else
		{
			$this->click();
			$this->loadinfo();
			header('Location: '.$this->banner_link);
			exit();
		}
	}
	public function __get($name)
	{
		return $this->info[$name];
	}
	
	public function __set($name,$value)
	{
		return $this->info[$name]=$value;
	}
	
}