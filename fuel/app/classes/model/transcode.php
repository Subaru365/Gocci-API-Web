<?php
/**
 * Transcode Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */
class Model_Transcode extends Model
{
    /**
     * @param String $profile_img
     *
     * @return String $profile_img
     */
    public static function decode_profile_img($profile_img)
    {
        $img_url = Config::get('_url.img');
        $profile_img = "$img_url" . "$profile_img" . '.png';
        return $profile_img;
    }

    /**
     * @param String $thumbnail
     *
     * @return String $thumbnail
     */
    public static function decode_thumbnail($thumbnail)
    {
        $thumbnail_url = Config::get('_url.thumbnail');
        $thumbnail = "$thumbnail_url" . "$thumbnail" . '.png';
        return $thumbnail;
    }

    /**
     * @param String $movie
     *
     * @return String $movie
     */
    public static function decode_hls_movie($movie)
    {
        $movie_url = Config::get('_url.hls_movie');
        $movie = "$movie_url" . "$movie" . '.m3u8';
        return $movie;
    }

    /**
     * @param String movie
     *
     * @return String $movie
     */
    public static function decode_mp4_movie($movie)
    {
        $movie_url = Config::get('_url.mp4_movie');
        $movie = "$movie_url" . "$movie" . '.mp4';
        return $movie;
    }
}