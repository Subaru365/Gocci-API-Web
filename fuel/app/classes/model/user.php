<?php

class Model_User extends Model
{

    //最新レコードの次のuser_idを取得
    public static function get_id()
    {
        $query = DB::select('user_id')->from('users')
        ->order_by('user_id', 'desc')
        ->limit   ('1');

        $result = $query->execute()->as_array();

        $user_id = $result[0]['user_id'];
        $user_id++;

        return $user_id;
    }


    //ユーザー名を取得
    public static function get_name($user_id)
    {
        $query = DB::select('username')->from('users')
        ->where('user_id', "$user_id");

        $username = $query->execute()->as_array();
        return $username[0];
    }

/*    //register_id取得
    public static function get_register($target_user_id)
    {
        $query = DB::select('register_id')->from('devices')
        ->where('device_user_id', "$target_user_id");

        $register_id = $query->execute()->as_array();

        return $register_id[0]['register_id'];
    }
*/

    public static function get_data($user_id, $target_user_id)
    {
        $query = DB::select(
            'user_id', 'username',
            'profile_img', 'cover_img')

        ->from('users')
        ->where('user_id', "$target_user_id");

        $user_data = $query->execute()->as_array();


        //---------------------------------------------------------//
        //付加情報格納(follow_num, fllower_num, cheer_num, status_flag)

        $follow_num   = Model_Follow::follow_num($target_user_id);
        $user_data[0]['follow_num']   = $follow_num;

        $follower_num = Model_Follow::follower_num($target_user_id);
        $user_data[0]['follower_num'] = $follower_num;

        $cheer_num    = Model_Post::cheer_num($target_user_id);
        $user_data[0]['cheer_num']    = $cheer_num;

        $want_num     = Model_Want::want_num($target_user_id);
        $user_data[0]['want_num']     = $want_num;

        $follow_flag  = Model_Want::get_flag($user_id, $target_user_id);
        $user_data[0]['follow_flag']  = $follow_flag;


        return $user_data[0];
    }


    //ユーザー登録
    public static function post_data($username, $profile_img, $identity_id)
    {

        if ($profile_img == 'none') {
        $profile_img = 'https://s3-us-west-2.amazonaws.com/gocci.img.provider/tosty_' . mt_rand(1, 7) . '.png';
        }

        $query = DB::insert('users')
        ->set(array(
            'username'    => "$username",
            'profile_img' => "$profile_img",
            'identity_id' => "$identity_id"
        ))
        ->execute();

        return $profile_img;
    }

}
