<?php
global $vbulletin;

// Fetch required classes
require_once(DIR . '/dbtech/vbshout/includes/class_core.php');
require_once(DIR . '/dbtech/vbshout/includes/class_cache.php');
if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
{
	// We need the template class
	require_once(DIR . '/dbtech/vbshout/includes/class_template.php');
}

if (is_object($this))
{
	// Loads the cache class
	VBSHOUT_CACHE::init($vbulletin, $this->datastore_entries);
}
else if (is_object($bootstrap))
{
	// Loads the cache class
	VBSHOUT_CACHE::init($vbulletin, $bootstrap->datastore_entries);
}
else
{
	// Loads the cache class
	VBSHOUT_CACHE::init($vbulletin, $specialtemplates);
}

// Initialise
VBSHOUT::init($vbulletin);

if (defined('IN_CONTROL_PANEL'))
{
	if (!function_exists('fetch_tag_list'))
	{
		require_once(DIR . '/includes/class_bbcode.php');
	}
	
	// Store all possible BBCode tags
	VBSHOUT::$tag_list = fetch_tag_list('', true);
}