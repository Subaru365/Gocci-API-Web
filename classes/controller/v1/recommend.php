<?php
/**
 * recommend
 * @package    Gocci-Web
 * @version    3.0 <2016/1/13>
 * @author     bitbuket ta_kazu Kazunori Tani <k-tani@inase-inc.jp>
 * @license    MIT License
 * @copyright  2014-2015 Inase,inc.
 * @link       https://bitbucket.org/inase/gocci-web-api
 */

/**
 * 協調フィルタリングインターフェース
 */
interface CollaborativeFiltering {
  public function action_cfr();
}

/**
 * コンテンツベースインターフェース
 */
interface ContentBasedFiltering {
  public function action_cbfr();
}

class Controller_V1_Recommend extends Controller_V1_Base implements CollaborativeFiltering, ContentBasedFiltering {

  const DEFAULT_TARGET_POINT = "東京都渋谷区道玄坂２丁目１８";
  const LAT = "35.658844";
  const LON = "139.696193";

  private $currentPosition;
  private $count;
  private $loginUserId;
  private $categoryIdList = [];
  private $array = [];

  // 各IDの値を0で初期化
  private $categoryNumId_1  = 0;
  private $categoryNumId_2  = 0;
  private $categoryNumId_3  = 0;
  private $categoryNumId_4  = 0;
  private $categoryNumId_5  = 0;
  private $categoryNumId_6  = 0;
  private $categoryNumId_7  = 0;
  private $categoryNumId_8  = 0;
  private $categoryNumId_9  = 0;
  private $categoryNumId_10 = 0;

  private $option = [];

  public function SetCurrentPosition($currentPosition)
  {
    $this->$currentPosition = $currentPosition;
  }

  public function getCurrentPosition()
  {
    return $this->currentPosition;
  }

  public function getArray()
  {
    return $this->array;
  }

  public function getOption()
  {
    $this->option = [
            'call'        => Input::get('call', 0),
            'order_id'    => Input::get('order_id', 0),
            'category_id' => Input::get('category_id', 0),
            'value_id'    => Input::get('value_id', 0),
            'lon'         => Input::get('lon', 0),
            'lat'         => Input::get('lat', 0)
    ];

    return $this->option;
  }

  public function setArray($array)
  {
    $this->array = $array;
  }

  /**
   * @param  Array $categoryidList
   * @param  Int   $user_id
   * @return Array $data
   */
  private function getRecommendRest($categoryIdList, $user_id)
  {
    $limit  = 18;
    $option = $this->getOption();
    $data  = Model_Post::get_recommend_posts($categoryIdList, $user_id, $sort_key = 'all', 0, $option, $limit);

    for ($i = 0; $i<$limit; $i++) {
        $post_id      = $data[$i]['post_id'];
        $post_user_id = $data[$i]['user_id'];
        $Comment_data = Model_Comment::get_data($post_id);
        $hash_id      = Hash_Id::video_hash($post_id);
        $user_hash_id = Hash_Id::create_user_hash($post_user_id);
        $data[$i]['hash_id']  = $hash_id;
        $data[$i]['user_hash_id'] = $user_hash_id;
        $data[$i] = [
            "post"     => $data[$i],
            "comments" => $Comment_data
        ];
    }

    return $data;
  }

  /**
   * 配列を昇順にソートします
   * @param  Array $ary
   * @return Array $arrayList
   */
  private function ArraySort($ary)
  {
    $arrayList = [];
    rsort($ary);

    $i = count($ary);

    foreach ($ary as $key => $val)
    {
      array_push($arrayList, $val);
    }
    return $arrayList;
  }

  /**
   * バブルソート
   * @param  Array $array
   * @return Array $array
   */
  private function bubble_sort($array)
  {
    // 要素数回繰り返し
    for ($i=0;$i<count($array); $i++) {
      // 要素数-1回繰り返し
      for ($n = 1; $n < count($array); $n++) {
        // 隣接要素を比較し大小が逆なら入れ替える
        if ($array[$n-1] > $array[$n]) {
          $temp = $array[$n];
          $array[$n] = $array[$n-1];
          $array[$n-1] = $temp;
        }
      }
    }
    return $array;
  }

