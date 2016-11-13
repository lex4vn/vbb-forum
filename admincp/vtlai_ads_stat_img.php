<?php
// ++ ============================================================= ++
//                vt.Lai VBB Ads Management 4.1
//				 Code by: Vũ Thanh Lai (vt.lai)
//					Shared at: sinhvienit.net
// ++ ============================================================= ++

	 include 'global.php';
	 include 'pChartLib/pChart/pData.class';   
	 include 'pChartLib/pChart/pChart.class';   
	  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
	 // Dataset definition    
	 $DataSet = new pData;   


 

 
 
 
	$banner_id=intval($_GET['banner_id']);
	$stat_type=$_GET['stat_type'];
	
	$start['day']=intval($_GET['startday']);
	$start['month']=intval($_GET['startmonth']);
	$start['year']=intval($_GET['startyear']);
	
	$stop['day']=intval($_GET['stopday']);
	$stop['month']=intval($_GET['stopmonth']);
	$stop['year']=intval($_GET['stopyear']);
	
	$startday="{$start['year']}-{$start['month']}-{$start['day']}";
	$stopday="{$stop['year']}-{$stop['month']}-{$stop['day']}";
	if($banner_id)
	{
		if($stat_type=='click')
		{
			$name='Click';
			$title=$vbphrase['vtlai_vbb_ads_statofclick'];
			$rs=$vbulletin->db->query_read("SELECT *,DATE_FORMAT(logdate,'%d/%c') AS `ldate` FROM ".TABLE_PREFIX."vtlai_ads_stat WHERE banner_id='$banner_id' AND logdate>='$startday' AND logdate<='$stopday'");
			while($vtlai=mysql_fetch_assoc($rs))
			{
				$values[]=$vtlai['click'];
				$labels[]=$vtlai['ldate'];

			}
			mysql_free_result($rs);
		}
		else
		{
			$name='Impression';
			$title=$vbphrase['vtlai_vbb_ads_statofimpression'];
			$rs=$vbulletin->db->query_read("SELECT *,DATE_FORMAT(logdate,'%d/%c') AS `ldate` FROM ".TABLE_PREFIX."vtlai_ads_stat WHERE banner_id='$banner_id' AND logdate>='$startday' AND logdate<='$stopday'");
			while($vtlai=mysql_fetch_assoc($rs))
			{
				$values[]=$vtlai['view'];
				$labels[]=$vtlai['ldate'];
			}
			mysql_free_result($rs);
		}
	}
	else
	{
		exit('<center>Please visit <a href="http://sinhvienit.net/">sinhvienit.net</a></center>');
	}
	
 //----Start Draw
 $DataSet->AddPoint($values,"Values");
 $DataSet->AddAllSeries();
 
 $DataSet->AddPoint($labels,"Time");
 $DataSet->SetAbsciseLabelSerie('Time');   
 
 $DataSet->SetSerieName("$name Count","$name Count");   
 
 $DataSet->SetYAxisName("$name Count");
 $DataSet->SetYAxisUnit("");//Click
  
 // Initialise the graph   
 $Test = new pChart(1200,300);
 $Test->setFontProperties(DIR."/{$vbulletin->config[Misc][admincpdir]}/pChartLib/Fonts/tahoma.ttf",8);   
 $Test->setGraphArea(70,30,1150,270);   
 $Test->drawFilledRoundedRectangle(7,7,1180,295,5,240,240,240);   
 $Test->drawRoundedRectangle(5,5,1182,225,5,300,300,300);   
 $Test->drawGraphArea(255,255,255,TRUE);
 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);   
 $Test->drawGrid(4,TRUE,300,300,300,50);
  
 // Draw the 0 line   
 $Test->setFontProperties(DIR."/{$vbulletin->config[Misc][admincpdir]}/pChartLib/Fonts/tahoma.ttf",6);   
 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);   
  
 // Draw the line graph
 $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());   
 $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);   
  
 // Finish the graph   
 $Test->setFontProperties(DIR."/{$vbulletin->config[Misc][admincpdir]}/pChartLib/Fonts/tahoma.ttf",8);   
 $Test->drawLegend(75,35,$DataSet->GetDataDescription(),255,255,255);   
 $Test->setFontProperties(DIR."/{$vbulletin->config[Misc][admincpdir]}/pChartLib/Fonts/tahoma.ttf",10);   
 $Test->drawTitle(60,22,$title,50,50,50,585);   
 $Test->Stroke();
?>