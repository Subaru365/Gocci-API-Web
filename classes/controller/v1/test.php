<?php
class Controller_V1_Test extends Controller_V1_Base
{
    const REQUEST_URL      = 'https://api.twitter.com/oauth/access_token';
    const PROVIDER_TWITTER = 'api.twitter.com';
    // const API_KEY          = '3rrbNV3OXeBjKlZV3NRQRNS0k'; // 自前で用意したKEY
    // const API_SECRET       = 'LEblop9pEOemasvddlGuvMzpkKc6608TuIhTaxU4YtiCaE3VjE'; // 自前
    const API_KEY          = 'kurJalaArRFtwhnZCoMxB2kKU'; // コグニートに既に設定されていたKEY
    const API_SECRET       = 'oOCDmf29DyJyfxOPAaj8tSASzSPAHNepvbxcfVLkA9dJw7inYa'; // コグニートに既に設定されていたKEY

    public function action_login_test()
    {
        $data = [];
        $data = self::get_twitter_data();

        $access_token = $data;// [0]['access_token'];
        error_log('access_tokenは');
        error_log($access_token);

        // Auth API 叩く
        $base_url  = 'http://btest.api.gocci.me';
        $token     = $access_token;
        $end_point = "/v1/auth/login/?provider=".self::PROVIDER_TWITTER."&token=".$token;
        $call_num  = 5;

        $curl = curl_init(); // curlハンドル
        curl_setopt($curl, CURLOPT_URL, $base_url.$end_point);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $result   = json_decode($response, true);
        curl_close($curl);
    }

    public function action_sns_sign_up_test()
    {
        /* 処理フロー */

        // 1 access_tokenを取得

        // 2 フロントに返す

        // 3 認証ページにフロントが飛んでもらう[フロント]
        // https://api.twitter.com/oauth/authorize?oauth_token=

        // 4 token/imageをサーバ側で保持

        // 5 サーバ側でcallback_URLをusername入力してアカウント登録するのボタンのページをフロント側で出す。



        $data = self::get_twitter_data();
        print_R($data);
        /*
        $access_token = $data[0]['access_token'];
        $image = $data[0]['image'];

        $data = [
            "access_token" => $access_token,
            "image"        => $image
        ];
        $base_data = self::base_template($api_code = "SUCCESS",
                  $api_message = "Successful API request",
                  $login_flag =  1, $data, $jwt = ""
        );
        echo self::output_json($base_data);
        */
    }

