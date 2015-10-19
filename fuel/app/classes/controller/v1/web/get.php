<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('X-Content-Type-Options: nosniff');
error_reporting(-1);

class Controller_V1_Web_Get extends Controller_V1_Web_Base
{
	public function before()
        {
	    // SCRIPT要素で埋め込まれないための対策
	    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
		$_SERVER['HTTP_X_REQUEST_WITH'] !== 'XMLHttpRequest') {
		// Ajaxリクエストではないため403を出す
		// exit;
	    }
	}

	/**
	 * jwt check
	 * @$uri         
	 * @$login_flag
	 */

	// create tokenというfunction nameだが、実際jwtがあるかどうかをチェックしているメソッド
	public function create_token($uri="",$login_flag)
	{
	    $jwt = @$_SERVER["HTTP_AUTHORIZATION"] ?  @$_SERVER["HTTP_AUTHORIZATION"] : "";

	    if(isset($jwt)) {
		    $data      = self::decode($jwt);
		    $user_data = session::get('data');
		    $obj       = json_decode($user_data);
		
		    if (empty($obj)) {
			self::unauth($uri,$login_flag);
		    }
		    $user_id   = $obj->{'user_id'};
		    session::set('user_id', $user_id);
		    $username  = $obj->{'username'};
		    session::set('username', $username);
	            $exp       = $obj->{'exp'};
		    session::set('exp', $exp);
 
		} else {
		    self::unauth();
		    error_log('UnAuthorized Accsess..');
		    exit;
		}
	}

	public function action_timeline()
  	{
	    $uri = "/get/timeline";
	    $login_flag = 0;

	    self::create_token($uri, $login_flag);
    	    $user_id  = session::get('user_id');
    	    $username = session::get('username');
	    $exp      = session::get('exp');
	    self::check_jwtExp($exp);

	    $sort_key = 'all';
	    $limit    = 20;
	    $data     = Model_Post::get_data($user_id, $sort_key, $sort_key, $limit);

	    for ($i = 0; $i<$limit; $i++) {
		$post_id = $data[$i]['post_id'];
		$Comment_data = Model_Comment::get_data($post_id);
		$data[$i] = [
		    "post"     => $data[$i],
		    "comments" => $Comment_data
		];
	    }
	    $base_data = [
	        "api_version" => 3,
    	        "api_code" => 0,
    		"api_message"=> "success",
    		"api_data"=>$data		
	    ];
	    $status   = $this->output_json($base_data);
        }

    	// Timeline loading
    	public function action_timeline_loading()
    	{
		$uri = "get/timeline_loading";
		$login_flag=2;
		self::create_token($uri,$login_flag);
        	$sort_key = 'all';
        	$user_id  = session::get('user_id');
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

	public function action_recommendation()
        {
	    $uri ="/get/recommendataion";
	    $login_flag=2;
	    self::create_token();
	    // ユーザー好きそうな傾向のある投稿・お店をレコメンドする R/Pythonの方が良い気がする
	    $user_id = session::get('user_id');
        }

    	// Popular Page
	/*
    	public function action_popular()
    	{
	    $uri = "/get/popular";
	    $login_flag = 0;
	    self::create_token($uri,$login_flag);
            $sort_key = 'post';
            // $user_id  = session::get('user_id');
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
	    $uri = "/get/popular_loading";
	    $login_flag = 2;
	    self::create_token($uri,$login_flag);
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
	$uri = "/get/comment";
	$login_flag = 2;
	self::create_token($uri,$login_flag);
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
	$uri = "get/rest";	
	$login_flag = 0;
        $sort_key  = 'rest';
        $user_id   = session::get('user_id');
        $rest_id   = Input::get('rest_id');

        $rest_data = Model_Restaurant::get_data($user_id, $rest_id);
        $rest_data[0]['want_flag'] = Model_Want::get_flag($user_id, $rest_id);
        $rest_data[0]['cheer_num'] = Model_Post::get_rest_cheer_num($rest_id);

        $post_data = Model_Post::get_data($user_id, $sort_key, $rest_id);

	if (empty($user_id)) {
	    $login_flag =0;
	} else {
	    $login_flag = 1;
	}

        $data = [
            "restaurants" => $rest_data[0],
            "posts"       => $post_data
        ];

	$base_data = [
            "api_version"=> 3,
	    "api_uri"    => $uri,
            "api_code"   => 0,
            "api_message"=> "success",
	    "login_flag" => $login_flag,
            "api_data"   => $data
        ];

        $status = $this->output_json($base_data);
    }

    // User Page
    // public function action_user($target_user_id)
    public function action_user($target_username)
    {
	$uri = "get/user";
	$jwt = @$_SERVER["HTTP_AUTHORIZATION"] ?  @$_SERVER["HTTP_AUTHORIZATION"] : "";

        if(isset($jwt)) {
                $data      = self::decode($jwt);
                $user_data = session::get('data');
                $obj       = json_decode($user_data);

                if (empty($obj)) {
		    $sort_key       = 'user';
       	  	    $limit          = 20;

                   if (ctype_digit($target_username)) { $this->notid();}
        		// ページを見る相手のユーザーID
        		$target_user_id = Model_User::get_id($target_username);
                        $user_id        = session::get('user_id');
        		// if (empty($user_id)) { $this->notfounduser(); exit;}
        		error_log('user apiを叩いたuser_id:');
		        $user_data      = Model_User::get_data($user_id, $target_user_id);
        		$post_data      = Model_Post::get_data(
               		        $target_user_id, $sort_key, $target_user_id, $limit
        		);

		    	for ($i = 0; $i<count($post_data); $i++) {
                	    $post_id = $post_data[$i]['post_id'];
                            $Comment_data = Model_Comment::get_data($post_id);
                	    $post_data[$i] = [
                    		    "post"     => $post_data[$i],
                    		    "comments" => $Comment_data
                            ];
        	  	}
        	  	$data = [
                            "header" => $user_data,
                            "posts"  => $post_data
                        ];
			$base_data = [
                            "api_version"=> 3,
			    "api_uri"    => $uri,
                            "api_code"   => 1,
                            "api_message"=> "UnAuthorized",
                            "login_flag" => 0,
                            "api_data"   => $data
                        ];

        		$status = $this->output_json($base_data);
			exit;
                }
                $user_id   = $obj->{'user_id'};
                session::set('user_id', $user_id);
                $username  = $obj->{'username'};
                session::set('username', $username);
                $exp       = $obj->{'exp'};
                session::set('exp', $exp);
	}
        $sort_key       = 'user';
        $limit          = 20;

	if (ctype_digit($target_username)) { $this->notid();}
      
	// ページを見る相手のユーザーID 
	$target_user_id = Model_User::get_id($target_username);
	
	$user_id        = session::get('user_id');
	if (empty($user_id)) { $this->notfounduser(); exit;}
	error_log('user apiを叩いたuser_id:');
	error_log($user_id);
	$login_flag = 1;
        $user_data      = Model_User::get_data($user_id, $target_user_id);
        $post_data      = Model_Post::get_data(
 	       $target_user_id, $sort_key, $target_user_id, $limit
        );
	// post_dataの中にtimelineでいう、$data (post/commtns)が入ったものを入れる
	for ($i = 0; $i<count($post_data); $i++) {
                $post_id = $post_data[$i]['post_id'];
                $Comment_data = Model_Comment::get_data($post_id);
                $post_data[$i] = [
                    "post"     => $post_data[$i],
                    "comments" => $Comment_data
                ];
        }
        $data = [
            "header" => $user_data,
            "posts"  => $post_data
        ];
	$base_data = [
            "api_version"=> 3,
	    "api_uri"    => $uri,
            "api_code"   => 0,
            "api_message"=> "sucess",
            "login_flag" => $login_flag,
            "api_data"   => $data
        ];

        $status = $this->output_json($base_data);
    }

    // Notice Page
    public function action_notice()
    {
	$uri = "/get/notice";
	$login_flag = 0;
	self::create_token($uri,$login_flag);
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

    // Follow Timeline
    public function action_follow()
    {
	self::create_token($uri="/get/follow",$login_flag=2);
        $user_id        = session::get('user_id');
	
	$option = [
                'call'        => Input::get('call', 0),
                'order_id'    => Input::get('order_id', 0),
                'category_id' => Input::get('category_id', 0),
                'value_id'    => Input::get('value_id', 0),
                'lon'         => Input::get('lon', 0),
                'lat'         => Input::get('lat', 0)
        ];
	
	$sort_key = 'all';
        $follow_user_id = Model_Follow::get_follow_id($user_id);
        $data = Model_Post::get_data($user_id, $sort_key, $follow_user_id, $option);

	for ($i = 0; $i< count($data); $i++) {
            $post_id = $data[$i]['post_id'];
            $Comment_data = Model_Comment::get_data($post_id);
            $data[$i] = [
                    "post"     => $data[$i],
                    "comments" => $Comment_data
            ];
        }

	$base_data = [
	    "api_version" => 3,
	    "api_code"    => 0,
	    "api_message" => "success",
	    "api_data" => $data
	];
 
        $status         = $this->output_json($base_data);
    }

    // Follower List
    public function action_follower()
    {
	$uri = "/get/follower";
	$login_flag = 2;
	self::create_token($uri,$login_flag);
        $user_id        = session::get('user_id');
        $target_user_id = Input::get('target_user_id');
        $data           = Model_Follow::get_follower($user_id, $target_user_id);
        $status         = $this->output_json($data);
    }

    // 行きたい登録 List
    public function action_want()
    {
	$uri = "/get/want";
	$login_flag = 2;
	self::create_token($uri,$login_flag);
        $target_user_id = Input::get('target_user_id');
        $data           = Model_Want::get_want($target_user_id);
        $status         = $this->output_json($data);
    }

    // 応援店舗 List
    public function action_user_cheer()
    {
	$uri = "/get/user_cheer";
        $login_flag = 2;
        self::create_token($uri,$login_flag);
        $target_user_id = Input::get('target_user_id');
        $data           = Model_Post::get_user_cheer($target_user_id);
        $status         = $this->output_json($data);
    }

    // 応援ユーザー List
    public function action_rest_cheer()
    {
	$uri = "/get/rest_cheer";
        $login_flag = 2;
        self::create_token($uri,$login_flag);
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
	$uri = "/get/user_search";
        $login_flag = 2;
        self::create_token($uri,$login_flag);
        $user_id         = session::get('user_id');
        $targetUserName  = Input::get('username');
        $targetUserId    = Model_User::get_id($targetUserName);
        $userData        = Model_User::get_data($user_id, $targetUserId);
	$status          = $this->output_json($userData);
    }
}

