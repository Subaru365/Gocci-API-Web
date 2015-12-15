<?php

class Person
{
    public $name;
    private $gender;
    private $birthdate;

    public function __construct($name, $gender, $birthdate) {
	$this->name = $name;
	$this->gender = $gender;
	$this->birthdate = $birthdate;
    }

    public function get_gender()
    {
	return $this->gender;
    }
}
