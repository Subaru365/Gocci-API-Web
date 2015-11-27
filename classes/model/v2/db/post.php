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

class Model_V2_DB_Post extends Model
{
    use GocciAPI;

    private static $_table_name = 'posts';

    public static function getUserId($post_id) {}

    public static function getPostId($hash_id)
    {
        $post_id `/usr/local/bin/inasehash -d {$hash_id}`;
        return $post_id;
    }

    public static function getData(
          $user_id, $sort_key,
          $sort_id, $option = 0, $limit = 20)
    {
        $query = DB::select(
            'post_id', 'movie', 'thumbnail', 'category',
            'tag', 'value', 'memo', 'post_data', 'cheer_flag',
            'user_id', 'username', 'profile_img', 'rest_id',
            'restname', 'locality', 'user_id', 'username',
            'profile_img', 'rest_id', 'restname', 'locality',
            DB::expr("GLength(GeomFromText(CONCAT('LineString(${option['lon']}
            ${option['lat']},', X(lon_lat), ' ', Y(lon_lat),')')) as distance")
        )
        ->from(self::$_table_name)
        ->join('restaurants', 'INNER')
        ->on('post_rest_id', '=', 'rest_id')
        ->join('categories', 'LEFT OUTER')
        ->on('post_category_id', '=', 'category_id')
        ->join('tags', 'LEFT OUTER')
        ->on('post_tag_id', '=', 'tag_id')
        ->where('post_status_flag', '1')
        ->limit($limit);

    }

}