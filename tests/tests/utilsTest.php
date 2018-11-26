<?php

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
	private $parse_array = array();

	public function test_api_parse_xml()
	{
		$expected = '';
		$actual = Framework\Utils::apiParseXml($this->parse_array);
		$this->assertXmlStringEqualsXmlString($expected, $actual);
	}
    
	public function test_api_parse_json()
	{
		$expected = json_encode($this->parse_array);
		$actual = Framework\Utils::apiParseJson($this->parse_array);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
    }
    
	public function test_api_parse_text()
	{
		$expected = '';
		$actual = Framework\Utils::apiParseText($this->parse_array);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}
}
?>
