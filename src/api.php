<?php

declare(strict_types = 1);

require_once("./autoload.php");

try
{
	$session = new Session::Session(\Framework\Constants::NO_NEW_SESSION);
}

function api_parse_text($a) : string
{
}

function api_parse_json($a) : string
{
}

function api_parse_xml($a) : string
{
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
			return api_parse_text($res);
		case _RETURN_JSON:
			return api_parse_json($res);
		case _RETURN_XML:
			return api_parse_xml($res);
	}

}

$fn = parse_global_var($_GET, '');
$args_type = parse_global_var($_GET, '');
$return_type = parse_global_var($_GET, '');

$session->buffer_clean();

echo api_call($fn, $args_type, $return_type);

$session->buffer_end_flush();

?>
