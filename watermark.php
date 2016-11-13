<?php
error_reporting(E_ALL & ~E_NOTICE);
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'watermark');
$phrasegroups = array(
);
$specialtemplates = array(
	'userstats'
);
$globaltemplates = array(
);
$actiontemplates = array(
);
require_once('./global.php');

$image = $_GET['src'];
$minwidth = $vbulletin->options[watermarkwidthdef];

$imagetype = getimagesize($image);
            switch ($imagetype['mime']) {
    case "image/gif":
        $im = imagecreatefromgif($image);
        break;
    case "image/jpeg":
        $im = imagecreatefromjpeg($image);
        break;
    case "image/png":
        $im = imagecreatefrompng($image);
        break;
	}

$imagewidth = imagesx($im);

if(empty($vbulletin->options[watermarktext])){
$watermarktext = $vbulletin->options[bbtitle];
}
	else {
		$watermarktext = $vbulletin->options[watermarktext];
	}

if($imagewidth <= $minwidth){
header ("Content-type: image/png"); 
imagepng($im);
imagedestroy($im);
}
else {
$stamp = imagecreatetruecolor($imagewidth, 20);
imagestring($stamp, 10, 10, 0, $watermarktext, 0xFFFFFF);

$marge_right = 0;
$marge_bottom = 0;
$sx = imagesx($stamp);
$sy = imagesy($stamp);
imagecopymerge($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp), 50);

header ("Content-type: image/png"); 
imagepng($im);
imagedestroy($im);
}

?>