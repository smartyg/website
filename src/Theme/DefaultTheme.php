<?php

namespace Theme;

class DefaultTheme extends \Framework\Theme implements \Framework\iAdminTheme
{
	private $messages;

	public function getJS() : array
	{
		return array();
	}

	public function getStylesheets() : array
	{
		return array();
	}
	
	public function getNumberSides() : int
	{
		return 2;
	}
	
	public function addMessage(\Exception $messages) : void
	{
		$this->messages = $messages;
	}
	
	public function output(string $article, string $navigation, array $sides = null) : string
	{
		$html = '';
		$html .= '<nav>' . $navigation . '</nav>';
		$html .= '<article>' . $article . '</article>';
		
		return $html;
	}
}

?>
