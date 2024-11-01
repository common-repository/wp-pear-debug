<?php

/*
 * Plugin Name: wp pear debug
 * Description: Troubleshoot and debug wordpress installation
 * Author: Garvin Casimir
 * Version: 1.5
 * =======================================================================
 */
//include class loader and let the magic happen 
//Loads class from format dir1.dirN.className
//className can be folder with same name as class
require_once(WP_PLUGIN_DIR.'/wp-pear-debug/lib/util/wpdutil.class.php');


//Step into plugin class to initialize
add_action('plugins_loaded',array('wp_pear_debug','init'));

//wordpress plugin completely wrapped in class
class wp_pear_debug
{

	//Option states
	const WPD_STATUS_DISABLE = 0;
	const WPD_STATUS_FRONT = 1;
	const WPD_STATUS_ADMIN = 2;
	const WPD_STATUS_BOTH = 3;
	//general status
	const WPD_ENABLE = 1;
	const WPD_DISABLE = 0;
	const WPD_PREFIX = 'wp_pear_debug_';
	
	//Script names
	const WPD_JS = 'wp_pear_debug_js';
	const WPD_CSS = 'wp_pear_debug_css';
	
	//Definitions
	const WPD_GUEST_ROLE = 'guest';	
	const WPD_STATUS = 'status';
	const WPD_OPTIONS_GROUP = 'wp_pear_debug_options';
	const WPD_SETTING_ROLE = 2;
	const WPD_SETTING_MAIN = 1;

	//Array for options handler. Define static options and their types 
	//WIll be useful in dynamic options builder in possibly 1.5
	protected static $settings = array(
									array('type'=> self::WPD_SETTING_ROLE,'key'=> self::WPD_GUEST_ROLE),
									array('type'=> self::WPD_SETTING_MAIN,'key'=> self::WPD_STATUS)
									);	
	
	//return the path to the plugin folder
	private static function path()
	{
		return WP_PLUGIN_DIR.'/wp-pear-debug';
	}
	//return the path to the plugin via wordpress url
	private static function url()
	{
		return WP_PLUGIN_URL.'/wp-pear-debug';
	}
		
	//include javascript
	public static function js()
	{ 
		wp_enqueue_script(self::WPD_JS); 
	}
	//include css
	public static function css()
	{
		wp_enqueue_style(self::WPD_CSS); 
	}
	//Configuration options for debug library
	//These really should not change for wordpress needs
	//Shis should probably be somewhere else
	public static function getConf()
	{
		return array
		(
			'render_type'          => 'HTML',
			'render_mode'          => 'Div',
			'restrict_access'      => false,
			'allow_url_access'     => false,
			'enable_watch'         => false,
			'replace_errorhandler' => true,
			'HTML_DIV_images_path' => self::url()."/lib/PHP_Debug/images",
		);
	}	
	
	//Class initialization
	public static function init()
	{
		//Load config and determine debug level for current user
		$sDebug = self::enabled();
	
		if($sDebug)
		{
			//Instruct wordpress to save queries
			//If not set in wp-config.php then set. Keep in mind we may miss some queries as a result
			if(!defined('SAVEQUERIES'))
			{
				define('SAVEQUERIES',true);
			}
			//register javascript
			wp_register_script(self::WPD_JS, "".self::url()."/lib/PHP_Debug/js/html_div.js"); 
			//register css
			wp_register_style(self::WPD_CSS, "".self::url()."/lib/PHP_Debug/css/html_div.css");
			 		
			//Include debug output for admin
			if($sDebug == self::WPD_STATUS_ADMIN || $sDebug == self::WPD_STATUS_BOTH)
			{ 
				add_action('admin_print_scripts',array('wp_pear_debug','js'));
				add_action('admin_print_styles',array('wp_pear_debug','css'));
				add_action('admin_footer',array('wp_pear_debug','show'));
			}
			//Include debug output for front end
			if( $sDebug == self::WPD_STATUS_FRONT || $sDebug == self::WPD_STATUS_BOTH )
			{ 
				add_action('wp_print_styles',array('wp_pear_debug','css'));
				add_action('wp_print_scripts',array('wp_pear_debug','js'));
				add_action('wp_footer',array('wp_pear_debug','show'));
			}		

			//Enable shortcode debug entry point
			add_shortcode('wp_pear_debug', array('wp_pear_debug','short'));
		}
		//add options page to the admin menu
		add_action('admin_menu',array('wp_pear_debug','admin'));
		//Register settings for options page
		add_action('admin_init',array('wp_pear_debug','settings'));
		
		//This needs to be standardized
		//Adapted from sample so I am just happy that it works...for now
		$plugin_dir = basename(dirname(__FILE__));
		//load language files (internationalize me)
		load_plugin_textdomain( 'wp-pear-debug', 'wp-content/plugins/' . $plugin_dir.'/lang', $plugin_dir.'/lang' );

		//Ok everything is set begin debugging
		//Mostly not useful
		self::add("Begin Debugging");
		self::error("Begin Debugging");
	}
	
