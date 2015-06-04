<?php
error_reporting(-1);
/**
 * restpage api
 *
 */
class Controller_V1_Restpage extends Controller_Rest
{
    // beforeメソッドでログイン認証チェック

    // privateにするとエラーになる
    protected $format = 'json';

    public function action_index()
    {
		$limit = Input::get('limit');

		if (empty($limit)) {
	    	$limit = 30;
		}

        $restname = Input::get('restname');
		if (!empty($restname)) {
	    	$query = DB::query('SELECT * FROM users');
	    	//$result = $query->execute()->as_array();
	    	$result = Model_Tag::find_all();
            /* 今はサンプルとしてやっているが、本来は
	       　$result = Model_Restpage::find_all();
	       　のようにしてModelから取得してくるようにする
	    	*/

	    	$rows = array("header" => $result);
	    	$this->response($rows);
	    	//echo json_encode( array( 1=>array( 'a' => '6' ),  'a'=>'x'  ) );
	    	//ob_get_clean();
		}
    }
}
