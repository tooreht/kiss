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
			$viewsPath = ROOT.DS.'app'.DS.'views'.DS;
			$fileName = $viewsPath.$viewName;
			$fileContent = $fileName.'.php';
			if (!file_exists($fileContent))
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
					$fileHeader = $fileName.DS.'header.php';
					$fileAltHeader = $viewsPath.'header.php';
					if(file_exists($fileHeader))
						include($fileHeader);
					else if(file_exists($fileAltHeader))
						include($fileAltHeader);
					else {
						throw new Exception('Header of View ' . $viewName . ' not found.');
						//Fallback?
					}
				}
	
				/** Content */
				include($fileContent);
				
				
				/** Footer */
				if($showWholePage)
				{
					$fileFooter = $fileName.DS.'footer.php';
					$fileAltFooter = $viewsPath.'footer.php';
					if(file_exists($fileFooter))
						include($fileFooter);
					else if(file_exists($fileAltFooter))
						include($fileAltFooter);
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