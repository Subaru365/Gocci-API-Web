<?php
use Aws\S3\S3Client;

/**
* S3 Model
*/
class Controller_Test extends Controller
{
    public function action_index()
    {
        $client = new S3Client([
            'region'  => 'ap-northeast-1',
            'version' => '2006-03-01'
        ]);

        $result = $client->putObject([
            'Bucket' => 'gocci.imgs.provider.jp-test',
            'Key' => "hogehoge.png",
            'SourceFile' => '/tmp/img/3.png',
        ]);
    }
}