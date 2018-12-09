<?php

use PHPUnit\Framework\TestCase;
use Api\Api;
use Api\Exceptions\ApiException;
use Framework\ShortArticle;
use Framework\Meta;
use Framework\Article;

class ApiTest extends DatabaseTestCase
{
	private $api;
	
	private $articles;
	
	/**
	 * Instantiates the instance under test.
	 */
	protected function setUp() : void
	{
		// \PHPUnit_Extensions_Database_TestCase uses this for important stuff, so be sure to call it
		parent::setUp();
		// Make sure a connection was established and the $pdo property has a value
		$pdo = $this->getConnection();
		//make a mock session class
		
		uopz_flags(Framework\Session::class, null, 0);

		$stub = $this->getMockBuilder(Framework\Session::class)
                     ->disableOriginalConstructor()
                     ->disableOriginalClone()
                     ->disableArgumentCloning()
                     ->disallowMockingUnknownTypes()
                     ->setMethods(['isValid','getPermissions','logon','logoff','__destruct'])
                     ->getMock();

        // Configure the stub.
        $stub->method('isValid')
             ->willReturn(true);
		$stub->method('getPermissions')
             ->willReturn((1 << 8) - 1);
		$stub->method('logon')
             ->willReturn(true);
		$stub->method('logoff')
             ->willReturn(true);
		$stub->method('__destruct')
             ->willReturn(null);
		$this->api = new Api($stub, $pdo);
		
		$this->articles = array(
			new ShortArticle(array('id' => 0, 'title' => 'Hello World!', 'description' => 'Welcome to my website')),
			new ShortArticle(array('id' => 1, 'title' => 'Second test page', 'description' => 'Another test page')),
			new ShortArticle(array('id' => 2, 'title' => 'Third test page', 'description' => 'Lorem Ipsum')),
			new ShortArticle(array('id' => 3, 'title' => 'Private test page', 'description' => 'This page is private'))
			);
	}
	
	protected function getDataSet()
	{
		return $this->getDataSetFromFile('test_1');
	}
	
	/**
	 * test method getRelatedArticlesByArticleId(int $id) : array
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Utils
	 */
	public function test_getRelatedArticlesByArticleId1()
	{
		$expected = array($this->articles[2]);
		$actual = $this->api->getRelatedArticlesByArticleId(1);
		$this->assertEquals($expected, $actual);
	}

	/** Test with two existing tags
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Utils
	 */
	public function test_getRelatedArticlesByArticleId2()
	{
		$expected = array($this->articles[0], $this->articles[1], $this->articles[3]);
		$actual = $this->api->getRelatedArticlesByArticleId(2);
		$this->assertEquals($expected, $actual);
	}

	/** Test with a non-exisitng tag
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_getRelatedArticlesByArticleId3()
	{
		$expected = array();
		$actual = $this->api->getRelatedArticlesByArticleId(404);
		$this->assertEquals($expected, $actual);
	}

	/** Test with one exisitng and one non-exisitng tag
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Utils
	 */
	public function test_getRelatedArticlesByTagIds()
	{
		$expected = array($this->articles[2], $this->articles[3]);
		$actual = $this->api->getRelatedArticlesByTagIds(array(1, 2));
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method getSubArticles(int $id = 0) : array
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	// test with default value
	public function test_getSubArticles1()
	{
        /* TODO: decide how to return a sub structure. */
        $this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getSubArticles();
		$this->assertEquals($expected, $actual);
	}

	/** test with a valid value
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_getSubArticles2()
	{
        /* TODO: decide how to return a sub structure. */
        $this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getSubArticles(1);
		$this->assertEquals($expected, $actual);
	}

