<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(-1);
/**
 * Get api
 *
 */

class Controller_V1_Mobile_Get extends Controller_V1_Mobile_Base
{
	//Timeline Page
	public function action_timeline()
    {
    	$sort_key    = 'all';
        $user_id     = session::get('user_id');
        $call        = Input::get('call', 0);
        $category_id = Input::get('category_id', 0);

		$data = Model_Post::get_data($user_id, $sort_key, 0, $call, $category_id);

	   	self::output_json($data);
	}


	//Timeline更新 [廃止予定]
	public function action_timeline_next()
	{
		$sort_key = 'all_next';
        $user_id  = session::get('user_id');
        $call     = Input::get('call');

		$data = Model_Post::get_data($user_id, $sort_key, 0, $call);

	   	self::output_json($data);
	}


	//Popular Page
	public function action_popular()
    {
    	$sort_key    = 'post';
        $user_id     = session::get('user_id');
        $call        = Input::get('call', 0);
        $category_id = Input::get('category_id', 0);

		$post_id = Model_Gochi::get_rank();
		$data = Model_Post::get_data($user_id, $sort_key, $post_id, $call, $category_id);

	   	self::output_json($data);
	}


	//Popular Page [廃止予定]
	public function action_popular_next()
    {
    	$sort_key = 'post';
        $user_id  = session::get('user_id');
        $call     = Input::get('call');

		$post_id = Model_Gochi::get_rank($call);
		$data = Model_Post::get_data($user_id, $sort_key, $post_id, 0, $category_id);

	   	self::output_json($data);
	}


	//Followline
	public function action_followline()
	{
		$sort_key    = 'user';
        $user_id     = session::get('user_id');
        $call        = Input::get('call', 0);
        $category_id = Input::get('category_id', 0);

		$follow_user_id = Model_Follow::get_follow_id($user_id);
		$data = Model_Post::get_data($user_id, $sort_key, $follow_user_id, $call, $category_id);

	   	self::output_json($data);
	}


	//Comment Page
    public function action_comment()
    {
    	$sort_key = 'post';
    	$user_id  = session::get('user_id');
        $post_id  = Input::get('post_id');

		$post_data    = Model_Post::get_data($user_id, $sort_key, $post_id);
	   	$comment_data = Model_Comment::get_data($post_id);

	   	$data = array(
	   		"post" 		=> $post_data[0],
	   		"comments" 	=> $comment_data
	   	);

	   	self::output_json($data);
	}


	//Restaurant Page
	public function action_rest()
    {
    	$sort_key = 'rest';
    	$user_id  = session::get('user_id');
    	$rest_id  = Input::get('rest_id');

		$rest_data = Model_Restaurant::get_data($user_id, $rest_id);
		$post_data = Model_Post::get_data($user_id, $sort_key, $rest_id);

	   	$data = array(
	   		"restaurants" => $rest_data,
	   		"posts" => $post_data
	   	);

	   	self::output_json($data);
	}


	//User Page
	public function action_user()
	{
		$sort_key       = 'user';
		$user_id 		= session::get('user_id');
		$target_user_id = Input::get('target_user_id');
		$call   		= Input::get('call');
		$category_id    = Input::get('category_id');


		$user_data = Model_User::get_data($user_id, $target_user_id);
        $post_data = Model_Post::get_data($user_id, $sort_key, $target_user_id);

	   	$data = array(
	   		"header" => $user_data,
	   		"posts"  => $post_data
	   	);

	   	self::output_json($data);
	}


	//Notice Page
	public function action_notice()
    {
    	$user_id = session::get('user_id');

    	$data = Model_Notice::get_data($user_id);

	   	Model_User::reset_badge($user_id);
	   	self::output_json($data);
	}


	//Near
	public function action_near()
	{
		$lon = Input::get('lon');
		$lat = Input::get('lat');

		$data = Model_Restaurant::get_near($lon, $lat);

	   	self::output_json($data);
	}


	//Follow
	public function action_follow()
	{
		$user_id = session::get('user_id');
		$target_user_id = Input::get('target_user_id');

		$data = Model_Follow::get_follow($user_id, $target_user_id);

	   	self::output_json($data);
	}


	//Follower List
	public function action_follower()
	{
		$user_id = session::get('user_id');
		$target_user_id = Input::get('target_user_id');

		$data = Model_Follow::get_follower($user_id, $target_user_id);

	   	self::output_json($data);
	}


	//行きたい登録リスト
	public function action_want()
	{
		$target_user_id = Input::get('target_user_id');

		$data = Model_Want::get_want($target_user_id);

	   	self::output_json($data);
	}


	//応援店舗リスト
	public function action_user_cheer()
	{
		$target_user_id = Input::get('target_user_id');

		$data = Model_Post::get_user_cheer($target_user_id);

	   	self::output_json($data);
	}


	//応援ユーザーリスト
	public function action_rest_cheer()
	{
		$user_id = Input::get('user_id');
		$rest_id = Input::get('rest_id');

		$data = Model_Post::get_rest_cheer($rest_id);

		$num = count($data);

		for ($i=0; $i < $num; $i++) {
			$target_user_id = $data[$i]['user_id'];
			$follow_flag = Model_Follow::get_flag($user_id, $target_user_id);
			$data[$i]['follow_flag'] = $follow_flag;
		}

	   	self::output_json($data);
	}


	public function action_user_search()
	{
		$user_id = session::get('user_id');
		$target_user_name = Input::get('username');

		$target_user_id = Model_User::get_id($target_user_name);
		$user_data = Model_User::get_data($user_id, $target_user_id);

	   	self::output_json($data);
	}
}

