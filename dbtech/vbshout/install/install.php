<?php

if (!file_exists(DIR . '/dbtech/vbshout/install/install/' . $shortversion . '.php'))
{
	print_dots_stop();
	print_cp_message('Missing install script!');
}

// Fetch the controller for this version
require(DIR . '/dbtech/vbshout/install/install/' . $shortversion . '.php');

// Finalise the installation
require(DIR . '/dbtech/vbshout/install/finalise.php');
?>