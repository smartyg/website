<?php

declare(strict_types = 1);

require_once("./autoload.php");

use Framework\Session;
use Framework\Utils;
use Api\Api;

const _ARGS_POST = 'P';
const _ARGS_GET = 'G';
const _RETURN_TEXT = 'T';
const _RETURN_JSON = 'J';
const _RETURN_XML = 'X';

function api_call(Api $api, string $fn, string $args_type, string $return_type = _RETURN_TEXT) : string
{
	if($args_type == _ARGS_POST) $v = $_POST;
	else $v = $_GET;

	$method = new ReflectionMethod($api, $fn);
//var_dump($method->getParameters());
	$args = array();
	foreach($method->getParameters() AS $arg)
	{
		if($v[$arg->getName()])
		{
			$var = $v[$arg->getName()];
			settype($var, $arg->getType()->getName());
			$args[$arg->getName()] = $var;
		}
	}
        
	if(($res = call_user_func_array(array($api, $fn), $args)) == null)
	{
		throw new Exception("wrong function call.");
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
/*
try
{*/
	$session = new Session(Session::_SESSION_NO_NEW, false);

	$fn = Utils::parseGlobalVar($_GET, 'q');
	$args_type = Utils::parseGlobalVar($_GET, 'a');
	$return_type = Utils::parseGlobalVar($_GET, 'r');

	$session->bufferClean();

	echo api_call($session->getApi(), $fn, $args_type, $return_type);

	$session->bufferFlush();/*
}
catch(Exception | Error $e)
{
	echo "error: " . $e->getMessage();
}
*/
?>
