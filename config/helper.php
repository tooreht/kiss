<?php

/** Check if environment is development and display errors **/

function setReporting()
{
	if (DEVELOPMENT_ENVIRONMENT == true) {
		error_reporting(E_ALL);
		ini_set('display_errors','On');
	} else {
		error_reporting(E_ALL);
		ini_set('display_errors','Off');
		ini_set('log_errors', 'On');
		ini_set('error_log', ROOT.DS.'tmp'.DS.'logs'.DS.'error.log');
	}
}

function searchIncludePath($className){
	$pre = ROOT.DS;
	$paths = array(
		$pre.'app'.DS.'models'.DS.$className.'.php',
		$pre.'lib'.DS.$className.'.php',
		$pre.'app'.DS.'controllerss'.DS.$className.'.php'
	);
	
	foreach($paths as $path)
	{
		if(file_exists($path)){
			include($path);
			return TRUE;
		}
	}
	return FALSE;
}
