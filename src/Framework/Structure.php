<?php

declare(strict_types = 1);

namespace Framework;

use Framework\iArraify;
use Framework\ShortArticle;
use Framework\Utils;

/**
 * 
 */
class Structure implements iArraify
{
	private $article;
	private $children = array();
	private $parent = null;
	
	public function __construct(?shortArticle $article, Structure $parent = null)
	{
		$this->article = $article;
		$this->parent = $parent;
	}
	
	public function toArray() : array
	{
		return array(
			'article' => $this->article,
			'parent' => $this->parent,
			'children' => $this->children
			);
	}

	public function addChild(ShortArticle $article, int $order = 0) : Structure
	{
		return ($this->children[] = new Structure($article, $this));
	}

	public function getArticle() : ?ShortArticle
	{
		return $this->article;
	}

	public function getChildren() : array
	{
		return $this->children;
	}

	public function getParent() : Structure
	{
		$this->parent;
	}
}
?>
