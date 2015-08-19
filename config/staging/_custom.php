<?php

return array(
    '_cognito' => array(
        'IdentityPoolId' => 'us-east-1:b0252276-27e1-4069-be84-3383d4b3f897',
        'developer_provider' => 'login.gocci',
    ),

    '_s3' => array(
        'Bucket' => 'gocci.imgs.provider.jp',
    ),

    '_sns' => array(
        'android_ApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/GCM/gocci-android',
        'iOS_ApplicationArn' => 'arn:aws:sns:ap-northeast-1:318228258127:app/APNS/Gocci_iOS',
    ),

    '_url' => array(
        'img' => 'http://imgs.gocci.me/',
        'thumbnail' => 'http://thumbnails.gocci.me/',
        'hls_movie' => 'http://hls-movies.gocci.me/',
        'mp4_movie' => 'http://mp4-movies.gocci.me/',
    ),
);