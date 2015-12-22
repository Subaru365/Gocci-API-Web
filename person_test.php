<?php

require __DIR__ . '/person.php';

class Person_Test extends PHPUnit_Framework_TestCase
{
    public function test_男性の場合は性別を取得するとmaleである()
    {
	$person = new Person('kazu', 'male', '1994/9/14');

	$text = $person->get_gender();
	$expected = 'male';

	$this->assertEquals($expected, $text);
	// $this->assertEquals($expected, $test); // Failed
    }
}
