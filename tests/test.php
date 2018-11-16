<?php
use PHPUnit\Framework\TestCase;

class test extends TestCase
{
	$parse_array = array();

	public function test_api_parse_xml()
	{
		$expected = '';
		$actual = api_parse_xml($parse_array);
		$this->assertXmlStringEqualsXmlString($expected, $actual);
	}
    
	public function test_api_parse_json()
	{
		$expected = json_encode($parse_array);
		$actual = api_parse_json($parse_array);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
    }
    
	public function test_api_parse_text()
	{
		$expected = ;
		$actual = api_parse_text($parse_array);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}
}
?>
