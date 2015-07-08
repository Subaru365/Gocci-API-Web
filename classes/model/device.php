<?php
class Model_Device extends Model
{
    public static function check_device($register_id)
    {
        $query = DB::select('device_id')->from('devices')
        ->where('register_id', "$register_id");

        $device_id = $query->execute()->as_array();
        return $device_id;
    }


    public static function get_old_data($register_id)
    {
        $query = DB::select('device_user_id', 'endpoint_arn')
        ->from('devices')
        ->where('register_id', "$register_id");

        $old_data = $query->execute()->as_array();
        return $old_data;
    }


    public static function get_register_id($user_id)
    {
        $query = DB::select('register_id')->from('devices')
        ->where('device_user_id', "$user_id");

        $register_id = $query->execute()->as_array();
        return $register_id[0]['register_id'];
    }


	public static function get_arn($user_id)
	{
		$query = DB::select('endpoint_arn')->from('devices')
		->where('device_user_id', "$user_id");

		$endpoint_arn = $query -> execute()->as_array();
		return $endpoint_arn[0]['endpoint_arn'];
	}


     public static function post_data(
     	$user_id, $os, $model, $register_id, $endpoint_arn)
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


     public static function delete_device($user_id)
     {
        $query = DB::delete('devices')
        ->where('device_user_id', "$user_id")
        ->execute();
     }
}


