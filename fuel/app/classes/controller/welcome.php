<?php
class Controller_Welcome extends Controller
{
	public function action_index()
	{
		return Response::forge(View::forge('welcome/index'));
	}

	public function action_hello()
	{
		return Response::forge(Presenter::forge('welcome/hello'));
	}

	public function action_404()
	{
		// return Response::forge(Presenter::forge('welcome/404'), 404);
		return Response::forge(View::forge('http/404'), 404);
	}
}
