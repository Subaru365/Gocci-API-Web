<?php
/**
* Controllerからの処理をここに集合させ、ハブとなるクラスです
* Aws, DBの処理は必ずこのモデルを通過します
*/
class Model_V2_Router extends Model
{
	public static function create_user($user_data)
	{
		$user_data['user_id'] = Model_V2_Db_User::get_user_id_next();
		session::set('user_id', $user_data['user_id']);

	    $result						= Model_V2_Aws_Cognito::set_data();
	    $user_data['identity_id']   = $result['IdentityId'];
	    $user_data['token']	        = $result['token'];

	    $user_data['profile_img']   = Model_V2_Db_User::set_data($user_data);
	    $user_data['endpoint_arn']  = Model_V2_Aws_Sns::set_endpoint($user_data);

	    Model_Device::set_data($user_data);

	    return $user_data;
	}


	public static function login($identity_id)
	{
        $user_data = Model_V2_Db_User::get_auth($identity_id);

        session::set('user_id', $user_data['user_id']);

        $user_data['token'] = Model_V2_Aws_Cognito::get_token($identity_id);
        Model_V2_Db_Login::post_login();

        return $user_data;
	}


}