<?php
class Presenter_Welcome_404 extends Presenter
{
		public function view()
		{
			$messages = array('Aw, crap!', 'Bloody Hell!', 'Uh Oh!', 'Nope, not here.', 'Huh?');
			$this->title = $messages[array_rand($messages)];
			//$this->title = $messages
		}
}
