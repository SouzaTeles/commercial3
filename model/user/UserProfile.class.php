<?php
	
	class UserProfile
	{
		public $user_profile_id;
		public $user_profile_name;
		public $user_profile_update;
		public $user_profile_date;
		
		public function __construct( $data, $gets=NULL )
		{
			$this->user_profile_id = (int)$data->user_profile_id;
			$this->client_id = @$data->client_id ? $data->client_id : NULL;
			$this->user_profile_name = $data->user_profile_name;
			$this->user_profile_update = @$data->user_profile_update ? $data->user_profile_update : NULL;
			$this->user_profile_date = $data->user_profile_date;

            GLOBAL $commercial;

            if( @$gets["get_user_profile_client"] || @$_POST["get_user_profile_client"] && @$data->client_id ){
                $this->user_profile_client = Model::get( $commercial, (Object)[
                    "class" => "Client",
                    "tables" => [ "client" ],
                    "filters" => [
                        [ "client_id", "i", "=", $data->client_id ]
                    ],
                    "gets" => $gets
                ]);
            }

			if( @$gets["get_user_profile_access"] || @$_POST["get_user_profile_access"] ){
				$this->user_profile_access = UserProfileAccess::treeAccess( Model::getList( $commercial, (Object)[
				    "class" => "UserProfileAccess",
				    "tables" => [ "UserProfileAccess" ],
                    "fields" => [
                        "user_profile_access_id",
                        "user_profile_id",
                        "user_profile_access_module",
                        "user_profile_access_name",
                        "user_profile_access_value",
                        "user_profile_access_data_type",
                        "user_profile_access_date=FORMAT(user_profile_access_date,'yyyy-MM-dd HH:mm:ss')"
                    ],
					"filters" => [[ "user_profile_id", "i", "=", $data->user_profile_id ]]
				]));
			}
		}
	}

?>