<?php

function my_autoloader($class)
{
	require_once(str_replace('\\', '/', $class) . '.php');
}

spl_autoload_register('my_autoloader');

?>
