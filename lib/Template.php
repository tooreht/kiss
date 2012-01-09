<?php

class Template
{
	private $vars = array();

	public function __set($index, $value)
 	{
        $this->vars[$index] = $value;
 	}

	public function show($viewName, $showWholePage = TRUE)
	{
		try
		{
			/**
			 * Check if view exists
			 */
			$file = ROOT.DS.'app'.DS.'views'.DS.$viewName.'.php';
			if (!file_exists($file))
			{
				throw new Exception('View ' . $viewName . ' not found.');
				//Fallback?
			} 
			else 
			{
	
				foreach ($this->vars as $key => $value)
				{
					$$key = $value;
				}
				
				/** Header */
				if($showWholePage)
				{
					$file = $file.DS.'header.php';
					if(file_exists($file))
						include($file);
					else {
						throw new Exception('Header of View ' . $viewName . ' not found.');
						//Fallback?
					}
				}
	
				/** Content */
				include($file);
				
				
				/** Footer */
				if($showWholePage)
				{
					$file = $file.DS.'footer.php';
					if(file_exists($file))
						include($file);
					else {
						throw new Exception('Footer of View ' . $viewName . ' not found.');
						//Fallback?
					}	
				}
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			exit(0);
		}
	}
}