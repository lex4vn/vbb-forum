<?php

$vbulletin->input->clean_gpc('p', 'dbtech_vbshoutadminperms', TYPE_ARRAY_INT);
$admindm->set_bitfield('dbtech_vbshoutadminperms', 'canadminvbshout', $vbulletin->GPC['dbtech_vbshoutadminperms']['canadminvbshout']);
?>