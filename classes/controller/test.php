<?php

use Aws\Sns\SnsClient;

/**
*
*/
class Controller_Test extends Controller
{
	public static function action_index()
	{
		$result	= Model_V2_Db_User::get_user_id_last();
		$result ++;

        return $result;
	}
}
