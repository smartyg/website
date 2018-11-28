<?php

use PHPUnit\Framework\TestCase;
use Api\Api;

class ApiTest extends DatabaseTestCase
{
	private $api;
	
	/**
	 * Instantiates the instance under test.
	 */
	protected function setUp() : void
	{
		// \PHPUnit_Extensions_Database_TestCase uses this for important stuff, so be sure to call it
		parent::setUp();
		// Make sure a connection was established and the $pdo property has a value
		$this->getConnection();
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
		$this->api = new Api($stub, self::$pdo);
	}
	
	protected function getDataSet()
	{
		return $this->getDataSetFromFile('test_api_1');
	}
	
	/**
	 * test method getRelatedArticles(string $tags = "") : array
	 */
	// Test with an existing tag
	public function test_getRelatedArticles1()
	{
		$expected = array();
		$actual = $this->api->getRelatedArticles("ExistingTag");
		$this->assertEquals($expected, $actual);
	}

	// Test with two existing tags
	public function test_getRelatedArticles2()
	{
		$expected = array();
		$actual = $this->api->getRelatedArticles("ExistingTag,OtherExistingTag");
		$this->assertEquals($expected, $actual);
	}

	// Test with a non-exisitng tag
	public function test_getRelatedArticles3()
	{
		$expected = array();
		$actual = $this->api->getRelatedArticles("NonExsistingTag");
		$this->assertEquals($expected, $actual);
	}

	// Test with one exisitng and one non-exisitng tag
	public function test_getRelatedArticles4()
	{
		$expected = array();
		$actual = $this->api->getRelatedArticles("ExistingTag,NonExsistingTag");
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method getSubArticles(int $id = 0) : array
	 */
	// test with default value
	public function test_getSubArticles1()
	{
		$expected = array();
		$actual = $this->api->getSubArticles();
		$this->assertEquals($expected, $actual);
	}

	// test with a valid value
	public function test_getSubArticles2()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(1);
		$this->assertEquals($expected, $actual);
	}

	// Another test with a vaild value
	public function test_getSubArticles3()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(3);
		$this->assertEquals($expected, $actual);
	}

	// Test with an non-existing value
	public function test_getSubArticles4()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(404);
		$this->assertEquals($expected, $actual);
	}

	// Test with a non existing huge value
	public function test_getSubArticles5()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(PHP_INT_MAX - 1);
		$this->assertEquals($expected, $actual);
	}

	// Test with an non existing huge negative value
	public function test_getSubArticles6()
	{
		$expected = array();
		$actual = $this->api->getSubArticles(PHP_INT_MIN + 1);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method getArticle(int $id) : array
	 */
	public function test_getArticle1()
	{
		$expected = array();
		$actual = $this->api->getArticle(1);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_getArticle2()
	{
		$expected = array();
		$actual = $this->api->getArticle(2);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_getArticle3()
	{
		$expected = array();
		$actual = $this->api->getArticle(404);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_getArticle4()
	{
		$expected = array();
		$actual = $this->api->getArticle(PHP_INT_MAX - 1);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_getArticle5()
	{
		$expected = array();
		$actual = $this->api->getArticle(PHP_INT_MIN + 1);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method getArticleMeta(int $id) : Meta
	 */
	public function test_getArticleMeta1()
	{
		$expected = array();
		$actual = $this->api->getArticleMeta(1);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_getArticleMeta2()
	{
		$expected = array();
		$actual = $this->api->getArticleMeta(3);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_getArticleMeta3()
	{
		$expected = array();
		$actual = $this->api->getArticleMeta(404);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_getArticleMeta4()
	{
		$expected = array();
		$actual = $this->api->getArticleMeta(PHP_INT_MAX - 1);
		$this->assertEquals($expected, $actual);
	}
	
	public function test_getArticleMeta5()
	{
		$expected = array();
		$actual = $this->api->getArticleMeta(PHP_INT_MIN + 1);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method getSideArticles(int $id) : array
	 */
	// test with
	public function test_getSideArticles1()
	{
		$expected = array();
		$actual = $this->api->getSideArticles(0);
		$this->assertEquals($expected, $actual);
	}

	// test with
	public function test_getSideArticles2()
	{
		$expected = array();
		$actual = $this->api->getSideArticles(2);
		$this->assertEquals($expected, $actual);
	}

	// test with
	public function test_getSideArticles3()
	{
		$expected = array();
		$actual = $this->api->getSideArticles(404);
		$this->assertEquals($expected, $actual);
	}

	// test with
	public function test_getSideArticles4()
	{
		$expected = array();
		$actual = $this->api->getSideArticles(PHP_INT_MAX - 1);
		$this->assertEquals($expected, $actual);
	}

	// test with
	public function test_getSideArticles5()
	{
		$expected = array();
		$actual = $this->api->getSideArticles(PHP_INT_MIN + 1);
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * test method getActivePlugins() : array
	 */
	public function test_getActivePlugins()
	{
		$expected = array();
		$actual = $this->api->getActivePlugins();
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * test method getAllPlugins() : array
	 */
	public function test_getAllPlugins()
	{
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
	 */
	public function test_getTheme1()
	{
		$actual = $this->api->getTheme();
		$this->assertInstanceOf(Framework\Theme::class, $actual);
	}
	
	// Test when 
	public function test_getTheme2()
	{
		//TODO: inject a faulty theme name in the database

		$this->expectException(Exception::class);
		//TODO: also test code and message
		$this->api->getTheme();
	}

	/**
	 * test method getAllThemes() : array
	 */
	public function test_getAllThemes()
	{
		$expected = array();
		$actual = $this->api->getAllThemes();
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method getAdminTheme() : Theme
	 */
	public function test_getAdminTheme1()
	{
		$actual = $this->api->getAdminTheme();
		$this->assertInstanceOf(Framework\Theme::class, $actual);
	}
	
	// Test when 
	public function test_getAdminTheme2()
	{
		//TODO: inject a faulty admin theme name in the database

		$this->expectException(Exception::class);
		//TODO: also test code and message
		$this->api->getAdminTheme();
	}

	/**
	 * test method getAllAdminThemes() : array
	 */
	public function test_getAllAdminThemes()
	{
		$expected = array();
		$actual = $this->api->getAllAdminThemes();
		$this->assertEquals($expected, $actual);
	}

	/**
	 * test method hasAdminTheme(Theme $theme) : bool
	 */
	// Test with the default theme
	public function test_hasAdminTheme1()
	{
		$expected = true;
		$actual = $this->api->hasAdminTheme(new defaultTheme());
		$this->assertEquals($expected, $actual);
	}
	
	// Test with a mock that is not an admin theme
	public function test_hasAdminTheme2()
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
