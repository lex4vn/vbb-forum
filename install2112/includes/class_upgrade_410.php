<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.0 - Null By VietvBB-TEAM
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
/*
if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}
*/

class vB_Upgrade_410 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '410';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '4.1.0';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '4.1.0 Beta 1';

	/**
	* Beginning version compatibility
	*
	* @var	string
	*/
	public $VERSION_COMPAT_STARTS = '';

	/**
	* Ending version compatibility
	*
	* @var	string
	*/
	public $VERSION_COMPAT_ENDS   = '';

	function step_1()
	{
		$from_version = $this->registry->options['upgrade_from'];

		if ($from_version[0] == '4')
		{
			//set the profile permission
			$this->set_option('socnet', 'user', $vbulletin->options['socnet'] || $vbulletin->bf_misc_socnet['enable_profile_styling'], 'integer', 63 );
			$this->show_message($this->phrase['version']['410']['setting_custom_profile_on']);
		}
		else
		{
			$this->skip_message();
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Null By VietvBB-TEAM
|| # CVS: $RCSfile$ - $Revision: 35750 $
|| ####################################################################
\*======================================================================*/