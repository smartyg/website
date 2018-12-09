<?php

use PHPUnit\Framework\TestCase;
use Framework\Utils;
use Framework\ShortArticle;
use Framework\Meta;
use Framework\Article;
use Framework\Userdata;

class UtilsTest extends TestCase
{
	private $parse_array = array();
	
	static private function checkSort(array $array) : bool
	{
		if(!$length = count($array)) return true;
		for($i = 0; $i < $length; $i++)
		{
			if(isset($array[$i + 1]))
			{
				if($array[$i] > $array[$i + 1]) return false;
			}
		}
		return true;
	}

	/**
	 * @covers \Framework\Utils::apiParseXml
	 * @uses \Framework\Utils::arraify
	 */
	public function test_api_parse_xml()
	{
		$this->markTestIncomplete();
		$expected = '';
		$actual = Utils::apiParseXml($this->parse_array);
		$this->assertXmlStringEqualsXmlString($expected, $actual);
	}
    
    /**
	 * @covers \Framework\Utils::apiParseJson
	 * @uses \Framework\Utils::arraify
	 */
	public function test_api_parse_json()
	{
		$this->markTestIncomplete();
		$expected = json_encode($this->parse_array);
		$actual = Utils::apiParseJson($this->parse_array);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
    }
    
    /**
	 * @covers \Framework\Utils::apiParseText
	 * @uses \Framework\Utils::arraify
	 */
	public function test_api_parse_text()
	{
		$this->markTestIncomplete();
		$expected = '';
		$actual = Utils::apiParseText($this->parse_array);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}
	
	/**
	 * @coversNothing
	 */
	public function test_sortCheck1()
	{
		$input = array(1, 2, 3, 4, 5, 5, 5, 5, PHP_INT_MAX - 2, PHP_INT_MAX - 1, PHP_INT_MAX);
		$this->assertTrue(self::checkSort($input));
	}
	
	/**
	 * @coversNothing
	 */
	public function test_sortCheck2()
	{
		$input = array(1, 1, 1, 1, 1, -1);
		$this->assertFalse(self::checkSort($input));
	}
	
	/**
	 * @coversNothing
	 */
	public function test_sortCheck3()
	{
		$input = array();
		$this->assertTrue(self::checkSort($input));
	}
	
	/**
	 * @covers \Framework\Utils::compareInt
	 */
	public function test_compareInt1()
	{
		$actual = Utils::compareInt(1, 2);
		$this->assertTrue($actual < 0);
	}
	
	/**
	 * @covers \Framework\Utils::compareInt
	 */
	public function test_compareInt2()
	{
		$actual = Utils::compareInt(2, -2);
		$this->assertTrue($actual > 0);
	}
	
	/**
	 * @covers \Framework\Utils::compareInt
	 */
	public function test_compareInt3()
	{
		$actual = Utils::compareInt(10, 10);
		$this->assertTrue($actual == 0);
	}
	
	/**
	 * @covers \Framework\Utils::sortArray
	 * @uses \Framework\Utils::compareInt
	 */
	public function test_sortArray1()
	{
		$input = array(1,3,-3424,64,2,3,34,6,546,3,212,5,6,32,0,1,4,6,23,41,43,6,24,2,657,7,5,32,21,4,6,2,5,65,23,35,4,91,47,322,243,2,5,5,2,4,56);
		$actual = Utils::sortArray($input, '\Framework\Utils::compareInt');
		$this->assertTrue(self::checkSort($actual));
		return $actual;
	}

