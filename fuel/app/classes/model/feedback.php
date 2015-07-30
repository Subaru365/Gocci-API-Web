<?php
class Model_Feedback extends Model
{
	public static function post_add($user_id, $feedback)
	{
		$query = DB::insert('feedbacks')
		->set(array(
			'feedback_user_id' => "$user_id",
			'feedback'         => "$feedback"
		))
		->execute();

		return $query;
	}
}
