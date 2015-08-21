<?php

return array(
    '_cognito' => array(
        'IdentityPoolId' => 'us-east-1:b563cebf-1de2-4931-9f08-da7b4725ae35',
        'developer_provider' => 'test.login.gocci',
    ),

    '_s3' => array(
		'Bucket' => 'gocci.imgs.provider.jp-test',
    ),

    '_sns' => array(
		'android_ApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/GCM/gocci-android',
		'iOS_ApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/APNS_SANDBOX/Gocci_iOS_Dev',
    ),

    '_url' => array(
    	'img' => 'http://test.imgs.gocci.me/',
    	'thumbnail' => 'http://test.thumbnails.gocci.me/',
    	'hls_movie' => 'http://test.hls-movies.gocci.me/',
    	'mp4_movie' => 'http://test.mp4-movies.gocci.me/',
    ),
);