<?php
if ($instance['permissions_parsed']['cansearcharchive'])
{
	$vbulletin->input->clean_array_gpc('r', array(
		'username'	 => TYPE_STR,
		'hours' 	 => TYPE_UINT,
		'filter' 	 => TYPE_ARRAY_UINT,
		'from' 	 	=> TYPE_ARRAY,
		'end' 	 	=> TYPE_ARRAY,
	));
	
	if ($vbulletin->GPC['from']['month'])
	{
		// We had only month, fill in the rest
		$vbulletin->GPC['from']['year'] 	= ($vbulletin->GPC['from']['year'] 	? $vbulletin->GPC['from']['year'] 	: date('Y'));
		$vbulletin->GPC['from']['day'] 		= ($vbulletin->GPC['from']['day'] 	? $vbulletin->GPC['from']['day'] 	: 1);
	}
	else if ($vbulletin->GPC['from']['year'])
	{
		// We had only year, fill in the rest
		$vbulletin->GPC['from']['month'] 	= ($vbulletin->GPC['from']['month'] ? $vbulletin->GPC['from']['month'] 	: 1);
		$vbulletin->GPC['from']['day'] 		= ($vbulletin->GPC['from']['day'] 	? $vbulletin->GPC['from']['day'] 	: 1);		
	}
	else if ($vbulletin->GPC['from']['day'])
	{
		$vbulletin->GPC['from']['year'] 	= ($vbulletin->GPC['from']['year'] 	? $vbulletin->GPC['from']['year'] 	: date('Y'));
		$vbulletin->GPC['from']['month'] 	= ($vbulletin->GPC['from']['month'] ? $vbulletin->GPC['from']['month'] 	: date('j'));
	}
	
	
	if ($vbulletin->GPC['end']['month'])
	{
		// We had only month, fill in the rest
		$vbulletin->GPC['end']['year'] 		= ($vbulletin->GPC['end']['year'] 	? $vbulletin->GPC['end']['year'] 	: date('Y'));
		$vbulletin->GPC['end']['day'] 		= ($vbulletin->GPC['end']['day'] 	? $vbulletin->GPC['end']['day'] 	: date('j', mktime(0, 0, 0, $vbulletin->GPC['end']['month'] + 1, 0, $vbulletin->GPC['end']['year'])));
	}
	else if ($vbulletin->GPC['end']['year'])
	{
		// We had only year, fill in the rest
		$vbulletin->GPC['end']['month'] 	= ($vbulletin->GPC['end']['month'] 	? $vbulletin->GPC['end']['month'] 	: 12);
		$vbulletin->GPC['end']['day'] 		= ($vbulletin->GPC['end']['day'] 	? $vbulletin->GPC['end']['day'] 	: 31);		
	}
	else if ($vbulletin->GPC['end']['day'])
	{
		$vbulletin->GPC['end']['year'] 		= ($vbulletin->GPC['end']['year'] 	? $vbulletin->GPC['end']['year'] 	: date('Y'));
		$vbulletin->GPC['end']['month'] 	= ($vbulletin->GPC['end']['month'] 	? $vbulletin->GPC['end']['month'] 	: date('j'));
	}
	
	
	if ($vbulletin->GPC['from']['month'] AND $vbulletin->GPC['from']['year'] AND $vbulletin->GPC['from']['day'])
	{
		// We had only month, fill in the rest
		$hook_query_and .= " AND vbshout.dateline >= " . mktime(0, 0, 0, $vbulletin->GPC['from']['month'], $vbulletin->GPC['from']['day'], $vbulletin->GPC['from']['year']);
		$pagevars['from[month]']= $vbulletin->GPC['from']['month'];		
		$pagevars['from[year]'] = $vbulletin->GPC['from']['year'];		
		$pagevars['from[day]'] 	= $vbulletin->GPC['from']['day'];		
	}
	
	if ($vbulletin->GPC['end']['month'] AND $vbulletin->GPC['end']['year'] AND $vbulletin->GPC['end']['day'])
	{
		// We had only month, fill in the rest
		$hook_query_and .= " AND vbshout.dateline <= " . mktime(0, 0, 0, $vbulletin->GPC['end']['month'], $vbulletin->GPC['end']['day'], $vbulletin->GPC['end']['year']);
		$pagevars['end[month]'] = $vbulletin->GPC['end']['month'];		
		$pagevars['end[year]'] 	= $vbulletin->GPC['end']['year'];		
		$pagevars['end[day]'] 	= $vbulletin->GPC['end']['day'];		
	}
	
		
	if ($vbulletin->GPC['username'])
	{
		// Limit by username
		$hook_query_and .= " AND user.username LIKE '%" . $db->escape_string($vbulletin->GPC['username']) . "%'";
		$pagevars['username'] = $vbulletin->GPC['username'];
	}
	
	if ($vbulletin->GPC['hours'])
	{
		// Limit by username
		$hook_query_and .= " AND vbshout.dateline >= " . (TIMENOW - ($vbulletin->GPC['hours'] * 3600));
		$pagevars['hours'] = $vbulletin->GPC['hours'];
	}
	
	if ($vbulletin->GPC['perpage'])
	{
		// Limit by username
		$pagevars['perpage'] = $vbulletin->GPC['perpage'];
	}
	else
	{
		// Limit by username
		$pagevars['perpage'] = $vbulletin->GPC['perpage'] = 25;
	}
	
	$filters = array();
	if (!empty($vbulletin->GPC['filter']))
	{
		foreach ($vbulletin->GPC['filter'] as $key => $val)
		{
			// Store all the chosen filters
			$filters[] = VBSHOUT::$shouttypes["$key"];
			$pagevars["filter[{$key}]"] = $val;
		}
		
		if (!empty($filters))
		{
			// Do query
			$hook_query_and .= " AND vbshout.type IN(" . implode(',', $filters) . ")";
		}
	}
	
	// Ensure that the PM shouttype is only active for managers
}

// Override number of top shouters
$numtopshouters = ($instance['options']['archive_numtopshouters'] ? $instance['options']['archive_numtopshouters'] : $numtopshouters);
?>