<?php
//$ratval = '%LFGSALKDJSALKD%';
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2007-2009 Fillip Hannisdal AKA Revan/NeoRevan/Belazor # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #############################################################################
// vBShout functionality class

/**
* Handles everything to do with vBShout.
*
* @package	vBShout
* @version	$ $Rev$ $
* @date		$ $Date$ $
*/
class VBSHOUT
{
	/**
	* Version info
	*
	* @public	mixed
	*/	
	public static $jQueryVersion 	= '1.6.4';	
	public static $version 			= '5.4.8';
	public static $versionnumber	= 548;
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $vbulletin 	= NULL;
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $prefix 		= 'dbtech_';
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $bitfieldgroup	= 'vbshoutpermissions';
	
	/**
	* Array of permissions to be returned
	*
	* @public	array
	*/	
	public static $permissions 		= NULL;
	
	/**
	* Array of cached items
	*
	* @public	array
	*/		
	public static $cache			= array();
	
	/**
	* Whether we've called the DM fetcher
	*
	* @public	boolean
	*/		
	protected static $called		= false;
	
	/**
	* Array of cached items
	*
	* @public	array
	*/		
	public static $unserialize		= array(
		'chatroom' => array(
			'members',
		),
		'instance' => array(
			'permissions',
			'bbcodepermissions',
			'notices',
			'options',
			'forumids',
		),
	);
	
	/**
	* List of shout types
	*
	* @private	array
	*/	
	public static $shouttypes 		= array(
		'shout'		=> 1,
		'pm'		=> 2,
		'me'		=> 4,
		'notif'		=> 8,
		'custom'	=> 16,
		'system'	=> 32,
		'mention'	=> 64,
		'tag'		=> 128,
		'thanks'	=> 256,
	);
	
	/**
	* List of shout styles for the current user
	*
	* @private	array
	*/	
	private static $shoutstyle 	= array();
	
	/**
	* List of all info returned by the fetcher
	*
	* @public	array
	*/	
	public static $fetched 		= array();
	
	/**
	* The source of our information.
	*
	* @public	string
	*/	
	public static $fetchtype 		= 'p';
	
	/**
	* The Tab ID we're working with
	*
	* @public	string
	*/	
	public static $tabid 			= '';
	
	/**
	* What instance we are working with
	*
	* @public	array
	*/	
	public static $instance 		= array();	
	
	/**
	* What chatroom we are working with
	*
	* @public	array
	*/	
	public static $chatroom 		= array();	
	
	/**
	* The currently active users
	*
	* @public	array
	*/	
	public static $activeusers 		= array();
	
	/**
	* The list of BBCode tags enabled globally
	*
	* @public	array
	*/	
	public static $tag_list 		= array();
	
	/**
	* Counter
	*
	* @protected	integer
	*/	
	protected static $i 			= 0;	
	
	/**
	* Dropdown cacher
	*
	* @protected	array
	*/	
	protected static $dropdown 		= array();	
	
	/**
	* Whether we have the pro version or not
	*
	* @public	boolean
	*/		
	public static $isPro		= false;

	
	/**
	* Does important checking before anything else should be going on
	*
	* @param	vB_Registry	Registry object
	*/
	public static function init($vbulletin)
	{
		// Check if the vBulletin Registry is an object
		if (!is_object($vbulletin))
		{
			// Something went wrong here I think
			trigger_error("Registry object is not an object", E_USER_ERROR);
		}
		
		// Set registry
		self::$vbulletin =& $vbulletin;
		
		// Set permissions shorthand
		self::get_permissions();
		
		// What permissions to override
		$override = array(
			'canview',
		);
		
		foreach ($override as $permname)
		{
			// Override various permissions
			self::$permissions[$permname] = (self::$permissions['ismanager'] ? 1 : self::$permissions[$permname]);
		}
		
		foreach (self::$unserialize as $cachetype => $keys)
		{
			foreach ((array)self::$cache[$cachetype] as $id => $arr)
			{
				foreach ($keys as $key)
				{
					// Do unserialize
					self::$cache[$cachetype][$id][$key] = @unserialize($arr[$key]);
				}
			}
		}
		
		foreach ((array)self::$cache['instance'] as $id => $arr)
		{
			// Load default options
			self::load_default_instance_options(self::$cache['instance'][$id]);
			
			// Load instance permissions
			self::load_instance_permissions(self::$cache['instance'][$id]);
			
			// Load instance permissions
			self::load_instance_bbcodepermissions(self::$cache['instance'][$id]);
		}
		
		// Set pro version
		self::$isPro = (file_exists(DIR . '/includes/xml/bitfield_dbtech_vbshout_pro.xml') OR is_dir(DIR . '/dbtech/vbshout_pro'));
	}
	
	/**
	* Grabs what permissions we have got
	*/
	private static function get_permissions()
	{
		// Override bitfieldgroup variable
		$bitfieldgroup = self::$prefix . self::$bitfieldgroup;
		
		if (!is_array(self::$vbulletin->bf_ugp[$bitfieldgroup]))
		{
			// Something went wrong here I think
			require_once(DIR . '/includes/class_bitfield_builder.php');
			if (vB_Bitfield_Builder::build(false) !== false)
			{
				$myobj =& vB_Bitfield_Builder::init();
				if (sizeof($myobj->data['ugp'][$bitfieldgroup]) != sizeof(self::$vbulletin->bf_ugp[$bitfieldgroup]))
				{
					require_once(DIR . '/includes/adminfunctions.php');					
					$myobj->save(self::$vbulletin->db);
					build_forum_permissions();
					
					if (IN_CONTROL_PANEL === true)
					{
						define('CP_REDIRECT', self::$vbulletin->scriptpath);
						print_stop_message('rebuilt_bitfields_successfully');
					}
					else
					{
						self::$vbulletin->url = self::$vbulletin->scriptpath;
						eval(print_standard_redirect('redirect_updatethanks', true, true));				
					}
				}
			}
			else
			{
				echo "<strong>error</strong>\n";
				print_r(vB_Bitfield_Builder::fetch_errors());
				die();
			}
		}
		
		if (!self::$vbulletin->userinfo['permissions'])
		{
			// For some reason, this is missing
			cache_permissions(self::$vbulletin->userinfo);
		}
		
		foreach ((array)self::$vbulletin->bf_ugp[$bitfieldgroup] as $permname => $bit)
		{
			// Set the permission
			self::$permissions[$permname] = (self::$vbulletin->userinfo['permissions'][$bitfieldgroup] & $bit ? 1 : 0);
		}
	}
	
	/**
	* Quick Method of building the CPNav Template
	*
	* @param	string	The selected item in the CPNav
	*/	
	public static function construct_nav($selectedcell = 'main')
	{
		global $navclass, $vbphrase;
		global $vbulletin, $show, $template_hook;
	
		$cells = array(
			'main',
			'issuelist',
			'breakdown',
		);
	
		//($hook = vBulletinHook::fetch_hook('usercp_nav_start')) ? eval($hook) : false;
		
		// set the class for each cell/group
		$navclass = array();
		foreach ($cells AS $cellname)
		{
			$navclass[$cellname] = (intval(self::$vbulletin->versionnumber) == 3 ? 'alt2' : 'inactive');
		}
		$navclass[$selectedcell] = (intval(self::$vbulletin->versionnumber) == 3 ? 'alt1' : 'active');
		
		//($hook = vBulletinHook::fetch_hook('usercp_nav_complete')) ? eval($hook) : false;
	}
		
	/**
	* Check if we have permissions to perform an action
	*
	* @param	array		User info
	* @param	array		Permissions info
	*/		
	public static function check_permissions(&$user, $permissions)
	{
		if (!$user['usergroupid'] OR (!isset($user['membergroupids']) AND $user['userid']))
		{
			// Ensure we have this
			$user = fetch_userinfo($user['userid']);
		}
		
		if (!is_array($user['permissions']))
		{
			// Ensure we have the perms
			cache_permissions($user);
		}
		
		$ugs = fetch_membergroupids_array($user);		
		if (!$ugs[0])
		{
			// Hardcode guests
			$ugs[0] = 1;
		}
		
		//self::$vbulletin->usergroupcache
		foreach ($ugs as $usergroupid)
		{
			$value = $permissions["$usergroupid"];
			$value = (isset($value) ? $value : -1);
			
			switch ($value)
			{
				case 1:
					// Allow
					return true;
					break;
				
				case -1:
					// Usergroup Default		
					if (!($user['permissions'][self::$prefix . self::$bitfieldgroup] & 4))
					{
						// Allow by default
						return true;
					}
					break;
			}
		}
		
		// We didn't make it
		return false;
	}
	
	/**
	* Class factory. This is used for instantiating the extended classes.
	*
	* @param	string			The type of the class to be called (user, forum etc.)
	* @param	vB_Registry		An instance of the vB_Registry object.
	* @param	integer			One of the ERRTYPE_x constants
	*
	* @return	vB_DataManager	An instance of the desired class
	*/
	public static function &datamanager_init($classtype, &$registry, $errtype = ERRTYPE_STANDARD)
	{
		if (empty(self::$called))
		{
			// include the abstract base class
			require_once(DIR . '/includes/class_dm.php');
			self::$called = true;
		}
	
		if (preg_match('#^\w+$#', $classtype))
		{
			require_once(DIR . '/dbtech/vbshout/includes/class_dm_' . strtolower($classtype) . '.php');
	
			$classname = 'vB_DataManager_' . $classtype;
			$object = new $classname($registry, $errtype);
	
			return $object;
		}
	}
	
	/**
	* JS class fetcher for AdminCP
	*
	* @param	string	The JS file name or the code
	* @param	boolean	Whether it's a file or actual JS code
	*/
	public static function js($js = '', $file = true, $echo = true)
	{
		global $vbulletin;

		$output = '';
		if ($file)
		{
			$output = '<script type="text/javascript" src="' . $vbulletin->options['bburl'] . '/dbtech/vbshout/clientscript/vbshout' . $js . '.js?v=' . self::$versionnumber . '"></script>';
		}
		else
		{
			$output = "
				<script type=\"text/javascript\">
					<!--
					$js
					// -->
				</script>
			";
		}
		
		if ($echo)
		{
			echo $output;
		}
		else
		{
			return $output;
		}
	}
	
