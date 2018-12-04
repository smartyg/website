<?php

declare(strict_types = 1);

namespace Framework;

use \Framework\Meta;
use \Framework\Exceptions\InternalException;

/** Main class for managing a (web)page.
 *
 */
class Article extends Meta
{
	public $content;

	function __construct(array $input = null)
	{
		if(is_array($input))
		{
			if(array_key_exists('content', $input)) $this->content = $input['content'];
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
			'tags' => $this->tags,
			'content' => $this->content
			);
	}

	static public function extend(ShortArticle $article, $input)
	{
		if(key_exists('id', $input) && $input['id'] != $article->id) throw new InternalException("error.", InternalException::ARTICLES_NO_MATCH);
		if(!key_exists('id', $input) || empty($input['id'])) $input['id'] = $article->id;
		if(!key_exists('title', $input) || empty($input['title'])) $input['title'] = $article->title;
		if(!key_exists('description', $input) || empty($input['description'])) $input['description'] = $article->description;
		if(!key_exists('date_created', $input) || empty($input['date_created'])) $input['date_created'] = $article->date_created;
		if(!key_exists('date_modified', $input) || empty($input['date_modified'])) $input['date_modified'] = $article->date_modified;
		if(!key_exists('author', $input) || empty($input['author'])) $input['author'] = $article->author;
		if(!key_exists('tags', $input) || empty($input['tags'])) $input['tags'] = $article->tags;
		return new Article($input);
	}
}
?>
