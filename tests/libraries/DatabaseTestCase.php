<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

const _DB_TEST_FILE = './tests/resources/sql_test.db';
const _DB_TEST_STRING = 'sqlite:' . _DB_TEST_FILE;
const _TEST_CONFIG_FILE = './tests/resources/test_config.inc';

abstract class DatabaseTestCase extends TestCase
{
	use TestCaseTrait;

	// only instantiate pdo once for test clean-up/fixture load
	static protected $pdo = null;

	// only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
	private $conn = null;

	protected function setUp() : void
	{
		// \PHPUnit_Extensions_Database_TestCase uses this for important stuff, so be sure to call it
		parent::setUp();
	}

	final public function getConnection()
	{
		if ($this->conn === null)
		{
			if (self::$pdo == null)
				self::$pdo = new PDO(_DB_TEST_STRING);
			$this->conn = $this->createDefaultDBConnection(self::$pdo, _DB_TEST_FILE);
		}

		return $this->conn;
	}
	
	final protected function getDataSetFromFile($name)
	{
		return $this->createMySQLXMLDataSet(__DIR__ . '/../resources/' . $name . '.xml');
	}
}
?>
