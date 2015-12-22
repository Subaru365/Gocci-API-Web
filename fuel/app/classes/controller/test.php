<?php
use Aws\Sns\SnsClient;
/**
*
*/
class Controller_Test extends Controller
{
	public static function action_index()
	{
		$body = array(
		    'Cache-Control'     => 'no-cache, no-store, max-age=0, must-revalidate',
		    'Expires'           => 'Mon, 26 Jul 1997 05:00:00 GMT',
		    'Pragma'            => 'no-cache',
		);

		// $response = new Response($body, 404, $headers);
		// $response->send_headers();

		return Response::forge($body, 404);
	}

	public static function push($endpointArn, $alert)
	{

		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$client->publish(array(

        	'TargetArn' => $endpointArn,
        	'MessageStructure' => 'json',

        	'Message' => json_encode(array
	        	(
		        	'APNS_SANDBOX' => json_encode(array
		          	(
		                'aps' => array(
		                    'alert' => $alert,
		                    'sound' => 'default',
		                    'badge' => 1
		            ),
		            // カスタム
		         	//'custom_text' => "$message",
		        	))
		    	)
		    )
        ));

	}
}
