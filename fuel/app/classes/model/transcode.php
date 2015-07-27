<?php

class Model_Transcode extends Model
{
	public static function encode_profile_img($profile_img)
	{
        $profile_img = "$profile_img" . '.png';

        return $profile_img;
	}

	public static function decode_profile_img($profile_img)
    {
         $profile_img = 'http://test.imgs.gocci.me/' . "$profile_img";
        return $profile_img;
    }
}