	/**
	* Loads default instance options
	*
	* @param	array	The instance in question
	*/
	public static function load_default_instance_options(&$instance)
	{
		$instance['options']['logging'] 				= (isset($instance['options']['logging']) 					? $instance['options']['logging'] 					: 15);
		$instance['options']['editors'] 				= (isset($instance['options']['editors']) 					? $instance['options']['editors'] 					: 127);
		$instance['options']['notices'] 				= (isset($instance['options']['notices']) 					? $instance['options']['notices'] 					: 3);
		$instance['options']['optimisation'] 			= (isset($instance['options']['optimisation']) 				? $instance['options']['optimisation'] 				: 1);
		$instance['options']['allowsmilies'] 			= (isset($instance['options']['allowsmilies']) 				? $instance['options']['allowsmilies'] 				: 1);
		$instance['options']['activeusers'] 			= (isset($instance['options']['activeusers']) 				? $instance['options']['activeusers'] 				: 0);
		$instance['options']['sounds'] 					= (isset($instance['options']['sounds']) 					? $instance['options']['sounds'] 					: 1);
		$instance['options']['enablemenu'] 				= (isset($instance['options']['enablemenu']) 				? $instance['options']['enablemenu'] 				: 1);
		$instance['options']['altshouts'] 				= (isset($instance['options']['altshouts']) 				? $instance['options']['altshouts'] 				: 0);
		$instance['options']['enableaccess'] 			= (isset($instance['options']['enableaccess']) 				? $instance['options']['enableaccess'] 				: 1);
		$instance['options']['maxshouts'] 				= (isset($instance['options']['maxshouts']) 				? $instance['options']['maxshouts'] 				: 20);
		$instance['options']['maxarchiveshouts'] 		= (isset($instance['options']['maxarchiveshouts']) 			? $instance['options']['maxarchiveshouts'] 			: 20);
		$instance['options']['height'] 					= (isset($instance['options']['height']) 					? $instance['options']['height'] 					: 150);
		$instance['options']['floodchecktime'] 			= (isset($instance['options']['floodchecktime']) 			? $instance['options']['floodchecktime'] 			: 3);
		$instance['options']['maxchars'] 				= (isset($instance['options']['maxchars']) 					? $instance['options']['maxchars'] 					: 256);
		$instance['options']['maximages'] 				= (isset($instance['options']['maximages']) 				? $instance['options']['maximages'] 				: 2);
		$instance['options']['idletimeout'] 			= (isset($instance['options']['idletimeout']) 				? $instance['options']['idletimeout'] 				: 180);
		$instance['options']['refresh'] 				= (isset($instance['options']['refresh']) 					? $instance['options']['refresh'] 					: 5);
		$instance['options']['maxchats'] 				= (isset($instance['options']['maxchats']) 					? $instance['options']['maxchats'] 					: 5);
		$instance['options']['shoutorder'] 				= (isset($instance['options']['shoutorder']) 				? $instance['options']['shoutorder'] 				: 'DESC');
		$instance['options']['maxsize'] 				= (isset($instance['options']['maxsize']) 					? $instance['options']['maxsize'] 					: 3);
		$instance['options']['postping_interval'] 		= (isset($instance['options']['postping_interval']) 		? $instance['options']['postping_interval'] 		: 50);
		$instance['options']['threadping_interval'] 	= (isset($instance['options']['threadping_interval']) 		? $instance['options']['threadping_interval'] 		: 50);
		$instance['options']['memberping_interval'] 	= (isset($instance['options']['memberping_interval']) 		? $instance['options']['memberping_interval'] 		: 50);
		$instance['options']['shoutboxtabs'] 			= (isset($instance['options']['shoutboxtabs']) 				? $instance['options']['shoutboxtabs'] 				: 7);
		$instance['options']['logging_deep'] 			= (isset($instance['options']['logging_deep']) 				? $instance['options']['logging_deep'] 				: 0);
		$instance['options']['logging_deep_system'] 	= (isset($instance['options']['logging_deep_system']) 		? $instance['options']['logging_deep_system'] 		: 0);
		$instance['options']['enablepms'] 				= (isset($instance['options']['enablepms']) 				? $instance['options']['enablepms'] 				: 1);
		$instance['options']['enablepmnotifs'] 			= (isset($instance['options']['enablepmnotifs']) 			? $instance['options']['enablepmnotifs'] 			: 1);
		$instance['options']['enable_sysmsg'] 			= (isset($instance['options']['enable_sysmsg']) 			? $instance['options']['enable_sysmsg'] 			: 1);
		$instance['options']['sounds_idle'] 			= (isset($instance['options']['sounds_idle']) 				? $instance['options']['sounds_idle'] 				: 0);
		$instance['options']['avatars_normal'] 			= (isset($instance['options']['avatars_normal']) 			? $instance['options']['avatars_normal'] 			: 0);
		$instance['options']['avatar_width_normal'] 	= (isset($instance['options']['avatar_width_normal']) 		? $instance['options']['avatar_width_normal'] 		: 11);
		$instance['options']['avatar_height_normal'] 	= (isset($instance['options']['avatar_height_normal']) 		? $instance['options']['avatar_height_normal'] 		: 11);
		$instance['options']['avatars_full'] 			= (isset($instance['options']['avatars_full']) 				? $instance['options']['avatars_full'] 				: 0);
		$instance['options']['avatar_width_full'] 		= (isset($instance['options']['avatar_width_full']) 		? $instance['options']['avatar_width_full'] 		: 22);
		$instance['options']['avatar_height_full'] 		= (isset($instance['options']['avatar_height_full']) 		? $instance['options']['avatar_height_full'] 		: 22);
		$instance['options']['maxshouts_detached'] 		= (isset($instance['options']['maxshouts_detached']) 		? $instance['options']['maxshouts_detached'] 		: 40);
		$instance['options']['height_detached'] 		= (isset($instance['options']['height_detached']) 			? $instance['options']['height_detached'] 			: 300);
		$instance['options']['refresh_idle'] 			= (isset($instance['options']['refresh_idle']) 				? $instance['options']['refresh_idle'] 				: 5);
		$instance['options']['archive_numtopshouters'] 	= (isset($instance['options']['archive_numtopshouters']) 	? $instance['options']['archive_numtopshouters'] 	: 10);
		$instance['options']['autodelete'] 				= (isset($instance['options']['autodelete']) 				? $instance['options']['autodelete'] 				: 0);
		$instance['options']['shoutarea'] 				= (isset($instance['options']['shoutarea']) 				? $instance['options']['shoutarea'] 				: 'left');
		$instance['options']['archive_link'] 			= (isset($instance['options']['archive_link']) 				? $instance['options']['archive_link'] 				: 0);
		$instance['options']['minposts'] 				= (isset($instance['options']['minposts']) 					? $instance['options']['minposts'] 					: 0);
		$instance['options']['timeformat'] 				= (isset($instance['options']['timeformat']) 				? $instance['options']['timeformat'] 				: self::$vbulletin->options['timeformat']);
		$instance['options']['blogping_interval'] 		= (isset($instance['options']['blogping_interval']) 		? $instance['options']['blogping_interval'] 		: 50);
		$instance['options']['shoutping_interval'] 		= (isset($instance['options']['shoutping_interval']) 		? $instance['options']['shoutping_interval'] 		: 50);
		$instance['options']['aptlping_interval'] 		= (isset($instance['options']['aptlping_interval']) 		? $instance['options']['aptlping_interval'] 		: 50);
		$instance['options']['tagping_interval'] 		= (isset($instance['options']['tagping_interval']) 			? $instance['options']['tagping_interval'] 			: 50);
		$instance['options']['mentionping_interval'] 	= (isset($instance['options']['mentionping_interval']) 		? $instance['options']['mentionping_interval'] 		: 50);
		$instance['options']['quoteping_interval'] 		= (isset($instance['options']['quoteping_interval']) 		? $instance['options']['quoteping_interval'] 		: 50);
		$instance['options']['quizmadeping_interval'] 	= (isset($instance['options']['quizmadeping_interval']) 	? $instance['options']['quizmadeping_interval'] 	: 50);
		$instance['options']['quiztakenping_interval'] 	= (isset($instance['options']['quiztakenping_interval']) 	? $instance['options']['quiztakenping_interval'] 	: 50);
		
	}
	
	/**
	* Sets up the permissions based on instance
	*
	* @param	array		The instance
	* @param	array|null	User Info to check (null = vBulletin Userinfo)
	*/
	public static function load_instance_permissions(&$instance, $userinfo = NULL)
	{
		// Set permissions shorthand
		$permarray = array();
		
		// Ensure we can fetch bitfields
		require_once(DIR . '/includes/adminfunctions_options.php');
		$permissions = fetch_bitfield_definitions('nocache|dbtech_vbshoutpermissions');
		
		if ($userinfo === NULL)
		{
			// We're using our own user info
			$userinfo = self::$vbulletin->userinfo;
		}
		else if ($userinfo['userid'] == self::$vbulletin->userinfo['userid'] AND is_array($instance['permissions_parsed']))
		{
			// Just return parsed
			return $instance['permissions_parsed'];
		}
		
		foreach (array_merge(array($userinfo['usergroupid']), explode(',', $userinfo['membergroupids'])) as $usergroupid)
		{
			if (!$usergroupid)
			{
				// Just skip it
				continue;
			}
			
			foreach ((array)$permissions as $permname => $bit)
			{
				if (!isset($permarray[$permname]))
				{
					// Default to false
					$permarray[$permname] = false;
				}
				
				if (!$permarray[$permname] AND ((int)$instance['permissions']["$usergroupid"] & (int)$bit))
				{
					// Override to true
					$permarray[$permname] = true;
				}
			}			
		}
		
		// Some hardcoded ones
		//$permarray['isprotected'] 	= ((int)$userinfo['permissions']['dbtech_vbshoutpermissions'] & (int)self::$vbulletin->bf_ugp_dbtech_vbshoutpermissions['isprotected']);
		$permarray['ismanager'] 	= ((int)$userinfo['permissions']['dbtech_vbshoutpermissions'] & (int)self::$vbulletin->bf_ugp_dbtech_vbshoutpermissions['ismanager']);
		$permarray['canpm']			= (isset($permarray['canpm']) ? $permarray['canpm'] : 1);
		
		if ($userinfo == self::$vbulletin->userinfo)
		{
			// Set the completed permissions array
			$instance['permissions_parsed'] = $permarray;
		}
		
		return $permarray;
	}
	
