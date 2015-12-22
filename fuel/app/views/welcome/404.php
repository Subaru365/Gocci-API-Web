<?php

$api_data = [
      'message' => 'このページは存在しません'
];
$base_data = Controller_V1_Base::base_template($api_code = 1,
                  $api_message = "Failed", 
                  $login_flag = 0, 
                  $api_data, 
                  $jwt = "");

echo $status    = Controller_V1_Base::json_encode_template($base_data);