	//not using globals
	//use this to get single instance of debug class
	public static function get()
	{	
		//return current instance of the pear debug class	
		//using utility class to load libraries
		return wpdutil::getClass('PHP_Debug');
		
	}
	
	public static function show()
	{
		//Add query info before render
		//This method should gather most if not all the query info
		//Use define('SAVEQUERIES',true); in wp-config.php for best results
		self::_processQueries();
		//Render debug information
		self::get()->display();
		
	}
	
	//No longer used internally to add query information
	//Still available as utility
	//add database query to debug
	public static function query($query)
	{
		//Record query string
		self::get()->query($query);
		return $query;
	}
	
	//add all available query data to debug
	private static function _processQueries()
	{
		foreach(wpdutil::getDB()->queries as $query)
		{	
			self::get()->query('[ '.self::queryCaller($query[2]).' ] '.' [ '.wpdutil::toSeconds($query[1]).' '.__('seconds','wp-pear-debug').' ] '.$query[0]);
		}	
	}
	
	//May have to test for array result but doubtful
	public static function queryCaller($callers)
	{
		$aCallers = explode(',',$callers);
		return $aCallers[count($aCallers)-1];
	}
	

	
	protected static function enabled()
	{

		//Got rid of switch
		//Read options if the plugin is enabled
		/******
		Get option automatically creates options that are non existent
		Therefore the initial state of the plugin will be the desired effect where all options are disabled
		Hence the absence of an installer.
	
		******/
		if( get_option( self::opt( self::WPD_STATUS) ) == self::WPD_ENABLE )
		{
			//Attempt to load the current user
			$u = wp_get_current_user();
			//Get current user role
			if( is_array($u->roles) && count($u->roles) )
			{
				$role = array_shift( $u->roles );
			}
			else
			{
				//Can't obtain role so user must be guest
				$role = self::WPD_GUEST_ROLE;
			}
				
			//load option for user role
			if(  is_numeric(get_option(self::opt($role)))  )
			{
				return get_option(self::opt($role));
			}
		
		}
		else
		{
			return false;
		}
		

				
	}

	//wrapper functions
	//Library contains more functionality but these are essentials
	//Add plain text message
	public static function add($sVar)
	{
		self::get()->add($sVar);			
	}
	
	//add array like print_r or array item like $foo['bar']
	public static function dump($obj, $varName = '')
	{
		self::get()->dump($obj, $varName);			
	}
	
	//Database related info
	public static function queryRel($sInfo)
	{
		self::get()->queryRel($sInfo);			
	}
	
	//Your own generated error
	public static function error($sInfo)
	{		
		self::get()->error($sInfo);	
	}
	
	//shortcode entry point to debug
	//Useful for debugging other short codes
	//Usage: [wp_pear_debug]foo bar[/wp_pear_debug]
	//Usage: [wp_pear-debug foo="bar" foo1="bar2"]
	public static function short($atts,$sContent='')
	{
		if( isset($sContent) && !empty($sContent) )
		{
			self::add($sContent);
		}
		else
		{
			self::dump($atts);
		}
	}	

	//Add in admin options with link under settings "Debugger"
	public static function admin() 
	{
		add_options_page(__('Debugger Options','wp-pear-debug'), __('Debugger','wp-pear-debug'), 10, '/wp-pear-debug/'.basename(__FILE__),array('wp_pear_debug','options'));
	}
	

	
	//shortcut for getting and setting distinct option name						 
	protected static function opt($suffix)
	{
		return self::WPD_PREFIX.$suffix;
	}
	
