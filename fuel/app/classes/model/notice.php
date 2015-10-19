<?php

class Model_Notice extends Model
{

    public static function get_data($user_id)
    {
        $query = DB::select(
    		    'notice_id', 'notice_a_user_id', 'username',
    		    'profile_img', 'notice', 'notice_post_id',
    		    'read_flag', 'notice_date')
       	->from('notices')

        ->join('users', 'INNER')
        ->on('notices.notice_a_user_id', '=', 'users.user_id')

        ->join('posts', 'INNER')
        ->on('notice_post_id', '=', 'post_id')

        ->order_by('notices.notice_date','desc')

        ->limit('15')
        ->where('notices.notice_p_user_id', "$user_id")
        ->where('post_status_flag', '1');

        $notice_data = $query->execute()->as_array();


        $num = count($notice_data);

        for ($i=0; $i < $num; $i++) {
          	//日付情報を現在との差分に書き換え
          	$notice_data[$i]['notice_date'] = Model_Date::get_data($notice_data[$i]['notice_date']);
            $notice_data[$i]['profile_img'] = Model_Transcode::decode_profile_img($notice_data[$i]['profile_img']);
        }

    		return $notice_data;
    }


    //Notice登録
    public static function post_data(
   	    $keyword, $a_user_id, $p_user_id, $post_id = 1)
    {
       	if ($keyword == 'gochi!') {
       		  $notice = 'like';

       	}elseif ($keyword == 'コメント') {
       	  	$notice = 'comment';

        }elseif ($keyword == 'フォロー') {
            $notice = 'follow';

        }else{
       	  	$notice = 'announce';
       	}

       	$query = DB::insert('notices')
       	->set(array(
       		  'notice_a_user_id' => "$a_user_id",
       		  'notice_p_user_id' => "$p_user_id",
       		  'notice'           => "$notice",
       		  'notice_post_id'   => "$post_id"
       	))
       	->execute();


        //SNS Publish 外部処理
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
            'http://localhost/v1/mobile/background/publish/'
            .'?keyword='   . "$keyword"
            .'&a_user_id=' . "$a_user_id"
            .'&p_user_id=' . "$p_user_id"
        );

        curl_exec($ch);
        curl_close($ch);
    }

    // web notice
    public static function web_notice_insert($keyword, $a_user_id, $p_user_id, $post_id = 1)
    {
	if ($keyword == 'gochi!') {
                  $notice = 'like';

        }elseif ($keyword == 'コメント') {
                $notice = 'comment';

        }elseif ($keyword == 'フォロー') {
            $notice = 'follow';

        }else{
                $notice = 'announce';
        }

        $query = DB::insert('notices')
        ->set(array(
                  'notice_a_user_id' => "$a_user_id",
                  'notice_p_user_id' => "$p_user_id",
                  'notice'           => "$notice",
                  'notice_post_id'   => "$post_id"
        ))
        ->execute();





    }

}
