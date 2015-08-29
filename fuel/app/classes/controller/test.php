<?php

use Aws\Sns\SnsClient;

/**
*
*/
class Controller_Test extends Controller
{

	//Followline
	public function action_index()
	{

		/**
		 * このコードは、サーバーをベンチマークして、どの程度のコストに耐えられるかを判断します。
		 * サーバーに負荷をかけすぎない範囲で、できるだけ高めのコストを設定したいものです。
		 * 基準として 8 から 10 程度からはじめ、サーバーが十分に高速なら、できるだけ上げていきましょう。
		 * 以下のコードでは、ストレッチングの時間を 50 ミリ秒以内にすることを狙っています。
		 * 対話形式のログインを扱う際の許容時間としては、このあたりが妥当なところでしょう。
		 */
		$timeTarget = 0.05; // 50 ミリ秒

		$cost = 8;
		do {
		    $cost++;
		    $start = microtime(true);
		    password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
		    $end = microtime(true);
		} while (($end - $start) < $timeTarget);

		echo "Appropriate Cost Found: " . $cost . "\n";
	}

}