	//convert array of option names to add prefix: destinct name
	private static function opt_item(&$item,$key)
	{
		$item = array('type' => $item['type'], 'key' => self::opt($item['key']), 'def' => $item['key']);
	}
		
	//Return list of settings for plugin
	private static function getSettingFeilds()
	{
		//get our static settings
		$aSettings = self::$settings;
		//generate dynamic user role settings
		foreach(get_option(wpdutil::getDB()->prefix . 'user_roles')  as $key => $value)
		{
			$aSettings[] = array('type'=> self::WPD_SETTING_ROLE, 'key' => $key);
		}
		//add wpd prefix to each setting name
		array_walk($aSettings,array('wp_pear_debug','opt_item'));
		
		//Here you go. All the settings we need to update
		return $aSettings;
		
	}
	
	public static function settings()
	{
		foreach(self::getSettingFeilds() as $settingsField)
		{
			//Leave intval callback for now as all settings are integers
			register_setting( self::WPD_OPTIONS_GROUP , $settingsField['key'], 'intval' );
		}	
	}

	//Print options page
	public static function options()
	{
	
		?>
	<div class=wrap>
	  <form method="post" action="options.php">
	
		<h3><?php _e('Debugger Settings','wp-pear-debug') ?></h3>
	<div>
	<div class="postbox">
	
	
	
	<select name="<?php echo self::opt(self::WPD_STATUS);?>">
	<option value="<?php echo self::WPD_DISABLE;?>" <?php  if( get_option(self::opt(self::WPD_STATUS))== self::WPD_DISABLE ) echo "selected"; ?> ><?php _e('Disable Debugging','wp-pear-debug') ?></option>
	<option  value="<?php echo self::WPD_ENABLE;?>" <?php  if( get_option(self::opt(self::WPD_STATUS))== self::WPD_ENABLE ) echo "selected"; ?> ><?php _e('Enable Debugging','wp-pear-debug') ?></option>
	</select>
	
	
	</div>
	<h3> <?php _e('Display Debug for Roles:','wp-pear-debug') ?></h3>
	<div class="postbox">
	<table>
	
	<?php 
	
	foreach(self::getSettingFeilds() as $sField):
	
			//we only want settings for roles
			if($sField['type'] != self::WPD_SETTING_ROLE):
				continue;
			endif;	
			
				
		?>
		<tr>
		<td>
	<strong><?php echo ucfirst($sField['def']); ?></strong>	
	</td>
	<td>
	<select name="<?php echo $sField['key']; ?>">	
	<option value="<?php echo self::WPD_STATUS_DISABLE;?>" <?php  if(get_option($sField['key'])== self::WPD_STATUS_DISABLE) echo "selected"; ?> >
		<?php _e('Disable','wp-pear-debug','wp-pear-debug') ?>
	</option>	
	<option value="<?php echo self::WPD_STATUS_FRONT;?>" <?php  if(get_option($sField['key'])== self::WPD_STATUS_FRONT) echo "selected"; ?> >
		<?php _e('Front End Only','wp-pear-debug') ?>
	</option>	
	
	<?php if($sField['def'] != self::WPD_GUEST_ROLE): ?>
	<option value="<?php echo self::WPD_STATUS_ADMIN;?>" <?php  if(get_option($sField['key'])== self::WPD_STATUS_ADMIN) echo "selected"; ?> >
		<?php _e('Admin Only','wp-pear-debug') ?>
	</option>	
	<option value="<?php echo self::WPD_STATUS_BOTH;?>" <?php  if(get_option($sField['key'])== self::WPD_STATUS_BOTH) echo "selected"; ?> >
		<?php _e('Admin & Front End','wp-pear-debug') ?>
	</option>	
	<?php endif; ?>
	</select>	
	
	</td>
	</tr>
	<?php endforeach; ?>
	</table>
	</div>
	<?php
	//output hidden fields for settings (sweet)
	settings_fields(self::WPD_OPTIONS_GROUP);
	?>
	
	</div>
	<div class="submit">
	<input type="submit" name="Submit" value="<?php _e('Save Changes','wp-pear-debug') ?>" />
	</div>
	
	  </form>
	 </div>
	
	 <?php
	}
	
		
	
}	






?>