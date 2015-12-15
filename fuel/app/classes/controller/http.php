<?php
class Controller_Http extends Controller
{
    public function action_404()
    {
	return Response::forge(View::forge('http/404'), 404);
    }
}
