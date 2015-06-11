<?php
header('Content-Type: application/json; charset=UTF-8');


class Controller_V1_Test extends Controller
{

	public function action_index()
	{
		$post_id = Input::get('post_id');
        $user_id = Input::get('user_id');

		$data = Model_Post::get_data($post_id);

		$post_data = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );
	    echo "$post_data";
	}

}