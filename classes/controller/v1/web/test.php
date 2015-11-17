<?php
class Controller_V1_Web_Test extends Controller_V1_Web_Base
{
    public function action_index()
    {
	echo Util\Hoge::getFoo();
    }

}