	/**
	* Sets up the BBCode permissions based on instance
	*
	* @param	array		The instance
	* @param	array|null	User Info to check (null = vBulletin Userinfo)
	*/
	public static function load_instance_bbcodepermissions(&$instance, $userinfo = NULL)
	{
		// Set permissions shorthand
		$bitvalue 	= 0;
		$permarray = array();
		
		if ($userinfo === NULL)
		{
			// We're using our own user info
			$userinfo = self::$vbulletin->userinfo;
		}
		else if ($userinfo['userid'] == self::$vbulletin->userinfo['userid'] AND is_array($instance['bbcodepermissions_parsed']))
		{
			// Just return parsed
			return $instance['bbcodepermissions_parsed'];
		}		
		
		// Fetch all our usergroup ids
		$usergroupids = array_merge(array($userinfo['usergroupid']), explode(',', $userinfo['membergroupids']));
		
		// Ensure we can fetch bitfields
		require_once(DIR . '/includes/adminfunctions_options.php');
		$permissions = fetch_bitfield_definitions('nocache|allowedbbcodesfull');
		
		foreach ($usergroupids as $usergroupid)
		{
			if (!$usergroupid)
			{
				// Just skip it
				continue;
			}
			
			foreach ((array)$permissions as $permname => $bit)
			{
				if (!isset($permarray[$permname]))
				{
					// Default to false
					$permarray[$permname] = false;
				}
				
				if (!$permarray[$permname] AND ((int)$instance['bbcodepermissions']["$usergroupid"] & (int)$bit))
				{
					// Override to true
					$permarray[$permname] = true;
					$bitvalue += $bit;
				}
			}			
		}
		
		if ($userinfo == self::$vbulletin->userinfo)
		{
			// Set the completed permissions array
			$instance['bbcodepermissions_parsed'] = array('bit' => $bitvalue, 'array' => $permarray);
		}
		
		return array('bit' => $bitvalue, 'array' => $permarray);
	}
	
