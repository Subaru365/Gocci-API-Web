<?php
/**
 * Get Model
 *
 * @package    Gocci-Web
 * @version    3.0.0 <2016/1/27>
 * @author     kazunori tani <k-tania@inase-inc.jp>
 * @copyright  (C) 2015 kazunori tani
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_Get extends Model 
{
    public static function getNearRestId($lat, $lon)
    {
        $query = DB::select('post_rest_id','rest_category')
        ->from('posts')
        ->join('restaurants', 'INNER')
        ->on('post_rest_id', '=', 'rest_id')
        ->order_by(DB::expr("Glength(GeomFromText(
          CONCAT('LineString(
          {$lon} {$lat},', X(lon_lat),' ', Y(lon_lat),')')))"))
        ->where('post_status_flag', '1')
        ->distinct(true)
        ->limit($limit = 15)->execute()->as_array();

        if (empty($query)) {
          return $query = [];
        } else {
          return $query;
          // return $query['post_rest_id'];
        }
    }
}
