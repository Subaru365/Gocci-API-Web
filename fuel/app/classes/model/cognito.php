<?php

use Aws\CognitoIdentity\CognitoIdentityClient;

/**
* CognitoIdentity Model
*/
class Model_Cognito extends Model
{

	//IdentityID取得 DataSet [User_Info]
	public static function post_data($user_id, $username, $os, $model, $register_id)
	{
		$IdentityPoolId = 'us-east-1:a8cc1fdb-92b1-4586-ba97-9e6994a43195';

		$client = new CognitoIdentityClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);


		//Identity_ID作成
		$result = $client->getOpenIdTokenForDeveloperIdentity([
    		'IdentityPoolId' => "$IdentityPoolId",
   			'Logins' => ['login.inase.gocci'=> "$user_id",],
		]);

		$identity_id = $result['IdentityId'];


		//CognitoSync Dataset 外部処理
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
            'http://localhost/v1/background/cognito/dataset/?' .
                'identity_id=' . "$identity_id" . '&' .
                'username='    . "$username"    . '&' .
                'os='          . "$os"          . '&' .
                'model='       . "$model"       . '&' .
                'register_id=' . "$register_id"
        );

        curl_exec($ch);
        //curl_close($ch);


		return $identity_id;
	}



	//DataSetからユーザー情報を取得
	public static function get_data($identity_id)
	{
		$client = new CognitoSyncClient([
			'region'  => 'us-east-1',
    		'version' => 'latest'
		]);

		$result = $client->listRecords([
    		'DatasetName' 	 => 'user_info',
    		'IdentityId' 	 => "$identity_id",
    		'IdentityPoolId' => 'us-east-1:a8cc1fdb-92b1-4586-ba97-9e6994a43195'
		]);

		return $result;
	}

}

