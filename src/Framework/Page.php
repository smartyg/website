<?php

declare(strict_types = 1);

namespace Framework;

/** Main class for managing a (web)page.
 *
 */
final class Page
{
	private $theme;
	private $meta;
	private $sides = array();
	private $aricle;
	private $navigation = "";

	function __construct(Theme $theme)
	{
		if(Theme::isValid($theme)) $this->theme = $theme;
		else throw new Exception("invalid theme");
	}

	public function getTheme() : Theme
	{
		return $this->theme;
	}
	
	public function setArticle(string $article) : void
	{
		$this->article = $article;
	}
	
	public function setSides(int $n, string $side) : void
	{
		$this->sides[$n] = $side;
	}
	
	public function setMeta(Meta $meta) : void
	{
		if(!$meta->isValid()) throw new Exception("no title given.");
		else $this->meta = $meta;
	}
	
	public function addMessage(Throwable $e) : void
	{
		$this->theme->addMessage($e);
	}

	public function output() : void
	{
		echo '<!DOCTYPE html><html><head>';
		echo '<title>' . $this->meta->title . '</title>';
		echo '<meta charset="UTF-8">';
		if(!empty($this->meta->description)) echo  '<meta name="description" content="' . $this->meta->description . '">';
		if(!empty($this->meta->tags)) echo '<meta name="keywords" content="' . $this->meta->tags . '">';
		if(!empty($this->meta->author)) echo '<meta name="author" content="' . $this->meta->author . '">';
		echo '<meta name="application-name" content="">';
		echo '<meta name="generator" content="">';
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

		foreach($this->theme->getStylesheets() as $css)
		{
			echo '<link rel="stylesheet" type="text/css" href="' . $css . '">';
		}

		foreach($this->theme->getJS() as $js)
		{
			echo '<script type="application/javascript" src="' . $js . '" charset="UTF-8"></script>';
		}

		echo '</head><body>';
		echo $this->theme->output($this->article, $this->navigation, $this->sides);
		if(!$this->theme->messagesPrinted()) $this->theme->printMessages();
		echo '</body></html>';
	}
}

?>
