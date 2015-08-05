<?php

/**
* Test Controller
*/
class Controller_Test extends Controller
{
    public function action_index()
    {
        $provider = '';
        $token = '';

        $result = Model_Cognito::get_identity_id($provider, $token);

        print_r($result);
    }
}