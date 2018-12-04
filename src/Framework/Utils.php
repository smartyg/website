<?php

declare(strict_types = 1);

namespace Framework;

use Framework\iArraify;
use Framework\Exceptions\BaseException;

final class Utils
{
	static public function arraify($input)
	{
		switch(gettype($input))
		{
			case 'boolean':
			case 'string':
			case 'integer':
			case 'double':
			case 'float':
				return $input;
			case 'NULL':
				return 'null';
			case 'array':
				foreach($input as $key => $item)
				{
					$input[$key] = self::arraify($item);
				}
				return $input;
			case 'object':
				if(is_subclass_of($input, '\Framework\iArraify')) return self::arraify($input->toArray());
			default:
				throw new Exception("Cannot arrify this item.");
		}
	}

	static public function parseGlobalVar(array $a, string $name) : string
	{
		if(array_key_exists($name, $a) && !empty($a[$name])) return (string)$a[$name];
	}

	static public function apiParseText($a) : string
	{
		return serialize(self::arraify($a));
	}

	static public function apiParseJson($a) : string
	{
		//return json_encode($a, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG);
		return json_encode(self::arraify($a), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
	}

	static public function apiParseXml($a) : string
	{
	}

	static public function compareInt(int $a, int $b) : int
	{
		if($a < $b) return -1;
		if($a > $b) return 1;
		else return 0;
	}

	static public function sortArray(array &$input, callable $fn, int $start = 0, int $length = -1) : array
	{
		if($length < 0) $length = count($input);

		if($length > 2)
		{
			$startA = $start;
			$endA = $start + (int)($length / 2);
			$startB = $endA;
			$endB = $start + $length;
			$input_A = self::sortArray($input, $fn, $startA, $endA - $startA);
			self::sortArray($input, $fn, $startB, $endB - $startB);
		}
		elseif($length == 2)
		{
			$startA = $start;
			$endA = $start + 1;
			$startB = $startA + 1;
			$endB = $start + 2;
			$input_A[$startA] = $input[$startA];
		}
		else
		{
			return $input;
		}
		$n = $start;

		while($startA < $endA)
		{
			if($startB < $endB && $fn($input_A[$startA], $input[$startB]) > 0)
			{
				$input[$n] = $input[$startB];
				$startB++;
			}
			else
			{
				$input[$n] = $input_A[$startA];
				$startA++;
			}
			$n++;
		}
		return $input;
	}
}

?>
