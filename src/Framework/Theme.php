<?php

declare(strict_types = 1);

namespace Framework;

/** Base theme class.
 * All themes should inherit this class and must be part of the Theme namespace.
 */
abstract class Theme
{
	final function __construct()
	{
	}

	abstract public function addMessage(\Exception $messages) : void;
	abstract public function getNumberSides() : int;
	abstract public function getJS() : array;
	abstract public function getStylesheets() : array;
	abstract public function output(string $article, string $navigation, array $sides = null) : string;
	
	static public function isValid() : bool
	{
		return true;
	}
}

?>
