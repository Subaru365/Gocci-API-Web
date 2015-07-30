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

        Model_Cognito::dataset(
            $identity_id, $username, $os, $model, $register_id);
    }
}