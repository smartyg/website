<?php

declare(strict_types = 1);

namespace Framework;

/** Main class for managing a (web)page.
 *
 */
final class Meta
{
	public $title;
	public $date;
	public $author;
	public $tags;
	public $description;
	
	function __construct(array $input = null)
	{
		if(is_array($input))
		{
			if(array_key_exists('title', $input)) $this->title = $input['title'];
			if(array_key_exists('date', $input)) $this->date = $input['date'];
			if(array_key_exists('author', $input)) $this->author = $input['author'];
			if(array_key_exists('tags', $input)) $this->tags = $input['tags'];
			if(array_key_exists('description', $input)) $this->description = $input['description'];
		}
	}
	
	public function isValid() : bool
	{
		return !empty($this->title);
	}
}
?>
