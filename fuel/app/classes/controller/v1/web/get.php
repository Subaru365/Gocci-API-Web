<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);

class Controller_V1_Web_Get extends Controller_V1_Web_Base
{
	public function action_timeline()
    {
    	$user_id = session::get('user_id');
    	print "あなたのuser_idは" . $user_id;
    	// Timeline 
	    $sort_key = 'all';
	    $limit    = 10;

	    $user_id   = ''; // jwtで取得したuser_idを取得。
	    $data     = Model_Post::get_data($user_id, $sort_key, $sort_key,$limit);
	   	$status   = $this->output_json($data);
	   	
    }

}