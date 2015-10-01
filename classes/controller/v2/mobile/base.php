<?php
header('Content-Type: application/json; charset=UTF-8');
/**
*
*/

class Controller_V2_Mobile_Base extends Controller
{
	//Check session
	public function before()
	{
		$user_id = session::get('user_id');

		if(empty($user_id))
		{
			self::output_error(0);
			error_log('UnAuthorized Accsess.');
			exit;
		}
	}

    public static function output_success($specific_data)
    {
        $api_data = array(
            'version'   => 2.0,
            'code'      => 100,
            'subject'   => '[OK]',
            'message'   => 'Success!',
            'data'      => $specific_data
        );

        self::output_json($api_data);
    }


    public static function output_validation_error($key, $message)
    {
        $api_data = array(
            'version'   => 2.0,
            'code'      => 301,
            'subject'   => "[ERROR] $key Validation Error!",
            'message'   => "$message",
            'data'      => array()
        );

        self::output_json($api_data);
    }


    public static function output_error($error_code)
    {
        $api_data = array(
            'version'   => 2.0,
            'code'      => $error_code,
            'subject'   => Model_V2_Error::get_subject($error_code),
            'message'   => Model_V2_Error::get_message($error_code),
            'data'      => array()
        );

        self::output_json($api_data);
    }


	public static function output_json($api_data)
	{
		$json = json_encode(
			$api_data,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
		);

		echo $json;
	}
}