	/**
	* Renders the main shoutbox template.
	* A method because this needs to happen on
	* multiple locations under different conditions.
	*/
	public function render($instance)
	{
		global $vbphrase, $show, $template_hook, $vbulletin;		
		
		if (intval(self::$vbulletin->versionnumber) == 3)
		{
			global $instance, $stylevar, $session;
			global $bbuserinfo, $vboptions, $vbulletin, $css, $show, $cells;			
		}
		
		// Empty out this
		$template_hook['dbtech_vbshout_shoutcontrols_below'] = '';
		$template_hook['dbtech_vbshout_below_shout'] = '';
		$template_hook['dbtech_vbshout_popupbody'] = '';
		$template_hook['dbtech_vbshout_editortools_end'] = '';
		$template_hook['dbtech_vbshout_activeusers_right'] = '';
		$template_hook['dbtech_vbshout_activeusers_left'] = '';
		$template_hook['dbtech_vbshout_shoutarea_left'] = '';
		$template_hook['dbtech_vbshout_shoutarea_right'] = '';
		$template_hook['dbtech_vbshout_shoutarea_above'] = '';
		$template_hook['dbtech_vbshout_shoutarea_below'] = '';
				
		foreach (array(
			'dbtech_vbshout_activeusers',
			'dbtech_vbshout_editortools_pro',
			'dbtech_vbshout_shoutbox',
			'dbtech_vbshout_editortools',
			'dbtech_vbshout_shoutarea_horizontal',
			'dbtech_vbshout_shoutarea_vertical',			
			'dbtech_vbshout_shoutcontrols',			
			'dbtech_vbshout_shouttype_me',
			'dbtech_vbshout_shouttype_pm',
			'dbtech_vbshout_shouttype_shout',
			'dbtech_vbshout_shouttype_system'
		) AS $templatename)
		{
			if (intval(self::$vbulletin->versionnumber) != 3)
			{
				// Register the instance variable on all these
				vB_Template::preRegister($templatename, array('instance' => $instance));
			}
			else
			{
				// vB3 code
				$GLOBALS['instance'] = &$instance;
			}
		}
		
		if (!is_array($show))
		{
			// Init
			$show = array();
		}
		
		// Create the template rendering engine
		$shoutbox = vB_Template::create('dbtech_vbshout_shoutbox');
			$shoutbox->register('permissions', $instance['permissions_parsed']);
				
		// Whether we need to do a CSS Hack
		$csshack = ' dbtech_fullshouts';

		// The main components of the shoutbox link
		$title 	= $instance['name'];
		if ($instance['permissions_parsed']['canviewarchive'])
		{
			$start 	= '<a href="vbshout.php?' . self::$vbulletin->session->vars['sessionurl'] . 'do=archive&amp;instanceid=' . $instance['instanceid'] . '">';
			$end 	= '</a>';
		}
		
		// Create the actual shoutbox variable
		//$headerlink = $start . $title . $end;
		$headerlink = '';
		
		// Re-add this, lol
		self::$shoutstyle = @unserialize(self::$vbulletin->userinfo['dbtech_vbshout_shoutstyle']);
		
		$shoutstyle = self::$shoutstyle["{$instance[instanceid]}"];
		if (self::$vbulletin->userinfo['userid'] AND $instance['permissions_parsed']['canshout'])
		{		
			// Local user overrides to this should only hide it from the template, not from initialisation
			if ($instance['options']['editors'])
			{
				// Create the template containing the Editor Tools
				$tools = vB_Template::create('dbtech_vbshout_editortools');
					$tools->register('editorid', 	'dbtech_shoutbox_editor_wrapper');
					$tools->register('permissions', $instance['permissions_parsed']);
				
				if ($instance['options']['editors'] & 1 AND $shoutstyle['bold'])
				{
					// Bold
					$shoutbox->register('bold', 		$shoutstyle['bold']);
				}
				
				if ($instance['options']['editors'] & 2 AND $shoutstyle['italic'])
				{
					// Italic
					$shoutbox->register('italic', 		$shoutstyle['italic']);
				}
				
				if ($instance['options']['editors'] & 4 AND $shoutstyle['underline'])
				{
					// Underline
					$shoutbox->register('underline', 	$shoutstyle['underline']);
				}
				
				if ($instance['options']['editors'] & 16)
				{
					// Check if we need to go with the default font
					$foundfont 	= false;
					
					// Grab the user's font
					$chosenfont = $shoutstyle['font'];
										
					$templater = vB_Template::create('editor_jsoptions_font');
					$string = $templater->render(true);
					$fonts = preg_split('#\r?\n#s', $string, -1, PREG_SPLIT_NO_EMPTY);
					foreach ($fonts AS $font)
					{
						if (strpos($font, 'editor_jsoptions_font'))
						{
							// We don't need template comments
							continue;
						}
						
						if (trim($font) == $chosenfont)
						{
							// Yay we found the font
							$foundfont = true;
						}
						
						if (intval(self::$vbulletin->versionnumber) == 3)
						{
							$fontnames .= '<option value="' . trim($font) . '" style="font-family:' . trim($font) . '">' . trim($font) . '</option>';
						}
						else
						{
							$templater = vB_Template::create('dbtech_vbshout_editor_toolbar_fontname');
								$templater->register('fontname', trim($font));
							$fontnames .= $templater->render(true);
						}
					}
					if (!$foundfont)
					{
						if (intval(self::$vbulletin->versionnumber) != 3)
						{
							// Find the default font
							$chosenfont = explode(' ', trim(vB_Template_Runtime::fetchStyleVar('font')));
							$chosenfont = str_replace(',', '', $chosenfont[1]);
						}
						else
						{
							// vB3 code
							$chosenfont = 'Tahoma';
						}
					}
					
					// Register font stuff
					$tools->register('fontnames', 	$fontnames);
					$shoutbox->register('font', 	$chosenfont);
				}
			
				if ($instance['options']['editors'] & 8)
				{
					// Grab the user's chosen color / default colour
					$chosencolor = ($shoutstyle['color'] ? $shoutstyle['color'] : '');
					
					if (intval(self::$vbulletin->versionnumber) > 3)
					{
						// Begin checking colours
						$colors = vB_Template::create('dbtech_vbshout_editor_toolbar_colors')->render();
					}
					
					// Register colour stuff
					$tools->register('colors', 		$colors);
					$shoutbox->register('color', 	$chosencolor);
				}
				
				($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shoutbox_editortools')) ? eval($hook) : false;
				
				// Finally render the editor tools
				$tools->register('template_hook', $template_hook);
				$editortools = $tools->render();
				
				// Set the rendered Editor Tools
				//$shoutbox->register('editortools', $editortools);	
			}
		}
		
		$domenu = false;
		$chatrooms = '';
		$direction = 'left';
		$addedpx = 0;
		$chattabs = array();
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_shoutbox_start')) ? eval($hook) : false;
		
		$chattabs['shoutreports'] = (((!isset($instance['options']['shoutboxtabs']) OR ($instance['options']['shoutboxtabs'] & 4)) AND $instance['permissions_parsed']['canmodchat']) ? "vBShout" . $instance['instanceid'] . ".add_tab('shoutreports', '" . $vbphrase['dbtech_vbshout_unhandled_reports'] . ": <span id=\"dbtech_shoutbox_shoutreports" . $instance['instanceid'] . "\">0</span>', false, 'show_reports()');" : '');
		
		foreach ((array)self::$cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['active'])
			{
				// Inactive chat room
				continue;
			}
			
			if ($chatroom['instanceid'] != $instance['instanceid'] AND $chatroom['instanceid'] != 0)
			{
				// Wrong instance id
				continue;
			}
			
			if ($chatroom['membergroupids'])
			{
				if (is_member_of(self::$vbulletin->userinfo, explode(',', $chatroom['membergroupids'])))
				{
					// Do join it
					$chattabs["chatroom_{$chatroomid}_"] = "vBShout" . $instance['instanceid'] . ".create_chatroom_tab($chatroomid, '" . addslashes($chatroom['title']) . "', false);";
				}
			}
			else
			{
				$userid = self::$vbulletin->userinfo['userid'];
				if ($chatroom['members']["$userid"] == '1')
				{
					// Do join it
					$chattabs["chatroom_{$chatroomid}_"] = "vBShout" . $instance['instanceid'] . ".create_chatroom_tab($chatroomid, '" . addslashes($chatroom['title']) . "', true);";
				}
			}
		}
		
		if (!is_array(self::$vbulletin->userinfo['dbtech_vbshout_displayorder']))
		{
			// Only unserialize if it's not an array
			self::$vbulletin->userinfo['dbtech_vbshout_displayorder'] = @unserialize(self::$vbulletin->userinfo['dbtech_vbshout_displayorder']);
		}
		
		$tabdisplayorder = (array)self::$vbulletin->userinfo['dbtech_vbshout_displayorder'];
		if (is_array($tabdisplayorder["{$instance[instanceid]}"]))
		{
			asort($tabdisplayorder["{$instance[instanceid]}"]);
			foreach ($tabdisplayorder["{$instance[instanceid]}"] as $tabid => $tab)
			{
				// Add the tab
				$chatrooms .= $chattabs["$tabid"];
				unset($chattabs["$tabid"]);
			}
		}
		
		// Add new / unknown tabs
		$chatrooms .= implode(' ', $chattabs);
		
		// Register the chat rooms we are joined to
		$shoutbox->register('chatrooms', $chatrooms);		
		
		if ($instance['options']['activeusers'])
		{
			// Array of all active users
			//self::fetch_active_users(false);
			
			// Begin creating the template
			$templater = vB_Template::create('dbtech_vbshout_activeusers');
				$templater->register('activeusers', (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_active_users']));
				$templater->register('addedpx', 	$addedpx);			
			
			// We're using the separate Active Users block
			switch ($direction)
			{
				case 'left':
				case 'above':
				case 'below':
					// Register the active users frame
						$templater->register('direction', 	'left');
					$template_hook['dbtech_vbshout_activeusers_right'] = $templater->render();
					break;
					
				case 'right':
					// Register the active users frame
						$templater->register('direction', 	'right');
					$template_hook['dbtech_vbshout_activeusers_left'] = $templater->render();
					break;
			}
		}		
		
		// Register the header link variable
		$shoutbox->register('title', 		$title);
		$shoutbox->register('headerlink', 	$headerlink);
		
		// Register template variables
		if (!self::$vbulletin->userinfo['userid'] OR !$instance['permissions_parsed']['canshout'])
		{
			// Set the CSS hack
			$shoutbox->register('csshack', $csshack);
			
			// We can't shout
			$show['canshout'] = false;
		}
		else
		{
			// We can shout
			$show['canshout'] = true;
			
			if (!$shoutbox->is_registered('shoutarea'))
			{
				// We haven't registered a shout area yet
				$templater = vB_Template::create('dbtech_vbshout_shoutarea_vertical');
					$templater->register('direction', 'left');
					$shoutbox->register('direction', 'left');
				$shoutarea = $templater->render();
				
				// Register the shout controls also
				$templater = vB_Template::create('dbtech_vbshout_shoutcontrols');
					$templater->register('permissions', $instance['permissions_parsed']);
					$templater->register('editortools', $editortools);
				$template_hook['dbtech_vbshout_shoutcontrols_below'] = $templater->render();			
				
				// Register the shout area as being on the left
				$shoutbox->register('shoutarea', $shoutarea);
			}
		}
		
		$templates = array(
			'shout' 	=> vB_Template::create('dbtech_vbshout_shouttype_shout')->render(),
			'pm' 		=> vB_Template::create('dbtech_vbshout_shouttype_pm')->render(),
			'me' 		=> vB_Template::create('dbtech_vbshout_shouttype_me')->render(),
			'system' 	=> vB_Template::create('dbtech_vbshout_shouttype_system')->render(),
		);
		vbshout_js_escape_string($templates);
		
		// Register template hooks
		$shoutbox->register('template_hook', 	$template_hook);
		$shoutbox->register('show', 			$show);
		$shoutbox->register('timenow', 			TIMENOW);
		$shoutbox->register('instanceOptions', 	vbshout_json_encode($instance['options']));
		$shoutbox->register('userOptions', 		vbshout_json_encode(array()));
		$shoutbox->register('templates', 		json_encode($templates));
		$shoutbox->register('instance', 		$instance);
		
		// Finally render the template
		return $shoutbox->render();
	}
	
	/**
	* Handles an AJAX request from the Shoutbox.
	*
	* @param	string	What we're upto
	*/
	public static function ajax_handler($do)
	{
		global $vbphrase;
		
		// Grab instance id
		$instanceid = self::$vbulletin->input->clean_gpc(self::$fetchtype, 'instanceid', TYPE_UINT);
		
		if (!self::$instance = self::$cache['instance']["$instanceid"])
		{
			// Wrong instance
			self::$fetched['error'] = 'Invalid Instance: ' . $instanceid;
			
			// Prints the XML for reading by the AJAX script
			self::print_ajax_xml();
			
			return false;
		}
		
		// Any additional arguments we may be having to the fetching of shouts
		$args = array(
			'instanceid' => $instanceid
		);
		
		$chatroomid = self::$vbulletin->input->clean_gpc(self::$fetchtype, 'chatroomid', TYPE_UINT);
		if ($chatroomid)
		{
			// Check if the chatroom is active
			self::$chatroom = self::$cache['chatroom']["$chatroomid"];
			
			if ($do != 'joinchat')
			{
				if (!self::$chatroom OR !self::$chatroom['active'])
				{
					// Wrong chatroom
					self::$fetched['error'] = 'disband_' . $chatroomid;
				}
				
				if (!self::$chatroom['membergroupids'])
				{
					// This is not a members-only group
					$userid = self::$vbulletin->userinfo['userid'];
					if (!isset(self::$chatroom['members']["$userid"]))
					{
						// We're not a member
						self::$fetched['error'] = 'disband_' . $chatroomid;
					}
				}
				else
				{
					if (!is_member_of(self::$vbulletin->userinfo, explode(',', self::$chatroom['membergroupids'])))
					{
						// Usergroup no longer a member
						self::$fetched['error'] = 'disband_' . $chatroomid;
					}			
				}
				
				// Override tabid for AOP purposes
				self::$tabid = 'chatroom_' . $chatroomid . '_' . self::$chatroom['instanceid'];
			}
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_ajax_handler_start')) ? eval($hook) : false;
		
		if (self::$fetched['error'])
		{
			// We had errors, don't bother
			
			// Prints the XML for reading by the AJAX script
			self::print_ajax_xml();
			
			return false;
		}
		
		// Strip non-valid characters
		$do = preg_replace("/[^a-zA-Z0-9-_]/", "", $do);		
				
		if (file_exists(DIR . '/dbtech/vbshout/actions/ajax/' . $do . '.php'))
		{
			// Set where we're coming from
			self::$fetched['ajax'] = $do;
			
			// Fetch the file in question
			require(DIR . '/dbtech/vbshout/actions/ajax/' . $do . '.php');
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_ajax_handler')) ? eval($hook) : false;
		
		// Prints the XML for reading by the AJAX script
		self::print_ajax_xml();		
	}
	
	/**
	* Prints out the AJAX XML
	*/
	public static function print_ajax_xml()
	{
		if (!self::$fetched['error'])
		{
			// Bugfix
			unset(self::$fetched['error']);
		}
		
		// Initialise the XML object
		$xml = new vB_AJAX_XML_Builder(self::$vbulletin, 'text/xml');
		
		// Add a default group
		$xml->add_group('vbshout');
		
		$tags = array(
			'aoptime',
			'success',
			'sticky',
			'error',
			'clear',
			'editor',
			'content',
			'activeusers',
			'activereports',
			'activeusers2',
			'shoutid',
			'archive',
			'menucode',
			'pmuserid',
			'chatroomid',
			'roomname',
			'pmtime',
		);
		
		foreach ($tags as $tagname)
		{
			if (isset(self::$fetched["$tagname"]))
			{
				// Include this tag
				$xml->add_tag("$tagname", self::$fetched["$tagname"]);
			}
		}
		
		if (self::$fetched['chatroomids'])
		{
			// Add the shouts group
			$xml->add_group('chatroomids');
			
			// Go through every shout
			foreach (self::$fetched['chatroomids'] as $chatroomid)
			{
				// Add the shout
				$xml->add_tag('chatroomid2', $chatroomid);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if (self::$fetched['roomnames'])
		{
			// Add the shouts group
			$xml->add_group('roomnames');
			
			// Go through every shout
			foreach (self::$fetched['roomnames'] as $roomname)
			{
				// Add the shout
				$xml->add_tag('roomname2', $roomname);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if (self::$fetched['usernames'])
		{
			// Add the shouts group
			$xml->add_group('usernames');
			
			// Go through every shout
			foreach (self::$fetched['usernames'] as $username)
			{
				// Add the shout
				$xml->add_tag('username2', $username);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if (self::$fetched['aoptimes'])
		{
			// Add the shouts group
			$xml->add_group('aoptimes');
			
			// Go through every shout
			foreach (self::$fetched['aoptimes'] as $aoptime)
			{
				// Add the shout
				$xml->add_tag('aoptime2', $aoptime);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if (self::$fetched['tabids'])
		{
			// Add the shouts group
			$xml->add_group('tabids');
			
			// Go through every shout
			foreach (self::$fetched['tabids'] as $tabid)
			{
				// Add the shout
				$xml->add_tag('tabid2', $tabid);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		if (self::$fetched['shouts'])
		{
			// Add the shouts group
			$xml->add_group('shouts');
			
			// Go through every shout
			foreach (self::$fetched['shouts'] as $shoutinfo)
			{
				/*				
				$styleprops = array();
				
				if ($shoutarray['styleprops']['bold'])
				{
					// Set bold font
					$styleprops['bold'] = $shoutarray['styleprops']['bold'];
				}
				
				if ($shoutarray['styleprops']['italic'])
				{
					// Set italic font
					$styleprops['italic'] = $shoutarray['styleprops']['italic'];
				}
				
				if ($shoutarray['styleprops']['underline'])
				{
					// Set underline font
					$styleprops['underline'] = $shoutarray['styleprops']['underline'];
				}
				
				if ($shoutarray['styleprops']['color'])
				{
					// Set font color
					$styleprops['color'] = $shoutarray['styleprops']['color'];
				}
				
				if ($shoutarray['styleprops']['font'])
				{
					// Set font face
					$styleprops['font'] = $shoutarray['styleprops']['font'];
				}
				*/
				
				// Add the shout
				$xml->add_tag('shout', $shoutinfo);
			}
			
			// Close shouts group
			$xml->close_group();
		}
		
		$xml->close_group();
		$xml->print_xml();
	}
	
	/**
	* Processes an AJAX fetching request using AOP.
	*
	* @param	string	When we last fetched shouts
	*/
	public function fetch_aop($tabid, $instanceid)
	{
		if (!is_writable(DIR . '/dbtech/vbshout/aop/'))
		{
			// Fall back to database
			self::$fetched['error'] = $vbphrase['dbtech_vbshout_aop_error'];
			
			// Time now
			$mtime = TIMENOW;
		}
		
		// File system
		$mtime = intval(@file_get_contents(DIR . '/dbtech/vbshout/aop/' . $tabid . $instanceid . '.txt'));
		
		if (!$mtime)
		{
			$mtime = 0;
		}
		
		if ((TIMENOW - $mtime) >= 60)
		{
			// Reset AOP
			self::set_aop($tabid, $instanceid, false);
			return false;
		}
		
		if ($mtime > $aoptime)
		{
			// Include the new AOP time
			self::$fetched['aoptimes'][] = $mtime;
			self::$fetched['tabids'][] = $tabid;					
		}
		/*
		else
		{
			// Query for active users
			$activeusers = self::$vbulletin->db->query_first_slave(self::print_activeusers_query());
			self::$fetched['activeusers'] = $activeusers['numactiveusers'];			
		}
		*/
	}
	
	/**
	* Sets the new AOP time.
	*/
	public function set_aop($tabid, $instanceid = 0, $markread = true)
	{
		// Ensure this is taken into account
		clearstatcache();
		
		if (!is_writable(DIR . '/dbtech/vbshout/aop'))
		{
			// Fall back to database
			self::$fetched['error'] = $vbphrase['dbtech_vbshout_aop_error'];
			return false;			
			//self::$instance['options']['optimisation'] = 2;
		}
		
		// Touch the files
		@file_put_contents(DIR . '/dbtech/vbshout/aop/' . $tabid . $instanceid . '.txt', TIMENOW);
		
		if ($markread)
		{
			// Duplicate this
			@file_put_contents(DIR . '/dbtech/vbshout/aop/markread-' . $tabid . $instanceid . '.txt', TIMENOW);
		}
		
		// Include the new AOP time
		self::$fetched['aoptimes'][] = TIMENOW;
		self::$fetched['tabids'][] = $tabid;					
	}
	
	/**
	* Kill an AOP file.
	*/
	public function kill_aop($tabid, $instanceid)
	{
		// Ensure this is taken into account
		clearstatcache();
		
		// Touch the file
		@unlink(DIR . '/dbtech/vbshout/aop/' . $tabid . $instanceid . '.txt');
		
		// Include the new AOP time
		self::$fetched['aoptimes'][] = TIMENOW;
		self::$fetched['tabids'][] = $tabid;					
	}
	
	/**
	* Fetches shouts based on parameters.
	*
	* @param	array		(Optional) Additional arguments
	*/
	public function fetch_shouts($args = array())
	{
		global $vbphrase;
		
		foreach (array(
			'dbtech_vbshout_activeusers',
			'dbtech_vbshout_editortools_pro',
			'dbtech_vbshout_menu',
			'dbtech_vbshout_shoutbox',
			'dbtech_vbshout_css',
			'dbtech_vbshout_css_pro',
			'dbtech_vbshout_editortools',
			'dbtech_vbshout_shoutarea_horizontal',
			'dbtech_vbshout_shoutarea_vertical',
			'dbtech_vbshout_shoutcontrols',						
			'dbtech_vbshout_shouttype_me',
			'dbtech_vbshout_shouttype_pm',
			'dbtech_vbshout_shouttype_shout',
			'dbtech_vbshout_shouttype_system'
		) AS $templatename)
		{
			// Register the instance variable on all these
			if (intval(self::$vbulletin->versionnumber) != 3)
			{
				// Register the instance variable on all these
				vB_Template::preRegister($templatename, array('instance' => self::$instance));
			}
			else
			{
				// vB3 code
				$GLOBALS['instance'] = self::$instance;
			}
		}
		
		// Cache array for fetch_musername()
		$shoutusers = array();
		
		// Various SQL hooks
		$hook_query_select = $hook_query_join = $hook_query_and = '';
		
		if ($args['type'] == -1 OR !$args['types'])
		{
			// Everything
			$hook_query_and .= 'AND (
				vbshout.userid IN(-1, ' . self::$vbulletin->userinfo['userid'] . ') OR
				vbshout.id IN(0, ' . self::$vbulletin->userinfo['userid'] . ')
			)';				// That either system or us posted, or was a message to us/anybody
			
			if (is_array($args['excludetypes']))
			{
				// Exclude types
				$hook_query_and .= 'AND vbshout.type NOT IN(' . implode(',', $args['excludetypes']) . ')';
			}
		}
		else
		{
			$types = array();
			foreach (self::$shouttypes as $key => $val)
			{
				// Go through all shout types
				if ($args['types'] & self::$shouttypes[$key])
				{
					switch ($key)
					{
						case 'shout':
							if ($args['onlyuser'])
							{
								// Every PM posted by us to the user
								// or to us
								$hook_query_and .= "AND vbshout.userid = '" . intval($args['onlyuser']) . "'";
							}
							break;
						
						case 'pm':
							if ($args['onlyuser'])
							{
								// Every PM posted by us to the user
								// or to us
								$hook_query_and .= 'AND (
									vbshout.userid = ' . self::$vbulletin->userinfo['userid'] . ' AND
										vbshout.id = ' . intval($args['onlyuser']) . '
								) OR (
									vbshout.id = ' . self::$vbulletin->userinfo['userid'] . ' AND
										vbshout.userid = ' . intval($args['onlyuser']) . '
								)';
							}
							break;
					}
					
					// Set the type
					$types[] = self::$shouttypes[$key];
				}
			}
			
			// Include all our types
			$hook_query_and .= 'AND vbshout.type IN(' . implode(',', $types) . ')';
		}
		
		// Fetch the shout order
		$shoutorder = self::$vbulletin->input->clean_gpc(self::$fetchtype, 'shoutorder', TYPE_STR);
		$shoutorder = (in_array($shoutorder, array('ASC', 'DESC')) ? $shoutorder : self::$instance['options']['shoutorder']);
		
		$hook_query_and .= " AND vbshout.chatroomid = " . self::$vbulletin->db->sql_prepare(intval($args['chatroomid']));
		
		if (self::$instance['options']['activeusers'])
		{
			self::fetch_active_users(true, true);
			if ($args['chatroomid'])
			{
				// Array of all active users
				self::$fetched['activeusers2'] = (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_chat_users']);
				if (self::$instance['options']['enableaccess'])
				{
					self::$fetched['activeusers2'] .= '<br /><br /><a href="vbshout.php?' . self::$vbulletin->session->vars['sessionurl'] . 'do=chataccess&amp;instanceid=' . self::$instance['instanceid'] . '&amp;chatroomid=' . $args['chatroomid'] . '" target="_blank"><b>' . $vbphrase['dbtech_vbshout_chat_access'] . '</b></a>';
				}
		
			}
			else
			{
				// Array of all active users
				self::$fetched['activeusers2'] = (count(self::$activeusers) ? implode('<br />', self::$activeusers) : $vbphrase['dbtech_vbshout_no_active_users']);
			}
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_fetch_shouts_query')) ? eval($hook) : false;
		
		// Query the shouts
		$shouts_q = self::$vbulletin->db->query_read_slave("
			SELECT
				user.avatarid,
				user.avatarrevision,
				user.username,
				user.usergroupid,
				user.membergroupids,
				user.infractiongroupid,
				user.displaygroupid,
				user.dbtech_vbshout_settings AS shoutsettings,
				user.dbtech_vbshout_shoutstyle AS shoutstyle" . (self::$vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . ",
				vbshout.*
				" . (self::$vbulletin->options['avatarenabled'] ? ', avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline, customavatar.width AS avwidth, customavatar.height AS avheight, customavatar.height_thumb AS avheight_thumb, customavatar.width_thumb AS avwidth_thumb, customavatar.filedata_thumb' : '') . ",
				pmuser.username AS pmusername
				$hook_query_select
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
			" . (self::$vbulletin->options['avatarenabled'] ? "
			LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON (avatar.avatarid = user.avatarid)
			LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON (customavatar.userid = user.userid)
			" : '') . "
			LEFT JOIN " . TABLE_PREFIX . "user AS pmuser ON(pmuser.userid = vbshout.id)			
			$hook_query_join
			WHERE vbshout.instanceid IN(-1, 0, " . intval(self::$instance['instanceid']) . ")
				AND vbshout.userid NOT IN(
					SELECT ignoreuserid
					FROM " . TABLE_PREFIX . "dbtech_vbshout_ignorelist AS ignorelist
					WHERE userid = " . self::$vbulletin->userinfo['userid'] . "
				)
				AND vbshout.forumid IN(" . implode(',', self::fetch_forumids()) . ")
				$hook_query_and
			ORDER BY dateline DESC
			LIMIT " . (self::$instance['options']['maxshouts'] ? self::$instance['options']['maxshouts'] : 20)
		);
		
		if (!self::$vbulletin->db->num_rows($shouts_q))
		{
			// We have no shouts
			self::$fetched['content'] = $vbphrase['dbtech_vbshout_nothing_to_display'];
			return false;
		}
		
		// Set sticky		
		self::$fetched['sticky'] = self::$instance['sticky'];
		
		// Set active users
		$activeusers = self::$vbulletin->db->query_first_slave(self::print_activeusers_query());
		self::$fetched['activeusers'] = $activeusers['numactiveusers'];
		
		// Re-add this, lol
		self::$shoutstyle = @unserialize(self::$vbulletin->userinfo['dbtech_vbshout_shoutstyle']);		
		
		$i = 1;
		while ($shouts_r = self::$vbulletin->db->fetch_array($shouts_q))
		{
			// Parses action codes like /me
			self::parse_action_codes($shouts_r['message'], $shouts_r['type']);

			// By default, we can't pm or edit
			$canpm = $canedit = false;

			if ($shouts_r['userid'] > -1)
			{
				if (!$shoutusers["$shouts_r[userid]"])
				{
					// Uncached user
					$shoutusers["$shouts_r[userid]"] = array(
						'userid' 					=> $shouts_r['userid'],
						'username' 					=> $shouts_r['username'],
						'usergroupid' 				=> $shouts_r['usergroupid'],
						'infractiongroupid' 		=> $shouts_r['infractiongroupid'],
						'displaygroupid' 			=> $shouts_r['displaygroupid'],
						'dbtech_vbshop_purchase' 	=> $shouts_r['dbtech_vbshop_purchase']
					);
				}
				
				// fetch the markup-enabled username
				fetch_musername($shoutusers["$shouts_r[userid]"]);
				
				if ($shouts_r['userid'] != self::$vbulletin->userinfo['userid'])
				{
					// We can PM this user
					$canpm = true;
				}
			}
			else
			{
				// This was the SYSTEM
				$shoutusers["$shouts_r[userid]"] = array(
					'userid' 	=> 0,
					'username' 	=> $vbphrase['dbtech_vbshout_system'],
					'musername' => $vbphrase['dbtech_vbshout_system'],
				);
				
				// We can't PM the system
				$canpm = false;
			}
			
			// Only registered users can have shoutbox styles
			if (!$shouts_r['shoutstyle'] = unserialize($shouts_r['shoutstyle']))
			{
				// This shouldn't be false
				$shouts_r['shoutstyle'] = array();
			}
			
			// Ensure it's an array for the sake of bugfix
			$instanceid = self::$instance['instanceid'];
			$shouts_r['shoutstyle'] = (!$shouts_r['shoutstyle']["$instanceid"] ? array() : $shouts_r['shoutstyle']["$instanceid"]);
			
			// Init the styleprops
			$styleprops = array();
			
			if (self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 8192)
			{
				// Override!
				$shouts_r['shoutstyle'] = self::$shoutstyle["$instanceid"];
			}
			
			if (self::$instance['options']['editors'] & 1 AND $shouts_r['shoutstyle']['bold'] > 0)
			{
				// Bold
				$styleprops[] = 'font-weight:bold;';
			}
			
			if (self::$instance['options']['editors'] & 2 AND $shouts_r['shoutstyle']['italic'] > 0)
			{
				// Italic
				$styleprops[] = 'font-style:italic;';
			}
			
			if (self::$instance['options']['editors'] & 4 AND $shouts_r['shoutstyle']['underline'] > 0)
			{
				// Underline
				$styleprops[] = 'text-decoration:underline;';
			}
			
			if (self::$instance['options']['editors'] & 16 AND $shouts_r['shoutstyle']['font'])
			{
				// Font
				$styleprops[] = 'font-family:' . $shouts_r['shoutstyle']['font'] . ';';
			}
			
			if (self::$instance['options']['editors'] & 8 AND $shouts_r['shoutstyle']['color'])
			{
				// Color
				$styleprops[] = 'color:' . $shouts_r['shoutstyle']['color'] . ';';
			}			
			
			if (($shouts_r['userid'] == self::$vbulletin->userinfo['userid'] AND self::$instance['permissions_parsed']['caneditown']) OR
				($shouts_r['userid'] != self::$vbulletin->userinfo['userid'] AND self::$instance['permissions_parsed']['caneditothers']))
			{
				// We got the perms, give it to us
				$canedit = true;
			}
			
			switch ($shouts_r['type'])
			{
				case self::$shouttypes['me']:
				case self::$shouttypes['notif']:
					// slash me or notification
					$time = vbdate(self::$vbulletin->options['timeformat'], 	$shouts_r['dateline'], self::$vbulletin->options['yestoday']);
					break;
					
				default:
					// Everything else
					$time = '[' . vbdate(self::$vbulletin->options['dateformat'], 	$shouts_r['dateline'], self::$vbulletin->options['yestoday']) . ' ' .
							vbdate(self::$vbulletin->options['timeformat'], 	$shouts_r['dateline'], self::$vbulletin->options['yestoday']) . ']';
					break;
			}
			
			// Get our usergroup permissions
			cache_permissions($shouts_r, false);
			
			// By default, we can't add infractions
			self::$instance['permissions_parsed']['giveinfraction'] = (
				// Must have 'cangiveinfraction' permission. Branch dies right here majority of the time
				self::$vbulletin->userinfo['permissions']['genericpermissions'] & self::$vbulletin->bf_ugp_genericpermissions['cangiveinfraction']
				// Can not give yourself an infraction
				AND $shouts_r['userid'] != self::$vbulletin->userinfo['userid']
				// Can not give an infraction to a post that already has one
				// Can not give an admin an infraction
				AND !($shouts_r['permissions']['adminpermissions'] & self::$vbulletin->bf_ugp_adminpermissions['cancontrolpanel'])
				// Only Admins can give a supermod an infraction
				AND (
					!($shouts_r['permissions']['adminpermissions'] & self::$vbulletin->bf_ugp_adminpermissions['ismoderator'])
					OR self::$vbulletin->userinfo['permissions']['adminpermissions'] & self::$vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
				)
			);

			($hook = vBulletinHook::fetch_hook('dbtech_vbshout_fetch_shouts_loop')) ? eval($hook) : false;

			// Store all the information regarding a shout 
			$shout = array(
				'shoutid' 		=> $shouts_r['shoutid'],
				'userid' 		=> $shouts_r['userid'],
				'time'			=> $time,
				'jsusername'	=> addslashes($shouts_r['username']),
				'username'		=> $shouts_r['username'],
				'musername'		=> $shoutusers["$shouts_r[userid]"]['musername'],
				'usertitle'		=> $shoutusers["$shouts_r[userid]"]['displayusertitle'],
				'message'		=> $shouts_r['message'],
				'message_raw'	=> htmlspecialchars_uni($shouts_r['message_raw']),
				'shoutuserinfo'	=> array(
					'userid'	=> $shouts_r['userid'],
					'username'	=> $shouts_r['username']
				),
				'canedit'		=> $canedit,
				'canpm'			=> $canpm,
				'isprotected' 	=> self::check_protected_usergroup($shouts_r, true),
			);
			
			// Per-usergroup switch
			$domenu = (self::$instance['permissions_parsed']['enablemenu'] ? true : false);
			
			// Global shut-off switch
			$domenu = (self::$instance['options']['enablemenu'] ? $domenu : false);			
			
			if ($domenu)
			{
				// Generate the dropdown
				$shout['memberaction_dropdown'] = self::create_memberaction_dropdown(
					$shout['userid'],
					$shout['username'],
					$shout['jsusername'],
					$shout['musername'],
					$shout['usertitle'],
					$shout['canpm'],
					$shout['isprotected'],
					$shout['shoutid']
				);
			}
			else
			{
				// Fetch the SEO'd URL to a member's profile
				if (intval(self::$vbulletin->versionnumber) == 3)
				{
					$shout['memberaction_dropdown'] = '<a href="member.php?' . self::$vbulletin->session->vars['sessionurl'] . 'u=' . $shout['userid'] . '" target="_blank">' . $shout['musername'] . '</a>';
				}
				else
				{
					$shout['memberaction_dropdown'] = '<a href="' . fetch_seo_url('member', $shout) . '" target="_blank">' . $shout['musername'] . '</a>';
				}				
			}
			
			switch ($shouts_r['type'])
			{
				case self::$shouttypes['shout']:
					// Normal shout
					$template = 'shout';
					break;
					
				case self::$shouttypes['pm']:
					// PM
					$template = 'pm';
					break;
					
				case self::$shouttypes['me']:
				case self::$shouttypes['notif']:
					// slash me or a notification
					$template = 'me';
					break;
					
				default:
					// Error handler
					$template = 'shout';
					break;
			}
			
			if ($shouts_r['userid'] == -1)
			{
				// System message
				$template = 'system';
			}
			
			/*
			// Create the template rendering engine
			$templater = vB_Template::create('dbtech_vbshout_shouttype_' . $template);
				$templater->quickRegister($shout);
				//$templater->register('styleprops', implode(' ', $styleprops));
			*/
			
			$altclass = 'alt1';
			if (self::$instance['options']['altshouts'] AND !((int)self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 131072))
			{
				$altclass = ($i % 2 == 0 ? ' alt2' : ' alt1');
			}
			
			self::$fetched['shouts'][] = vbshout_json_encode(array(
				'template'				=> $template,
				'shoutid' 				=> $shouts_r['shoutid'],
				'instanceid' 			=> self::$instance['instanceid'],
				'message_raw'			=> str_replace(array("%", "$", "\\"), array("&#37;", "&#36;", "\\\\"), htmlspecialchars_uni($shouts_r['message_raw'])),
				'canedit'				=> ($canedit ? " ondblclick=\"return vBShout" . self::$instance['instanceid'] . ".edit_shout('$shouts_r[shoutid]');\"" : ''),				
				'time'					=> $time,
				'musername'				=> str_replace(array("%", "$"), array("&#37;", "&#36;"), $shoutusers["$shouts_r[userid]"]['musername']),
				'memberaction_dropdown' => str_replace(array("%", "$"), array("&#37;", "&#36;"), $shout['memberaction_dropdown']),
				'styleprops' 			=> implode(' ', $styleprops),
				'message'				=> str_replace(array("%", "$", "\\"), array("&#37;", "&#36;", "\\\\"), $shouts_r['message']),
				'pmuser'				=> $shouts_r['pmusername'],
				'altclass'				=> $altclass,
			));
			
			//self::$fetched['shouts'][] = array('template' => $templater->render());
			
			$i++;
		}
		
		if ($shoutorder == 'ASC')
		{
			// Reverse sort order
			krsort(self::$fetched['shouts']);
		}
		
		if (!self::$fetched['shouts'])
		{
			// Show no content
			self::$fetched['content'] = $vbphrase['dbtech_vbshout_nothing_to_display'];
		}
		
		// No longer needed
		unset($shoutusers, $shout);
	}
	
	/**
	* Checks for action codes, and executes their meaning.
	* 
	* @param	string	The shout.
	* @param	string	The default shout type.
	* @param	integer	(Optional) The default id.
	* @param	integer	(Optional) The default userid.
	*
	* @return	mixed	Any new information we may have.
	*/
	public function parse_action_codes(&$message, &$type)
	{
		global $vbphrase;
		
		if (preg_match("#^(\/[a-z]*?)\s(.+?)$#i", $message, $matches))
		{
			// 2-stage command
			switch ($matches[1])
			{
				case '/me':
					// A slash me
					$message 	= trim($matches[2]);
					$type 		= self::$shouttypes['me'];
					break;
					
				default:
					($hook = vBulletinHook::fetch_hook('dbtech_vbshout_parsecommand_2')) ? eval($hook) : false;
					break;				
			}
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_command_complete')) ? eval($hook) : false;
		
		return array($retval['type'], $retval['id'], $retval['userid']);
	}
	
	/**
	* Checks for a protected usergroup
	*
	* @param	array	Usergroup information
	* @param	boolean	(Optional) Whether we should just return boolean
	*/
	public function check_protected_usergroup($exists, $boolreturn = false)
	{
		global $vbphrase;
		
		// Loads instance permissions
		$permarray = self::load_instance_permissions(self::$instance, $exists);
		
		if ($permarray['isprotected'])
		{
			if (!$boolreturn)
			{
				// Flag for clearance
				//self::$fetched['clear'] = 'editor';
				
				// Err0r
				self::$fetched['error'] = construct_phrase($vbphrase['dbtech_vbshout_x_is_protected'], $exists['username']);
			}
			return true;
		}
		
		return false;
	}
	
	/**
	* Logs a specified command.
	*
	* @param	string	The executed command.
	* @param	mixed	(Optional) Additional comments.
	*/
	public function log_command($command, $comment = NULL)
	{
		$bit = 0;
		switch ($command)
		{
			case 'shoutedit':
			case 'shoutdelete':
				$bit = 8;
				break;
			
			case 'prune':
				$bit = 1;
				break;
			
			case 'setsticky':
			case 'removesticky':
				$bit = 2;
				break;
				
			case 'ban':
			case 'unban':
				$bit = 4;
				break;
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_vbshout_log_process')) ? eval($hook) : false;
		
		if (!$bit OR !(self::$instance['options']['logging'] & $bit))
		{
			// We didn't have this option on
			return;
		}
		
		self::$vbulletin->db->query_write("
			INSERT INTO " . TABLE_PREFIX . "dbtech_vbshout_log
				(userid, dateline, ipaddress, command, comment)
			VALUES (
				" . self::$vbulletin->db->sql_prepare(self::$vbulletin->userinfo['userid']) . ",
				" . self::$vbulletin->db->sql_prepare(TIMENOW) . ",
				" . self::$vbulletin->db->sql_prepare(IPADDRESS) . ",
				" . self::$vbulletin->db->sql_prepare($command) . ",
				" . self::$vbulletin->db->sql_prepare($comment) . "
			)
		");
	}
	
	/**
	* Determines the replacement for the BBCode SIZE limiter.
	*
	* @param	integer	The attempted SIZE value.
	*
	* @return	string	The new SIZE BBCode.
	*/
	public function process_bbcode_size($size)
	{
		// Returns the prepared string
		return '[size=' . (intval($size) > self::$instance['options']['maxsize'] ? self::$instance['options']['maxsize'] : $size) . ']';
	}
	
	/**
	* Prints the query for fetching active users
	*
	* @return	string	The query code.
	*/
	private function print_activeusers_query()
	{
		return "
			SELECT COUNT(DISTINCT userid) AS numactiveusers
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
			WHERE dateline >= " . (TIMENOW - (self::$instance['options']['idletimeout'] ? self::$instance['options']['idletimeout'] : 600)) . "
					AND vbshout.instanceid = " . intval(self::$instance['instanceid']) . "
				" . (self::$chatroom ? "AND vbshout.chatroomid = " . intval(self::$chatroom['chatroomid']) : 'AND vbshout.chatroomid = 0') . "
				AND userid > 0
		";
	}
	
	/**
	* Fetch all currently active users.
	*/	
	private function fetch_active_users($domenu = false, $force = false, $chatroomid = false)
	{
		global $vbphrase;
		
		if ($domenu)
		{
			// Per-usergroup switch
			$domenu = (self::$instance['permissions_parsed']['enablemenu'] ? true : false);
			
			// Global shut-off switch
			$domenu = (self::$instance['options']['enablemenu'] ? $domenu : false);
		}
		
		if (empty(self::$activeusers) OR $force)
		{
			// Array of all active users
			self::$activeusers = array();
			
			// Query active users
			$activeusers_q = self::$vbulletin->db->query_read_slave("
				SELECT
					DISTINCT user.userid,
					username,
					usergroupid,
					membergroupids,
					infractiongroupid,
					displaygroupid,
					user.dbtech_vbshout_settings AS shoutsettings
					" . (self::$vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : '') . "
				FROM " . TABLE_PREFIX . "dbtech_vbshout_shout AS vbshout
				LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = vbshout.userid)
				WHERE vbshout.dateline >= " . (TIMENOW - (self::$instance['options']['idletimeout'] ? self::$instance['options']['idletimeout'] : 600)) . "
					AND vbshout.userid > 0	
					" . (!self::$chatroom ? "AND vbshout.instanceid = " . intval(self::$instance['instanceid']) : '')  . "
					" . (self::$chatroom ? "AND vbshout.chatroomid = " . intval(self::$chatroom['chatroomid']) : 'AND vbshout.chatroomid = 0') . "
				ORDER BY username ASC
			");
			while ($activeusers_r = self::$vbulletin->db->fetch_array($activeusers_q))
			{
				// fetch the markup-enabled username
				fetch_musername($activeusers_r);
				
				if (!$domenu)
				{					
					// Fetch the SEO'd URL to a member's profile
					if (intval(self::$vbulletin->versionnumber) == 3)
					{
						self::$activeusers[] = '<a href="member.php?' . self::$vbulletin->session->vars['sessionurl'] . 'u=' . $activeusers_r['userid'] . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
					}
					else
					{
						self::$activeusers[] = '<a href="' . fetch_seo_url('member', $activeusers_r) . '" target="_blank">' . $activeusers_r['musername'] . '</a>';
					}
				}
				else
				{
					// Ensure this is set
					$activeusers_r['jsusername'] = addslashes($activeusers_r['username']);
					
					$canpm = ($activeusers_r['userid'] != self::$vbulletin->userinfo['userid']);
					if (self::$isPro)
					{
						if (!($activeusers_r['shoutsettings'] & 128) OR !(self::$vbulletin->userinfo['dbtech_vbshout_settings'] & 128) OR !self::$instance['options']['enablepms'])
						{
							// You plain can't pm this person or PMs are disabled globally
							$canpm = false;
						}
						
						if (!self::$instance['permissions_parsed']['canpm'])
						{
							// We don't have permissions to PM
							$canpm = false;
						}
					}
					
					// Fetch the SEO'd URL to a member's profile
					self::$activeusers[] = self::create_memberaction_dropdown(
						$activeusers_r['userid'],
						$activeusers_r['username'],
						$activeusers_r['jsusername'],
						$activeusers_r['musername'],
						$activeusers_r['usertitle'],
						$canpm,
						self::check_protected_usergroup($activeusers_r, true)
					);
				}
			}
			
		}
	}
	
	/**
	* Creates the member action dropdown template.
	*
	* @param	integer	User ID
	* @param	string	User Name
	* @param	string	JS-Safe User Name
	* @param	string	Markup User Name
	* @param	string	User Title
	* @param	boolean	User ID
	* @param	boolean	User ID
	* 
	* @return	string	The rendered template
	*/	
	private function create_memberaction_dropdown($userid, $username, $jsusername, $musername, $usertitle, $canpm, $isprotected, $shoutid = 0)
	{
		self::$i++;
		
		// Get the dropdown template
		$templater = vB_Template::create('dbtech_vbshout_memberaction_dropdown_link');
			$templater->register('memberinfo', 	$memberinfo);
			$templater->register('userid', 		$userid);
			$templater->register('username', 	$username);
			$templater->register('jsusername', 	$jsusername);
			$templater->register('musername', 	$musername);
			$templater->register('usertitle', 	$canpm);
			$templater->register('canpm', 		$canpm);
			$templater->register('isprotected', $isprotected);
			$templater->register('instance', 	self::$instance);
			$templater->register('i', 			self::$i);
		self::$dropdown[self::$instance['instanceid']][$userid]['template'] = $templater->render();
		
		if (self::$dropdown[self::$instance['instanceid']][$userid]['cached'])
		{
			return self::$dropdown[self::$instance['instanceid']][$userid]['template'];
		}
		
		// Temp array
		$memberinfo = array('userid' => $userid, 'username' => $username);
		
		// Get the dropdown template
		$templater = vB_Template::create('dbtech_vbshout_memberaction_dropdown');
			$templater->register('memberinfo', 	$memberinfo);
			$templater->register('userid', 		$userid);
			$templater->register('username', 	$username);
			$templater->register('jsusername', 	$jsusername);
			$templater->register('musername', 	$musername);
			$templater->register('usertitle', 	$canpm);
			$templater->register('canpm', 		$canpm);
			$templater->register('isprotected', $isprotected);
			$templater->register('permissions', self::$instance['permissions_parsed']);
			$templater->register('instance', 	self::$instance);
			$templater->register('chatroom', 	self::$chatroom);
			$templater->register('shoutid', 	$shoutid);
		self::$fetched['menucode'] .= $templater->render();
		
		// We have now cached the menu code
		self::$dropdown[self::$instance['instanceid']][$userid]['cached'] = true;
		
		return self::$dropdown[self::$instance['instanceid']][$userid]['template'];
	}
	
	/**
	* Fetches all forumids we are allowed access to.
	* 
	* @return	array	The list of forumids we can access
	*/
	public function fetch_forumids()
	{
		$forumcache = self::$vbulletin->forumcache;
		/*
		$excludelist = explode(',', self::$vbulletin->options['dbtech_infopanels_forum_exclude']);
		foreach ($excludelist AS $key => $excludeid)
		{
			$excludeid = intval($excludeid);
			unset($forumcache["$excludeid"]);
		}
		*/
	
		$forumids = array_keys($forumcache);
		
		// get forum ids for all forums user is allowed to view
		foreach ($forumids AS $key => $forumid)
		{
			if (is_array($includearray) AND empty($includearray["$forumid"]))
			{
				unset($forumids[$key]);
				continue;
			}
	
			$fperms =& self::$vbulletin->userinfo['forumpermissions']["$forumid"];
			$forum =& self::$vbulletin->forumcache["$forumid"];
	
			if (!($fperms & self::$vbulletin->bf_ugp_forumpermissions['canview']) OR !($fperms & self::$vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR !verify_forum_password($forumid, $forum['password'], false))
			{
				unset($forumids[$key]);
			}
		}
		
		// Those shouts with 0 as their forumid
		$forumids[] = 0;
		
		return $forumids;
	}
		
	/**
	* Rebuilds the shout counter for every user.
	*/
	public function build_shouts_counter()
	{
		// Begin shout counter
		$counters = array();
		
		// Grab all shouts
		$shouts_q = self::$vbulletin->db->query_read_slave("
			SELECT userid, shoutid
			FROM " . TABLE_PREFIX . "dbtech_vbshout_shout
		");
		while ($shouts_r = self::$vbulletin->db->fetch_array($shouts_q))
		{
			// Build shout counters
			$counters["$shouts_r[userid]"]++;
			
		}
		self::$vbulletin->db->free_result($shouts_q);
		unset($shouts_r);	
		
		$cases = array();
		foreach ($counters as $userid => $shouts)
		{
			// Set the case
			$cases[] = "WHEN $userid THEN $shouts";
		}
		
		if (count($cases))
		{
			// Finally update the user table
			self::$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "user
				SET dbtech_vbshout_shouts = CASE userid
				" . implode(' ', $cases) . "
				ELSE 0 END
			");
		}
	}
	
	/**
	* Leaves the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public function leave_chatroom(&$chatroom, $userid)
	{
		$SQL = '';
		if ($chatroom['creator'] == $userid)
		{
			$null = array();
			
			// init data manager
			$dm =& VBSHOUT::datamanager_init('Chatroom', self::$vbulletin, ERRTYPE_ARRAY);
				$dm->set_existing($chatroom);
				$dm->set('active', 	'0');
				$dm->set('members', $null);
			$dm->save();
		}
		else
		{
			// We weren't the creator, only we should abandon ship
			$SQL = "AND userid = " . self::$vbulletin->db->sql_prepare($userid);
		}
		
		
		// Leave the chat room
		self::$vbulletin->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
			WHERE chatroomid = " . self::$vbulletin->db->sql_prepare($chatroom['chatroomid']) . 
				$SQL
				. ($status ? " AND status = 0" : '')
		);
		
		if ($SQL)
		{
			// init data manager
			$dm =& VBSHOUT::datamanager_init('Chatroom', self::$vbulletin, ERRTYPE_ARRAY);
				$dm->set_existing($chatroom);
				
			unset($chatroom['members']["$userid"]);
			
				$dm->set('members', $chatroom['members']);
			$dm->save();
		}
	}
	
	/**
	* Joins the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public function join_chatroom(&$chatroom, $userid)
	{
		// Join the chat room
		self::$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
			SET status = 1
			WHERE chatroomid = " . self::$vbulletin->db->sql_prepare($chatroom['chatroomid']) . "
				AND userid = " . self::$vbulletin->db->sql_prepare($userid) . "
		");	
		
		// init data manager
		$dm =& VBSHOUT::datamanager_init('Chatroom', self::$vbulletin, ERRTYPE_ARRAY);
			$dm->set_existing($chatroom);
			
		// We're now fully joined
		$chatroom['members']["$userid"] = '1';
					
			$dm->set('members', 	$chatroom['members']);
		$dm->save();
	}
	
	/**
	* Creates the chatroom
	*
	* @param	array	The chat room being left
	* @param	integer	The userid leaving the chat
	*/
	public function invite_chatroom(&$chatroom, $userid, $invitedby)
	{
		// Invite to join the chat room
		self::$vbulletin->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_vbshout_chatroommember
				(chatroomid, userid, status, invitedby)
			VALUES (
				" . self::$vbulletin->db->sql_prepare($chatroom['chatroomid']) . ",
				" . self::$vbulletin->db->sql_prepare($userid) . ",
				0,
				" . self::$vbulletin->db->sql_prepare(self::$vbulletin->userinfo['userid']) . "				
			)
		");
		
		if (self::$vbulletin->db->affected_rows())
		{
			// init data manager
			$dm =& VBSHOUT::datamanager_init('Chatroom', self::$vbulletin, ERRTYPE_ARRAY);
				$dm->set_existing($chatroom);
				
			// We're now fully joined
			$chatroom['members']["$userid"] = '0';
						
				$dm->set('members', 	$chatroom['members']);
			$dm->save();
		}
	}
	
	/**
	* Fetches what chatrooms we're a member of
	*
	* @param	array	The user info we're checking membership of
	* @param	mixed	Whether we're checking a status or not
	* @param	mixed	Whether we're checking an instanceid or not
	*/
	public function fetch_chatroom_memberships($userinfo, $status = NULL, $instanceid = NULL)
	{
		$memberof = array();
		foreach ((array)self::$cache['chatroom'] as $chatroomid => $chatroom)
		{
			if (!$chatroom['active'])
			{
				// Inactive chatroom
				continue;
			}
			
			if ($instanceid !== NULL)
			{
				if ($chatroom['instanceid'] != $instanceid AND $chatroom['instanceid'] != 0)
				{
					// Skip this instance id
					continue;
				}
			}
			
			if ($chatroom['membergroupids'])
			{
				if (is_member_of($userinfo, explode(',', $chatroom['membergroupids'])))
				{
					// Do join it
					$memberof[] = $chatroomid;
				}
			}
			else
			{
				if (!isset($chatroom['members']["{$userinfo[userid]}"]))
				{
					// We're not a part this
					continue;
				}
				
				if ($status !== NULL AND $chatroom['members']["{$userinfo[userid]}"] !== $status)
				{
					// Wrong status
					continue;
				}
				
				// We're a member
				$memberof[] = $chatroomid;
			}
		}
		
		return $memberof;
	}
	
	/**
	* Rebuilds the shout counter for every user.
	*
	* @param	string	The new sticky note.
	*/
	public function set_sticky($sticky)
	{
		// Store raw sticky
		$sticky_raw = $sticky;
		
		// Ensure we got BBCode Parser
		require_once(DIR . '/includes/class_bbcode.php');
		if (!function_exists('convert_url_to_bbcode'))
		{
			require_once(DIR . '/includes/functions_newpost.php');
		}
		
		// Initialise the parser (use proper BBCode)
		$parser = new vB_BbCodeParser(self::$vbulletin, fetch_tag_list());
		
		if (self::$vbulletin->options['allowedbbcodes'] & 64)
		{
			// We can use the URL BBCode, so convert links
			$sticky = convert_url_to_bbcode($sticky);
		}	
		
		// BBCode parsing
		$sticky = $parser->parse($sticky, 'nonforum');		
		
		// init data manager
		$dm =& VBSHOUT::datamanager_init('Instance', self::$vbulletin, ERRTYPE_ARRAY);
			$dm->set_existing(self::$instance);
			$dm->set('sticky', 		$sticky);
			$dm->set('sticky_raw', 	$sticky_raw);
		$dm->save();
		
		// Set new sticky
		self::$instance['sticky'] = $sticky;		
	}		
}


// #############################################################################
// filter functionality class

/**
* Class that handles filtering arrays
*
* @package	Framework
* @version	$ $Rev$ $
* @date		$ $Date$ $
*/
class VBSHOUT_FILTER
{
	/**
	* Id Field we are using
	*
	* @private	string
	*/	
	private static $idfield 	= NULL;
	
	/**
	* Id value we are looking for
	*
	* @private	mixed
	*/	
	private static $idval 		= NULL;
	
	
	
	/**
	* Sets up and begins the filtering process 
	*
	* @param	array	Array to filter
	* @param	string	What the ID Field is
	* @param	mixed	What we are looking for
	*
	* @return	array	Filtered array
	*/
	public static function filter($array, $idfield, $idval)
	{
		// Set the two things we can't pass on to the callback
		self::$idfield 	= $idfield;
		self::$idval	= $idval;
		
		// Filter this shiet
		return array_filter($array, array(__CLASS__, 'do_filter'));
	}
	
	/**
	* Checks if this element should be included
	*
	* @param	array	Array to filter
	*
	* @return	boolean	Whether we should include this or not
	*/	
	protected static function do_filter($array)
	{
		$idfield 	= self::$idfield;
		$idval		= self::$idval;
		return ($array["$idfield"] == $idval);
	}
}

function vbshout_js_escape_string(&$arr)
{
	$find = array(
		"\r\n",
		"\r",
		"\n",
		"\t",
		'"'
	);
	
	$replace = array(
		'\r\n',
		'\r',
		'\n',
		'\t',
		'\"',
	);
	
	$arr = str_replace($find, $replace, $arr);
}

function vbshout_json_encode($arr, $assoc = true, $doescape = true)
{
	if ($doescape)
	{
		vbshout_js_escape_string($arr);
	}
	if (!$assoc)
	{
		// Not associative, simple return
		return '{"' . implode('","', $arr) . '"}';
	}
	
	$content = array();
	foreach ($arr as $key => $val)
	{
		$content[] = '"' . $key . '":"' . $val . '"';
	}
	$retval = '{' . implode(',', $content) . '}';
	
	return $retval;
}


/*======================================================================*\
|| ####################################################################
|| # Created: 16:52, Sat Dec 26th 2009
|| # SVN: $ $Rev$ $ - $ $Date$ $
|| ####################################################################
\*======================================================================*/