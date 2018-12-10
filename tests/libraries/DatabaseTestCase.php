<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\DataSet\XmlDataSet;
use PHPUnit\DbUnit\DataSet\AbstractDataSet;

const _DB_TEST_FILE = './tests/resources/sql_test.db';
const _DB_TEST_STRING = 'sqlite:' . _DB_TEST_FILE;
const _TEST_CONFIG_FILE = './tests/resources/test_config.inc';

abstract class DatabaseTestCase extends TestCase
{
	abstract protected function getDataSet();

	// only instantiate pdo once for test clean-up/fixture load
	static protected $pdo = null;
	
	private $columns = array();

	protected function setUp() : void
	{
		parent::setUp();
		
		$data = $this->getDataSet();

		//insert data into table
		$this->insertDataIntoSQL($data, $this->getConnection());
	}
	
	protected function tearDown() : void
	{
		$this->truncateDB($this->getConnection());
		parent::tearDown();
	}

	final public function getConnection()
	{
		if (self::$pdo == null)
			self::$pdo = new PDO(_DB_TEST_STRING);
		return self::$pdo;
	}
	
	final protected function getDataSetFromFile($name)
	{
		return new XmlDataSet(__DIR__ . '/../resources/' . $name . '.xml');
	}
	
	final private function insertDataIntoSQL(AbstractDataSet $data, PDO $pdo)
	{
		$this->columns = array();
		foreach($data->getTableNames() as $table_name)
		{
			$this->columns[] = $table_name;
			$table = $data->getTable($table_name);
			$columns = $table->getTableMetaData()->getColumns();

			if($table->getRowCount() < 1) continue;
			$v = array();
			for($r = 0; $r < $table->getRowCount(); $r++)
			{
				$t = array();
				foreach($columns as $column)
				{
					$t[] = ':' . $column . (string)$r;
				}
				$v[] = '(' . implode(',', $t) . ')';
			}
			$sql = 'INSERT INTO ' . $table_name . ' (`' . implode('`,`', $columns) . '`) VALUES ' . implode(',', $v) . ';';

			$stmt = $pdo->prepare($sql);
			
			for($r = 0; $r < $table->getRowCount(); $r++)
			{
				$row = $table->getRow($r);
				foreach($columns as $column)
				{
					$stmt->bindValue(':' . $column . (string)$r, $row[$column]);
				}
			}
			if(!$stmt->execute())
				throw new Exception('failed to execute query: ' . $sql . '.');
		}
	}
	
	final private function truncateDB(PDO $pdo) : void
	{
		foreach($this->columns as $column)
		{
			$pdo->exec('DELETE FROM ' . $column . ';');
		}
	}
}
?>
