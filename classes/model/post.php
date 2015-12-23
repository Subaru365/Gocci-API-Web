<?php
/**
 * Post Model Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Post extends Model
{
    use GocciAPI;
    /**
     * @param  Int $post_id
     *
     * @return Int $user_id
     */
    public static function get_user_id($post_id)
    {
        $query = DB::select('user_id')->from('posts')
        // ->where('post_id', '=', 'post_id');
        ->where('post_id', '=', $post_id);
        $user_id = $query->execute()->as_array();
        return $user_id = $user_id[0]['user_id'];
    }

    /**
     * post_hash_idからpost_idを取得する
     * @param Int $hash_id
     *
     * @param Array $notice_data
     */
    public static function get_post_id($hash_id)
    {
        // echo $hash_id . "\n";
        $post_id = `/usr/local/bin/inasehash -d {$hash_id}`;
        self::check_post_id_exist($post_id);
        return $post_id;
    }

    /**
     * 変換されたpost_idがDBに存在するかチェックします。
     * @param Int $post_id
     */
    public static function check_post_id_exist($post_id)
    {
        $query = DB::select('post_id')->from('posts')
        ->where('post_id', '=', $post_id);

        $post_id = $query->execute()->as_array();

        if (isset($post_id[0]['post_id'])) {
            // exists!
        } else {
             GocciAPI::error_json("Not Found");
             exit;
        }
    }

    /**
     * POST取得
     * @param Int $user_id
     * @param Int $sort_id
     * @param Int $option
     * @param Int $limit
     * @param Array post_data
     */
    public static function get_data(
            $user_id, $sort_key,
            $sort_id, $option = 0, $limit = 15)
    {
        $query = DB::select(
            'post_id', 'movie', 'thumbnail', 'category', 'tag', 'value',
            'memo', 'post_date', 'cheer_flag',
            'user_id', 'username', 'profile_img', 'rest_id', 'restname', 'locality',
            DB::expr("GLength(GeomFromText(CONCAT('LineString(${option['lon']} ${option['lat']},', X(lon_lat),' ', Y(lon_lat),')'))) as distance")
        )
        ->from('posts')
        ->join('restaurants', 'INNER')
        ->on('post_rest_id', '=', 'rest_id')
        ->join('users', 'INNER')
        ->on('post_user_id', '=', 'user_id')
        ->join('categories', 'LEFT OUTER')
        ->on('post_category_id', '=', 'category_id')
        ->join('tags', 'LEFT OUTER')
        ->on('post_tag_id', '=', 'tag_id')
        ->where('post_status_flag', '1')
        ->limit($limit);

        // $sort_keyによる絞り込み
        if ($sort_key == 'all') {
            // 何もせず全て出力。
        } elseif ($sort_key == 'post') {
            $query->where('post_id', $sort_id);
        } elseif ($sort_key == 'rest') {
            $query->where('post_rest_id', $sort_id);
        } elseif ($sort_key == 'user') {
            $query->where('user_id', $sort_id);
        } elseif ($sort_key == 'users') {
            $query->where('user_id', 'in', $sort_id);
        } else {
            error_log("Model_Post:${sort_key}が不正です");
            exit;
        }

        // 並び替え
        if ($option['order_id'] == 0) {
            $query->order_by('post_date','desc');
        } elseif ($option['order_id'] == 1) {
            // 近い順
            $query->order_by(DB::expr("GLength(GeomFromText(CONCAT('LineString(${option['lon']} ${option['lat']},', X(lon_lat),' ', Y(lon_lat),')')))"));
        } elseif ($option['order_id'] == 2) {
            // Gochi!ランキング
            // 対象となる投稿の期間($interval)
            $now_date = date("Y-m-d",strtotime("+1 day"));
            $interval = date("Y-m-d",strtotime("-1 month"));

            $query->join('gochis', 'RIGHT')
            ->on('gochi_post_id', '=', 'post_id')

            ->where('gochi_date', 'BETWEEN', array("$interval", "$now_date"))

            ->group_by('gochi_post_id')
            ->order_by(DB::expr('COUNT(gochi_post_id)'), 'desc');
        }

        // カテゴリー絞り込み
        if ($option['category_id'] != 0) {
            $query->where('category_id', $option['category_id']);
        }

        // 価格絞り込み
        if ($option['value_id'] != 0) {
            if ($option['value_id'] == 1) {
                $query->where('value', 'between', array(1, 700));
            }
            if ($option['value_id'] == 2) {
                $query->where('value', 'between', array(500, 1500));
            }
            if ($option['value_id'] == 3) {
                $query->where('value', 'between', array(1500, 5000));
            }
            if ($option['value_id'] == 4) {
                $query->where('value', '>', 3000);
            }
        }

        // 追加読み込み
        if ($option['call'] != 0) {
            $call_num = $option['call'] * $limit;
            // echo $call_num;exit;
            $query->offset($call_num);
        }

        $query ->order_by('post_date','desc');
        $post_data = $query->execute()->as_array();
        $post_num  = count($post_data);

        for ($i=0; $i < $post_num; $i++) {
            $movie = $post_data[$i]['movie'];
            $post_data[$i]['mp4_movie']   = Model_Transcode::decode_mp4_movie($post_data[$i]['movie']);
            $post_data[$i]['movie']       = Model_Transcode::decode_hls_movie($post_data[$i]['movie']);
            $post_data[$i]['thumbnail']   = Model_Transcode::decode_thumbnail($post_data[$i]['thumbnail']);
            $post_data[$i]['profile_img'] = Model_Transcode::decode_profile_img($post_data[$i]['profile_img']);
            $post_data[$i]['share'] = 'mp4/' . "$movie" . '.mp4';

            $dis          = $post_data[$i]['distance'];
            $dis_meter    = $dis * 112120;
            $post_data[$i]['distance'] = round($dis_meter);

            $post_id      = $post_data[$i]['post_id'];
            $post_user_id = $post_data[$i]['user_id'];
            $post_rest_id = $post_data[$i]['rest_id'];
            $post_date    = $post_data[$i]['post_date'];

            $post_data[$i]['gochi_num']   = Model_Gochi::get_num($post_id);
            $post_data[$i]['comment_num'] = Model_Comment::get_num($post_id);
            $post_data[$i]['want_flag']   = Model_Want::get_flag($user_id, $post_rest_id);
            $post_data[$i]['follow_flag'] = Model_Follow::get_flag($user_id, $post_user_id);
            $post_data[$i]['gochi_flag']  = Model_Gochi::get_flag($user_id, $post_id);
            $post_data[$i]['post_date']   = Model_Date::get_data($post_date);

            $post_data[$i]['user_hash_id']= Hash_Id::create_user_hash($post_user_id);
        }
        return $post_data;
    }

    /**
     * 1投稿分のデータを取得
     * @param Int $user_id
     * @param Int $limit
     * @param Int $post_id
     * @param Array post_data
     */
    public static function get_one_data($user_id, $limit = 1, $post_id)
    {
        error_log('user_id');
        error_log($user_id);

        $query = DB::select(
                'post_id', 'movie', 'thumbnail', 'category', 'tag', 'value',
                'memo', 'post_date', 'cheer_flag',
                'user_id', 'username', 'profile_img', 'rest_id', 'restname','tell', 'locality',
                DB::expr('X(lon_lat), Y(lon_lat)')
        )
        ->from('posts')
        ->join('restaurants', 'INNER')
        ->on('post_rest_id', '=', 'rest_id')
        ->join('users', 'INNER')
        ->on('post_user_id', '=', 'user_id')
        ->join('categories', 'LEFT OUTER')
        ->on('post_category_id', '=', 'category_id')
        ->join('tags', 'LEFT OUTER')
        ->on('post_tag_id', '=', 'tag_id')
        ->where('post_status_flag', '1')
        // ->and_where('post_id','=', $post_id)
        ->limit($limit);

        $post_data = $query->execute()->as_array();

        $post_num  = count($post_data);
        // print_R($post_num);exit;
        for ($i=0; $i < $post_num; $i++) {
            $movie = $post_data[$i]['movie'];
            $post_data[$i]['mp4_movie']   = Model_Transcode::decode_mp4_movie($post_data[$i]['movie']);
            $post_data[$i]['movie']       = Model_Transcode::decode_hls_movie($post_data[$i]['movie']);
            $post_data[$i]['thumbnail']   = Model_Transcode::decode_thumbnail($post_data[$i]['thumbnail']);
            $post_data[$i]['profile_img'] = Model_Transcode::decode_profile_img($post_data[$i]['profile_img']);
            $post_data[$i]['share']       = 'mp4/' . "$movie" . '.mp4';
            $post_id                      = $post_data[$i]['post_id'];
            $post_user_id                 = $post_data[$i]['user_id'];
            $post_rest_id                 = $post_data[$i]['rest_id'];
            $post_date                    = $post_data[$i]['post_date'];
            $post_data[$i]['gochi_num']   = Model_Gochi::get_num($post_id);
            $post_data[$i]['comment_num'] = Model_Comment::get_num($post_id);
            $post_data[$i]['want_flag']   = Model_Want::get_flag($user_id, $post_rest_id);
            $post_data[$i]['follow_flag'] = Model_Follow::get_flag($user_id, $post_user_id);
            $post_data[$i]['gochi_flag']  = Model_Gochi::get_flag($user_id, $post_id);
            $post_data[$i]['post_date']   = Model_Date::get_data($post_date);
        }
        return $post_data;
    }

    /**
     * 送られてきたpost_idがDBに存在するか確認
     * @param  Int $post_id
     * @return Int $post_id
     */
    public static function check_post_id($post_id) 
    {
        // 送られてきたpost_idがDBに存在するか
        $query = DB::select('post_id')->from('posts')
        ->where('post_id', $post_id);

        $post_id = $query->execute()->as_array();
        if (empty($post_id[0])) {
            exit;
        } else {
            return $post_id;
        }
    }

    /**
     * 動画データを取得
     * @param  Int $post_id
     * @return String post_data
     */
    public static function get_video_data($post_id)
    {
        $query = DB::select('movie', 'thumbnail')->from('posts')
        ->where('post_id', "$post_id");
        $movie = $query->execute()->as_array();
        $post_data[0]['mp4_movie']   = Model_Transcode::decode_mp4_movie($movie[0]['movie']);
        return $post_data[0]['mp4_movie'];
    }

    /**
     * ユーザデータを取得
     * @param  Int $post_id
     * @return Int $post_user_id
     */
    public static function get_user($post_id)
    {
        $query = DB::select('post_user_id')->from('posts')
        ->where('post_id', "$post_id");

        $post_user_id = $query->execute()->as_array();
        return $post_user_id[0]['post_user_id'];
    }

    /**
     * メモデータを取得
     * @param  Int $post_id
     * @return String post_comment
     */
    public static function get_memo($post_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img', 'memo', 'post_date')
        ->from('posts')

        ->join('users', 'INNER')
        ->on('post_user_id', '=', 'user_id')

        ->where('post_id', "$post_id");

        $value = $query->execute()->as_array();

        try {
            if (empty($value))
                $value[0] = [];
        } catch (Exception $e) {
            var_dump($e);exit;
        }
        // print_r($value);exit;
        $re_user = array();
        array_push ($value[0], $re_user);

        $key = array('comment_user_id', 'username', 'profile_img', 'comment', 'comment_date', 're_user');
        $post_comment = array_combine($key, $value[0]);

        return $post_comment;
    }

    /**
     * 1ユーザーが応援している店舗リストを取得
     * @param  Int $user_id
     * @return Array $cheer_list
     */
    public static function get_user_cheer($user_id)
    {
        $query = DB::select('rest_id', 'restname', 'locality')
        ->from('posts')

        ->join('restaurants', 'INNER')
        ->on('post_rest_id', '=', 'rest_id')

        ->where('post_user_id', "$user_id")
        ->and_where('cheer_flag', '1')
        ->and_where('post_status_flag', '1')

        ->distinct(true);

        $cheer_list = $query->execute()->as_array();
        return $cheer_list;
    }

    /**
     * 1店舗に対して応援しているユーザーリストを取得
     * @param  Int $rest_id
     * @return Array $cheer_list
     */
    public static function get_rest_cheer($rest_id)
    {
        $query = DB::select('user_id', 'username', 'profile_img')
        ->from('posts')

        ->join('users', 'INNER')
        ->on('post_user_id', '=', 'user_id')

        ->where('post_rest_id', "$rest_id")
        ->and_where('cheer_flag', '1')
        ->and_where('post_status_flag', '1')

        ->distinct(true);

        $cheer_list = $query->execute()->as_array();

        $num = count($cheer_list);

        for ($i=0; $i < $num; $i++) {
            $cheer_list[$i]['profile_img'] = Model_Transcode::decode_profile_img($cheer_list[$i]['profile_img']);
        }
        return $cheer_list;
    }

    /**
     * ユーザーに対する応援店数を取得
     * @param  Int $user_id
     * @return Int $cheer_num
     */
    public static function get_user_cheer_num($user_id)
    {
        $query = DB::select('post_rest_id')->from('posts')

        ->where    ('post_user_id', "$user_id")
        ->and_where('cheer_flag', '1')
        ->and_where('post_status_flag', '1')

        ->distinct(true);

        $result = $query->execute()->as_array();

        $cheer_num = count($result);
        return $cheer_num;
    }

    /**
     * 店舗に対する応援総数
     * @param  Int $rest_id
     * @return Int $cheer_num
     */
    public static function get_rest_cheer_num($rest_id)
    {
        $query = DB::select('post_id')->from('posts')

        ->where    ('post_rest_id', "$rest_id")
        ->and_where('cheer_flag', '1')
        ->and_where('post_status_flag', '1');

        $result = $query->execute()->as_array();

        $cheer_num = count($result);
        return $cheer_num;
    }

    /**
     * 動画投稿
     * @param  Int $user_id
     * @param  Int $rest_id
     * @param  String $movie_name
     * @param  Int $category_id
     * @param  Int $tag_id
     * @param  String $value
     * @param  String $memo
     * @param  Int $cheer_flag
     *
     * @return $query
     */
    public static function post_data(
            $user_id, $rest_id, $movie_name, $category_id, $tag_id, $value, $memo, $cheer_flag)
    {
        $directory = explode('-', $movie_name);

        $movie     = "$directory[0]" . '/' . "$directory[1]" . '/'  . "$movie_name" . '_movie';
        $thumbnail = "$directory[0]" . '/' . "$directory[1]" . '/'  . '00002_' . "$movie_name" . '_img';

        $query = DB::insert('posts')
        ->set(array(
            'post_user_id'      => "$user_id",
            'post_rest_id'      => "$rest_id",
            'movie'             => "$movie",
            'thumbnail'         => "$thumbnail",
            'post_category_id'  => "$category_id",
            'post_tag_id'       => "$tag_id",
            'value'             => "$value",
            'memo'              => "$memo",
            'cheer_flag'        => "$cheer_flag"
        ))
        ->execute();
        return $query;
    }

    /**
     * 投稿を更新
     * @param String $movie
     * @return Int $cheer_num
     */
    public static function post_publish($movie)
    {
        $query = DB::update('posts')
        ->set  (array('post_status_flag' => '1'))
        ->where('movie', "$movie");
        $result = $query->execute();
    }

    /**
     * 投稿を消去
     * @param  String $post_id
     * @return Array $result
     */
    public static function post_delete($post_id)
    {
        $query = DB::update('posts')
        ->set  (array('post_status_flag' => '0'))
        ->where('post_id', "$post_id");

        $result = $query->execute();
        return $result;
    }
}
