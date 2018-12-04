<?php

declare(strict_types = 1);

namespace Framework;

use Framework\iArraify;

/** Main class for managing a (web)page.
 *
 */
class ShortArticle implements iArraify
{
	public $id;
	public $title;
	public $description;
	
	function __construct(array $input = null)
	{
		if(is_array($input))
		{
			if(array_key_exists('id', $input)) $this->id = $input['id'];
			if(array_key_exists('title', $input)) $this->title = $input['title'];
			if(array_key_exists('description', $input)) $this->description = $input['description'];
		}
	}

	public function toArray() : array
	{
		return array(
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description
			);
	}

	public function isValid() : bool
	{
		return !empty($this->title) && !empty($this->id);
	}

	static public function compareId(ShortArticle $a, shortArticle $b) : int
	{
		if($a->id < $b->id) return -1;
		if($a->id > $b->id) return 1;
		else return 0;
	}

	static public function compareTitle(ShortArticle $a, shortArticle $b) : int
	{
		return strnatcmp($a->title, $b->title);
	}
}
?>
