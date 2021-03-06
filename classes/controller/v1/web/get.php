<?php
/**
 * GET  API    リソースの取得
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Controller_V1_Web_Get extends Controller_V1_Web_Base
{
    /**
     * before
     */
    public function before()
    {
        // SCRIPT要素で埋め込まれないための対策
        if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            $_SERVER['HTTP_X_REQUEST_WITH'] !== 'XMLHttpRequest') {
            // Not Ajax Request
            // json output
        }
        self::accessLog();
        // $this->start_basic();
    }

    public function start_basic()
    {
        switch (true) {
            case !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']):
            case $_SERVER['PHP_AUTH_USER'] !== 'gocci_web':
            case $_SERVER['PHP_AUTH_PW']   !== 'gocci_web':
                header('WWW-Authenticate: Basic realm="Enter username and password."');
                die('ログインが必要です');
        }
    }

    /**
     * jwtがあるかどうかをcheckするメソッド
     * @param $uri
     * @param $login_flag
     */
    public function create_token($uri, $login_flag)
    {
        $jwt = self::get_jwt();
        if(isset($jwt)) {
            $data      = self::decode($jwt);
            $user_data = session::get('data');
            $obj       = json_decode($user_data);

            if (empty($obj)) {
                self::unauth($uri, $login_flag);
            }

            $user_id   = $obj->{'user_id'};
            session::set('user_id', $user_id);
            $username  = $obj->{'username'};
            session::set('username', $username);
            $exp       = $obj->{'exp'};
            session::set('exp', $exp);
        } else {
            self::unauth();
            error_log('JWT nothing. UnAuthorized Accsess..');
            exit;
        }
    }

    /**
     * timeline
     */
    public function action_timeline()
    {
        $jwt = self::get_jwt();
        $obj = self::getJwtObject($jwt);

        if (empty($obj)) {
            $data = self::timeline_template();
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "UnAuthorized", 
                $login_flag = 0,$data, $jwt);
            $this->output_json($base_data);
            exit;
        } else {
            self::create_token($uri=Uri::string(), $login_flag=0);	
            $data = self::timeline_template();
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag =  1,$data, $jwt);
            $this->output_json($base_data);
        }
     }

    /**
     * badge数を取得するapi
     */
    public function action_notice_badge()
    {
        self::create_token($uri=Uri::string(), $login_flag=0);
        $user_id = session::get('user_id');
        $exp     = session::get('exp');
        $jwt     = self::check_jwtExp($exp);
        try {
            $badge_num = Model_notice::get_badge($user_id);
            $data = [
                "badge_num" => $badge_num
            ];
            $base_data = self::base_template($api_code = "SUCCESS", 
                $api_message = "Successful API request", 
                $login_flag =  1, $data, $jwt);
            $status = $this->output_json($base_data);
        } catch (\Database_Exception $e) {

        }
    }

    /**
     * Restaurant recommendation
     */
    public function action_recommendation()
    {
        self::create_token($uri=Uri::string(), $login_flag=1);
        $user_id = session::get('user_id');
        $exp     = session::get('exp');
        $jwt     = self::check_jwtExp($exp);
    }

    /**
     * Popular loading
     */
    public function action_popular_loading()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $sort_key = 'post';
        $page_num = Input::get('page');
        $user_id  = session::get('user_id');
        $exp      = session::get('exp');
        $jwt      = self::check_jwtExp($exp);
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
        $status = $this->output_json($data);
    }

    /**
     * Comment Page
     */
    public function action_comment()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $sort_key     = 'post';
        $user_id      = session::get('user_id');
        $post_id      = Input::get('post_id');
        if (empty($post_id)) {
            exit;
        }
        $exp          = session::get('exp');
        $jwt          = self::check_jwtExp($exp);
        $post_data    = Model_Post::get_data($user_id, $sort_key, $post_id);
        $Comment_data = Model_Comment::get_data($post_id);

        $data = [
            "post"     => $post_data[0],
            "comments" => $Comment_data
        ];
        $base_data = self::base_template($api_code = "SUCCESS", $api_message = "Successful API request", $login_flag = 1, $data, $jwt);
        $status = $this->output_json($data);
    }

    /**
     * Restarants Page ver3
     */
    public function action_rest()
    {
        $rest_id    = Input::get('rest_id');
        $uri        = Uri::string();
        $login_flag = 0;
        $sort_key   = 'rest';

        $jwt = self::get_jwt();
        if (isset($jwt)) {
            $data      = self::decode($jwt);
            $user_data = session::get('data');
            $obj       = json_decode($user_data);
            if (empty($obj)) {
                $jwt= "";
                $user_id= 0;

                $data = self::rest_template($user_id, $rest_id, $sort_key);
                $base_data = self::base_template($api_code = "SUCCESS", $api_message = "UnAuthorized", $login_flag, $data, $jwt);
                $status = $this->output_json($base_data);
                exit;
            }
        }
        $user_id   = $obj->{'user_id'};
        error_log('user_id');
        error_log($user_id);
        session::set('user_id', $user_id);
        $username  = $obj->{'username'};
        session::set('username', $username);
        $exp       = $obj->{'exp'};
        session::set('exp', $exp);
        $rest_id= Input::get('rest_id');
        $jwt    = self::check_jwtExp($exp);
        $data   = self::rest_template($user_id, $rest_id, $sort_key);

        $base_data = self::base_template($api_code = "SUCCESS", $api_message = "Successful API request", $login_flag =  1, $data, $jwt);
        $status = $this->output_json($base_data);
    }

    /**
     * User Page
     */
    public function action_user($target_username)
    {
        $uri = Uri::string();
        $jwt = @$_SERVER["HTTP_AUTHORIZATION"] ? @$_SERVER["HTTP_AUTHORIZATION"] : "";

        if(isset($jwt)) {
            $data      = self::decode($jwt);
            $user_data = session::get('data');
            $obj       = json_decode($user_data);

            if (empty($obj)) {
                $sort_key       = 'user';
                $limit          = 20;
                $data = self::user_template($target_username, $limit, $sort_key);

                $base_data = self::base_template($api_code = "SUCESS", $api_message = "UnAuthorized", $login_flag =  0, $data, $jwt);
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

        $data = self::user_template($target_username, $limit, $sort_key);
        $base_data = self::base_template($api_code = "SUCCESS", $api_message = "Successful API request", $login_flag =  1, $data, $jwt);
        $status = $this->output_json($base_data);
    }

    /**
     * Notice Page
     */
    public function action_notice()
    {
        self::create_token($uri=Uri::string(), $login_flag=1);
        $user_id = session::get('user_id');
        // $user_id = 4; // debug
        $exp     = session::get('exp');
        $jwt     = self::check_jwtExp($exp);
        $data    = Model_Notice::get_data($user_id);
        Model_User::reset_badge($user_id);
        $base_data = self::base_template($api_code = 0, $api_message = "SUCCESS", $login_flag =  1,$data, $jwt);
        // $status  = $this->debug_output_json($base_data);// debug
        $status  = $this->output_json($base_data);
    }

    /**
     * Near
     */
    public function action_near()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $lon  = Input::get('lon');
        $lat  = Input::get('lat');
        $exp  = session::get('exp');
        $jwt  = self::check_jwtExp($exp);
        $data = Model_Restaurant::get_near($lon, $lat);
        $base_data = self::base_template($api_code = "SUCCESS", 
        $api_message = "Successful API request", 
        $login_flag =  1,$data, $jwt);
        $status = $this->output_json($base_data);	
    }

    /**
     * Follow Timeline
     */
    public function action_followline()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id  = session::get('user_id');

        $exp      = session::get('exp');
        $jwt      = self::check_jwtExp($exp);
        $sort_key = 'users';

        $option = [
            'call'        => Input::get('call', 0),
            'order_id'    => Input::get('order_id', 0),
            'category_id' => Input::get('category_id', 0),
            'value_id'    => Input::get('value_id', 0),
            'lon'         => Input::get('lon', 0),
            'lat'         => Input::get('lat', 0)
        ];

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
        error_log('followline api');
        $base_data = self::base_template($api_code = 0, $api_message = "SUCCESS", $login_flag =  1,$data, $jwt);
        $status    = $this->output_json($base_data);
    }

    /**
     * Follow List
     */
    public function action_follow()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id        = session::get('user_id');
        $target_user_id = Input::get('target_user_id');
        $exp            = session::get('exp');
        $jwt            = self::check_jwtExp($exp);
        $data           = Model_Follow::get_follow($user_id, $target_user_id);
        $base_data      = self::base_template($api_code = "SUCCESS", $api_message = "Successful API request", $login_flag =  1,$data, $jwt);
        $stats          = $this->output_json($base_data);
    }

    /**
     * Follower List
     */
    public function action_follower()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id        = session::get('user_id');
        $target_user_id = Input::get('target_user_id');
        $exp            = session::get('exp');
        $jwt            = self::check_jwtExp($exp);
        $data           = Model_Follow::get_follower($user_id, $target_user_id);
        $base_data      = self::base_template($api_code = "SUCCESS", $api_message = "Successful API request", $login_flag =  1,$data, $jwt);
        $status         = $this->output_json($base_data);
    }

    /**
     * 行きたい登録 List
     */
    public function action_want()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $target_user_id = Input::get('target_user_id');
        $exp = session::get('exp');
        $jwt = self::check_jwtExp($exp);
        $data= Model_Want::get_want($target_user_id);
        $base_data = self::base_template($api_code = "SUCCESS", 
            $api_message = "Successful API request", $login_flag =  1,$data, $jwt);
        $status = $this->output_json($data);
    }

    /**
     * 店舗登録 LIst
     */
    public function action_user_cheer()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $target_user_id = Input::get('target_user_id');
        $exp  = session::get('exp');
        $jwt  = self::check_jwtExp($exp);
        $data = Model_Post::get_user_cheer($target_user_id);
        $base_data = self::base_template($api_code = "SUCCESS", 
                    $api_message = "Successful API request",
                    $login_flag =  1, $data, $jwt);
        $status = $this->output_json($base_data);
    }

    /**
     * 応援ユーザ List
     */
    public function action_rest_cheer()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id = session::get('user_id');
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
        $base_data = self::base_template($api_code = "SUCCESS", 
            $api_message = "Successful API request", 
            $login_flag =  1,$data, $jwt);
        $status = $this->output_json($base_data);
    }

    /**
     * User Search
     */
    public function action_user_search()
    {
        self::create_token($uri=Uri::string(), $login_flag=2);
        $user_id         = session::get('user_id');
        $targetUserName  = Input::get('username');
        $exp             = session::get('exp');
        $jwt             = self::check_jwtExp($exp);
        $targetUserId    = Model_User::get_id($targetUserName);
        $data            = Model_User::get_data($user_id, $targetUserId);
        $base_data       = self::base_template($api_code = 0, $api_message = "SUCCESS", 
                            $login_flag =  1, $data, $jwt);
        $status          = $this->output_json($base_data);
    }

    /**
     * Video api
     */
    public function action_video($hash_id)
    {

        // jwtを取得して、そこからuser_idを取得しないと、いくらでも、gochi/commentで来てしまう。
        $jwt = self::get_jwt();
        if (isset($jwt)) {
            $data      = self::decode($jwt);
            $user_data = session::get('data');
            $obj       = json_decode($user_data);


            if (empty($obj)) {
                // 未ログインユーザー
                $jwt= "";
                $user_id= 0;
                $data = self::video_template($user_id, $hash_id);
                $base_data = self::base_template($api_code = "SUCESS", $api_message = "UnAuthorized", $login_flag = 0, $data, $jwt);
                $status = $this->output_json($base_data);
                exit;
            }
        }
        // このページにアクセスしたユーザーがログイン済みのユーザーなのか、
        // もしくは、未ログインのユーザーなのかで、いいね、コメントの処理を分岐させる。
        // 未ログインであれば、gochi/commentをしようとした際にダイアログを表示する。
        // $user_id = session::get('user_id');
        // sessionではなく、$objからuser_idを取得
        $user_id   = $obj->{'user_id'};
        error_log('user_id');
        error_log($user_id);

        $data = self::video_template($user_id, $hash_id);

        $base_data = self::base_template($api_code = "SUCCESS", $api_message = "Successful API request", $login_flag = 1, $data, $jwt = "");
        $status = $this->output_json($base_data);
    }
}
