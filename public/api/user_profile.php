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

            if( !@$post->user_profile_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $profile = Model::get($commercial,(Object)[
                "class" => "UserProfile",
                "tables" => [ "user_profile" ],
                "filters" => [[ "user_profile_id", "i", "=", $post->user_profile_id ]]
            ]);

            if( !@$profile || (!@$profile->client_id && @$site) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Perfil não encontrado."
                ]);
            }

            Json::get( $headerStatus[200], $profile );

            break;

		case "edit":

		    if( !@$post->user_profile_id || !@$post->user_profile_name ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

		    Model::update($commercial,(Object)[
                "table" => "user_profile",
		        "fields" => [[ "user_profile_name", "s", $post->user_profile_name ]],
                "filters" => [[ "user_profile_id", "i", "=", $post->user_profile_id ]]
            ]);

		    Model::delete($commercial,(Object)[
		        "table" => "user_profile_access",
                "filters" => [[ "user_profile_id", "i", "=", $post->user_profile_id ]]
            ]);

		    UserProfileAccess::insert($post->user_profile_id);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Perfil de usuário atualizado com sucesso."
            ]);

		break;

        case "access":

            Json::get( $headerStatus[200], json_decode( file_get_contents( PATH_DATA . "access.json" )) );

        break;

        case "insert":

            if( !@$post->user_profile_name ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $user_profile_id = Model::insert($commercial,(Object)[
                "table" => "user_profile",
                "fields" => [[ "user_profile_name", "s", $post->user_profile_name ]]
            ]);

            UserProfileAccess::insert($user_profile_id);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Perfil de usuário cadastrado com sucesso."
            ]);

        break;

		case "getList":

            $profiles = Model::getList($commercial,(Object)[
                "tables" => ["UserProfile"],
                "fields" => [
                    "user_profile_id",
                    "user_profile_name",
                    "user_profile_date",
                    "user_profile_date_br=user_profile_date",
                ]
            ]);

            Json::get( $headerStatus[200], $profiles );

		break;

	}

?>