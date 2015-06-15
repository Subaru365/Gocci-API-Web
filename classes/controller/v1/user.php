<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
*  User api
*/
class Controller_V1_User extends Controller
{

	public static function action_index()
	{

		$user_id 		= Input::get('user_id');
		$target_user_id = Input::get('target_user_id');


		$user_data = Model_User::get_data($user_id, $target_user_id);

		//---------------------------------------------------------//
        //投稿データ($post_data)

        $sort_key  = 'user';
        $limit     = 30;

        $post_data = Model_Post::get_data(
            $target_user_id, $sort_key, $target_user_id, $limit);


 		//--------------------------------------------//
	   	//成型->出力

	   	$data = array(
	   		"header" => $user_data,
	   		"posts"  => $post_data
	   	);

	   	$userpage = json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );


	   	echo "$userpage";

	}

}