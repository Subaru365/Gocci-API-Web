<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
error_reporting(-1);

class Controller_V1_Web_Get extends Controller_V1_Web_Base
{
	public function action_timeline()
    {
    	$user_id  = session::get('user_id');
    	$username = session::get('username');

	    $sort_key = 'all';
	    $limit    = 10;

	    $data     = Model_Post::get_data($user_id, $sort_key, $sort_key,$limit);
	   	$status   = $this->output_json($data);	   	
    }

    // Timeline loading
    public function action_timeline_loading()
    {
        $sort_key = 'next';
        $user_id  = session::get('user_id');

        $page_num = Input::get('page');
        $data     = Model_Post::get_data($user_id, $sort_key, $page_num);
        
        $status   = $this->output_json($data);

    }

    // Popular Page
    public function action_popular()
    {
        $sort_key = 'post';
        $user_id  = session::get('user_id');
        $post_id  = Model_Gochi::get_rank();
        
        $num      = count($post_id);

        for ($i=0;$i<$num;$i++) {
            $tmp[$i] = Model_Post::get_data(
                $user_id, $sort_key, $post_id[$i]['post_id']);

            $data[$i] =  $tmp[$i][0];
        }

        $status = $this->output_json($data);
    }

    // Popular loading
    public function action_popular_loading()
    {
        $sort_key = 'post';
        $page_num = Input::get('page');

        $user_id  = session::get('user_id');
        $post_id  = Model_Gochi::get_rank($page_num);

        $num      = count($post_id);

        for ($i=0;$i<$num;$i++) {
            $tmp[$i]  = Model_Post::get_data(
                $user_id, $sort_key, $post_id[$i]['post_id']
            );
            $data[$i] = $tmp[$i][0];
        }
        
        if ($num === 0) {
            $data = array();
        }

        $status   = $this->output_json($data);
    } 

    // Comment Page
    public function action_comment()
    {

    }

    // Restaurant Page
    public function action_rest()
    {

    }

    // User Page
    public function action_user()
    {

    }

    // Notice Page
    public function action_notice()
    {

    }

    // Near

    // Follow


    // Follower List

    // 

    // 



}