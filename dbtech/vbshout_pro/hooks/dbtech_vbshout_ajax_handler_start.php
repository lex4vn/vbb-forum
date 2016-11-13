<?php
$detached = self::$vbulletin->input->clean_gpc(self::$fetchtype, 'detached', TYPE_BOOL);
if ($detached)
{
	// Override amount of shouts to fetch
	VBSHOUT::$instance['options']['maxshouts'] = VBSHOUT::$instance['options']['maxshouts_detached'];
}
?>