	/**
	 * @covers \Framework\Utils::unique
	 * @uses \Framework\Utils::compareInt
	 */
	public function test_unique1()
	{
        $input = array(1,1,2,2,2,3,3,3,3,4,6,7,8,9,9);
        $expected = array(1,2,3,4,6,7,8,9);
        $actual = Utils::unique($input, '\Framework\Utils::compareInt');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers \Framework\Utils::unique
	 * @uses \Framework\Utils::compareInt
	 * @depends test_sortArray1
	 */
	public function test_unique2($input)
	{
        $expected = array(-3424,0,1,2,3,4,5,6,7,21,23,24,32,34,35,41,43,47,56,64,65,91,212,243,322,546,657);
        $actual = Utils::unique($input, '\Framework\Utils::compareInt');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\ShortArticle
	 */
	public function test_arraify1()
	{
		$input = array(
			'id' => 3,
			'title' => 'a title',
			'description' => 'a description of this page'
			);
		$article = new ShortArticle($input);
		$expected = $input;

		$actual = Utils::arraify($article);
		$this->assertEquals($expected, $actual);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\ShortArticle
	 * @depends test_arraify1
	 */
	public function test_ShortArticleFromArraify($input)
	{
		$article = new ShortArticle($input);
		$actual = Utils::arraify($article);
		$this->assertEquals($input, $actual);
	}

	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\ShortArticle
	 * @covers \Framework\Meta
	 */
	public function test_arraify2()
	{
		$input = array(
			'id' => 3,
			'title' => 'a title',
			'description' => 'a description of this page',
			'date_created' => 34253254,
			'date_modified' => 4326343,
			'author' => 'Me, Myself & I',
			'tags' => array(0,4,6)
			);
		$meta = new Meta($input);
		$expected = $input;

		$actual = Utils::arraify($meta);
		$this->assertEquals($expected, $actual);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\ShortArticle
	 * @covers \Framework\Meta
	 * @depends test_arraify2
	 */
	public function test_MetaFromArraify($input)
	{
		$article = new Meta($input);
		$actual = Utils::arraify($article);
		$this->assertEquals($input, $actual);
	}
	
	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\ShortArticle
	 * @covers \Framework\Meta
	 * @covers \Framework\Article
	 */
	public function test_arraify3()
	{
		$input = array(
			'id' => 3,
			'title' => 'a title',
			'description' => 'a description of this page',
			'date_created' => 34253254,
			'date_modified' => 4326343,
			'author' => 'Me, Myself & I',
			'tags' => array(0,4,6),
			'content' => '<h1>Hello World!</h1><p>Lorem Ipsum</p>'
			);
		$article = new Article($input);
		$expected = $input;

		$actual = Utils::arraify($article);
		$this->assertEquals($expected, $actual);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\ShortArticle
	 * @covers \Framework\Meta
	 * @covers \Framework\Article
	 * @depends test_arraify3
	 */
	public function test_ArticleFromArraify($input)
	{
		$article = new Article($input);
		$actual = Utils::arraify($article);
		$this->assertEquals($input, $actual);
	}
	
	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\Userdata
	 */
	public function test_arraify4()
	{
		$input = array(
			'u_id' => 5,
			'first_name' => 'John',
			'middle_name' => 'Stranger',
			'last_name' => 'Doe',
			'display_name' => 'John (Stranger) Doe',
			'email_address' => 'me@example.com',
			'permissions' => 21
			);
		$article = new Userdata($input);
		$expected = $input;

		$actual = Utils::arraify($article);
		$this->assertEquals($expected, $actual);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\Userdata
	 * @depends test_arraify4
	 */
	public function test_UserdataFromArraify($input)
	{
		$article = new Userdata($input);
		$actual = Utils::arraify($article);
		$this->assertEquals($input, $actual);
	}
	
	/**
	 * @covers \Framework\Utils::arraify
	 * @covers \Framework\Userdata
	 * @covers \Framework\ShortArticle
	 * @covers \Framework\Meta
	 * @covers \Framework\Article
	 * @depends test_arraify1
	 * @depends test_arraify2
	 * @depends test_arraify3
	 * @depends test_arraify4
	 */
	public function test_arraify5($input1, $input2, $input3, $input4)
	{
		$input = array(
			1,
			PHP_INT_MAX,
			PHP_INT_MIN,
			"a string",
			true,
			false,
			0.0001,
			NULL,
			array(
				new Userdata($input4),
				new Userdata($input4)
				),
			array(
				'a' => 4,
				'b' => 'a second string',
				'c' => false,
				'd' => 10.2456
				),
			'ShortArticle' => new ShortArticle($input1),
			'Meta' => new Meta($input2),
			'Article' => new Article($input3)
			);
			
		$expected = array(
			1,
			PHP_INT_MAX,
			PHP_INT_MIN,
			"a string",
			true,
			false,
			0.0001,
			'null',
			array(
				$input4,
				$input4
				),
			array(
				'a' => 4,
				'b' => 'a second string',
				'c' => false,
				'd' => 10.2456
				),
			'ShortArticle' => $input1,
			'Meta' => $input2,
			'Article' => $input3
			);
		$actual = Utils::arraify($input);
		$this->assertEquals($expected, $actual);
	}
}
?>
