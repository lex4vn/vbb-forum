<?php

$detached = $this->registry->input->clean_gpc($this->fetchtype, 'detached', TYPE_BOOL);
if ($detached)
{
	// Override amount of shouts to fetch
	$this->registry->options['dbtech_vbshout_maxshouts'] = $this->registry->options['dbtech_vbshout_maxshouts_detached'];
}
?>