<?php

class Model_User extends Model
{
    //ユーザー名チェック
    public static function check_name($username)
    {
        $query = DB::select('username')->from('users')
        ->where('username', "$username");

        $result = $query->execute()->as_array();

        if (!empty($result)) {
        //username使用済み
            Controller_V1_Mobile_Base::output_none();
            $username = $result[0]['username'];
            error_log("$username" . 'は既に使用されています。');
            exit;
        }
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
        $query = DB::select(
            'user_id', 'username', 'profile_img', 'badge_num')
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


    //通知数リセット
    public static function reset_badge($user_id)
    {
        $query = DB::update('users')
        ->value('badge_num', '0')
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
        $query = DB::update('users')
        ->value('username', "$username")
        ->where('user_id', "$user_id")
        ->execute();

        return $query;
    }


    //プロフィール画像・ユーザー名変更
    public static function update_profile($user_id, $username, $profile_img)
    {
        $query = DB::update('users')
        ->set(array(
            'username'    => "$username",
            'profile_img' => "$profile_img"
        ))
        ->where('user_id', "$user_id")
        ->execute();

        return $query;
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
        $username, $profile_img, $identity_id)
    {
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
}
