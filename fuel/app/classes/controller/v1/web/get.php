<?php
/**
 * Get Class Api
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('X-Content-Type-Options: nosniff');
error_reporting(-1);

class Controller_V1_Web_Get extends Controller_V1_Web_Base
{
    /**
    * before
    *
    * @return string
    */
    public function before()
    {
	// SCRIPT要素で埋め込まれないための対策
	if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
	    $_SERVER['HTTP_X_REQUEST_WITH'] !== 'XMLHttpRequest') {
	    // Ajaxリクエストではないため403を出す
	    // json output
	    // exit;
	}
    }

    /**
    * jwtがあるかどうかをcheckするメソッド
    *
    * @return string
    */
    public function create_token($uri,$login_flag)
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

    /**
    * timeline
    *
    * @return string
    */
    public function action_timeline()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
    	$user_id  = session::get('user_id');
    	$username = session::get('username');
	$exp      = session::get('exp');
	$jwt = self::check_jwtExp($exp);

	$sort_key = 'all';
	$limit    = 20;
	$option   = [
		'call'		=> Input::get('call', 0),
		'order_id'      => Input::get('order_id', 0),
		'category_id'   => Input::get('category_id', 0),
		'value_id'      => Input::get('value_id', 0),
		'lon'           => Input::get('lon', 0),
		'lat'		=> Input::get('lat', 0)
	];

	$data = Model_Post::get_data($user_id, $sort_key, $sort_key, $limit);

	for ($i = 0; $i<$limit; $i++) {
		$post_id = $data[$i]['post_id'];
		$Comment_data = Model_Comment::get_data($post_id);
		$data[$i] = [
		    "post"     => $data[$i],
		    "comments" => $Comment_data
		];
	}
	$base_data = [
	        "api_version" => 3.0,
		"api_uri"     => Uri::string(),
    	        "api_code"    => 0,
    		"api_message" => "success",
    		"api_data"    => $data,
		"jwt"         => $jwt		
	];
	$status   = $this->output_json($base_data);
    }

    /**
    * Timeline loading
    *
    * @return string
    */
    public function action_timeline_loading()
    {
	self::create_token($uri=Uri::string(),$login_flag=2);
        $sort_key = 'all';
        $user_id  = session::get('user_id');
        $page_num = Input::get('page');
	$limit    = 20;
	$exp      = session::get('exp');
        $jwt = self::check_jwtExp($exp);
        $data     = Model_Post::get_data($user_id, $sort_key, $page_num);

	for ($i = 0; $i<$limit; $i++) {
		$post_id = $data[$i]['post_id'];
		$Comment_data = Model_Comment::get_data($post_id);
		$data[$i] = [
		    "post"     => $data[$i],
		    "comments" => $Comment_data
		];
	}
	$base_data = [
                "api_version" => 3.0,
                "api_uri"     => Uri::string(),
                "api_code"    => 0,
                "api_message" => "success",
                "api_data"    => $data,
                "jwt"         => $jwt
        ];
        $status   = $this->output_json($base_data);
    }

    /**
    * Restaurant recommendation 
    *
    * @return string
    */
    public function action_recommendation()
    {
	self::create_token($uri=Uri::string(), $login_flag=2);
	// ユーザー好きそうな傾向のある投稿・お店をレコメンドする R/Pythonの方が良い気がする
	$user_id = session::get('user_id');
	$exp      = session::get('exp');
        $jwt = self::check_jwtExp($exp);
    }

    /**
    * Popular loading
    *
    * @return string
    */
    public function action_popular_loading()
    {
        self::create_token($uri=Uri::string(),$login_flag=2);
        $sort_key = 'post';
        $page_num = Input::get('page');
        $user_id  = session::get('user_id');
	$exp      = session::get('exp');
        $jwt = self::check_jwtExp($exp);
        $post_id  = Model_Gochi::get_rank($page_num);
        $num      = count($post_id);

        for ($i=0;$i<$num;$i++) {
           $tmp[$i]  = Model_Post::get_data(
               	$user_id, $sort_key, $post_id[$i]['post_id']
           );
           $data[$i] = $tmp[$i][0];
        }

        if ($num === 0) {
            $data = [];
        }
        $status   = $this->output_json($data);
    }

    /**
    * Comment Page
    *
    * @return string
    */
    public function action_comment()
    {
	self::create_token($uri=Uri::string(), $login_flag=2);
        $sort_key     = 'post';
        $user_id      = session::get('user_id');
        $post_id      = Input::get('post_id');
	$exp          = session::get('exp');
        $jwt          = self::check_jwtExp($exp);
        $post_data    = Model_Post::get_data($user_id, $sort_key, $post_id);
        $Comment_data = Model_Comment::get_data($post_id);

        $data = [
            "post"     => $post_data[0],
            "comments" => $Comment_data
        ];
	// $base_data

        $status = $this->output_json($data);
    }

    /**
    * Restarants Page
    *
    * @return string
    */
    public function action_rest()
    {
	$rest_id    = Input::get('rest_id');
	$uri        = Uri::string();
        $login_flag = 0;
        $sort_key   = 'rest';

	$jwt = @$_SERVER["HTTP_AUTHORIZATION"] ? @$_SERVER["HTTP_AUTHORIZATION"] : "";
	if (isset($jwt)) {
	    $data      = self::decode($jwt);
	    $user_data = session::get('data');
	    $obj       = json_decode($user_data); 

	    if (empty($obj)) {
		$jwt = "";
		// ログインしていない
	        $user_id = 0;
		$rest_data = Model_Restaurant::get_data($user_id, $rest_id);
        	$rest_data[0]['want_flag'] = Model_Want::get_flag($user_id, $rest_id);
        	$rest_data[0]['cheer_num'] = Model_Post::get_rest_cheer_num($rest_id);
        	$post_data = Model_Post::get_data($user_id, $sort_key, $rest_id);

        	if (empty($user_id)) {
            	    $login_flag = 0;
        	} else {
            	    $login_flag = 1;
       	        }

        	$data = [
            	    "restaurants" => $rest_data[0],
            	    "posts"       => $post_data
        	];	

		$base_data = [
                    "api_version"=> 3.0,
                    "api_uri"    => $uri,
                    "api_code"   => 0,
            	    "api_message"=> "UnAuthorized",
            	    "login_flag" => $login_flag,
           	    "api_data"   => $data,
		    "jwt"        => $jwt
       	         ];
        	$status = $this->output_json($base_data);
		exit;
	    }
	}
        $user_id   = $obj->{'user_id'};
	session::set('user_id', $user_id);
        $username  = $obj->{'username'};
        session::set('username', $username);
        $exp       = $obj->{'exp'};
        session::set('exp', $exp);
        $rest_id   = Input::get('rest_id');
	// $exp      = session::get('exp');
        $jwt = self::check_jwtExp($exp);

        $rest_data = Model_Restaurant::get_data($user_id, $rest_id);
        $rest_data[0]['want_flag'] = Model_Want::get_flag($user_id, $rest_id);
        $rest_data[0]['cheer_num'] = Model_Post::get_rest_cheer_num($rest_id);

        $post_data = Model_Post::get_data($user_id, $sort_key, $rest_id);

	if (empty($user_id)) {
	    $login_flag = 0;
	} else {
	    $login_flag = 1;
	}

        $data = [
            "restaurants" => $rest_data[0],
            "posts"       => $post_data
        ];

	$base_data = [
            "api_version"=> 3.0,
	    "api_uri"    => $uri,
            "api_code"   => 0,
            "api_message"=> "success",
	    "login_flag" => $login_flag,
            "api_data"   => $data,
	    "jwt"        => $jwt
        ];

        $status = $this->output_json($base_data);
    }

    /**
    * User Page
    *
    * @return string
    */
    public function action_user($target_username)
    {
	$uri = Uri::string();
	$jwt = @$_SERVER["HTTP_AUTHORIZATION"] ?  @$_SERVER["HTTP_AUTHORIZATION"] : "";

        if(isset($jwt)) {
            $data      = self::decode($jwt);
            $user_data = session::get('data');
            $obj       = json_decode($user_data);

            if (empty($obj)) {
	        $sort_key       = 'user';
       	  	$limit          = 20;
                if (ctype_digit($target_username)) { $this->notid();}
        	    // 相手のユーザーID
        	    $target_user_id = Model_User::get_id($target_username);
                    $user_id        = session::get('user_id');
        	    error_log('user apiを叩いたuser_id: /get/user api');
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
                            "api_version"=> 3.0,
			    "api_uri"    => $uri,
                            "api_code"   => 1,
                            "api_message"=> "UnAuthorized",
                            "login_flag" => 0,
                            "api_data"   => $data,
			    "jwt"        => $jwt
                    ];
        	    $status = $this->output_json($base_data);
		    exit;
                }
	}
        $user_id  = $obj->{'user_id'};
        session::set('user_id', $user_id);
        $username = $obj->{'username'};
        session::set('username', $username);
        $exp      = $obj->{'exp'};
        session::set('exp', $exp);
        $jwt = self::check_jwtExp($exp);

        $sort_key = 'user';
        $limit    = 20;

	if (ctype_digit($target_username)) { $this->notid();}
      
	// ページを見る相手のユーザーID 
	$target_user_id = Model_User::get_id($target_username);
	
	$user_id  = session::get('user_id');
	if (empty($user_id)) { $this->notfounduser(); exit;}
	$login_flag = 1;
        $user_data  = Model_User::get_data($user_id, $target_user_id);
        $post_data  = Model_Post::get_data(
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
            "api_version"=> 3.0,
	    "api_uri"    => $uri,
            "api_code"   => 0,
            "api_message"=> "sucess",
            "login_flag" => $login_flag,
            "api_data"   => $data,
	    "jwt"        => $jwt
        ];
        $status = $this->output_json($base_data);
    }

    /**
    * Notice Page
    *
    * @return string
    */
    public function action_notice()
    {	
	self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id = session::get('user_id');
	$exp     = session::get('exp');
        $jwt = self::check_jwtExp($exp);
        $data    = Model_Notice::get_data($user_id);
        Model_User::reset_badge($user_id);
	// $base_data = 
        $status  = $this->output_json($data);
    }

    /**
    * Near
    *
    * @return string
    */
    public function action_near()
    {
	self::create_token($uri=Uri::string(), $login_flag=2);
	$lon  = Input::get('lon');
	$lat  = Input::get('lat');
	$exp  = session::get('exp');
        $jwt  = self::check_jwtExp($exp);
	$data = Model_Restaurant::get_near($lon, $lat);

	// JSON
	$base_data = [
	    "api_version" => 3.0,
	    "api_uri"     => Uri::string(),
	    "api_code"    => 0,
	    "api_message" => "success",
	    "login_flag"  => 1,
	    "api_data"    => $data,
	    "jwt"         => $jwt
	];
	$status = $this->output_json($base_data);	
    }

    /**
    * Follow Timeline
    *
    * @return string
    */
    public function action_follow()
    {
	self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id  = session::get('user_id');
	$exp      = session::get('exp');
        $jwt      = self::check_jwtExp($exp);

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
	    "api_version" => 3.0,
            "api_uri"     => Uri::string(),
	    "api_code"    => 0,
	    "api_message" => "success",
	    "login_flag"  => 1,
	    "api_data"    => $data,
	    "jwt"         => $jwt
	]; 
        $status         = $this->output_json($base_data);
    }

    /**
    * Follower List
    *
    * @return string
    */
    public function action_follower()
    {
	self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id        = session::get('user_id');
        $target_user_id = Input::get('target_user_id');
	$exp            = session::get('exp');
        $jwt            = self::check_jwtExp($exp);
        $data           = Model_Follow::get_follower($user_id, $target_user_id);
	// $base_data      = 
        $status         = $this->output_json($data);
    }

    /**
    * 行きたい登録 List
    *
    * @return string
    */
    public function action_want()
    {
	self::create_token($uri=Uri::string(), $login_flag=2);
        $target_user_id = Input::get('target_user_id');
	$exp      = session::get('exp');
        $jwt = self::check_jwtExp($exp);
        $data           = Model_Want::get_want($target_user_id);
	// $base_data = 
        $status         = $this->output_json($data);
    }

    /**
    * 店舗登録 LIst
    *
    * @return string
    */
    public function action_user_cheer()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $target_user_id = Input::get('target_user_id');
	$exp      = session::get('exp');
        $jwt = self::check_jwtExp($exp);
        $data           = Model_Post::get_user_cheer($target_user_id);
	// $base_data = 
        $status         = $this->output_json($data);
    }

    /**
    * 応援ユーザ List
    *
    * @return string
    */
    public function action_rest_cheer()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id = Input::get('user_id');
        $rest_id = Input::get('rest_id');
	$exp     = session::get('exp');
        $jwt     = self::check_jwtExp($exp);
        $data    = Model_Post::get_rest_cheer($rest_id);
        $num     = count($data);
        for ($i=0;$i<$num;$i++) {
            $target_user_id = $data[$i]['user_id'];
            $follow_flag    = Model_Follow::get_flag($user_id, $target_user_id);
            $adta[$i]['follow_flag'] = $follow_flag;
        }
	// $base_data
        $status = $this->output_json($data);
    }

    /**
    * User Search
    *
    * @return string
    */
    public function action_user_search()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id         = session::get('user_id');
        $targetUserName  = Input::get('username');
	$exp             = session::get('exp');
        $jwt             = self::check_jwtExp($exp);
        $targetUserId    = Model_User::get_id($targetUserName);
        $userData        = Model_User::get_data($user_id, $targetUserId);
	// $base_data       = 
	$status          = $this->output_json($userData);
    }
}
