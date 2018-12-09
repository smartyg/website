<?php

declare(strict_types = 1);

namespace Framework;

use \Framework\ShortArticle;
use \Framework\Exceptions\InternalException;

/** Main class for managing a (web)page.
 *
 */
class Meta extends ShortArticle
{
	public $date_created;
	public $date_modified;
	public $author;
	public $tags;

	function __construct(array $input = null)
	{
		if(is_array($input))
		{
			if(array_key_exists('date_created', $input)) $this->date_created = (int)$input['date_created'];
			if(array_key_exists('date_modified', $input)) $this->date_modified = (int)$input['date_modified'];
			if(array_key_exists('author', $input)) $this->author = (string)$input['author'];
			if(array_key_exists('tags', $input)) $this->tags = (array)$input['tags'];
			parent::__construct($input);
		}
	}

	public function toArray() : array
	{
		return array(
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'date_created' => $this->date_created,
			'date_modified' => $this->date_modified,
			'author' => $this->author,
			'tags' => $this->tags
			);
	}

	static public function extend(ShortArticle $article, $input)
	{
		if(key_exists('id', $input) && $input['id'] != $article->id) throw new InternalException("error.", InternalException::ARTICLES_NO_MATCH);
		if(!key_exists('id', $input) || empty($input['id'])) $input['id'] = $article->id;
		if(!key_exists('title', $input) || empty($input['title'])) $input['title'] = $article->title;
		if(!key_exists('description', $input) || empty($input['description'])) $input['description'] = $article->description;
		return new Meta($input);
	}

	static public function compareAuthor(Meta $a, Meta $b) : int
	{
		return strnatcmp($a->author, $b->author);
	}

	static public function compareCreatedDate(Meta $a, Meta $b) : int
	{
		if($a->date_created < $b->date_created) return -1;
		if($a->date_created > $b->date_created) return 1;
		else return 0;
	}

	static public function compareModifiedDate(Meta $a, Meta $b) : int
	{
		if($a->date_modified < $b->date_modified) return -1;
		if($a->date_modified > $b->date_modified) return 1;
		else return 0;
	}
}
?>
