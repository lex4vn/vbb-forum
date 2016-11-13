<?php

switch ($log['command'])
{
	case 'silence':
	case 'unsilence':
	case 'pruneuser':
		$celldata = construct_phrase($vbphrase["dbtech_vbshout_log_$log[command]"], $logusers["$log[comment]"]);
		break;
}
?>