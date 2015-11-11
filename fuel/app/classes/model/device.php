<?php
/**
 * Device Class 
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Device extends Model
{
    /**
     * 登録履歴確認
     * @param    $register_id
     */
    public static function check_register_id($register_id)
    {
        $query = DB::select('device_user_id', 'endpoint_arn')
        ->from('devices')
        ->where('register_id', "$register_id");

        $device_data = $query->execute()->as_array();

        if (!empty($device_data)) {
            // 登録あり→消去
            $old_user_id  = $device_data[0]['device_user_id'];
            $endpoint_arn = $device_data[0]['endpoint_arn'];

            Model_User::update_logout($old_user_id);
            self::delete_device($old_user_id);
            Model_Sns::delete_endpoint($endpoint_arn);
        }
    }

    /**
     * 登録履歴確認
     * @param Int $user_id
     *
     * @return Int $register_id
     */
    public static function get_register_id($user_id)
    {
        $query = DB::select('register_id')->from('devices')
        ->where('device_user_id', "$user_id");

        $register_id = $query->execute()->as_array();
        return $register_id[0]['register_id'];
    }

    /**
     * arn取得
     * @param Int $user_id
     */
    public static function get_arn($user_id)
    {
        $query = DB::select('endpoint_arn')->from('devices')
        ->where('device_user_id', "$user_id");

        $result = $query -> execute()->as_array();

        if (!empty($result)) {
            Model_Sns::delete_endpoint($result[0]['endpoint_arn']);
        }
    }

    /**
     * 登録
     * @param Int $user_id
     * @param String $os
     * @param String $model
     * @param Int $register_id
     * @param String $register_id
     */
    public static function post_data($user_id, $os, $model, $register_id, $endpoint_arn)
    {
        $query = DB::insert('devices')
        ->set(array(
          'device_user_id' => "$user_id",
          'os'             => "$os",
          'model'          => "$model",
          'register_id'    => "$register_id",
          'endpoint_arn'   => "$endpoint_arn"
        ))
        ->execute();
    }

    /**
     * register_id更新
     * @param Int $user_id
     * @param Int $register_id
     * @param String $register_id
     *
     * @return $query
     */
    public static function update_register_id($user_id, $register_id, $endpoint_arn)
    {
        $query = DB::update('devices')
        ->set(array(
            'register_id' => "$register_id",
            'endpoint_arn'=> "$endpoint_arn"
        ))
        ->where('device_user_id', "$user_id")
        ->execute();

        return $query;
    }

    /**
     * データ更新
     * @param Int $user_id
     * @param String $os
     * @param String $model
     * @param Int $register_id
     * @param String $endpoint_arn
     *
     * @return $query
     */
    public static function update_data(
       $user_id, $os, $model, $register_id, $endpoint_arn)
    {
       $query = DB::update('devices')
       ->set(array(
           'os'          => "$os",
           'model'       => "$model",
           'register_id' => "$register_id",
           'endpoint_arn'=> "$endpoint_arn"
       ))
       ->where('device_user_id', "$user_id")
       ->execute();

       return $query;
    }

    /**
     * デバイスID削除
     * @param Int $user_id
     */
    public static function delete_device($user_id)
    {
        $query = DB::delete('devices')
        ->where('device_user_id', "$user_id")
        ->execute();
    }

    /**
     * Conversion
     * @param Int $register_id
     */
    public static function check_conversion($register_id)
    {
        $query = DB::select('device_id')
        ->from('devices')
        ->where('register_id', "$register_id");

        $device_id = $query->execute()->as_array();

        if (!empty($device_id)) {
          // 登録あり→エラー
          Controller_V1_Mobile_Base::output_none();
          exit;
        }
    }
}


