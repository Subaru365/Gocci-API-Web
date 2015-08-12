<?php

use Aws\Sns\SnsClient;

/**
*
*/
class Controller_Test extends Controller
{

	public function action_index()
	{

		// POSTされてきた緯度・経度を取得
		$lat = input::get('lat');
		$lon = input::get('lon');
		if (!$lat || !$lon || !is_numeric($lat) || !is_numeric($lon)) {
		  exit;
		}

		// APIを使用するURLを生成
		$url = 'http://search.olp.yahooapis.jp/OpenLocalPlatform/V1/localSearch?'
		  . 'appid=dj0zaiZpPWdpaGw5dkR4WjFINCZzPWNvbnN1bWVyc2VjcmV0Jng9Y2Q-'
		  . '&lat='."$lat"
		  . '&lon='."$lon"
		  . '&dist=0.1' // 上記指定座標からの検索範囲（km）
		  . '&category=address' // 地名のみ検索
		  . '&sort=gio';
		  //. '&n=1'; // 検索結果は1つだけ取得
		  //. '&datum=wgs' // 世界測地系を使う
		  //. '&al=3'; // 丁目、字レベルまで取得

		// 結果を取得
		$contents = file_get_contents($url);
		echo $contents;

		// 住所部分を取得してecho
		$xml = simplexml_load_string($contents);
		//echo $xml;

		$address = (string)$xml->Item->Address;
		//echo $address;
		//exit;

	}

	public function action_publish()
	{
		$client = new SnsClient([
			'region'  => 'ap-northeast-1',
    		'version' => '2010-03-31'
		]);

		$result = $client->publish([
    		'Message'   => '{"message":"コメント"}',
    		'TargetArn' => 'arn:aws:sns:ap-northeast-1:318228258127:endpoint/GCM/gocci-android/fd741ca4-05b6-377c-a608-5ca0f24a6131',
		]);
	}
}