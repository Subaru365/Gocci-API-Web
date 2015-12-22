<?php

class Controller_V1_Check extends Controller
{
    /**
     * @param String $message
     * @param String $previous
     */
    private function e($message, $previous = null)
    {
        return new Exception($message, 0, $previous);
    }

    /**
     * 例外スタックから順次取り出して配列に変換するメソッド
     * @param $e
     */
    private function exception_to_array(Exception $e)
    {
        do {
          $errors[] = $e->getMessage();
        } while ($e = $e->getPrevious());
        return array_reverse($errors);
    }

    /**
     * 文字エンコーディングの検証フィルタ
     *
     * @param  String | Array $value
     * @return String | Array
     * @throws HttpInvalidInputException
     */
    public static function check_encoding($value)
    {
        // 配列の場合は再帰的に処理
        if (is_array($value)) {
            array_map(arary('InputFilters', 'check_encoding'), $value);
            return $value;
        }

        // 文字エンコーディング検証
        if (mb_check_encoding($value, Fuel::$encoding)) {
            return $value;
        } else {
            // エラーの場合はログに記録
            Log::error(
              'Invalid character encoding: ' . Input::uri() . ' ' .
              rawurlencode($value) > ' ' . 
              Input::ip() . ' "' . Input::user_agent() . '"'
            );
            // エラーを表示して終了
            throw new HttpInvalidInputException('Invalid input data');
        }
    }
}