    private static function get_twitter_data()
    {
        $api_key    = self::API_KEY;
        $api_secret = self::API_SECRET;

        // Callback URL
        $callback_url = ( !isset($_SERVER['HTTPS']) ||
        empty($_SERVER['HTTPS']) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ;

        // 「連携アプリを認証」をクリックして帰ってきた時
        if(isset( $_GET['oauth_token'] ) && !empty( $_GET['oauth_token'] ) ) {
            // [リクエストトークン・シークレット]をセッションから呼び出す
            session_start() ;
            @$request_token_secret = $_SESSION['oauth_token_secret'] ;
            // リクエストURL
            $request_url = self::REQUEST_URL;
            // リクエストメソッド
            $request_method = 'POST' ;
            // キーを作成する
            $signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $request_token_secret ) ;
            // パラメータ([oauth_signature]を除く)を連想配列で指定
            $params = [
              'oauth_consumer_key'     => $api_key ,
              'oauth_token'            => $_GET['oauth_token'] ,
              'oauth_signature_method' => 'HMAC-SHA1' ,
              'oauth_timestamp'        => time() ,
              'oauth_verifier'         => $_GET['oauth_verifier'] ,
              'oauth_nonce'            => microtime() ,
              'oauth_version'          => '1.0' ,
            ] ;
            // 配列の各パラメータの値をURLエンコード
            foreach( $params as $key => $value )
            {
                $params[ $key ] = rawurlencode( $value ) ;
            }
            // 連想配列をアルファベット順に並び替え
            ksort($params) ;
            // パラメータの連想配列を[キー=値&キー=値...]の文字列に変換
            $request_params = http_build_query( $params , '' , '&' ) ;
            // 変換した文字列をURLエンコードする
            $request_params = rawurlencode($request_params) ;
            // リクエストメソッドをURLエンコードする
            $encoded_request_method = rawurlencode( $request_method ) ;
            // リクエストURLをURLエンコードする
            $encoded_request_url = rawurlencode( $request_url ) ;
            // リクエストメソッド、リクエストURL、パラメータを & で繋ぐ
            $signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;
            // キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
            $hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;
            // base64エンコードして、署名[$signature]が完成する
            $signature = base64_encode( $hash ) ;
            // パラメータの連想配列、[$params]に、作成した署名を加える
            $params['oauth_signature'] = $signature ;
            // パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
            $header_params = http_build_query( $params , '' , ',' ) ;
            // リクエスト用のコンテキストを作成する
            $context = array(
              'http' => array(
                'method' => $request_method , //リクエストメソッド
                'header' => array(        //カスタムヘッダー
                  'Authorization: OAuth ' . $header_params ,
                ) ,
              ) ,
            ) ;
            // cURLを使ってリクエスト
            $curl = curl_init() ;
            curl_setopt( $curl , CURLOPT_URL , $request_url ) ;
            curl_setopt( $curl , CURLOPT_HEADER, 1 ) ;
            curl_setopt( $curl , CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ; // メソッド
            curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false ) ;                     // 証明書の検証を行わない
            curl_setopt( $curl , CURLOPT_RETURNTRANSFER , true ) ;                      // curl_execの結果を文字列で返す
            curl_setopt( $curl , CURLOPT_HTTPHEADER , $context['http']['header'] ) ;    // ヘッダー
            curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;                                // タイムアウトの秒数
            $res1 = curl_exec( $curl ) ;
            $res2 = curl_getinfo( $curl ) ;
            curl_close( $curl ) ;
            // 取得したデータ
            $response = substr( $res1, $res2['header_size'] ) ;       // 取得したデータ(JSONなど)
            $header   = substr( $res1, 0, $res2['header_size'] ) ;    // レスポンスヘッダー (検証に利用したい場合にどうぞ)
            // リクエストが成功しなかった場合
            if( !isset( $response ) || empty( $response ) ) {
                $error = 'リクエストが失敗してしまったようです。Twitterからの応答自体がありません。' ;
                $data = [
                    "error_msg" => $error,
                ];
                $base_data = self::base_template($api_code = "SUCCESS",
                          $api_message = "Successful API request",
                          $login_flag =  1, $data, $jwt = ""
                );
                echo self::output_json($base_data);
            } else {
                // 文字列を[&]で区切る
                $parameters = explode( '&' , $response ) ;
                // エラー判定
                if( !isset( $parameters[1] ) || empty( $parameters[1] ) ) {
                    $error_msg = true ;
                } else {
                    // それぞれの値を格納する配列
                    $query = array() ;
                    // [$parameters]をループ処理
                    foreach( $parameters as $parameter )
                    {
                        // 文字列を[=]で区切る
                        $pair = explode( '=' , $parameter ) ;
                        // 配列に格納する
                        if( isset($pair[1]) ) {
                            $query[ $pair[0] ] = $pair[1] ;
                        }
                    }
                    if( !isset( $query['oauth_token'] ) || !isset( $query['oauth_token_secret'] ) ) {
                        $error_msg = true ;
                    } else {
                        // 各データの整理
                        $access_token = $query['oauth_token'] ;   // アクセストークン

                        $access_token_secret = $query['oauth_token_secret'] ;   // アクセストークン・シークレット
                        $user_id = $query['user_id'] ;    // ユーザーID
                        $screen_name = $query['screen_name'] ;    // スクリーンネーム
                        $image = "http://www.paper-glasses.com/api/twipi/" . $screen_name;
                    }
                }
                // エラーの場合
                if( isset( $error_msg ) && !empty( $error_msg ) ) {
                      $error = '' ;
                      $error .= 'アクセストークンを取得できませんでした。セッションが上手く働いていない可能性があります。' ;
                      $data = [
                          "error_msg" => $error
                      ];
                      $base_data = self::base_template($api_code = "SUCCESS", 
                                $api_message = "Successful API request", 
                                $login_flag =  1, $data, $jwt = ""
                      );
                      echo self::output_json($base_data);
                }
            }
            // セッション終了
            $_SESSION = array() ;
            session_destroy() ;
        } else if( isset( $_GET['denied'] ) && !empty( $_GET['denied'] ) ) {
            // 「キャンセル」をクリックして帰ってきた時
            // エラーメッセージを出力して終了
            die('You have rejected the app...Bye...');
            exit ;
        } else {
            /* apiをGET Requestした時にrequestTokenを取得する */
            $oauth_token = self::getRequestToken();
            return $oauth_token;
            // echo 'https://api.twitter.com/oauth/authorize?oauth_token=';
            // print_r($oauth_token);
            // exit;
        }
        if( isset( $error ) && $error ) {
            die($error);
        }
        if ( empty($data)) {
            $data = [];
        } else {
            $access_token = $data['oauth_token'];
        }
        $token = $access_token . ";" . $query['oauth_token_secret'];
        $data[0]['access_token']        = $access_token;
        $data[0]['access_token_secret'] = $query['oauth_token_secret'];
        $data[0]['token']               = $token;
        $data[0]['image']               = $image;
        return $data;
    }

