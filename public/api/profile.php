<?php
 
	include "../../config/start.php";

    GLOBAL $commercial, $site, $login, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro POST não informado."
        ]);
    }

	switch( $get->action )
	{

        case "del":

            if( !@$post->user_profile_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $check = Model::get($commercial,(Object)[
                "tables" => [ "user" ],
                "fields" => [ "COUNT(user_id) AS quantity" ],
                "filters" => [[ "user_profile_id", "i", "=", $post->user_profile_id ]]
            ]);

            if( @$check && $check->quantity ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não será possível excluir o perfil de usuário.<br/>O perfil está vinculado à {$check->quantity} usuário(s)."
                ]);
            }

            Model::delete($commercial,(Object)[
                "table" => "user_profile",
                "filters" => [[ "user_profile_id", "i", "=", $post->user_profile_id ]]
            ]);

            Model::delete($commercial,(Object)[
                "table" => "user_profile_access",
                "filters" => [[ "user_profile_id", "i", "=", $post->user_profile_id ]]
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Perfil de usuário removido com sucesso."
            ]);

        break;

	    case "get":

            if( !@$post->profile_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $profile = Model::get($commercial,(Object)[
                "class" => "UserProfile",
                "tables" => [ "[UserProfile]" ],
                "fields" => [
                    "user_profile_id",
                    "user_profile_name",
                    "user_profile_update",
                    "user_profile_date",
                ],
                "filters" => [[ "user_profile_id", "i", "=", $post->profile_id ]]
            ]);

            if( !@$profile ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Perfil não encontrado."
                ]);
            }

            Json::get( $headerStatus[200], $profile );

        break;

		case "edit":

            if( $login->access->profile->edit->value == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Você não possuí acesso para realizar essa operação."
                ]);
            }

		    if( !@$post->profile_id || !@$post->profile_name ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $date = date("Y-m-d H:i:s");
		    Model::update($commercial,(Object)[
                "table" => "[UserProfile]",
		        "fields" => [
                    [ "user_profile_date", "s", $date ],
                    [ "user_profile_name", "s", $post->profile_name ]
                ],
                "filters" => [[ "user_profile_id", "i", "=", $post->profile_id ]]
            ]);

		    Model::delete($commercial,(Object)[
		        "top" => "100",
		        "table" => "[UserProfileAccess]",
                "filters" => [[ "user_profile_id", "i", "=", $post->profile_id ]]
            ]);

            foreach( $post->access as $module => $access ){
                foreach( $access as $name => $data ){
                    if( is_array($data) ){
                        $data = (Object)$data;
                        if( !@$data->data_type ) var_dump($module);
                        if( $data->data_type != "bool" || ( $data->data_type == "bool" && $data->value == "Y" )) {
                            Model::insert( $commercial, (Object)[
                                "table" => "[UserProfileAccess]",
                                "fields" => [
                                    ["user_profile_id", "i", $post->profile_id],
                                    ["user_profile_access_module", "s", $module],
                                    ["user_profile_access_name", "s", $name],
                                    ["user_profile_access_value", "s", $data->value],
                                    ["user_profile_access_data_type", "s", $data->data_type],
                                    ["user_profile_access_date", "s", $date],
                                ]
                            ]);
                        }
                    }
                }
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "Perfil de usuário atualizado com sucesso."
            ]);

		break;

        case "insert":

            if( $login->access->profile->add->value == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Você não possuí acesso para realizar essa operação."
                ]);
            }

            if( !@$post->profile_name ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $date = date("Y-m-d");
            $profile_id = (int)Model::insert( $commercial, (Object)[
                "table" => "[UserProfile]",
                "fields" => [
                    [ "user_profile_date", "s", $date ],
                    [ "user_profile_name", "s", $post->profile_name ]
                ]
            ]);

            foreach( $post->access as $module => $access ){
                foreach( $access as $name => $data ){
                    if( is_array($data) ){
                        $data = (Object)$data;
                        if( $data->data_type != "bool" || ( $data->data_type == "bool" && $data->value == "Y" )) {
                            Model::insert( $commercial, (Object)[
                                "table" => "[UserProfileAccess]",
                                "fields" => [
                                    ["user_profile_id", "i", $profile_id],
                                    ["user_profile_access_module", "s", $module],
                                    ["user_profile_access_name", "s", $name],
                                    ["user_profile_access_value", "s", $data->value],
                                    ["user_profile_access_data_type", "s", $data->data_type],
                                    ["user_profile_access_date", "s", $date],
                                ]
                            ]);
                        }
                    }
                }
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "Perfil de usuário cadastrado com sucesso."
            ]);

        break;

		case "getList":

            $profiles = Model::getList($commercial,(Object)[
                "tables" => ["[UserProfile] UP"],
                "fields" => [
                    "UP.user_profile_id",
                    "UP.user_profile_name",
                    "user_profile_date=FORMAT(UP.user_profile_date,'yyyy-MM-dd HH:mm:ss')",
                    "user_profile_date_br=UP.user_profile_date",
                    "users=(SELECT COUNT(*) FROM [User] U WHERE U.user_profile_id = UP.user_profile_id)"
                ]
            ]);

            Json::get( $headerStatus[200], $profiles );

		break;

	}

?>