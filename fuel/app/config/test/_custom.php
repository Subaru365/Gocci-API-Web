<?php

return array(
    '_cognito' => array(
        'IdentityPoolId' => 'us-east-1:2ef43520-856b-4641-b4a1-e08dfc07f802',
        'developer_provider' => 'test.login.gocci',
    ),

    '_s3' => array(
		'Bucket' => 'gocci.imgs.provider.jp-test',
    ),

    '_sns' => array(
		'android_ApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/GCM/gocci-android',
		'iOS_ApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/APNS_SANDBOX/gocci-iOS-dev',
    ),

    '_url' => array(
    	'img' => 'http://test.imgs.gocci.me/',
    	'thumbnail' => 'http://test.thumbnails.gocci.me/',
    	'hls_movie' => 'http://test.hls-movies.gocci.me/',
    	'mp4_movie' => 'http://test.mp4-movies.gocci.me/',
    ),
);