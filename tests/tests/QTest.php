<?php

use PHPUnit\Framework\TestCase;
use Framework\Q;

class QTest extends TestCase
{
	/**
	 * Covers \Framework\Q
	 */
	public function test_Q()
	{
		$q = new Q('SELECT * FROM articles', 4, 10, 11);
		$this->assertEquals('SELECT * FROM articles', $q->query);
		$this->assertEquals(4, $q->n);
		$this->assertEquals(10, $q->options);
		$this->assertEquals(11, $q->permissions);
	}
}
?>