    // Request Token
    public static function getRequestToken()
    {
        $api_key    = '3rrbNV3OXeBjKlZV3NRQRNS0k' ;                         // APIキー
        $api_secret = 'LEblop9pEOemasvddlGuvMzpkKc6608TuIhTaxU4YtiCaE3VjE'; // APIシークレット
        $callback_url = ( !isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ;
        // リクエストトークンの取得
        $access_token_secret = '' ;
        // エンドポイントURL
        $request_url = 'https://api.twitter.com/oauth/request_token' ;
        // リクエストメソッド
        $request_method = 'POST' ;
        // キーを作成する (URLエンコードする)
        $signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;
        // パラメータ([oauth_signature]を除く)を連想配列で指定
        $params = array(
          'oauth_callback' => $callback_url ,
          'oauth_consumer_key' => $api_key ,
          'oauth_signature_method' => 'HMAC-SHA1' ,
          'oauth_timestamp' => time() ,
          'oauth_nonce' => microtime() ,
          'oauth_version' => '1.0' ,
        ) ;
        // 各パラメータをURLエンコードする
        foreach( $params as $key => $value )
        {
            // コールバックURLだけはここでエンコードしNG
            if( $key == 'oauth_callback' ) {
                continue ;
            }
            // URLエンコード処理
            $params[ $key ] = rawurlencode( $value ) ;
        }
        // 連想配列をアルファベット順に並び替える
        ksort( $params ) ;
        // パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
        $request_params = http_build_query( $params , '' , '&' ) ;
        // 変換した文字列をURLエンコードする
        $request_params = rawurlencode( $request_params ) ;
        // リクエストメソッドをURLエンコードする
        $encoded_request_method = rawurlencode( $request_method ) ;
        // リクエストURLをURLエンコードする
        $encoded_request_url = rawurlencode( $request_url ) ;
        // リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
        $signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;
        // キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
        $hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;
        // base64エンコードして、署名[$signature]が完成する
        $signature = base64_encode( $hash ) ;
        // パラメータの連想配列、[$params]に、作成した署名を加える
        $params['oauth_signature'] = $signature ;
        // パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
        $header_params = http_build_query( $params , '' , ',' ) ;
        // リクエスト用のコンテキストを作成する
        $context = array(
          'http' => array(
            'method' => $request_method , //リクエストメソッド
            'header' => array(        //カスタムヘッダー
              'Authorization: OAuth ' . $header_params ,
            ) ,
          ) ,
        ) ;
        // cURLを使ってリクエスト
        $curl = curl_init() ;
        curl_setopt( $curl , CURLOPT_URL , $request_url ) ;
        curl_setopt( $curl , CURLOPT_HEADER, 1 ) ; 
        curl_setopt( $curl , CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;     // メソッド
        curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false ) ;               // 証明書の検証を行わない
        curl_setopt( $curl , CURLOPT_RETURNTRANSFER , true ) ;                // curl_execの結果を文字列で返す
        curl_setopt( $curl , CURLOPT_HTTPHEADER , $context['http']['header'] ) ;      // ヘッダー
        curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;                    // タイムアウトの秒数
        $res1 = curl_exec( $curl ) ;
        $res2 = curl_getinfo( $curl ) ;
        curl_close( $curl ) ;

        // 取得したデータ
        $response = substr( $res1, $res2['header_size'] ) ;       // 取得したデータ(JSONなど)
        $header = substr( $res1, 0, $res2['header_size'] ) ;    // レスポンスヘッダー (検証に利用したい場合にどうぞ)

        // リクエストが成功しなかった場合
        if( !isset( $response ) || empty( $response ) ) {
            $error = 'リクエストが失敗してしまったようです。Twitterからの応答自体がありません…。' ;
        } else {
            // 成功した場合
            // 文字列を[&]で区切る
            $parameters = explode( '&' , $response ) ;
            if( !isset( $parameters[1] ) || empty( $parameters[1] ) ) {
                $error_msg = true ;
            } else {
                // それぞれの値を格納する配列
                $query = array() ;
                // [$parameters]をループ処理
                foreach( $parameters as $parameter )
                {
                    // 文字列を[=]で区切る
                    $pair = explode( '=' , $parameter ) ;

                    // 配列に格納する
                    if( isset($pair[1]) ) {
                      $query[ $pair[0] ] = $pair[1] ;
                    }
                }
                // エラー判定
                if( !isset( $query['oauth_token'] ) || !isset( $query['oauth_token_secret'] ) ) {
                    $error_msg = true ;
                } else {
                    return $query['oauth_token'] .";" . $query['oauth_token_secret'];
                    exit;
                }
        }
        // エラーの場合
        if( isset( $error_msg ) && !empty( $error_msg ) ) {
            $error = '' ;
            $error .= 'リクエストトークンを取得できませんでした…。[$api_key]と[$callback_url]、そしてTwitterのアプリケーションに設定している[Callback URL]を確認して下さい。' ;
            $error .= '([Callback URLに設定されているURL]→<mark>' . $callback_url . '</mark>)' ;
            error_log($error);
        }
    }
    // エラーメッセージがある場合
    if( isset( $error ) && $error ) {
        error_log($error);
        exit;
    }
    return $query['oauth_token'];
    }
}