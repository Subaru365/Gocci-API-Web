<?php

class Controller_Hoge extends Controller_Rest
{
    public function get_list()
    {
	$this->response( array("abc"));
    }
}
