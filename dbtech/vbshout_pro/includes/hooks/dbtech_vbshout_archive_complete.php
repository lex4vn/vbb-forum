<?php

if ($vbshout->permissions['cansearcharchive'])
{
	// Begin the search parameters array
	$searchparams = array(
		'username'	 => $vbulletin->GPC['username'],
		'hours' 	 => ($vbulletin->GPC['hours'] ? $vbulletin->GPC['hours'] : ''),
	);
	$searchparams['orderby']["{$vbulletin->GPC[orderby]}"] = ' selected="selected"';
	
	foreach ($filters as $shouttype)
	{
		// Include this filter
		$searchparams['filter']["$shouttype"] = ' checked="checked"';
	}
	
	$searchparams['from']['month']["{$vbulletin->GPC['from']['month']}"] = ' selected="selected"';
	$searchparams['end']['month']["{$vbulletin->GPC['end']['month']}"] = ' selected="selected"';
	
	$searchparams['from']['day'] = $vbulletin->GPC['from']['day'];
	$searchparams['end']['day'] = $vbulletin->GPC['end']['day'];
	
	$from_yearbits = "\t\t<option value=\"0\">&nbsp;</option>";
	$to_yearbits = "\t\t<option value=\"0\">&nbsp;</option>";
	for ($gyear = 2010; $gyear <= 2036; $gyear++)
	{
		$from_yearbits .= "\t\t<option value=\"$gyear\"" . ($vbulletin->GPC['from']['year'] == $gyear ? ' selected="selected"' : '') . ">$gyear</option>";
		$to_yearbits .= "\t\t<option value=\"$gyear\"" . ($vbulletin->GPC['end']['year'] == $gyear ? ' selected="selected"' : '') . ">$gyear</option>";
	}
	
	// Begin creating the search form template
	$templaterr = vB_Template::create('dbtech_vbshout_archive_search');
		$templaterr->register('instanceid', 	$instance['instanceid']);
		$templaterr->register('permissions', 	$vbshout->permissions);
		$templaterr->register('shouttypes', 	$vbshout->shouttypes);
		$templaterr->register('searchparams', 	$searchparams);
		$templaterr->register('from_yearbits', 	$from_yearbits);
		$templaterr->register('to_yearbits', 	$to_yearbits);
	
	$template_hook['dbtech_vbshout_archive_bottom'] .= $templaterr->render();
}
?>