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
	 */
	public function test_logon1()
	{
        $this->markTestIncomplete();
		$expected = true;
		$actual = $this->session->login();
		$this->assertEquals($expected, $actual);
		
		return $actual;
	}
	
	/**
	 * @covers \Framework\Session
	 * @depends test_logon1
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
	 * @depends test_logon1
	 */
	public function test_logoff(Session $input)
	{
		$input->logoff();

		$expected = 0;
		$actual = $input->getPermissions();
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * @covers \Framework\Session
	 * @uses \Framework\Settings
	 * @uses \Api\Api
	 */
	public function test_sessionLoad1()
	{
        $this->markTestIncomplete();
		unset($this->session);
		self::$session_static = null;

		$input = array(
		'is_admin' => true,
		'started_time' => 1544180400,
		'current_article' => 3,
		'user_id' => 1
		);
		$_SESSION[Session::_SESSION_SAVE_ID] = $input;		

		$session = new Session(Session::_SESSION_NO_NEW, false, null, _TEST_CONFIG_FILE);
		$this->assertEquals(3, $session->getArticleId());
		unset($session);
		$this->assertEquals($input, $_SESSION[Session::_SESSION_SAVE_ID]);
		
	}
}
?>
