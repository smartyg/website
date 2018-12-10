<?php

use PHPUnit\Framework\TestCase;
use Framework\Session;
use Framework\Exceptions\SessionException;

class SessionTest extends DatabaseTestCase
{
	private $session;
	static private $session_static = null;
	
	/**
	 * Instantiates the instance under test.
	 */
	protected function setUp() : void
	{
		// \PHPUnit_Extensions_Database_TestCase uses this for important stuff, so be sure to call it
		parent::setUp();

		//$this->session = new Session(Session::_SESSION_NEW, false, null, _TEST_CONFIG_FILE);
		if(self::$session_static == null)
            self::$session_static = new Session(Session::_SESSION_NEW, false, null, _TEST_CONFIG_FILE);
        $this->session = self::$session_static;
	}
	
	protected function getDataSet()
	{
		return $this->getDataSetFromFile('test_1');
	}

	/**
	 * @covers \Framework\Session
	 * @uses \Api\Api
     * @uses \Framework\Permissions
     * @uses \Framework\Q
     * @uses \Framework\Query
     * @uses \Framework\Settings
     * @uses \Framework\userdata
	 */
	public function test_getArticleId()
	{
		$expected = 0;
		$actual = $this->session->getArticleId();
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * @covers \Framework\Session
	 */
	public function test_isValid()
	{
		$expected = true;
		$actual = $this->session->isValid();
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * @covers \Framework\Session
	 * @uses \Framework\userdata
	 */
	public function test_getPermissions1()
	{
		$expected = 0;
		$actual = $this->session->getPermissions();
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers \Framework\Session
	 * @covers \Framework\Query
	 * @covers \Api\Api
	 * @uses \Framework\Userdata
	 * @uses \Framework\Permissions
	 */
	public function test_login1()
	{
		$expected = true;
		$actual = $this->session->login('John.Doe@example.org', 'ATestPassword');
		$this->assertEquals($expected, $actual);
		
		return $this->session;
	}

	/**
	 * @covers \Framework\Session
	 * @covers \Framework\Query
	 * @covers \Api\Api
	 * @uses \Framework\Userdata
	 * @uses \Framework\Permissions
	 * @uses \Framework\Exceptions\SessionException
	 * @uses \Framework\Exceptions\ExternalException
	 */
	public function test_login2()
	{
		$this->expectException(SessionException::class);
		$this->expectExceptionCode(SessionException::LOGIN_FAILED);
		$this->session->login('John.Doe@example.org', 'AWrongPassport');
	}

	/**
	 * @covers \Framework\Session
	 * @covers \Framework\Query
	 * @covers \Api\Api
	 * @uses \Framework\Userdata
	 * @uses \Framework\Permissions
	 * @uses \Framework\Exceptions\SessionException
	 * @uses \Framework\Exceptions\ExternalException
	 */
	public function test_login3()
	{
		$this->expectException(SessionException::class);
		$this->expectExceptionCode(SessionException::LOGIN_FAILED);
		$this->session->login('no.existing.user@example.org', 'ATestPassword');
	}

	/**
	 * @covers \Framework\Session
	 * @covers \Framework\Query
	 * @covers \Api\Api
	 * @uses \Framework\Userdata
	 * @uses \Framework\Permissions
	 */
	public function test_loginByApi1()
	{
		$expected = true;
		$actual = $this->session->getApi()->login('John.Doe@example.org', 'ATestPassword');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers \Framework\Session
	 * @covers \Framework\Query
	 * @covers \Api\Api
	 * @uses \Framework\Userdata
	 * @uses \Framework\Permissions
	 * @uses \Framework\Exceptions\SessionException
	 * @uses \Framework\Exceptions\ExternalException
	 */
	public function test_loginByApi2()
	{
		$this->assertFalse($this->session->getApi()->login('John.Doe@example.org', 'AWrongPassport'));
	}

	/**
	 * @covers \Framework\Session
	 * @covers \Framework\Query
	 * @covers \Api\Api
	 * @uses \Framework\Userdata
	 * @uses \Framework\Permissions
	 * @uses \Framework\Exceptions\SessionException
	 * @uses \Framework\Exceptions\ExternalException
	 */
	public function test_loginByApi3()
	{
		$this->assertFalse($this->session->getApi()->login('no.existing.user@example.org', 'ATestPassword'));
	}

	/**
	 * @covers \Framework\Session
	 * @uses \Framework\Userdata
	 * @depends test_login1
	 */
	public function test_getPermissions2(Session $input)
	{
		$expected = 1;
		$actual = $input->getPermissions();
		$this->assertEquals($expected, $actual);

		return $input;
	}

	/**
	 * @covers \Framework\Session
	 * @covers \Framework\Query
	 * @covers \Api\Api
	 * @uses \Framework\Permissions
	 * @uses \Framework\Userdata
	 * @depends test_login1
	 */
	public function test_logoff(Session $input)
	{
		$input->logoff();

		$expected = 0;
		$actual = $input->getPermissions();
		$this->assertEquals($expected, $actual);
	}
}
?>