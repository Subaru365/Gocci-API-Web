<?php

class Controller_V1_Test extends Controller
{
    public function action_index()
    {
        $input_data = array_merge(Input::get(), Input::post());
	print_R($input_data);
    }
}
