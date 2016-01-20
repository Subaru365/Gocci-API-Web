<?php
/**
 * Token Model Class
 * @package    Gocci-Web
 * @version    3.0 <2016/1/16>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Token extends Model
{
  use GocciAPI;

  /**
   * 既にDBに同じtokenがないかを調べます。
   * @param Int    $user_id
   * @param String $token
   */
  public static function check_tokne($token)
  {
    $num = 1;
    try {
      $query = DB::select('token')->from('tokens')
      ->where('token','=', $token)
      ->order_by('token_date', 'desc')
      ->limit($num)
      ->execute()->as_array();
    } catch (ExcepitonError $e) {
      error_log($e);
    }
    if (isset($query[0]['token'])) {
      error_log('既に登録されたアカウントです');
      return $query[0]['token'];
      exit;
    }
    // まだ登録されていないので処理続行
    return true;
  }

  /**
   * @param  String $token
   * @return String $image
   */
  public static function getImage($token) {
    $num = 1;
    try {
      $query = DB::select('image')->from('tokens')
      ->where('token','=', $token)
      ->order_by('token_date', 'desc')
      ->limit($num)
      ->execute()->as_array();
    } catch (ExcepitonError $e) {
      error_log($e);
    }
    if (!isset($query[0]['iamge'])) {
      error_log('登録されていないアカウントです');
      exit;
    }
    return $query[0]['image'];
  }

  /**
   * @param  String $token
   * @return Int    $user_id
   */
  public static function getUserId($token) {
    $num = 1;
    try {
      $query = DB::select('user_id')->from('tokens')
      ->where('token','=', $token)
      ->order_by('token_date', 'desc')
      ->limit($num)
      ->execute()->as_array();
    } catch (ExcepitonError $e) {
      error_log($e);
    }
    if (!isset($query[0]['user_id'])) {
      error_log('登録されていないアカウントです');
      exit;
    }
    return $query[0]['user_id'];
  }

  /**
   * DBにtokenとimageをinsertします
   * @param String $token
   * @param String $image
   */
  public static function insert_token($user_id, $token, $image)
  {
    $query = DB::insert('tokens')
    ->set(array(
      'user_id'=> $user_id,
      'token'  => $token,
      'image'  => $image
    ))->execute();
    return $query;
  }

  /**
   * DBからtokenとimageを取得します
   * @param  Int    $user_id
   * @param  String $token
   * @param  String $image
   * @return Array
   */
  public static function get_token_data($user_id)
  {
    $num = 1;

    try {
      $query = DB::select('token', 'image')->from('tokens')
      ->where('user_id', '=', $user_id)
      ->order_by('token_date', 'desc')
      ->limit($num)
      ->execute()->as_array();
    } catch (ExcepitonError $e) {
      error_log($e);
    }

   return $query;
  }
}