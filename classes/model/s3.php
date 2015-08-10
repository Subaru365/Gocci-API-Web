<?php
use Aws\S3\S3Client;

/**
* S3 Model
*/
class Model_S3 extends Model
{
    public static function input($user_id, $profile_img_url)
    {
        $bucket = Config::get('_s3.Bucket');

        $i = rand(1, 10);
        $code = 'wget -O /tmp/img/' . "$i" . '.png ' . "$profile_img_url";
        exec("$code");

        $put_name = "$user_id" . '_' . date("Y-m-d-H-i-s") . '.png';

        $client = new S3Client([
            'region'  => 'ap-northeast-1',
            'version' => '2006-03-01'
        ]);

        $result = $client->putObject([
            'Bucket' => "$bucket",
            'Key' => "$put_name",
            'SourceFile' => '/tmp/img/' . "$i" . '.png',
        ]);

        $name = explode('.', $put_name);
        $profile_img = Model_Transcode::decode_profile_img($name[0]);

        return $profile_img;
    }
}