  /**
   * $aryCountData
   * @param  Array $sortAry
   * @return Array $aryCountData
   */
  private function getAryCountData($sortAry)
  {
    // 配列を0番目から最後の要素まで調べる
    $loopnum = count($sortAry);
    for ($i=0;$i<$loopnum;$i++) { # for start
      switch ($sortAry[$i]) {
        case 1:
          $this->categoryNumId_1++;
          break;
        case 2:
          $this->categoryNumId_2++;
          break;
        case 3:
          $this->categoryNumId_3++;
          break;
        case 4:
          $this->categoryNumId_4++;
          break;
        case 5:
          $this->categoryNumId_5++;
          break;
        case 6:
          $this->categoryNumId_6++;
          break;
        case 7:
          $this->categoryNumId_7++;
          break;
        case 8:
          $this->categoryNumId_8++;
          break;
        case 9:
          $this->categoryNumId_9++;
          break;
        case 10:
          $this->categoryNumId_10++;
          break;
        default:
          break;
      }
    } # for end

    $aryCountData = [
        "none",                 // categoryID0は存在しない
        $this->categoryNumId_1, // categoryID1
        $this->categoryNumId_2, // categoryID2
        $this->categoryNumId_3, // categoryID3
        $this->categoryNumId_4, // categoryID4
        $this->categoryNumId_5, // categoryID5
        $this->categoryNumId_6, // categoryID6
        $this->categoryNumId_7, // categoryID7
        $this->categoryNumId_8, // categoryID8
        $this->categoryNumId_9, // categoryID9
        $this->categoryNumId_10 // categoryID10
    ];
    return $aryCountData;
  } # ArrayListIdCount end

  /**
   * 1-10のcategory_idの中で、上位3つのカテゴリに絞る
   * @param  Array $sortAry
   * @return Array $aryCountData
   */
  private function getSelectId($aryCountData)
  {
    $selectId = [];

    for ($i = 1; $i<=3; $i++) {
      $value =  max($aryCountData);
      $key = array_search($value, $aryCountData);
      $selectId[] = $key;
      array_splice($aryCountData,$key,1,0);
    }
    return $selectId;
  }

  /**
   * @param  Array $array
   * @return Array $categoryIdList
   */
  private function begineRecommendEngine($cid)
  {
      foreach ($cid as $key => $value) {
        $this->array[] = $value['post_category_id'];
      }
      $sortAry        = $this->ArraySort($this->array);
      $aryCountData   = $this->getAryCountData($sortAry);
      $selectIdList   = $this->getSelectId($aryCountData);
      $categoryIdList = $this->bubble_sort($selectIdList);

      return $categoryIdList;
  }

  /**
   * @param Array $categoryIdList
   */
  private function checkRecommendExists($categoryIdList)
  {
    if (empty($categoryIdList)) {
      echo 'あなたのオススメは見つかりませんでした。';
      exit;
    }
  }

  // ユーザーの現在値から周辺のお店をレコメンドする
  private function sim_distance()
  {
    // call python script
  }

  //
  public function action_cfr()
  {
    // call python script
  }

  public function action_cbfr()
  {
    // call python script
  }

  /**
   *
   *
   */
  public function action_rest()
  {
    // Controller_V1_Post::create_token($uri=Uri::string(), $login_flag=1);
    $jwt = self::get_jwt();

    @$user_id = session::get('user_id');
    if (empty($user_id)) {
      $user_id = 799; // test
    }

    try {
    $categoryIdList = Model_Post::get_category_id($user_id);
      $this->checkRecommendExists($categoryIdList);
      $categoryIdList = $this->begineRecommendEngine($categoryIdList);
      $data = $this->getRecommendRest($categoryIdList, $user_id);

      $base_data = self::base_template($api_code = "SUCCESS",
        $api_message = "Successful API request",
        $login_flag  = 1,
        $data, $jwt);
      self::debug_output_json($base_data);

    } catch (ErrorException $e) {
      error_log($e);
      exit;
    }
  } # end action_rest

} # class end