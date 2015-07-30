<?php
use Aws\S3\S3Client;

/**
* S3 Model
*/
class Model_S3 extends Model
{
    public static function input($user_id, $profile_img_url)
    {
        $i = rand(1, 10);
        exec('wget -O /tmp/img/' . "$i" . '.png ' . "$profile_img_url");

        $put_name = "$user_id" . '_' . date("Y-m-d-H-i-s") . '.png';

        $client = new S3Client([
            'region'  => 'ap-northeast-1',
            'version' => '2006-03-01'
        ]);

        $result = $client->putObject([
            'Bucket' => 'gocci.imgs.provider.jp-test',
            'Key' => "$put_name",
            'SourceFile' => '/tmp/img/' . "$i" . '.png',
        ]);

        return $put_name;
    }
}

