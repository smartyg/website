<?php

namespace Theme;

class DefaultTheme extends \Framework\Theme implements \Framework\iAdminTheme
{
	public function getJS() : array
	{
		return array();
	}

	public function getStylesheets() : array
	{
		return array();
	}
	
	public function output(string $article, string $navigation, array $sides = null) : string
	{
		return $article;
	}
}

?>
