<?php

// require_once '/var/www/gocci/fuel/core/classes/request.php';

class Test_Controller_Hoge extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
	parent::setUp();
    }

    protected function tearDown()
    {
	parent::tearDown();
    }

    public function test_index()
    {
	$_GET = array( "format" => "json");
	$request = Request::forge('/hoge/list', true)->execute();

	$expect = '["abc"]';
	$this->assertEquals( $expect, $response->body);

    }
}
