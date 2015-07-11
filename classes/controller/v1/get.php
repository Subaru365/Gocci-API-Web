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

		//"POST_Data"
		$sort_key  = 'all';
		$post_data = Model_Post::get_data($user_id, $sort_key, $sort_key);

	   	$status = $this->output_json($data);
	}


	//Popular Page
	public function action_popular()
    {
        $user_id = session::get('user_id');

		//"POST_Data"
		$sort_key  = 'post';

		$post_id = Model_Gochi::get_rank();


		$num = count($post_id);

		for ($i=0; $i < $num; $i++) {

			$tmp[$i] = Model_Post::get_data(
				$user_id, $sort_key, $post_id[$i]['gochi_post_id']);

			$data[$i] =  $tmp[$i][0];
		}

	   	$status = $this->output_json($data);
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


	    //"Comments_data"
	   	$comment_data = Model_Comment::get_data($post_id);


	   	$data = array(
	   		"post" 		=> $post_data[0],
	   		"comments" 	=> $comment_data
	   	);

	   	$status = $this->output_json($data);
	}


	//Restaurant Page
	public function action_rest()
    {
    	$user_id = session::get('user_id');
    	$rest_id = Input::get('rest_id');


		//"Rest_Data"
		$rest_data = Model_Restaurant::get_data($rest_id);

		$want_flag = Model_Want::get_flag($user_id, $rest_id);
		$rest_data['0']['want_flag']= $want_flag;


		//"POST_Data"
		$sort_key  = 'rest';
		$post_data = Model_Post::get_data($user_id, $sort_key, $rest_id);


	   	$data = array(
	   		"restaurants" => $rest_data[0],
	   		"posts" => $post_data
	   	);

	   	$status = $this->output_json($data);
	}


	//User Page
	public static function action_user()
	{
		$user_id 		= session::get('user_id');
		$target_user_id = Input::get('target_user_id');


		$user_data = Model_User::get_data($user_id, $target_user_id);


        //"POST_Data"
        $sort_key  = 'user';

        $post_data = Model_Post::get_data(
            $target_user_id, $sort_key, $target_user_id);


	   	$data = array(
	   		"header" => $user_data,
	   		"posts"  => $post_data
	   	);

	   	$status = $this->output_json($data);
	}


	//Notice Page
	public function action_notice()
    {
    	$user_id = session::get('user_id');

    	$data = Model_Notice::get_data($user_id);

    	$status = $this->output_json($data);

	   	$tmp = Model_User::reset_badge($user_id);
	}


	//Near
	public function action_near()
	{
		$lon = Input::get('lon');
		$lat = Input::get('lat');

		$data = Model_Restaurant::get_near($lon, $lat);

    	$status = $this->output_json($data);
	}


	//Follow
	public function action_follow()
	{
		$user_id = session::get('user_id');
		$target_user_id = Input::get('target_user_id');

		$data = Model_Follow::get_follow($user_id, $target_user_id);

    	$status = $this->output_json($data);
	}


	//Follower List
	public function action_follower()
	{
		$user_id = session::get('user_id');
		$target_user_id = Input::get('target_user_id');

		$data = Model_Follow::get_follower($user_id, $target_user_id);

    	$status = $this->output_json($data);
	}


	public function action_want()
	{
		$target_user_id = Input::get('target_user_id');

		$data = Model_Want::get_want($target_user_id);

    	$status = $this->output_json($data);
	}


	public function action_user_cheer()
	{
		$target_user_id = Input::get('target_user_id');

		$data = Model_Post::get_user_cheer($target_user_id);

    	$status = $this->output_json($data);
	}


	public function action_rest_cheer()
	{
		$rest_id = Input::get('rest_id');

		$data = Model_Post::get_rest_cheer($rest_id);

    	$status = $this->output_json($data);
	}
}

