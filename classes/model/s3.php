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
        error_log($code);
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
        return $name[0];
    }

    public static function input_img($user_id, $profile_img)
    {
        $bucket = Config::get('_s3.Bucket');

        $client = new S3Client([
            'region'  => 'ap-northeast-1',
            'version' => '2006-03-01'
        ]);
        $put_name = "$user_id" . '_' . date("Y-m-d-H-i-s") . '.png';

        $result = $client->putObject(array(
           'Bucket' => $bucket,
           // 'Key'    => date('Y/m/d/H/i/s'),
           'Key'    => $put_name,
           'Body'   => fopen($profile_img, 'r')
           // 'Body' => EntityBody::factory(fopen($profile_img, 'r'))
        ));
        $name = explode('.', $put_name);
        return $name[0];
    }
}
