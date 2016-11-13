<?php
// ++ ============================================================= ++
//                vt.Lai VBB Ads Management 4.1
//				 Code by: Vũ Thanh Lai (vt.lai)
//					Shared at: sinhvienit.net
// ++ ============================================================= ++
class VTLAI_ADSLOC
{
	protected $info=array();
	
	public function __construct($id=0)
	{
		$this->loc_id=intval($id);
	}
	
	public function addnew($loc_name,$loc_width,$loc_height,$loc_default_code,$loc_painful=0)
	{
		global $vbulletin;
		
		$this->loc_name=$loc_name;
		$this->loc_width=$loc_width;
		$this->loc_height=$loc_height;
		$this->loc_default_code=$loc_default_code;
		$this->loc_painful=$loc_painful;

		
		$insert_key=array();
		$insert_value=array();
		foreach($this->info as $key=>&$value)
		{
			if($key!='loc_id')
			{
				$insert_key[]=$key;
				$insert_value[]=addslashes($value);
			}
		}
		$insert1=implode(',',$insert_key);
		$insert2=implode("','",$insert_value);
		
		$vbulletin->db->query_write("INSERT INTO ".TABLE_PREFIX."vtlai_ads_loc($insert1) VALUES('$insert2')");
		return $this->loc_id=mysql_insert_id();
	}
	
	public function update($loc_name,$loc_width,$loc_height,$loc_default_code,$loc_painful=0)
	{
		global $vbulletin;
		$this->loc_name=$loc_name;
		$this->loc_width=$loc_width;
		$this->loc_height=$loc_height;
		$this->loc_default_code=$loc_default_code;
		$this->loc_painful=$loc_painful;
		
		foreach($this->info as $key=>&$value)
		{
			if($key!='loc_id')
				$qr[]="$key='".addslashes($value)."'";
		}
		$updateqr=implode(',',$qr);
		
		$vbulletin->db->query_write("UPDATE ".TABLE_PREFIX."vtlai_ads_loc SET $updateqr WHERE loc_id='{$this->loc_id}'");
		return mysql_affected_rows();
	}
	public function loadinfo()
	{
		global $vbulletin;
		$rs=$vbulletin->db->query_read("SELECT * FROM ".TABLE_PREFIX."vtlai_ads_loc WHERE loc_id='{$this->loc_id}'");
		if(mysql_num_rows($rs))
		{
			$this->info=mysql_fetch_assoc($rs);
		}
	}
	
	public function delete()
	{
		global $vbulletin;
		$vbulletin->db->query_write("DELETE FROM ".TABLE_PREFIX."vtlai_ads_stat WHERE banner_id IN (SELECT banner_id FROM ".TABLE_PREFIX."vtlai_ads_banner WHERE banner_loc_id='{$this->loc_id}')");
		$vbulletin->db->query_write("DELETE FROM ".TABLE_PREFIX."vtlai_ads_click WHERE banner_id IN (SELECT banner_id FROM ".TABLE_PREFIX."vtlai_ads_banner WHERE banner_loc_id='{$this->loc_id}')");
		$vbulletin->db->query_write("DELETE FROM ".TABLE_PREFIX."vtlai_ads_banner WHERE banner_loc_id='{$this->loc_id}'");
		$vbulletin->db->query_write("DELETE FROM ".TABLE_PREFIX."vtlai_ads_loc WHERE loc_id='{$this->loc_id}'");
		return mysql_affected_rows();
	}
	
	public function show()
	{
		global $vbulletin,$show;
		$now=time();
		$this->loadinfo();
		if($show['search_engine'])
			return $this->loc_default_code;
		
		$rs=$vbulletin->db->query_read("SELECT * FROM ".TABLE_PREFIX."vtlai_ads_banner WHERE banner_loc_id='{$this->loc_id}' AND banner_start_time<=$now AND $now<=banner_stop_time ORDER BY RAND() LIMIT 1");
		if(mysql_num_rows($rs))
		{
			$info=mysql_fetch_assoc($rs);
			mysql_free_result($rs);
			
			//--Update Impression
			$vbulletin->db->query_write("INSERT INTO ".TABLE_PREFIX."vtlai_ads_stat (`banner_id` ,`logdate` ,`view`)	VALUES ('{$info['banner_id']}', CURDATE( ) , '1') ON DUPLICATE KEY UPDATE view = view +1");
			//--If user have post enought to don't show Painful Ads
			if(!$show['vtlai_is_show_ads'] && $this->loc_painful && !$show['vtlai_alway_show_ads'])
				return $this->loc_default_code;
			
			if($info['banner_use_code'])
				return $info['banner_code'];
			elseif(eregi('\.swf$',$info['banner_src']))
			{
				if(!defined('IN_CONTROL_PANEL'))
					$l='vtlai_adiframe.php';
				else
					$l='../vtlai_adiframe.php';
				return '<iframe src="'.$l.'?banner_id='.$info['banner_id'].'" width="'.$this->loc_width.'" height="'.$this->loc_height.'" frameborder="0" scrolling="no" name="banner_'.$this->banner_id.'" marginheight="0" marginwidth="0">
				  <p>Trình duyệt của bạn ko hỗ trợ iframes.</p>
				</iframe> ';
				
				/*
				return '<object width="'.$info['banner_width'].'" height="'.$info['banner_width'].'"> 
				<param name="movie" value="'.$info['banner_src'].'"> 
                <param name="allowFullScreen" value="false">
                <param name="wmode" value="transparent">
				<embed src="'.$info['banner_src'].'" width="'.$info['banner_width'].'" height="'.$info['banner_width'].'" type="application/x-shockwave-flash" wmode="transparent"></embed> 
				</object>';
				*/
			}
			else
			{
				if(!defined('IN_CONTROL_PANEL'))
					return '<a href="vtlai_goads.php?banner_id='.$info['banner_id'].'" target="_blank"><img border="0" src="'.$info['banner_src'].'" width="'.$this->loc_width.'"></a>';
				else
					return '<a href="../vtlai_goads.php?banner_id='.$info['banner_id'].'" target="_blank"><img border="0" src="'.$info['banner_src'].'" width="'.$this->loc_width.'"></a>';
			}
		}
		return $this->loc_default_code;
	}
	public function __get($name)
	{
		return $this->info[$name];
	}
	
	public function __set($name,$value)
	{
		return $this->info[$name]=$value;
	}
	
	public function setinfo($arr)
	{
		$this->info=$arr;
	}
	
}