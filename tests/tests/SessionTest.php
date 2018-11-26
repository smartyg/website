<?php

use PHPUnit\Framework\TestCase;
use Framework\Session;

class SessionTest extends DatabaseTestCase
{
	private $session;
	
	/**
	 * Instantiates the instance under test.
	 */
	protected function setUp() : void
	{
		// \PHPUnit_Extensions_Database_TestCase uses this for important stuff, so be sure to call it
		parent::setUp();
		$this->session = new Session(Session::_SESSION_NEW, false, null, _TEST_CONFIG_FILE);
	}
	
	protected function getDataSet()
	{
		return $this->getDataSetFromFile('test_1');
	}

	public function test_one()
	{
		$expected = 0;
		$actual = $this->session->getArticleId();
		$this->assertEquals($expected, $actual);
	}
}
?>
