<?php

	class UserSession
	{
		public $user_session_value;
		public $user_session_date;

		public function __construct( $data )
		{
			$this->user_session_value = $data->user_session_value;
			$this->user_session_date = $data->user_session_date;
		}
	}

?>