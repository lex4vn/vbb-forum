<?php
if (!($this->registry->userinfo['dbtech_vbshout_settings'] & 128))
{
	$this->error('dbtech_vbshout_pming_disabled_user');
	$return_value = false;
}

if (!($exists['dbtech_vbshout_settings'] & 128))
{
	$this->error('dbtech_vbshout_pming_disabled_target');
	$return_value = false;	
}
?>