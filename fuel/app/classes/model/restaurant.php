<?php
class Model_Restaurant extends Model
{

	public static function get_data($rest_id)
	{
		$query = DB::select(
			'rest_id', 'restname', 'locality', 'lat',
			'lon', 'tell', 'homepage', 'rest_category'
		)->from('restaurants');

		$query->where('rest_id', "$rest_id");

		//配列[comments]に格納
		$rest_data = $query->execute()->as_array();

		//--debug--//
		//echo "$rest_data";

		return $rest_data;
	}


	//店舗追加
	public static function post_add($rest_name, $lat, $lon)
	{
		$query = DB::insert('wants')
			->set(array(
				'restname' => "$rest_name",
				'lat' 	   => "$lat",
				'lon' 	   => "lon"
			));

		$result = $query->execute();

		return $result;
	}
}