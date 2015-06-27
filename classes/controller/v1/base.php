<?php
/**
*
*/
class Controller_V1_Base extends Controller
{
	//public $user_id = session::get('user_id');

	public function before()
	{
		 $user_id = session::get('user_id');

		if(empty($user_id))
		{
			$unauth = $this->unauth();
		}else{
			echo "signin!$user_id";
		}
	}

	public static function action_index()
	{
		echo 'Hello!';
	}



	protected function unauth()
	{
		$status = array(
			'status' => 'UnAuthorized');

		echo "$status";
	}

}


