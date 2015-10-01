<?php
/**
 * Get api
 *
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);

class Controller_V2_Web_Get extends Controller_V2_Web_Base
{
	// jwt check
	public function create_token()
	{
		$jwt = @$_SERVER["HTTP_AUTHORIZATION"] ? @$_SERVER["HTTP_AUTHORIZATION"] : "";

		if (isset($jwt)) {
			$data = self::decode($jwt);
			$user_data = session::get('data');
			$obj = json_decode($user_data);
			if (empty($obj)) {
				self::unauth();
			}
			// objにuser_idが入っていることが前提
			$user_id = $obj->{'user_id'};
			session::set('username', $username);

			// objにusernameが入っていることが前提
			$username = $obj->{'username'};
			session::set('username', $username);
		} else {
			self::unauth();
			error_log('UnAuthorized Accsess..');
		}
	}

	/*
	private static function get_input()
  {
        $user_data = array_merge (Input::get(), Input::post());
        return $user_data;
  }
	*/

	//Timeline Page
	public function action_timeline()
  {
			/*
			$potion		= self::get_input();
			$post_data  = Model_V2_Router::timeline($option);
	   	self::output_success($post_data);
			*/

			self::create_token();

    	$user_id  = session::get('user_id');
    	$username = session::get('username');

	    $sort_key = 'all';
	    $limit    = 20;
	    $data     = Model_Post::get_data($user_id, $sort_key, $sort_key,$limit);

			for ($i = 0; $i<$limit; $i++) {
				$post_id = $data[$i]['post_id'];
				$Comment_data = Model_Comment::get_data($post_id);

				$data[$i] = [
		        "post"     => $data[$i],
		        "comments" => $Comment_data
		    ];
				// $api_data = Model_
				$api_data = [
					"api_version" => 2,
					"api_code" => 0,
					"api_message" => "success",
					"api_data" => $data
				];

			}
	   	$status   = $this->output_json($api_data);
	}

	// ============================================================
	// 以下未実装
	// ============================================================

	//Followline
	public function action_followline()
	{
		//$option is [call, order_id, category_id, value_id, lon, lat]
        $potion		= self::get_input();

		$post_data	= Model_V2_Router::followline($option);

	   	self::output_success($post_data);
	}


	//Comment Page
    public function action_comment()
    {
    	//$option is [post_id]
    	$option			= self::get_input();

		$post_data   	= Model_V2_Router::comment_post($option);
	   	$comment_data   = Model_V2_Router::comment($option['post_id']);

	   	$data = array(
	   		"post" 		=> $post_data,
	   		"comments" 	=> $comment_data
	   	);

	   	self::output_success($data);
	}


	//Restaurant Page
	public function action_rest()
    {
    	//$option is [rest_id]
    	$option		= self::get_input();

		$rest_data 	= Model_V2_Router::rest($option['rest_id']);
		$post_data 	= Model_V2_Router::rest_post($option);

	   	$data = array(
	   		"restaurants" => $rest_data,
	   		"posts" => $post_data
	   	);

	   	self::output_json($data);
	}


	//User Page
	public function action_user()
	{
		  // $option is [target_user_id]
			/*
			$option		= self::get_input();
		  $user_data  = Model_V2_Router::user($target_user_id);
      $post_data  = Model_V2_Router::user_post($option);

	   	$data = array(
	   		"header" => $user_data,
	   		"posts"  => $post_data
	   	);
	   	self::output_json($data);
			*/
	}


	//Notice Page
	public function action_notice()
    {
    	$data = Model_Notice::get_data($user_id);

	   	Model_User::reset_badge();
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

		if (!empty($target_user_id)) {
			$data = array(
				'code' 	  => 200,
				'message' => 'ユーザーを見つけました',
				'user_id' => "$target_user_id"
		);

		}else{
			$data = array(
				'code' 	  => 500,
				'message' => 'ユーザーが見つかりませんでした',
				'user_id' => '0'
			);
		}

	   	self::output_json($data);
	}
}
