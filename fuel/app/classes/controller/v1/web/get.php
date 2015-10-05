<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('X-Content-Type-Options: nosniff');
error_reporting(-1);

class Controller_V1_Web_Get extends Controller_V1_Web_Base
{
	public function action_test_timeline()
    {
	$jwt = Input::get('jwt');
        if(isset($jwt)) {
                $data      = self::decode($jwt);
                $user_data = session::get('data');
                $obj       = json_decode($user_data);
                if (empty($obj)) {
                    self::unauth();
                }
            } else {
                  self::unauth();
                  error_log('UnAuthorized Accsess..');
                  exit;
            }

    }



	public function before()
        {
	    // SCRIPT要素で埋め込まれないための対策
	    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
		$_SERVER['HTTP_X_REQUEST_WITH'] !== 'XMLHttpRequest') {
		// Ajaxリクエストではないため403を出す
		// exit;
	    }
	}
	// jwt check
	public function create_token()
	{
		$jwt = @$_SERVER["HTTP_AUTHORIZATION"] ?  @$_SERVER["HTTP_AUTHORIZATION"] : "";
		if(isset($jwt)) {
			$data      = self::decode($jwt);
			// print_r($data);
			$user_data = session::get('data');
			$obj       = json_decode($user_data);
			if (empty($obj)) {
				self::unauth();
			}

			$user_id   = $obj->{'user_id'};
			session::set('user_id', $user_id);
			$username  = $obj->{'username'};
			session::set('username', $username);
		} else {
			self::unauth();
			error_log('UnAuthorized Accsess..');
			exit;
		}
	}

	public function action_timeline()
  	{
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
			}

	   	$status   = $this->output_json($data);
    }

    // Timeline loading
    public function action_timeline_loading()
    {
				self::create_token();

        $sort_key = 'all';
        // $user_id  = session::get('user_id');
				$user_id = 1;
        $page_num = Input::get('page');
				$limit    = 20;
        $data     = Model_Post::get_data($user_id, $sort_key, $page_num);

				for ($i = 0; $i<$limit; $i++) {
					$post_id = $data[$i]['post_id'];
					$Comment_data = Model_Comment::get_data($post_id);
					$data[$i] = [
			        "post"     => $data[$i],
			        "comments" => $Comment_data
			    ];
				}
        $status   = $this->output_json($data);

    }

    // Popular Page
		/*
    public function action_popular()
    {
				self::create_token();
        $sort_key = 'post';
        // $user_id  = session::get('user_id');
				// $user_id = 4;
        $post_id  = Model_Gochi::get_rank();

        $num      = count($post_id);

        for ($i=0;$i<$num;$i++) {
            $tmp[$i] = Model_Post::get_data(
            $user_id, $sort_key, $post_id[$i]['post_id']);

            $data[$i] =  $tmp[$i][0];
        }

        $status = $this->output_json($data);
    }
		*/

    // Popular loading
    public function action_popular_loading()
    {
				self::create_token();

        $sort_key = 'post';
        $page_num = Input::get('page');

        $user_id  = session::get('user_id');
        $post_id  = Model_Gochi::get_rank($page_num);

        $num      = count($post_id);

        for ($i=0;$i<$num;$i++) {
            $tmp[$i]  = Model_Post::get_data(
                $user_id, $sort_key, $post_id[$i]['post_id']
            );
            $data[$i] = $tmp[$i][0];
        }

        if ($num === 0) {
            $data = array();
        }

        $status   = $this->output_json($data);
    }

    // Comment Page
    public function action_comment()
    {
        $sort_key     = 'post';

        $user_id      = session::get('user_id');
        $post_id      = Input::get('post_id');

        $post_data    = Model_Post::get_data($user_id, $sort_key, $post_id);

        $Comment_data = Model_Comment::get_data($post_id);

        $data = [
            "post"     => $post_data[0],
            "comments" => $Comment_data
        ];

        $status = $this->output_json($data);
    }

    // Restaurant Page
    public function action_rest()
    {
        $sort_key  = 'rest';
        $user_id   = session::get('user_id');
				// $user_id = "";
        $rest_id   = Input::get('rest_id');

        $rest_data = Model_Restaurant::get_data($user_id, $rest_id);
        $rest_data[0]['want_flag'] = Model_Want::get_flag($user_id, $rest_id);
        $rest_data[0]['cheer_num'] = Model_Post::get_rest_cheer_num($rest_id);

        $post_data = Model_Post::get_data($user_id, $sort_key, $rest_id);

        $data = [
            "restaurants" => $rest_data[0],
            "posts"       => $post_data
        ];

        $status = $this->output_json($data);
    }

    // User Page
    // public function action_user($target_user_id)
    public function action_user($target_username)
    {
        $sort_key       = 'user';
        $limit          = 10;

	if (ctype_digit($target_username)) { $this->notid();}
        // $user_id        = session::get('user_id');
		$user_id = Model_User::get_id($target_username);
		$target_user_id = $user_id;
		if (empty($user_id)) { $this->notfounduser(); exit;}
        	$user_data      = Model_User::get_data($user_id, $target_user_id);
        	$post_data      = Model_Post::get_data(
            	$target_user_id, $sort_key, $target_user_id, $limit
        );

        $data = [
            "header" => $user_data,
            "posts"  => $post_data
        ];

        $status = $this->output_json($data);
    }

    // Notice Page
    public function action_notice()
    {
				self::create_token();

        $user_id = session::get('user_id');

        $data    = Model_Notice::get_data($user_id);

        // What is this code mean?
        Model_User::rest_badge($user_id);

        $status  = $this->output_json($data);
    }

    // Near
    public function action_near()
    {

    }

    // Follow
    public function action_follow()
    {
				self::create_token();

        $user_id        = session::get('user_id');
        $target_user_id = Input::get('target_user_id');

        $data           = Model_Follow::get_follow($user_id, $target_user_id);

        $status         = $this->output_json($data);
    }

    // Follower List
    public function action_follower()
    {
        $user_id        = session::get('user_id');
        $target_user_id = Input::get('target_user_id');

        $data           = Model_Follow::get_follower($user_id, $target_user_id);
        $status         = $this->output_json($data);
    }

    // 行きたい登録 List
    public function action_want()
    {
        $target_user_id = Input::get('target_user_id');

        $data           = Model_Want::get_want($target_user_id);

        $status         = $this->output_json($data);

    }

    // 応援店舗 List
    public function action_user_cheer()
    {
        $target_user_id = Input::get('target_user_id');

        $data           = Model_Post::get_user_cheer($target_user_id);

        $status         = $this->output_json($data);

    }

    // 応援ユーザー List
    public function action_rest_cheer()
    {
        $user_id = Input::get('user_id');
        $rest_id = Input::get('rest_id');

        $data    = Model_Post::get_rest_cheer($rest_id);

        $num     = count($data);

        for ($i=0;$i<$num;$i++) {
            $target_user_id = $data[$i]['user_id'];
            $follow_flag    = Model_Follow::get_flag($user_id, $target_user_id);
            $adta[$i]['follow_flag'] = $follow_flag;
        }

        $status = $this->output_json($data);
    }

    // User Search
    public function action_user_search()
    {
        $user_id         = session::get('user_id');
        $targetUserName  = Input::get('username');
        $targetUserId    = Model_User::get_id($targetUserName);
        $userData        = Model_User::get_data($user_id, $targetUserId);

        $status          = $this->output_json($userData);
    }
}
