<?php

/**
*
*/

class Model_V2_Validation extends Model
{
	public static function check_identity_id($user_data)
	{
		$val = Validation::forge();

		$val = self::format_identity_id($val);

		self::run($val, $user_data);
	}

	public static function check_signup($user_data)
	{
		$val = Validation::forge();

		$val = self::format_username($val);
		$val = self::format_register_id($val);

		self::run($val, $user_data);
		self::overlap_username($user_data['username']);
		self::overlap_register_id($user_data['register_id']);
	}

	public static function check_sns_login($user_data)
	{
		$val = Validation::forge();

		$val = self::format_identity_id($val);
		$val = self::format_register_id($val);

		self::run($val, $user_data);
	}

	public static function check_pass_login($user_data)
	{
		$val = Validation::forge();

		$val = self::format_username($val);
		$val = self::format_password($val);
		$val = self::format_identity_id($val);
		$val = self::format_register_id($val);

		self::run($val, $user_data);
		self::overlap_username($user_data['username']);
	}





	//Valodation Run
	protected static function run($val, $user_data)
	{
		if($val->run($user_data)){
		    //OK

		}else{
			//エラー 形式不備
		    foreach($val->error() as $key=>$value){
		    	$keys[]		= $key;
		    	$messages[] = $value;
		    }

		    $key 		= implode(", ", $keys);
		    $message    = implode(". ", $messages);

		    Controller_V2_Mobile_Base::output_validation_error($key, $message);
		    error_log("$message");

		    exit;
		}
	}


	//======================================================//
	//Validation methods

	private static function format_username($val)
	{
		$val->add('username', 'GET username')
		    ->add_rule('required')
		    ->add_rule('max_length', 15);

		return $val;
	}

	private static function format_password($val)
	{
		$val->add('pass', 'GET password')
		    ->add_rule('required')
		    ->add_rule('min_length', 5)
		    ->add_rule('max_length', 20);

		return $val;
	}

	private static function format_identity_id($val)
	{
		$val->add('identity_id', 'GET identity_id')
		    ->add_rule('required')
		    ->add_rule('match_pattern', 'us-east-1:[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}');

		return $val;
	}

	private static function format_register_id($val)
	{
		$val->add('register_id', 'GET register_id')
		    ->add_rule('required')
		    ->add_rule('match_pattern', '[a-zA-Z0-9.-_]{400,2200}');

		return $val;
	}


	//ユーザー名重複チェック
    private static function overlap_username($username)
    {
        $query = DB::select('user_id')->from('users')
        ->where('username', "$username");

        $result = $query->execute()->as_array();

        if (!empty($result)) {
            Controller_V2_Mobile_Base::output_error(301);
            exit;
        }
    }

    //デバイス登録履歴チェック
    private static function overlap_register_id($register_id)
    {
        $query = DB::select('device_id')
        ->from('devices')
        ->where('register_id', "$register_id");

        $result = $query->execute()->as_array();

        if (!empty($result)) {
            Controller_V2_Mobile_Base::output_error(302);
            exit;
        }
    }

}