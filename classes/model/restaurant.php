<?php
class Model_Restaurant extends Model
{
	public static function get_near($lon, $lat)
	{
		$query = DB::select('restname')->from('restaurants')
		->order_by(DB::expr('GLength(GeomFromText(CONCAT(' . "'" . 'LineString(' .
			"$lon" . ' ' . "$lat" . ",'" . ', X(lon_lat), ' . "' '," . ' Y(lon_lat),' . "')'" . ')))'))
		->limit(10);

		$near_data = $query->execute()->as_array();
		return $near_data;
	}


	public static function get_data($rest_id)
	{
		$query = DB::select(
			'rest_id', 'restname', 'locality', 'lat',
			'lon', 'tell', 'homepage', 'rest_category'
		)
		->from('restaurants')
		->where('rest_id', "$rest_id");

		$rest_data = $query->execute()->as_array();
		return $rest_data;
	}


	//店舗追加
	public static function post_add($rest_name, $lat, $lon)
	{
		$query = DB::insert('restaurants')
		->set(array(
			'restname' => "$rest_name",
			'lat' 	   => "$lat",
			'lon' 	   => "$lon"
		));

		$result = $query->execute();
		return $result;
	}
}