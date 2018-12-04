<?php

use PHPUnit\Framework\TestCase;
use Framework\Query;
use Framework\Meta;
use Framework\Exceptions\InternalException;

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

		// Remove the 'final' flag from the class definition
		uopz_flags(Framework\Session::class, null, 0);

		//make a mock session class
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
		return $this->getDataSetFromFile('test_1');
	}

	/**
	 * test calling an invalid method
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Exceptions\InternalException
	 */
	public function test_InvalidCall()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::QUERY_NOT_EXISTS);
		$this->query->InvalidCall(0, $this->session->getPermissions());
	}
	
	/**
	 * test calling with wrong number count
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Exceptions\InternalException
	 */
	public function test_InvalidArguments1()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_NUM_RECORDS_RETURNED);
		$this->query->getArticle("This is no number", "This is no permission");
	}
	
	/**
	 * test calling with wrong number count
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Exceptions\InternalException
	 */
	// Test with an existing tag
	public function test_InvalidArguments2()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_ARGUMENT_COUNT);
		$this->query->getArticle();
	}

	/**
	 * test method getArticle($id, $permissions)
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	// Test with an existing tag
	public function test_getArticle1()
	{
		$expected = '<h1>Hello World!</h1><p>This is my website</p>';
		$actual = $this->query->getArticle(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	/** Test another existing article
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getArticle2()
	{
		$expected = '<h2>Lorem Ipsum</h2><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui.</p>';
		$actual = $this->query->getArticle(2, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	/** Test an one existing article
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Exceptions\InternalException
	 */
	public function test_getArticle3()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_NUM_RECORDS_RETURNED);
		$this->query->getArticle(404, $this->session->getPermissions());
	}

	/** Test a non existing article
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Exceptions\InternalException
	 */
	public function test_getArticle4()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_NUM_RECORDS_RETURNED);
		$this->query->getArticle(PHP_INT_MAX - 1, $this->session->getPermissions());
	}
	
	/** Test a non existing article
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Exceptions\InternalException
	 */
	public function test_getArticle5()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_NUM_RECORDS_RETURNED);
		$this->query->getArticle(PHP_INT_MIN + 1, $this->session->getPermissions());
	}

	/**
	 * Test getArticleMeta query
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getArticleMeta1()
	{
		$actual = $this->query->getArticleMeta(0, $this->session->getPermissions());

		$this->assertTrue(is_array($actual), 'Variable is not an array.');

		$this->assertArrayHasKey('title', $actual);
		$this->assertEquals('Hello World!', $actual['title']);

		$this->assertArrayHasKey('author', $actual);
		$this->assertEquals('public', $actual['author']);

		$this->assertArrayHasKey('description', $actual);
		$this->assertEquals('Welcome to my website', $actual['description']);

		$this->assertArrayHasKey('date_modified', $actual);
		$this->assertEquals(1543681181, $actual['date_modified']);

		$this->assertArrayHasKey('date_created', $actual);
		$this->assertEquals(1543681181, $actual['date_created']);

		return $actual;
	}
	
	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getArticleMeta2()
	{
		$actual = $this->query->getArticleMeta(2, $this->session->getPermissions());

		$this->assertTrue(is_array($actual), 'Variable is not an array.');

		$this->assertArrayHasKey('title', $actual);
		$this->assertEquals('Third test page', $actual['title']);

		$this->assertArrayHasKey('author', $actual);
		$this->assertEquals('John Doe', $actual['author']);

		$this->assertArrayHasKey('description', $actual);
		$this->assertEquals('Lorem Ipsum', $actual['description']);

		$this->assertArrayHasKey('date_modified', $actual);
		$this->assertEquals(1543681181, $actual['date_modified']);

		$this->assertArrayHasKey('date_created', $actual);
		$this->assertEquals(1543681181, $actual['date_created']);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * @uses \Framework\Exceptions\InternalException
	 */
	public function test_getArticleMeta3()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_NUM_RECORDS_RETURNED);
		$this->query->getArticleMeta(404, $this->session->getPermissions());
	}

	/**
	 * Test getSubArticles query
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getSubArticles1()
	{
		$expected = array(
			array('id' => '1', 'title' => 'Second test page', 'description' => 'Another test page'),
			array('id' => '3', 'title' => 'Private test page', 'description' => 'This page is private')
			);
		$actual = $this->query->getSubArticles(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * Test getSubArticles query
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getSubArticles2()
	{
		$expected = array(
			array('id' => '2', 'title' => 'Third test page', 'description' => 'Lorem Ipsum')
			);
		$actual = $this->query->getSubArticles(1, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * Test getSubArticles query
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getSubArticles3()
	{
		$expected = array();
		$actual = $this->query->getSubArticles(3, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getSideArticles1()
	{
		$this->markTestIncomplete();
		$expected = '';
		$actual = $this->query->getSideArticles(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	/**
	 * Test getArticleTags query
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getArticleTags1()
	{
		$expected = '0';
		$actual = $this->query->getArticleTags(0);
		$this->assertEquals($expected, $actual);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getArticleTags2()
	{
		$expected = '0,2,3';
		$actual = $this->query->getArticleTags(2);
		$this->assertEquals($expected, $actual);
		
		return $actual;
	}
	
		/**
	 * @covers \Framework\Query
	 * @uses \Framework\Meta
	 * @uses \Framework\shortArticle
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * 
	 * @depends test_getArticleMeta1
	 * @depends test_getArticleTags1
	 */
	public function test_getArticleMeta1_toMeta(array $input, string $tags)
	{
		$input['tags'] = explode(',', $tags);

		$actual = new Meta($input);
		
		$this->assertEquals('Hello World!', $actual->title);
		$this->assertEquals('public', $actual->author);
		$this->assertEquals(['0'], $actual->tags);
		$this->assertEquals('Welcome to my website', $actual->description);
		$this->assertEquals(1543681181, $actual->date_modified);
		$this->assertEquals(1543681181, $actual->date_created);
	}

	/**
	 * @covers \Framework\Query
	 * @uses \Framework\Meta
	 * @uses \Framework\shortArticle
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 * 
	 * @depends test_getArticleMeta2
	 * @depends test_getArticleTags2
	 */
	public function test_getArticleMeta2_toMeta(array $input, string $tags)
	{
		$input['tags'] = explode(',', $tags);

		$actual = new Meta($input);
		
		$this->assertEquals('Third test page', $actual->title);
		$this->assertEquals('John Doe', $actual->author);
		$this->assertEquals(['0', '2', '3'], $actual->tags);
		$this->assertEquals('Lorem Ipsum', $actual->description);
		$this->assertEquals(1543681181, $actual->date_modified);
		$this->assertEquals(1543681181, $actual->date_created);
	}

	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 *//*
	public function test_getArticlesByTags1()
	{
		$expected = array(
			array('id' => '0', 'title' => 'Hello World!', 'description' => 'Welcome to my website'),
			array('id' => '2', 'title' => 'Third test page', 'description' => 'Lorem Ipsum'),
			array('id' => '3', 'title' => 'Private test page', 'description' => 'This page is private')
			);
		$actual = $this->query->getArticlesByTags('0,1', $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}
*/
	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 *//*
	public function test_getArticlesByTags2()
	{
		$expected = array(
			array('id' => '0', 'title' => 'Hello World!', 'description' => 'Welcome to my website'),
			array('id' => '1', 'title' => 'Second test page', 'description' => 'Another test page'),
			array('id' => '2', 'title' => 'Third test page', 'description' => 'Lorem Ipsum'),
			array('id' => '3', 'title' => 'Private test page', 'description' => 'This page is private')
			);
		$actual = $this->query->getArticlesByTags('0,1,3,2', $this->session->getPermissions());
		var_dump($actual);
		$this->assertEquals($expected, $actual);
	}
*/
	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getArticlesByTag1()
	{
		$expected = array(
			array('id' => '0', 'title' => 'Hello World!', 'description' => 'Welcome to my website'),
			array('id' => '2', 'title' => 'Third test page', 'description' => 'Lorem Ipsum')
			);
		$actual = $this->query->getArticlesByTag(0, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getArticlesByTag2()
	{
		$expected = array(
			array('id' => '1', 'title' => 'Second test page', 'description' => 'Another test page'),
			array('id' => '2', 'title' => 'Third test page', 'description' => 'Lorem Ipsum')
			);
		$actual = $this->query->getArticlesByTag(3, $this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getNumberOfArticlesByTag1()
	{
		$expected = array(
			array('id' => '0', 'number' => '2'),
			array('id' => '1', 'number' => '1'),
			array('id' => '2', 'number' => '2'),
			array('id' => '3', 'number' => '2')
			);
		$actual = $this->query->getNumberOfArticlesByTag($this->session->getPermissions());
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getSettingValue1()
	{
		$expected = 'DefaultTheme';
		$actual = $this->query->getSettingValue('theme');
		$this->assertEquals($expected, $actual);
	}
	
	/** Test with invalue key
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getSettingValue2()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_NUM_RECORDS_RETURNED);
		$this->query->getSettingValue('invalidKey');
	}

	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getPassword1()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_PERMISSION);
		$this->query->getPassword('me@example.org');
	}

	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getUserdata1()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_PERMISSION);
		$this->query->getUserdata('me@example.org');
	}

	/**
	 * @covers \Framework\Query
	 * @uses \Framework\permissions
	 * @uses \Framework\Q
	 */
	public function test_getUserdataById1()
	{
		$this->expectException(InternalException::class);
		$this->expectExceptionCode(InternalException::WRONG_PERMISSION);
		$this->query->getUserdataById(0);
	}
}
?>
