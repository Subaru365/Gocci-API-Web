<?php
use Aws\S3\S3Client;
use Aws\Common\Enum\Region;
use Aws\S3\Exception\S3Exception;
use Guzzle\Http\EntityBody;

/**
 * Upload Class
 * @package    Gocci-Web
 * @version    3.0 <2015/10/20>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

class Model_Upload extends Model
{
    /**
     * @param String $upload_file
     * @param String $save_filename
     */
    public static function picture_upload($upload_file, $save_filename)
    {
        if (!is_uploaded_file($upload_file)) {
           die('ファイルがアップロードされていません');
        }

        $bucket = Config::get('_s3.Bucket');
        $client = new S3Client([
            'region'  => 'ap-northeast-1',
            'version' => '2006-03-01'
        ]);

        try {
            $result = $client->putObject(array(
                'Bucket' => $bucket,
                'Key' => $save_filename,
                'Body' => EntityBody::factory(fopen($upload_file, 'r')),
           ));
        } catch (S3Exception $exc) {
            echo $exc->getMessage();
        }
    }
}