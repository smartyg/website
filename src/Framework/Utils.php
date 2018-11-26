<?php

declare(strict_types = 1);

namespace Framework;

use Framework\Exceptions\BaseException;

final class Utils
{
	static public function parseGlobalVar(array $a, string $name) : string
	{
		if(array_key_exists($name, $a) && !empty($a[$name])) return (string)$a[$name];
	}

	static public function apiParseText($a) : string
	{
		return serialize($a);
	}

	static public function apiParseJson($a) : string
	{
		//return json_encode($a, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG);
		return json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
	}

	static public function apiParseXml($a) : string
	{
	}
}

?>
