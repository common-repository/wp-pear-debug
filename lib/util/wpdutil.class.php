<?php



class wpdutil
{
	//include a class file
	//neat way of loading classes relative to base folder
	//dir1.dirN.class or dir1.dirN.classFolderName (same as class)
	public static function loadClass($sClassPath)
	{
		//not sure i like the paths here...will need to investigate a better way
		$sFile =  WP_PLUGIN_DIR.'/wp-pear-debug'.'/lib/';
		$sFile .= str_replace(".","/",$sClassPath);
		$sClass = array_pop(explode(".",$sClassPath));
	
		if(!class_exists($sClass))
		{
		
			//Look for file first
			if( file_exists($sFile.".class.php")  )
			{
				$sFile.= ".class.php";
			}
			//We did not fine a file let us look for it in a director with the same name
			elseif( file_exists($sFile) && is_dir($sFile) )
			{
				$sFile.= "/".$sClass.".class.php";
			}
	
			if(!file_exists($sFile) )
			{
				Throw new Exception("Unable to load class file: ". $sFile);
			}
			
			require_once($sFile);
		}

		return $sClass;
	}
	//return instance of class from file
	public static function getClass($sClassPath)
	{
		//Include file if not already included
		$sClass = self::loadClass($sClassPath);

		//New instance of class
		$oClass =  new $sClass;
		//Use exisiting instance if possible
		if(method_exists($oClass,'singleton'))
		{
			$oClass = $oClass->singleton();
		}	
		return $oClass;
	}
	
	//Database data in millionseconds. Change to seconds before output to user.
	public static function toSeconds($milli)
	{
		return $milli * 1000;
	}
	
	//don't ask...umm cause its pretty?
	//This call provides a standard access method for the database as opposed to global db in every method
	public static function getDB()
	{
		global $wpdb;
		return $wpdb;
	}

}	






?>