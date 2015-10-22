<?php

class Model_User extends Model
{
    // ユーザー名とパスワードをチェック【web】
    public static function check_name_pass($username, $password)
    {
	// username/passwordの両方が空の場合
	if (empty($username) && empty($password)) {
	    Controller_V1_Web_Base::error_json('Username and password do not enter.');
	} else if (empty($username) || empty($password)) {
	    // usernameもしくはpasswordが空の場合
	    Controller_V1_Web_Base::error_json('Username or password do not enter.');
	}
	
    }

    //ユーザー名チェック
    public static function check_name($username)
    {
        $query = DB::select('username')->from('users')
        ->where('username', "$username");

        $result = $query->execute()->as_array();
        return $result;
    }

    // ユーザー名チェック
    public static function check_web_name($username)
    {
        $query = DB::select('username')->from('users')
        ->where('username', "$username");

        $result = $query->execute()->as_array();

        if (!empty($result[0]['username'])) {
             Controller_V1_Web_Base::error_json("username already registered.");
             // 既に登録されているusername

        } else {
             // まだ登録されていないusername
	     // $username = $result[0]['username'];
             return $username;
         }

    }

    public static function empty_name($username)
    {

	if (empty($username)) {
	   // TRUEだとusernameは空である
	   return Controller_V1_Web_Base::error_json("Please enter your user name.");
	} else {
	   return $username;
	}
    }

    public static function empty_password($password)
    {
	if (empty($password)) {
	    return Controller_V1_Web_Base::error_json("Please enter your password.");
	} else {
	    return $password;
	}

    }

    public static function format_name_check($username)
    {
	//$username = '';
	// 文字数チェック
	if (preg_match('/^[a-z\d_]{4,20}$/i', $username)) {
   	     // 4 - 20文字以内
	     return $username;
	} else {
    	     return Controller_V1_Web_Base::error_json("ユーザーネームは4文字から20文字以内です");
	}

    }


    public static function check_pass($username, $password)
    {
        $query = DB::select('user_id', 'profile_img', 'identity_id', 'badge_num', 'password')
        ->from('users')
        ->where('username', "$username");

        $result = $query->execute()->as_array();

        self::verify_pass($password, $result[0]['password']);

        return $result;
    }


    public static function check_img($profile_img)
    {
        $query = DB::select('user_id', 'username')->from('users')
        ->where('profile_img', "$profile_img");

        $result = $query->execute()->as_array();

        if (empty($result)) {
        //profile_img該当なし
            Controller_V1_Mobile_Base::output_none();
            error_log("$profile_img" . 'は該当するものがありませんでした。');
            exit;
        }
        return $result[0];
    }


    //ログインフラグ取得
    public static function check_login($user_id)
    {
        $query = DB::select('login_flag')->from('users')
        ->where('user_id', "$user_id");

        $login_flag = $query->execute()->as_array();

        return $login_flag[0]['login_flag'];
    }


    //user_id取得
    public static function get_id($username)
    {
        $query = DB::select('user_id')->from('users')
        ->where('username', "$username");

        $user_id = $query->execute()->as_array();

        if (empty($user_id)) {
            $user_id[0]['user_id'] = '';
        }

        return $user_id[0]['user_id'];
    }


    //次のuser_id取得
    public static function get_next_id()
    {
        $query = DB::select('user_id')->from('users')
        ->order_by('user_id', 'desc')
        ->limit   ('1');

        $result = $query->execute()->as_array();

        $user_id = $result[0]['user_id'];
        $user_id++;

        return $user_id;
    }


    //identity_id取得
    public static function get_identity_id($user_id)
    {
        $query = DB::select('identity_id')->from('users')
        ->where('user_id', "$user_id");

        $identity_id = $query->execute()->as_array();
        return $identity_id[0]['identity_id'];
    }


    //ユーザー名取得
    public static function get_name($user_id)
    {
        $query = DB::select('username')->from('users')
        ->where('user_id', "$user_id");

        $username = $query->execute()->as_array();
        return $username[0]['username'];
    }


    //ユーザー名、プロフィール画像取得
    public static function get_profile($user_id)
    {
        $query = DB::select('username', 'profile_img')
        ->from('users')
        ->where('user_id', "$user_id");

        $user_data = $query->execute()->as_array();
        $user_data[0]['profile_img'] =
            Model_Transcode::decode_profile_img($user_data[0]['profile_img']);

        return $user_data[0];
    }


