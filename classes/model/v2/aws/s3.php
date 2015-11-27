<?php
/**
 * S3 Class
 * @package    Gocci-Web
 * @version    3.0 <2015/11/25>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_V2_Aws_S3 extends Model
{
    use Aws\S3\S3Client;
    /**
     * @param Int    $user_id
     * @param String $profile_img_url
     *
     * @param String $name
     */
    public static function input($user_id, $profile_img_url)
    {
        $bucket = Config::get('_s3.Bucket');

        $i = rand(1, 10);

        $code = 'wget -0 /tmp/img/' . $i . '.png ' . $profile_img_url;
        exec("$code");

        $put_name = $user_id . '_' . date("Y-m-d-H-i-s") . '.png';

        $client = new S3Client([
            'region'  => 'ap-northeast-1',
            'version' => '2006-03-01'
        ]);

        $result = $client->putObject([
            'Bucket'     => $bucket,
            'Key'        => $put_name,
            'SourceFile' => '/tmp/img' . $i . '.png',
        ]);

        $name = explode('.', $put_name);
        return $name[0];
    }

    /**
     * @param Int    $user_id
     * @param String $profile_img
     *
     * @param String $name
     */
    public static function input_img($user_id, $profile_img)
    {
        $bucket = Config::get('_s3.Bucket');

        $client = new S3Client([
            'region'  => 'ap-northeast-1',
            'version' => '2006-03-01'
        ]);
        $put_name = $user_id . '_' . date("Y-m-d-H-i-s") . '.png';

        $result = $client->putObject(array(
            'Bucket' => $bucket,
            'Key'    => $put_name,
            'Body'   => fopen($profile_img, 'r')
        ));
        $name = explode('.', $put_name);
        return $name[0];
    }
}