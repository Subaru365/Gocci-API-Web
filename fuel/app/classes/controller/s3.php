
<?php

use Aws\S3\S3Client;

/**
*
*/
class Controller_S3 extends Controller
{
	/*

	public function action_index()
	{

		//use Aws\S3\S3Client;

		/*
		$options = [
   		 'region'            => 'us-west-2',
   		 'version'           => '2006-03-01',
   		 'signature_version' => 'v4'
		];

		$s3 = new S3Client($options);

		*/

		$s3 = new S3Client([
   			//'profile' => 'default',
   			'region'  => 'ap-northeast-1',
    		'version' => 'latest'
		]);

		/*
		$s3 = S3Client::factory(array(
 			'profile' => 'default',
 			//'region'  => 'ap-northeast-1',
		));
		*/


		$buckets= $s3->listBuckets();

    	foreach ($buckets as $bucket)
   		{
      	var_dump($bucket);
    	}
	}

}
