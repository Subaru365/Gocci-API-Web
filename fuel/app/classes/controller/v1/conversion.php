<?php
/**
*
*/
class Controller_V1_Conversion extends Controller
{

	public function action_index()
	{
		$keyword     = '顧客様';

		$username    = Input::get('username');
		$profile_img = Input::get('profile_img');

		$os          = Input::get('os');
        $model       = Input::get('model');
        $register_id = Input::get('register_id');

        $user_id     = Model_User::check_conversion($username);


        //初期ユーザー
        if (empty($user_id)) {
        	$user_id     = Model_User::get_id();

        	//IdentityID取得
        	$identity_id = Model_Cognito::post_data(
        	    $user_id, $username, $os, $model, $register_id);

	        $status = Controller_V1_Auth::signup(
	            $keyword, $user_id, $username, $profile_img,
	            $os, $model, $register_id, $identity_id);


        //VIPユーザー
        }else {

        	//IdentityID取得
	        $identity_id = Model_Cognito::post_data(
	            $user_id, $username, $os, $model, $register_id);


	        try{

				$badge_num = 0;

				$user_data = Model_User::post_data(
	                $username, $profile_img, $identity_id);

	            //AWS SNSに端末を登録
	            $brand = explode('_', $os);

	            if ($brand[0] == 'android') {
	                $endpoint_arn = Model_Sns::post_android(
	                    $user_id, $identity_id, $register_id);
	            }
	            elseif ($brand[0] == 'iOS') {
	                $endpoint_arn = Model_Sns::post_iOS(
	                    $user_id, $identity_id, $register_id);
	            }
	            else{
	                //Webかな？ 何もしない。
	            }

	            //Device情報を登録
	            $device = Model_Device::update_data(
	                $user_id, $os, $model, $register_id, $endpoint_arn);

	            //success出力へ
	            $status = Controller_V1_Auth::success($keyword,
	                $user_id, $username, $profile_img, $identity_id, $badge_num);
	        }

	        //データベース登録エラー
	        catch(\Database_Exception $e)
	        {
	            //failed出力へ
	            $status = Controller_V1_Auth::failed(
	                $keyword, $username, $profile_img, $identity_id, $badge_num);

	            error_log($e);
	        }
   		}
        echo "$status";

	}
}