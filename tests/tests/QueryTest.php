<?php

use PHPUnit\Framework\TestCase;
use Framework\Query;

class QueryTest extends DatabaseTestCase
{
	private $query;
	private $session;

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

		$this->session = $this->getMockBuilder(Framework\Session::class)
                     ->disableOriginalConstructor()
                     ->disableOriginalClone()
                     ->disableArgumentCloning()
                     ->disallowMockingUnknownTypes()
                     ->setMethods(['isValid','getPermissions','logon','logoff','__destruct'])
                     ->getMock();

        // Configure the stub.
        $this->session->method('isValid')
             ->willReturn(true);
		$this->session->method('getPermissions')
             ->willReturn((1 << 8) - 1);
		$this->session->method('logon')
             ->willReturn(true);
		$this->session->method('logoff')
             ->willReturn(true);
		$this->session->method('__destruct')
             ->willReturn(null);
		$this->query = new Query($this->session, self::$pdo);
	}

	protected function getDataSet()
	{
		return $this->getDataSetFromFile('test_query_1');
	}

	/**
	 * test calling an invalid method
	 */
	// Test with an existing tag
	public function test_InvalidCall()
	{
		$this->expectException(Exception::class);
		$actual = $this->query->InvalidCall(0, $this->session->getPermissions());
	}

	/**
	 * test method getArticle($id, $permissions)
	 */
	// Test with an existing tag
	public function test_getArticle1()
	{
		$expected = '<h1>Hello World!</h1>';
		$actual = $this->query->getArticle(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	public function test_getArticleMeta1()
	{
		$expected = '';
		$actual = $this->query->getArticleMeta(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	public function test_getSubArticles1()
	{
		$expected = '';
		$actual = $this->query->getSubArticles(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	public function test_getSideArticles1()
	{
		$expected = '';
		$actual = $this->query->getSideArticles(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	public function test_getArticleTags1()
	{
		$expected = '1,2';
		$actual = $this->query->getArticleTags(0);
		$this->assertEquals($expected, $actual);
	}

	public function test_getArticlesByTags1()
	{
		$expected = '';
		$actual = $this->query->getArticlesByTags('0,1', $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	public function test_getArticlesByTag1()
	{
		$expected = '';
		$actual = $this->query->getArticlesByTag(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	public function test_getNumberOfArticlesByTag1()
	{
		$expected = '';
		$actual = $this->query->getNumberOfArticlesByTag($this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	public function test_getSettingValue1()
	{
		$expected = 'DefaultTheme';
		$actual = $this->query->getSettingValue('theme');
		$this->assertEquals($expected, $actual);
	}

	public function test_getPassword1()
	{
		$expected = '1234567890';
		$actual = $this->query->getPassword('me@example.org');
		$this->assertEquals($expected, $actual);
	}

	public function test_getUserdata1()
	{
		$expected = '';
		$actual = $this->query->getUserdata('me@example.org');
		$this->assertEquals($expected, $actual);
	}

	public function test_getUserdataById1()
	{
		$expected = '';
		$actual = $this->query->getUserdataById(0);
		$this->assertEquals($expected, $actual);
	}
}
?>
