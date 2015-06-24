<?php

class Model_User extends Model
{

    public static function get_data($user_id, $target_user_id)
    {
        //クエリ文
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


    public static function post_guest($username, $os, $model, $token)
    {

        $query = DB::insert('users')
        ->set(array(
            'username'    => "$username",
            'profile_img' => 'https://s3-ap-northeast-1.amazonaws.com/gocci.master/imgs/tosty_1.png'
        ))
        ->execute();


        $query = DB::select('user_id')->from('users')
        ->order_by('user_id', 'desc')
        ->limit   ('1');

        $user_id = $query->execute()->as_array();


        $query = DB::insert('devices')
        ->set(array(
            'device_user_id' => "$user_id"
            'os'             => "$os",
            'model'          => "$model",
            'register_id'    => "$token"
        ));




    }



/*
    public static function get_auth($username)
    {
        $status_ary = array(
                        'username'  => "$username",
                        'picture'   => 'OK',
                        'background'=> '',
                        'badge_num' => '0',
                        'message'   => '作成完了！Gocciへようこそ！'
                        );
    }
*/

















}
