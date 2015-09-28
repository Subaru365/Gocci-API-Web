<?php
/**
*
*/
class Model_V2_Db_Device extends Model
{
    public static function set_data($user_data)
    {
        $query = DB::insert('devices')
     		->set(array(
          	'device_user_id'	=> "$user_data['user_id']",
          	'os'                => "$user_data['os']",
          	'model'             => "$user_data['model']",
          	'register_id'       => "$user_data['register_id']",
         		'endpoint_arn'      => "$user_data['endpoint_arn']"
        ))
        ->execute();
  	}

    public static function get_device_id($register_id)
    {
        $query = DB::select('device_id')
        ->from('devices')
        ->where('register_id', "$register_id");

        $result = $query->execute()->as_array();
        return $result;
    }
}