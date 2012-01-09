<?php

class Template
{
	private $vars = array();

	public function __set($index, $value)
 	{
        $this->vars[$index] = $value;
 	}

	public function show($viewName)
	{
		try
		{
			$file = 'app/views/' . $viewName;

			if (!file_exists($file))
				throw new Exception('View ' . $viewName . ' not found.');		else

			foreach ($this->vars as $key => $value)
			{
				$$key = $value;
			}

			include($file);
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			exit(0);
		}
	}
}