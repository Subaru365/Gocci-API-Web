<?php

class Controller_V1_Check extends Controller
{
    /**
     * e method
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
}
