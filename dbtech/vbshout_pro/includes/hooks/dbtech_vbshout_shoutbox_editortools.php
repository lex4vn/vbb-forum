<?php

/*
$templater = vB_Template::create('editor_jsoptions_size');
$string = $templater->render(true);
$fonts = preg_split('#\r?\n#s', $string, -1, PREG_SPLIT_NO_EMPTY);
foreach ($fonts AS $font)
{
	if (trim($font) > $this->registry->options['dbtech_vbshout_maxsize'])
	{
		// This is too big!
		continue;
	}
	
	$templater = vB_Template::create('editor_toolbar_fontsize');
		$templater->register('fontsize', trim($font));
	$fontsizes .= $templater->render(true);
}
*/

//$template_hook['dbtech_vbshout_editortools_start'] .= vB_Template::create('dbtech_vbshout_editortools_pro')->render();
$templater = vB_Template::create('dbtech_vbshout_editortools_pro');
	$templater->register('instance', $this->instance);
	$templater->register('permissions', $this->permissions);
$template_hook['dbtech_vbshout_popupbody'] .= $templater->render();

$templater = vB_Template::create('dbtech_vbshout_editortools_pro2');
	$templater->register('editorid', 'dbtech_shoutbox_editor_wrapper');
	$templater->register('fontsizes', $fontsizes);
	$templater->register('permissions', $this->permissions);
$template_hook['dbtech_vbshout_editortools_end'] .= $templater->render();

$chosensize = ($this->shoutstyle["{$this->instance['instanceid']}"]['size'] ? $this->shoutstyle["{$this->instance['instanceid']}"]['size'] : '11px');
$shoutbox->register('size', 	$chosensize);
?>