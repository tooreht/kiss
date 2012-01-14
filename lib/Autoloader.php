<?php 

class Autoloader
{
	public function __construct()
	{
		spl_autoload_register(array($this, 'load'));
	}
	
	private function load($className)
	{
		try
		{
			if(!$this->searchIncludePath($className))
				throw new Exception('Class ' . $className . '.php not found');
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			exit(0);
		}
	}
	
	private function searchIncludePath($className)
	{
		$paths = array(
			ROOT.DS.'app'.DS.'models'.DS.$className.'.php',
			ROOT.DS.'lib'.DS.$className.'.php',
			ROOT.DS.'config'.DS.$className.'.php',
			ROOT.DS.'app'.DS.'controllers'.DS.$className.'.php',
			ROOT.DS.'app'.DS.'views'.DS.$className.'.php'
		);
		
		foreach($paths as $path)
		{
			echo 'Trying to load ', $className, ' via ', __METHOD__, "()\n";
			if(file_exists($path)){
				echo 'Succeded to load ', $className, ' in ', $path."\n"; "\n";
				include($path);
				return TRUE;
			}
		}
		return FALSE;
	}
}