<?php
switch ($command)
{
	case 'silence':
	case 'unsilence':
		$bit = 16;
		break;
		
	case 'pruneuser':
		$bit = 1;
		break;
}
?>