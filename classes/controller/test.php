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
		$url = 'http://geocode.didit.jp/reverse/?'
		  . 'lat='."$lat"
		  . '&lon='."$lon";

		// 結果を取得
		$contents = file_get_contents($url);
		echo $contents;

		// 住所部分を取得してecho
		//$xml = simplexml_load_string($contents);
		//echo $xml;

		//$address = (string)$xml->Item->Address;
		//echo $address;
		//exit;

	}
}