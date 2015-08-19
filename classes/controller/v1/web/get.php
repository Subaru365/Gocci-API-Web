<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);

class Controller_V1_Web_Get extends Controller_V1_Web_Base
{
	public function action_timeline()
    {
    	// Timeline 
	    $sort_key = 'all';
	    $limit    = 10;
	    $user_id  = session::get('user_id');
	    $data     = Model_Post::get_data($user_id, $sort_key, $sort_key,$limit);
	   	$status   = $this->output_json($data);
    }

}