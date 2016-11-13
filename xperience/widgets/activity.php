<?php	

global $vbulletin, $activity;

require_once('./includes/functions_xperience.php');

$date_start = mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"));
$date_end = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
GetActivityAll($date_start, $date_end, $do, 25, 0, 0, 0);

$output = $activity;



?>
