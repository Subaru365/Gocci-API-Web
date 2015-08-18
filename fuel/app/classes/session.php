<?php
class Session extends \Fuel\Core\Session
{
    public static function _init()
    {
        //\Config::load('session', true);
        \Config::load('session', true, false, true);

        if (\Config::get('session.auto_initialize', true))
        {
            static::instance();
        }
    }
}