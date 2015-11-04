<?php

use Aws\S3\S3Client;
use Aws\Common\Enum\Region;
use Aws\S3\Exception\S3Exception;
use Guzzle\Http\EntityBody;

class Model_Upload extends Model
{
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
	     error_log('アップロード成功');

	} catch (S3Exception $exc) {
    	    error_log("アップロード失敗");
    	    echo $exc->getMessage();
        }
    }
}
