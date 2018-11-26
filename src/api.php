<?php

declare(strict_types = 1);

require_once("./autoload.php");

+use Framework\Session;
use Framework\Utils; 
try
{
	$session = new Session::Session(Session::NO_NEW_SESSION);
}



function api_call(string $fn, string $args_type, string $return_type = _RETURN_TEXT)
{
	if($args_type == _ARGS_POST) $v = $_POST;
	else $v = $_GET;
	
	try
	{
		$method = new ReflectionMethod($session->getApi, $fn);

		foreach($method->getParameters() AS $arg)
		{
			if($v[$arg->name]) $args[$arg->name] = $v[$arg->name];
			//else $args[$arg->name] = null;
		}
        
		if(($res = call_user_func_array(array($session->getApi, $fn), $args)) == null)
		{
			throw ...
		}
	}
	catch(Exception $e)
	{
		$res = "error";
	}
	
	switch($return_type)
	{
		case _RETURN_TEXT:
			return Utils::apiParseText($res);
		case _RETURN_JSON:
			return Utils::apiParseJson($res);
		case _RETURN_XML:
			return Utils::apiParseXml($res);
	}

}

	$fn = Utils::parseGlobalVar($_GET, 'q');
	$args_type = Utils::parseGlobalVar($_GET, 'a');
	$return_type = Utils::parseGlobalVar($_GET, 'r');

$session->buffer_clean();

echo api_call($fn, $args_type, $return_type);

$session->buffer_end_flush();

?>
