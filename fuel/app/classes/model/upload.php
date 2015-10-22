<?php
// require_once("aws.phar");

use Aws\S3\S3Client;
use Aws\Common\Enum\Region;
use Aws\S3\Exception\S3Exception;
use Guzzle\Http\EntityBody;

class Model_Upload extends Model
{
    public static function picture_upload($upload_file, $save_filename)
    {
	/*
	$client = S3Client::factory(array(
  	    "key" => "xxxxxxxxxxxxxxxxxxxxx",
	    "secret" => "xxxxxxxxxxxxxxxxxxxxxxxxxxx",
 	    "region" => Region::AP_NORTHEAST_1 // AP_NORTHEAST_1はtokyo region
	));
  	*/
	

	if (!is_uploaded_file($upload_file)) {
	    die('ファイルがアップロードされていません');
	}
	// バケット名
	// $bucket = "gocci.imgs.provider.jp-test";
	$bucket = Config::get('_s3.Bucket');
	// アップロードファイル名

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
	     error_log('アップロード成功');
    	    // echo "アップロード成功！";
	} catch (S3Exception $exc) {
    	    error_log("アップロード失敗");
    	    echo $exc->getMessage();
        }
    }
}
