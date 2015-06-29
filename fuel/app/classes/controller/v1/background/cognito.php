<?php

use Aws\CognitoSync\CognitoSyncClient;

/**
 * CognitoSync api
 *
 */

class Controller_V1_Background_Cognito extends Controller
{

    //CognitoSync Dataset
    public function action_dataset()
    {
        $identity_id = Input::get('identity_id');
        $username    = Input::get('username');
        $os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');

        $IdentityPoolId = 'us-east-1:a8cc1fdb-92b1-4586-ba97-9e6994a43195';


        $client = new CognitoSyncClient([
            'region'  => 'us-east-1',
            'version' => 'latest'
        ]);


        //SyncSessionToken取得
        $result = $client->listRecords([
            'DatasetName'    => 'user_info',
            'IdentityId'     => "$identity_id",
            'IdentityPoolId' => "$IdentityPoolId",
        ]);

        $sync_session_token = $result['SyncSessionToken'];


        //DataSet
        $result = $client->updateRecords([
            'DatasetName'    => 'user_info',
            'IdentityId'     => "$identity_id",
            'IdentityPoolId' => "$IdentityPoolId",
            'RecordPatches'  => [
                [
                    'Key' => 'username',
                    'Op' => 'replace',
                    'SyncCount' => 0,
                    'Value' => "$username",
                ],
                [
                    'Key' => 'os',
                    'Op' => 'replace',
                    'SyncCount' => 0,
                    'Value' => "$os",
                ],
                [
                    'Key' => 'model',
                    'Op' => 'replace',
                    'SyncCount' => 0,
                    'Value' => "$model",
                ],
                [
                    'Key' => 'register_id',
                    'Op' => 'replace',
                    'SyncCount' => 0,
                    'Value' => "$register_id",
                ],
            ],
            'SyncSessionToken' => "$sync_session_token",
        ]);

    exit;
    }
}