    //通知数取得
    public static function get_badge($user_id)
    {
        $query = DB::select('badge_num')->from('users')
        ->where('user_id', "$user_id");

        $user_id = $query->execute()->as_array();
        return $user_id[0]['badge_num'];
    }


    //ログインユーザー情報取得
    public static function get_auth($identity_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img', 'badge_num')
        ->from ('users')
        ->where('identity_id', "$identity_id");

        $user_data = $query->execute()->as_array();


        if (empty($user_data)) {
            Controller_V1_Mobile_Base::output_none();
            error_log('登録されてないユーザー:' . "$identity_id");

            //Cognitoから消去
            Model_Cognito::delete_identity_id($identity_id);
            exit;
        }

        $user_data[0]['profile_img'] =
            Model_Transcode::decode_profile_img($user_data[0]['profile_img']);

        return $user_data[0];
    }

    // Web用 ログインユーザ情報取得
    public static function web_get_auth($identity_id)
    {
	$query = DB::select('user_id', 'username', 'profile_img', 'badge_num')->from('users')
	->where('identity_id', $identity_id);
	$user_data = $query->execute()->as_array();

	if (empty($user_data)) {
	    Controller_V1_Web_Base::error_json('登録されていないユーザです');
	    error_log('登録されていないユーザー' . $identity_id);
	    // Cognitoから消去
	    Model_Cognito::delete_identity_id($identity_id);
	    exit;
	}
	$user_data[0]['profile_img'] = Model_Transcode::decode_profile_img($user_data[0]['profile_img']);
	return $user_data[0];
    }

    // username/passwordからidentity_idを取得する【web】
    public static function get_web_identity_id($username, $password)
    {
	$query = DB::select('identity_id')
		->from('users')
		->where('username', $username)
		->and_where('password', $password);

	$identity_id = $query->execute()->as_array();

    }

    //ユーザーページ情報取得
    public static function get_data($user_id, $target_user_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img')
        ->from('users')
        ->where('user_id', "$target_user_id");

        $user_data = $query->execute()->as_array();


        //---------------------------------------------------------//
        //付加情報格納(follow_num, fllower_num, cheer_num, status_flag)

        $user_data[0]['profile_img']  = Model_Transcode::decode_profile_img($user_data[0]['profile_img']);
        $user_data[0]['follow_num']   = Model_Follow::follow_num($target_user_id);
        $user_data[0]['follower_num'] = Model_Follow::follower_num($target_user_id);
        $user_data[0]['cheer_num']    = Model_Post::get_user_cheer_num($target_user_id);
        $user_data[0]['want_num']     = Model_Want::want_num($target_user_id);
        $user_data[0]['follow_flag']  = Model_Follow::get_flag($user_id, $target_user_id);

        return $user_data[0];
    }


    //ユーザー登録
    public static function post_data($username, $identity_id)
    {
        $profile_img = '0_tosty_' . mt_rand(1, 7);

        $query = DB::insert('users')
        ->set(array(
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id"
        ))
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }

    // username/password登録
    public static function insert_data($username, $identity_id,$password)
    {
	$profile_img = '0_tosty_' . mt_rand(1, 7);

        $query = DB::insert('users')
        ->set(array(
            'username'    => $username,
            'profile_img' => $profile_img,
            'identity_id' => $identity_id,
	    'password'    => $password,
        ))
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;


    }

    // sns inset
    public static function sns_insert_data($username, $identity_id, $profile_img)
    {
	$query = DB::insert('users')
	->set(array(
	    'username'    => $username,
	    'profile_img' => $profile_img,
	    'identity_id' => $identity_id,
	))
	->execute();	
	$profile_img = Model_Transcode::decode_profile_img($profile_img);
	// error_log('usersテーブルにinsertしました');
        return $profile_img;
    }


    //通知数リセット
    public static function reset_badge($user_id)
    {
        $query = DB::update('users')
        ->value('badge_num', '0')
        ->where('user_id', "$user_id")
        ->execute();

        return $query;
    }


    //
    public static function update_pass($user_id, $pass)
    {
        $encryption_pass = self::encryption_pass($pass);

        $query = DB::update('users')
        ->value('password', "$encryption_pass")
        ->where('user_id', "$user_id")
        ->execute();

        return $query;
    }


    //SNS連携
    public static function update_sns_flag($user_id, $provider)
    {
        if ($provider == 'graph.facebook.com') {
            $flag = 'facebook_flag';
        } else {
            $flag = 'twitter_flag';
        }

        $query = DB::update('users')
        ->value("$flag", '1')
        ->where('user_id', "$user_id")
        ->execute();
    }


