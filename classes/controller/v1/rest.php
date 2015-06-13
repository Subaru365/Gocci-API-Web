<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * restpage api
 *
 */

class Controller_V1_Rest extends Controller
{

    public function action_index()
    {
    	$user_id = Input::get('user_id');
    	$rest_id = Input::get('rest_id');
		$limit   = Input::get('limit');

		if (empty($limit)) {
		    $limit = 30;
		}


		//--------------------------------------------//
		//"Rest_Data"

		$rest_data = Model_Restaurant::get_data($rest_id);

		//後に実装
		//$cheer_num = Model_Cheer::get_flag($rest_id);
		//$rest_data['0']['rest_cheer_num'] = $cheer_num;

		$want_flag = Model_Want::get_flag($user_id, $rest_id);
		$rest_data['0']['want_flag']= $want_flag;


		//--------------------------------------------//
		//"POST_Data"

		$sort_key  = 'rest';
		$post_data = Model_Post::get_data($user_id, $sort_key, $rest_id, $limit);


		//--------------------------------------------//
	   	//成型->出力

	   	$data = array(
	   		"restaurants" => $rest_data[0],
	   		"posts" => $post_data
	   	);

	   	$restaurantpage = json_encode($data , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );


	   	echo "$restaurantpage";


	}
}
