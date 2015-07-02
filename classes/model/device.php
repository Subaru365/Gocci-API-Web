<?php
class Model_Device extends Model
{
	public static function get_arn($target_user_id)
	{
		$query = DB::select('endpoint_arn')->from('devices')
		->where('device_user_id', "$target_user_id");

		$endpoint_arn = $query -> execute()->as_array();

		return $endpoint_arn[0];
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


     //Conversion
     //==================================================================//


     public static function update_data(
        $user_id, $os, $model, $register_id, $endpoint_arn)
     {
        $query = DB::update('devices')
        ->set(array(
            'os'             => "$os",
            'model'          => "$model",
            'register_id'    => "$register_id",
            'endpoint_arn'   => "$endpoint_arn"
        ))
        ->where('device_user_id', "$user_id")
        ->execute();

     }

}



