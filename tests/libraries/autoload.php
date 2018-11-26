<?php

function my_autoloader($class)
{
	$possibilities = array(
		'./src/' . str_replace('\\', '/', $class) . '.php',
		'./tests/tests/' . str_replace('\\', '/', $class) . '.php',
		'./tests/libraries/' . str_replace('\\', '/', $class) . '.php'
		);
	//echo "looking for class: $class\n";
	foreach($possibilities as $file)
	{
		//echo "checking file: $file\n";
		if(file_exists($file))
		{
			require_once($file);
			return;
		}
	}
	//throw new Error("class " . $class . " not found");
}

spl_autoload_register('my_autoloader');

?>
