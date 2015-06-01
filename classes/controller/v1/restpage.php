<?php


error_reporting(-1);
ini_set('default_charset', 'UTF-8');
//header('Content-Type: text/html; charset=UTF-8');
header('Content-Type: application/json; charset=UTF-8');
//htmlentities($var, ENT_COMPAT, 'UTF-8');
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
		//echo 'あいうえお';
//exit();
		// 以下の方法で書いてprint_rすると文字化けしない

		$result = DB::query('SELECT * FROM users LIMIT 30')->execute();

//echo "{ header: ";	
	//	echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
echo $this->response($result);
//echo	        $result = $this->response($result);
//echo json_encode($result);
//	echo '}';
//exit();
//	print_r($result);
/*
 $result = Model_Restaurant::find(array(
                'where' => array(
                array('restname', '=', $restname),
                ),
            ));
*/
//echo $this->reponse($result);
//echo $json = json_xencode($result);
exit();
		// 以下でechoすると{}になる
		//echo $json = json_encode($result);
		
		// 以下でやるとバックスラッシュがはいる(これをUTF8で出力したい)
	  //      return $this->response($result);
		//print_r($result);
	    //    exit();
	   // $result = DB::query("SELECT id FROM restaurants WHERE restname = $restname")->execute;
	   // $this->response($result);
/*	
	    $result = Model_Restaurant::find(array(
		'where' => array(
		array('restname', '=', $restname),
		),
	    ));
*/	
	   
//	    $json = mb_convert_encoding($result, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
//	echo $this->response($json, 200);
	   // echo 'あいうえお';
	//echo	$this->response($result, 200);

//echo $json = json_encode($result , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );
//	exit();

	    // print_r($result);
	
	   // $result = DB::query("SELECT rest_id FROM restaurants LIMIT 1")->find_by_pk();# WHERE restname = $restpage")->as_array();    
            //print_r($result);

	    //$query = DB::query('SELECT * FROM users');
	    //$result = $query->execute()->as_array();
	    //$result = Model_Tag::find_all();	
            /* 今はサンプルとしてやっているが、本来は
	       $result = Model_Restpage::find_all();
	       のようにしてModelから取得してくるようにする
	    */	

	    //$rows = array("header" => $result);
	    //$this->response($rows);
	    //echo json_encode( array( 1=>array( 'a' => '6' ),  'a'=>'x'  ) );
	    //ob_get_clean();
	}
    }
}
