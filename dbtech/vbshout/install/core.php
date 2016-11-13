<?php

if (!file_exists(DIR . '/includes/xml/bitfield_vbshout.xml'))
{
	print_dots_stop();
	print_cp_message('Please upload the files that came with the vBShout before installing or upgrading!');
}

function print_modification_message($msg)
{
	echo $msg;
	vbflush();
	usleep(500000);
}
?>