	/** Another test with a vaild value
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_getSubArticles3()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(3);
		$this->assertEquals($expected, $actual);
	}

	/** Test with an non-existing value
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_getSubArticles4()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(404);
		$this->assertEquals($expected, $actual);
	}

	/** Test with a non existing huge value
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_getSubArticles5()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(PHP_INT_MAX - 1);
		$this->assertEquals($expected, $actual);
	}

	/** Test with an non existing huge negative value
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_getSubArticles6()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(PHP_INT_MIN + 1);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method getArticle(int $id) : array
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticle1()
	{
		$actual = $this->api->getArticle(1);

		$this->assertInstanceOf(\Framework\Article::class, $actual);
		$this->assertEquals(1, $actual->id);
		$this->assertEquals('<h2>Bye World!</h2><p>This is my website &lt;&lt;</p>', $actual->content);
		$this->assertEquals('Second test page', $actual->title);
		$this->assertEquals('public', $actual->author);
		$this->assertEquals(['3'], $actual->tags);
		$this->assertEquals('Another test page', $actual->description);
		$this->assertEquals(1543681181, $actual->date_modified);
		$this->assertEquals(1543681181, $actual->date_created);
	}
	
	/**
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticle2()
	{
		$actual = $this->api->getArticle(2);

		$this->assertInstanceOf(\Framework\Article::class, $actual);
		$this->assertEquals(2, $actual->id);
		$this->assertEquals('<h2>Lorem Ipsum</h2><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui.</p>', $actual->content);
		$this->assertEquals('Third test page', $actual->title);
		$this->assertEquals('John Doe', $actual->author);
		$this->assertEquals(['0', '2', '3'], $actual->tags);
		$this->assertEquals('Lorem Ipsum', $actual->description);
		$this->assertEquals(1543681181, $actual->date_modified);
		$this->assertEquals(1543681181, $actual->date_created);
	}
	
	/**
	 * @covers \Api\Api
	 * @covers \Framework\Query
     * @uses \Api\Exceptions\ApiException
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticle3()
	{
		$this->expectException(ApiException::class);
		$this->expectExceptionCode(ApiException::NO_ARTICLE);
		$this->api->getArticle(404);
	}
	
	/**
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Api\Exceptions\ApiException
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticle4()
	{
		$this->expectException(ApiException::class);
		$this->expectExceptionCode(ApiException::NO_ARTICLE);
		$this->api->getArticle(PHP_INT_MAX - 1);
	}
	
	/**
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Api\Exceptions\ApiException
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticle5()
	{
		$this->expectException(ApiException::class);
		$this->expectExceptionCode(ApiException::NO_ARTICLE);
		$this->api->getArticle(PHP_INT_MIN + 1);
	}

	/**
	 * test method getArticleMeta(int $id) : Meta
	 * 
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticleMeta1()
	{
		$actual = $this->api->getArticleMeta(1);

		$this->assertInstanceOf(\Framework\Meta::class, $actual);
		$this->assertEquals('Second test page', $actual->title);
		$this->assertEquals('public', $actual->author);
		$this->assertEquals(['3'], $actual->tags);
		$this->assertEquals('Another test page', $actual->description);
		$this->assertEquals(1543681181, $actual->date_modified);
		$this->assertEquals(1543681181, $actual->date_created);
	}

	/**
	 * test method getArticleMeta(int $id) : Meta
	 * 
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticleMeta2()
	{
		$actual = $this->api->getArticleMeta(3);

		$this->assertInstanceOf(\Framework\Meta::class, $actual);
		$this->assertEquals('Private test page', $actual->title);
		$this->assertEquals('John Doe', $actual->author);
		$this->assertEquals(['1', '2'], $actual->tags);
		$this->assertEquals('This page is private', $actual->description);
		$this->assertEquals(1543681181, $actual->date_modified);
		$this->assertEquals(1543681181, $actual->date_created);
	}
	
	/**
	 * test method getArticleMeta(int $id) : Meta
	 * 
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Api\Exceptions\ApiException
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticleMeta3()
	{
		$this->expectException(ApiException::class);
		$this->expectExceptionCode(ApiException::NO_ARTICLE);
		$this->api->getArticleMeta(404);
	}
	
	/**
	 * test method getArticleMeta(int $id) : Meta
	 * 
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Api\Exceptions\ApiException
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticleMeta4()
	{
		$this->expectException(ApiException::class);
		$this->expectExceptionCode(ApiException::NO_ARTICLE);
		$this->api->getArticleMeta(PHP_INT_MAX - 1);
	}
	
	/**
	 * test method getArticleMeta(int $id) : Meta
	 * 
	 * @covers \Api\Api
	 * @covers \Framework\Query
	 * @uses \Api\Exceptions\ApiException
	 * @uses \Framework\Article
	 * @uses \Framework\Meta
	 * @uses \Framework\ShortArticle
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Utils
	 */
	public function test_getArticleMeta5()
	{
		$this->expectException(ApiException::class);
		$this->expectExceptionCode(ApiException::NO_ARTICLE);
		$this->api->getArticleMeta(PHP_INT_MIN + 1);
	}

