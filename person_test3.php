<?php

require __DIR__ . '/person.php';

class Person_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider_人データ
     */
    public function test_指定した性別は取得した性別と一致する($name, $gender, $birthdate)
    {
	$person = new Person($name, $gender, $birthdate);
	$test = $person->get_gender();
	$expected = $gender;

	$this->assertEquals($expected, $test);
    }

    public function provider_人データ()
    {
	return array(
	    array('kazu', 'male', '1994/9/14'),
	    array('tani', 'male', '1994/9/14'),
	    array('ai',   'female', '1994/9/14'),
	);
    }
}
