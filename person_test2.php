<?php

require __DIR__ . '/person.php';

class Person_Test2 extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
	fwrite(STDOUT, __METHOD__ . "\n");
    }

    protected function setUp()
    {
	fwrite(STDOUT, __METHOD__ . "\n");
    }

    public function test_男性の場合は性別を取得するとmaleである()
    {
	fwrite(STDOUT, __METHOD__ . "\n");

	$person = new Person('kazu', 'male', '1994/9/14');
	$test = $person->get_gender();
	$expected = 'male';
	$this->assertEquals($expected, $test);
    }

    public function test_女性の場合は性別を取得するとfemaleである()
    {
	fwrite(STDOUT, __METHOD__ . "\n");

	$person = new Person('ai', 'female', '1994/9/14');
	$test = $person->get_gender();
	$expected = 'female';
	$this->assertEquals($expected, $test);
    }

    public function tearDown()
    {
	fwrite(STDOUT, __METHOD__ . "\n");
    }

    public static function tearDownAfterClass()
    {
	fwrite(STDOUT, __METHOD__ . "\n");
    }

}