    //プロフィール画像変更
    public static function update_profile_img($user_id, $profile_img)
    {
        $query = DB::update('users')
        ->value('profile_img', "$profile_img")
        ->where('user_id', "$user_id")
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }


    //ユーザー名変更
    public static function update_name($user_id, $username)
    {
        $result = self::check_name($username);

        if (!empty($result)) {
        //username使用済み
            error_log("$username" . 'は既に使用されています。');
            $username = '変更に失敗しました';

        }else{
            $query = DB::update('users')
            ->value('username', "$username")
            ->where('user_id', "$user_id")
            ->execute();
        }
        return $username;
    }


    //プロフィール画像・ユーザー名変更
    public static function update_profile($user_id, $username, $profile_img)
    {
        $query = DB::update('users')
        ->value('profile_img', "$profile_img");

        if ($username != '変更に失敗しました') {
            $query->value('username', "$username");
        }

        $query->where('user_id', "$user_id")
        ->execute();

        return $username;
    }


    //Logout
    public static function update_logout($user_id)
    {
        $query = DB::update('users')
        ->value('login_flag', '0')
        ->where('user_id', "$user_id")
        ->execute();
    }


    //SNS連携
    public static function delete_sns_flag($user_id, $provider)
    {
        if ($provider == 'graph.facebook.com') {
            $flag = 'facebook_flag';
        } else {
            $flag = 'twitter_flag';
        }

        $query = DB::update('users')
        ->value("$flag", '0')
        ->where('user_id', "$user_id")
        ->execute();
    }


    private static function encryption_pass($pass)
    {
        $hash_pass = password_hash($pass, PASSWORD_BCRYPT);
        return $hash_pass;
    }


    private static function verify_pass($pass, $hash_pass)
    {
        if (password_verify($pass, $hash_pass)) {
            //認証OK
	    
        }else{
	    
            error_log('パスワードが一致しません');
            Controller_V1_Mobile_Base::output_none();
            exit;
        }
    }

    public static function web_verify_pass($pass, $hash_pass)
    {
	if (password_verify($pass, $hash_pass)) {
            //認証OK
	    $match_pass = password_verify($pass, $hash_pass);
        }else{
	     error_log('current_pass');
             error_log($pass);

             error_log('hash_pass');
             error_log($hash_pass);
             error_log('パスワードが一致しません');
             Controller_V1_Web_Base::error_json("パスワードが正しくありません");
             exit;
        }
	return $match_pass;
    }

    public static function get_current_db_pass($user_id, $current_password)
    {
	// ユーザから送られてきた生パスワードをエンクリプト
	$encrypt_password = self::encryption_pass($current_password);
	// TRUEだったらmatch_passは1が代入される
	// $match_pass = self::web_verify_pass($current_password, $encrypt_password);
	// dbからパスワードを取得
	$query = DB::select('password')
                ->from('users')
                ->where('user_id', $user_id);
	// これを実行すると、as_arrayには何が入るのか [成功と失敗で]
        return $db_password = $query->execute()->as_array();
	

    }

    //Conversion
    //===========================================================================//


    //Conversion
    public static function check_conversion($username)
    {
        $query = DB::select('user_id')->from('users')
        ->where('username', "$username")
        ->execute()->as_array();

        return $query;
    }


    //ユーザー登録
    public static function post_conversion(
        $user_id, $username, $profile_img, $identity_id)
    {
        $query = DB::insert('users')
        ->set(array(
            'user_id'     => "$user_id",
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id"
        ))
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }


    //更新
    public static function update_data(
        $user_id, $username, $profile_img, $identity_id)
    {
        $query = DB::update('users')
        ->set(array(
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id"
        ))
        ->where('user_id', "$user_id")
        ->execute();

        $profile_img = Model_Transcode::decode_profile_img($profile_img);
        return $profile_img;
    }

    // web: username/password check
    public static function pass_login_validate($username, $passsword)
    {
	$val = Validation::forge();
	$val->add_field('username', 'ユーザーネーム', 'required');
	$val->add_field('password', 'パスワード', 'required');
    }
   
    // web username/profile_img 空かどうかチェック
    public static function username_profile_img_check($username, $profile_img)
    {
	if (empty($username) || empty($profile_img)) {
	      Controller_V1_Web_Base::error_json('Username and profile_img do not enter.');
         }
    }
}
