<?php

/**
* input filter Controller
* 入力されたテキストに不正がないかチェックします
*/
class Controller_V1_Inputfilter extends Controller
{

	public static function action_encoding($value)
	{
		if (is_array($value))
		{
			array_map(array('Controller_Inputfilter', 'action_encoding'), $value);
			return $value;
		}

		if (mb_check_encoding($value, Fuel::$encoding))
		{
			//正常
			return $value;
		}
		else
		{
			//異常
			Log::error(
				'Invalid character encoding: ' . Input::uri() . ' ' .
				rawurldecode($value) . ' ' .
				Input::ip() . ' "' . Input::user_agent() . '"'
			);

			//後処理
			$value = 'encodeing_error: 攻撃はダメ。ゼッタイ。';
			return $value;
		}
	}
}