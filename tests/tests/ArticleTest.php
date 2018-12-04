<?php

use PHPUnit\Framework\TestCase;
use Framework\Article;
use Framework\Meta;
use Framework\ShortArticle;
use Framework\Exceptions\InternalException;

class ArticleTest extends TestCase
{
	private $input = array('id' => 1, 'title' => 'A test article', 'description' => 'This is a test article', 'author' => 'Me', 'tags' => array('1', '4', '11'), 'date_created' => '125341', 'date_modified' => '123146', 'content' => '<h1>Test Page</h1>');
	private $input2 = array('id' => 2, 'title' => 'A article to test', 'description' => 'This is a test article', 'author' => 'You', 'tags' => array('1', '4', '11'), 'date_created' => '125340', 'date_modified' => '123147', 'content' => '<h1>Second Test Page</h1>');

	/**
	 * @covers \Framework\ShortArticle
	 */
	public function test_ValidShortArticle()
	{
		$actual = new ShortArticle($this->input);
		
		$this->assertEquals('1', $actual->id);
		$this->assertEquals('A test article', $actual->title);
		$this->assertEquals('This is a test article', $actual->description);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\ShortArticle
	 * @depends test_ValidShortArticle
	 */
	public function test_IsValidShortArticle($input)
	{
		$this->assertEquals(true, $input->isValid());
	}
	
	/**
	 * @covers \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @depends test_ValidShortArticle
	 */
	public function test_ValidMetaExtend($input)
	{
		$actual = Meta::extend($input, $this->input);
		
		$this->assertEquals('1', $actual->id);
		$this->assertEquals('A test article', $actual->title);
		$this->assertEquals('This is a test article', $actual->description);
		$this->assertEquals('Me', $actual->author);
		$this->assertEquals(['1', '4', '11'], $actual->tags);
		$this->assertEquals(123146, $actual->date_modified);
		$this->assertEquals(125341, $actual->date_created);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @depends test_ValidMetaExtend
	 */
	public function test_IsValidMeta($input)
	{
		$this->assertEquals(true, $input->isValid());
	}
	
	/**
	 * @covers \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @depends test_ValidMetaExtend
	 */
	public function test_ValidArticleExtend($input)
	{
		$actual = Article::extend($input, $this->input);
		
		$this->assertEquals('1', $actual->id);
		$this->assertEquals('A test article', $actual->title);
		$this->assertEquals('This is a test article', $actual->description);
		$this->assertEquals('Me', $actual->author);
		$this->assertEquals(['1', '4', '11'], $actual->tags);
		$this->assertEquals(123146, $actual->date_modified);
		$this->assertEquals(125341, $actual->date_created);
		$this->assertEquals('<h1>Test Page</h1>', $actual->content);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @depends test_ValidArticleExtend
	 */
	public function test_IsValidArticle($input)
	{
		$this->assertEquals(true, $input->isValid());
	}
	
	/**
	 * @covers \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @depends test_ValidShortArticle
	 */
	public function test_InvalidMetaExtend($input)
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::ARTICLES_NO_MATCH);
		$actual = Meta::extend($input, array('id' => 3));
	}
	
	/**
	 * @covers \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @depends test_ValidMetaExtend
	 */
	public function test_InvalidArticleExtend($input)
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::ARTICLES_NO_MATCH);
		$actual = Meta::extend($input, array('id' => -1));
	}
	
	/**
	 * @covers \Framework\ShortArticle::compareId
	 * @uses \Framework\ShortArticle::__construct
	 * @uses \Framework\Meta
	 * @uses \Framework\Article
	 * @depends test_ValidShortArticle
	 */
	public function test_compareId1($input)
	{
		$compare_to = new Article($this->input2);
		$actual = Article::compareId($input, $compare_to);
		$this->assertTrue($actual < 0);
	}
	
	/**
	 * @covers \Framework\ShortArticle::compareId
	 * @uses \Framework\ShortArticle::__construct
	 * @uses \Framework\Meta
	 * @uses \Framework\Article
	 * @depends test_ValidShortArticle
	 */
	public function test_compareId2($input)
	{
		$actual = Article::compareId($input, $input);
		$this->assertTrue($actual == 0);
	}
	
	/**
	 * @covers \Framework\ShortArticle::compareTitle
	 * @uses \Framework\ShortArticle::__construct
	 * @uses \Framework\Meta
	 * @uses \Framework\Article
	 * @depends test_ValidShortArticle
	 */
	public function test_compareTitle1($input)
	{
		$compare_to = new Article($this->input2);
		$actual = Article::compareTitle($input, $compare_to);
		$this->assertTrue($actual > 0);
	}
	
	/**
	 * @covers \Framework\ShortArticle::compareTitle
	 * @uses \Framework\ShortArticle::__construct
	 * @uses \Framework\Meta
	 * @uses \Framework\Article
	 * @depends test_ValidShortArticle
	 */
	public function test_compareTitle2($input)
	{
		$actual = Article::compareTitle($input, $input);
		$this->assertTrue($actual == 0);
	}
	
	/**
	 * @covers \Framework\Meta::compareAuthor
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Meta::__construct
	 * @uses \Framework\Article
	 * @depends test_ValidMetaExtend
	 */
	public function test_compareAuthor1($input)
	{
		$compare_to = new Article($this->input2);
		$actual = Article::compareAuthor($input, $compare_to);
		$this->assertTrue($actual < 0);
	}
	
	/**
	 * @covers \Framework\Meta::compareAuthor
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Meta::__construct
	 * @uses \Framework\Article
	 * @depends test_ValidMetaExtend
	 */
	public function test_compareAuthor2($input)
	{
		$actual = Article::compareAuthor($input, $input);
		$this->assertTrue($actual == 0);
	}
	
	/**
	 * @covers \Framework\Meta::compareCreatedDate
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Meta::__construct
	 * @uses \Framework\Article
	 * @depends test_ValidMetaExtend
	 */
	public function test_compareCreatedDate1($input)
	{
		$compare_to = new Article($this->input2);
		$actual = Article::compareCreatedDate($input, $compare_to);
		$this->assertTrue($actual > 0);
	}
	
	/**
	 * @covers \Framework\Meta::compareCreatedDate
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Meta::__construct
	 * @uses \Framework\Article
	 * @depends test_ValidMetaExtend
	 */
	public function test_compareCreatedDate2($input)
	{
		$actual = Article::compareCreatedDate($input, $input);
		$this->assertTrue($actual == 0);
	}
	
	/**
	 * @covers \Framework\Meta::compareModifiedDate
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Meta::__construct
	 * @uses \Framework\Article
	 * @depends test_ValidMetaExtend
	 */
	public function test_compareModifiedDate1($input)
	{
		$compare_to = new Article($this->input2);
		$actual = Article::compareModifiedDate($input, $compare_to);
		$this->assertTrue($actual < 0);
	}
	
	/**
	 * @covers \Framework\Meta::compareModifiedDate
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Meta::__construct
	 * @uses \Framework\Article
	 * @depends test_ValidMetaExtend
	 */
	public function test_compareModifiedDate2($input)
	{
		$actual = Article::compareModifiedDate($input, $input);
		$this->assertTrue($actual == 0);
	}
}
?>
