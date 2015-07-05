<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * Get api
 *
 */

class Controller_V1_Get extends Controller_V1_Base
{

	//Timeline Page
	public function action_timeline()
    {
        $user_id = session::get('user_id');
        $limit 	 = Input::get('limit');


		if (empty($limit)) {
		    $limit = 20;
		}


		//"POST_Data"
		$sort_key  = 'all';
		$post_data = Model_Post::get_data($user_id, $sort_key, $sort_key, $limit);


	   	$timelinepage = json_encode($post_data,
	   		JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

	   	echo "$timelinepage";
	}


	//Popular Page
	public function action_popular()
    {
        $user_id = session::get('user_id');
        $limit 	 = Input::get('limit');
        $limit = 10;

		//"POST_Data"
		$sort_key  = 'post';

		$post_id = Model_Gochi::get_rank($limit);

		$num = count($post_id);

		//--debug
		//print_r($post_id);

		for ($i=0; $i < $num; $i++) {

			$post_data[$i] = Model_Post::get_data(
				$user_id, $sort_key, $post_id[$i]['gochi_post_id'], $limit);

		}


		$data = array("posts" => $post_data);

	   	$popularpage = json_encode($data,
	   		JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

	   	echo "$popularpage";
	}


	//Comment Page
    public function action_comment()
    {
    	$user_id  = session::get('user_id');
        $post_id  = Input::get('post_id');
        $limit = 1;
        $sort_key = 'post';


		//"POST_Data"
		$post_data = Model_Post::get_data($user_id, $sort_key, $post_id, $limit);

		//print_r($post_data);

	    //"Comments_data"
	   	$comment_data = Model_Comment::get_data($post_id);


	   	$data = array(
	   		"post" 		=> $post_data[0],
	   		"comments" 	=> $comment_data
	   	);

	   	$commentpage = json_encode($data,
	   		JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

	   	echo "$commentpage";
	}


	//Restaurant Page
	public function action_rest()
    {
    	$user_id = session::get('user_id');
    	$rest_id = Input::get('rest_id');
		$limit   = Input::get('limit');

		if (empty($limit)) {
		    $limit = 30;
		}


		//"Rest_Data"
		$rest_data = Model_Restaurant::get_data($rest_id);

		$want_flag = Model_Want::get_flag($user_id, $rest_id);
		$rest_data['0']['want_flag']= $want_flag;


		//"POST_Data"
		$sort_key  = 'rest';
		$post_data = Model_Post::get_data($user_id, $sort_key, $rest_id, $limit);


	   	$data = array(
	   		"restaurants" => $rest_data[0],
	   		"posts" => $post_data
	   	);

	   	$restaurantpage = json_encode($data,
	   		JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

	   	echo "$restaurantpage";
	}


	//User Page
	public static function action_user()
	{
		$user_id 		= session::get('user_id');
		$target_user_id = Input::get('target_user_id');


		$user_data = Model_User::get_data($user_id, $target_user_id);


        //"POST_Data"
        $sort_key  = 'user';
        $limit     = 30;

        $post_data = Model_Post::get_data(
            $target_user_id, $sort_key, $target_user_id, $limit);


	   	$data = array(
	   		"header" => $user_data,
	   		"posts"  => $post_data
	   	);

	   	$userpage = json_encode($data,
	   		JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

	   	echo "$userpage";
	}


	//Notice Page
	public function action_notice()
    {
    	$user_id = session::get('user_id');

    	$data = Model_Notice::get_data($user_id);


    	$notice = json_encode($data,
    		JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

	   	echo "$notice";


	   	$result = Model_User::reset_badge($user_id);
	}


}

