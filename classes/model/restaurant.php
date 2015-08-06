<?php
class Model_Restaurant extends Model
{
	public static function get_near($lon, $lat)
	{
		$query = DB::select('rest_id', 'restname')->from('restaurants')
		->order_by(DB::expr('GLength(GeomFromText(CONCAT(' . "'" . 'LineString(' .
			"$lon" . ' ' . "$lat" . ",'" . ', X(lon_lat), ' . "' '," . ' Y(lon_lat),' . "')'" . ')))'))
		->limit(30);

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
			'lon' 	   => "$lon",
			'lon_lat'  => DB::expr('GeomFromText(' . "'" .
				'POINT(' . "$lon" . ' ' . "$lat" .')' . "'" . ')')
		))
		->execute();

		$query = DB::select('rest_id')->from('restaurants')
        ->order_by('rest_id', 'desc')
        ->limit   ('1');

        $result = $query->execute()->as_array();
        $rest_id = $result[0]['rest_id'];

		return $rest_id;
	}
}