	/**
	 * test method getSideArticles(int $id) : array
	 */
	// test with
	public function test_getSideArticles1()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getSideArticles(0);
		$this->assertEquals($expected, $actual);
	}

	// test with
	public function test_getSideArticles2()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getSideArticles(2);
		$this->assertEquals($expected, $actual);
	}

	// test with
	public function test_getSideArticles3()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getSideArticles(404);
		$this->assertEquals($expected, $actual);
	}

	// test with
	public function test_getSideArticles4()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getSideArticles(PHP_INT_MAX - 1);
		$this->assertEquals($expected, $actual);
	}

	// test with
	public function test_getSideArticles5()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getSideArticles(PHP_INT_MIN + 1);
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * test method getActivePlugins() : array
	 */
	public function test_getActivePlugins()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getActivePlugins();
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * test method getAllPlugins() : array
	 */
	public function test_getAllPlugins()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getAllPlugins();
		$this->assertEquals($expected, $actual);
	}

	/*
	public function getSessionData() : array
	{
	}
	*/
	
	/**
	 * test method getTheme() : Theme
	 * @covers \Api\Api
	 * @uses \Framework\Query
	 * @uses \Framework\Theme
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_getTheme1()
	{
		$actual = $this->api->getTheme();
		$this->assertInstanceOf(\Framework\Theme::class, $actual);
		$this->assertTrue(get_class($actual) == 'Theme\DefaultTheme', "class " . get_class($actual) . " is not of expected class 'Theme\DefaultTheme'.");
		return $actual;
	}
	
	/**
	 * test method getTheme() : Theme
	 * @covers \Api\Api
	 * @uses \Framework\Query
	 * @uses \Framework\Theme
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	// Test when 
	public function test_getTheme2()
	{
		//TODO: inject a faulty theme name in the database
$this->markTestIncomplete();
		$this->expectException(Exception::class);
		//TODO: also test code and message
		$this->api->getTheme();
	}

	/**
	 * test method getAllThemes() : array
	 */
	public function test_getAllThemes()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getAllThemes();
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method getAdminTheme() : Theme
	 * @covers \Api\Api
	 * @uses \Framework\Query
	 * @uses \Framework\Theme
	 * @uses \Framework\Permissions
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_getAdminTheme1()
	{
		$actual = $this->api->getAdminTheme();
		$this->assertInstanceOf(\Framework\Theme::class, $actual);
		$this->assertInstanceOf(\Framework\iAdminTheme::class, $actual);
		$this->assertTrue(get_class($actual) == 'Theme\DefaultTheme', "class " . get_class($actual) . " is not of expected class 'Theme\DefaultTheme'.");
		return $actual;
	}
	
	// Test when 
	public function test_getAdminTheme2()
	{
		//TODO: inject a faulty admin theme name in the database
$this->markTestIncomplete();
		$this->expectException(Exception::class);
		//TODO: also test code and message
		$this->api->getAdminTheme();
	}

	/**
	 * test method getAllAdminThemes() : array
	 */
	public function test_getAllAdminThemes()
	{
		$this->markTestIncomplete();
		$expected = array();
		$actual = $this->api->getAllAdminThemes();
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method hasAdminTheme(Theme $theme) : bool
	 * @covers \Api\Api
	 * @uses \Framework\Theme
	 * @uses \Framework\Query
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 * @depends test_getTheme1
	 */
	// Test with the default theme
	public function test_hasAdminTheme1($theme)
	{
		$expected = true;
		$actual = $this->api->hasAdminTheme($theme);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method hasAdminTheme(Theme $theme) : bool
	 * @covers \Api\Api
	 * @uses \Framework\Theme
	 * @uses \Framework\Query
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 * @depends test_getAdminTheme1
	 */
	// Test with the default theme
	public function test_hasAdminTheme2($theme)
	{
		$expected = true;
		$actual = $this->api->hasAdminTheme($theme);
		$this->assertEquals($expected, $actual);
	}
	
	/** Test with a mock that is not an admin theme
	 * @covers \Api\Api
	 * @uses \Framework\Theme
	 * @uses \Framework\Query
	 * @uses \Framework\Q
	 * @uses \Framework\ShortArticle
	 */
	public function test_hasAdminTheme3()
	{
		$expected = false;
		$stub = $this->getMockBuilder(Framework\Theme::class)
                     ->disableOriginalConstructor()
                     ->disableOriginalClone()
                     ->disableArgumentCloning()
                     ->disallowMockingUnknownTypes()
                     ->getMock();
		$actual = $this->api->hasAdminTheme($stub);
		$this->assertEquals($expected, $actual);
	}

	/*
	public function getThemeNumberSides(Theme $theme) : int
	{
		return 0;
	}
	
	public function getThemeSettings(Theme $theme) : array
	{
	}
	
	public function setThemeSettings(Theme $theme, array $settings) : void
	{
	}
	
	public function addArticle(string $article, Meta $meta) : int
	{
	}
	
	public function removeArticle(int $id) : bool
	{
	}
	
	public function changeArticle(int $id, string $article, Meta $meta) : bool
	{
	}
	
	public function checkPassword(string $username, string $password) : bool
	{
		if($this->checkPerms(self::_PREM_ONLY_FRAMEWORK))
		{
			if(($check = $this->query->getPassword($username)) == null) return false;
			if(password_verify($password, $check) === true) return true;
			return false;
			
		}
		else throw new Exception("You can not call this function.");
	}

	public function getUserdata(string $username) : Userdata
	{
		if($this->checkPerms(self::_PREM_ONLY_FRAMEWORK))
		{
			if(is_array(($u = $this->query->getUserdata($username)))) return new Userdata($u);
			
		}
		else throw new Exception("You can not call this function.");
	}
	
	public function getUserdataById(int $id) : Userdata
	{*/
}
?>
