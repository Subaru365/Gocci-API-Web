<?php

/**
*
*/

class Model_V2_Error extends Model
{
	//
	public static function get_subject($error_code)
	{
		return 'Boom!';
	}

	//
	public static function get_message($error_code)
	{
		switch ($error_code){

			case 301:
				$message = 'ユーザーネームが登録されています';
			  	break;

			case 302:
				$message = '端末が登録されています';
			  	break;

			case 303:
				$message = 'identity_idが未登録です';
			  	break;

			case 304:
				$message = '';
			  	break;
		}

		return $message;
	}

}