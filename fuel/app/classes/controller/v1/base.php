<?php
/**
*
*/

class Controller_V1_Base extends Controller
{

	public function before()
	{
		 $user_id = session::get('user_id');

		if(empty($user_id))
		{
			$unauth = $this->unauth();
			error_log('UnAuthorized Accsess..');
			exit;
		}
	}


	private function unauth()
	{
		$status = array(
			'code'   => '401',
			'status' => 'UnAuthorized');

		$status = json_encode(
        	$status,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

		echo "$status";
	}


	public function output_json($data)
	{
		$json = json_encode(
			$data,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
		);

		echo "$json";
	}
}


