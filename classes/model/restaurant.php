<?php
/**
 * Restaurant Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Restaurant extends Model
{
    /**
     * 近くのお店30件取得
     * @param  Int $rest_id
     *
     * @return Array $query
     */
    public static function check_rest_id($rest_id)
    {
        $query = DB::select('rest_id')->from('restaurants')
        ->where('rest_id', '=', $rest_id)->execute()->as_array();
        return $query;
    }

    /**
     * 近くのお店30件取得
     * @param  Int $lon
     * @param  Int $lat
     *
     * @return $near_data
     */
    public static function get_near($lon, $lat)
    {
        $query = DB::select('rest_id', 'restname')->from('restaurants')
        ->order_by(DB::expr("GLength(GeomFromText(CONCAT('LineString(${lon} ${lat},', X(lon_lat),' ', Y(lon_lat),')')))"))
        ->limit(30);

        $near_data = $query->execute()->as_array();
        return $near_data;
    }

    /**
     * レストランデータ取得
     * @param  Int $user_id
     * @param  Int $rest_id
     *
     * @return $rest_data
     */
    public static function get_data($user_id, $rest_id)
    {
        $query = DB::select(
            'rest_id', 'restname', 'locality', 'lat',
            'lon', 'tell', 'homepage', 'rest_category'
        )
        ->from('restaurants')
        ->where('rest_id', "$rest_id");

        $rest_data = $query->execute()->as_array();
        $rest_data[0]['want_flag'] = Model_Want::get_flag($user_id, $rest_id);
        $rest_data[0]['cheer_num'] = Model_Post::get_rest_cheer_num($rest_id);

        return $rest_data[0];
    }

    /**
     * 店舗追加
     * @param  String $rest_name
     * @param  Int $lat
     * @param  Int $lon
     *
     * @return $rest_id
     */
    public static function post_add($rest_name, $lat, $lon)
    {
        $query = DB::insert('restaurants')
        ->set(array(
            'restname' => "$rest_name",
            'lat'      => "$lat",
            'lon'      => "$lon",
            'lon_lat'  => DB::expr('GeomFromText(' . "'" .
            'POINT(' . "$lon" . ' ' . "$lat" .')' . "'" . ')')
        ))
        ->execute();

        $query = DB::select('rest_id')->from('restaurants')
        ->order_by('rest_id', 'desc')
        ->limit   ('1');

        $result = $query->execute()->as_array();
        $rest_id = $result[0]['rest_id'];

        return $rest_id;
    }

}
