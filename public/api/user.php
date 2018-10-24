<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $conn, $commercial, $dafel, $smarty, $login, $dimensions, $headerStatus, $get, $post;

    switch( $get->action ){

        case "access":

            if( !@$post->user_user || !@$post->user_pass ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $user = Model::get( $commercial, (Object)[
                "tables" => [ "[User]" ],
                "fields" => [
                    "user_id",
                    "user_name",
                    "user_active"
                ],
                "filters" => [
                    [ "user_user", "s", "=", $post->user_user ],
                    [ "user_pass", "s", "=", md5($post->user_pass) ]
                ]
            ]);

            if( !@$user ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Login e/ou senha incorretos."
                ]);
            }

            if( $user->user_active == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O usuário está inativo."
                ]);
            }

            $access = Model::getList( $commercial, (Object)[
                "tables" => [ "[UserAccess]" ],
                "fields" => [
                    "user_access_name",
                    "user_access_value",
                ],
                "filters" => [[ "user_id", "s", "=", $user->user_id ]]
            ]);

            $ret = [];
            foreach( $access as $key => $data ){
                $ret[$data->user_access_name] = $data->user_access_value;
            }

            Json::get( $headerStatus[200], (Object)$ret );

        break;

        case "get":

            if( !@$post->user_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $user = Model::get($commercial,(Object)[
                "class" => "User",
                "tables" => [ "[User]" ],
                "fields" => [
                    "user_id",
                    "external_id",
                    "person_id",
                    "user_profile_id",
                    "user_user",
                    "user_pass",
                    "user_name",
                    "user_email",
                    "user_active"
                ],
                "filters" => [[ "user_id", "i", "=", $post->user_id ]]
            ]);

            if( !@$user ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Usuário não encontrado."
                ]);
            }

            Json::get( $headerStatus[200], $user );

        break;

        case "edit":

            if( $login->access->user->edit->value == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Você não possuí acesso para realizar essa operação."
                ]);
            }

            if( !@$post->user_id || !@$post->user_profile_id || !@$post->user_name || !@$post->user_email ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "[User]",
                "fields" => [
                    [ "external_id", "s", $post->external_id ],
                    [ "person_id", "s", $post->person_id ],
                    [ "user_profile_id", "i", $post->user_profile_id ],
                    [ "user_name", "s", utf8_decode($post->user_name) ],
                    [ "user_email", "s", $post->user_email ],
                    [ "user_active", "s", $post->user_active ],
                    [ "user_update", "s", date("Y-m-d H:i:s") ]
                ],
                "filters" => [[ "user_id", "s", "=", $post->user_id ]]
            ]);

            foreach( $post->access as $access ){
                $access = (Object)$access;
                Model::update($commercial,(Object)[
                    "table" => "[UserAccess]",
                    "fields" => [
                        [ "user_access_value", "s", $access->value ],
                        [ "user_access_date", "s", date("Y-m-d H:i:s") ]
                    ],
                    "filters" => [
                        [ "user_id", "i", "=", $post->user_id ],
                        [ "user_access_name", "s", "=", $access->name ]
                    ]
                ]);
            }

            Model::delete($commercial,(Object)[
                "top" => "20",
                "table" => "[UserCompany]",
                "filters" => [[ "user_id", "i", "=", $post->user_id ]]
            ]);

            if( @$post->companies ){
                foreach( $post->companies as $company ){
                    $company = (Object)$company;
                    Model::insert($commercial,(Object)[
                        "table" => "[UserCompany]",
                        "fields" => [
                            [ "user_id", "s", $post->user_id ],
                            [ "company_id", "i", $company->company_id ],
                            [ "user_company_main", "s", $company->user_company_main ],
                            [ "user_company_date", "s", date("Y-m-d H:i:s") ]
                        ]
                    ]);
                }
            }

            Model::delete($commercial,(Object)[
                "top" => "20",
                "table" => "[UserPrice]",
                "filters" => [[ "user_id", "i", "=", $post->user_id ]]
            ]);

            if( @$post->prices ){
                foreach( $post->prices as $price ){
                    $price = (Object)$price;
                    Model::insert($commercial,(Object)[
                        "table" => "[UserPrice]",
                        "fields" => [
                            [ "user_id", "s", $post->user_id ],
                            [ "price_id", "s", $price->price_id ],
                            [ "user_price_date", "s", date("Y-m-d H:i:s") ]
                        ]
                    ]);
                }
            }

            postLog((Object)[
                "parent_id" => $post->user_id
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Usuário atualizado com sucesso."
            ]);

        break;

        case "pass":

            if( !@$post->pass || !@$post->new_pass ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $user = Model::get($commercial,(Object)[
                "tables" => [ "[User]" ],
                "fields" => [ "user_id" ],
                "filters" => [
                    [ "user_id", "i", "=", $login->user_id ],
                    [ "user_pass", "s", "=", md5($post->pass) ]
                ]
            ]);

            if( !@$user ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "A senha atual está incorreta. Verifique a senha informada."
                ]);
            }

            $user = Model::update($commercial,(Object)[
                "table" => "[User]",
                "fields" => [[ "user_pass", "s", md5($post->new_pass) ]],
                "filters" => [[ "user_id", "i", "=", $login->user_id ]]
            ]);

            postLog([]);

            Json::get($headerStatus[200],(Object)[
                "message" => "Senha atualizada com sucesso."
            ]);

        break;

        case "insert":

            if( $login->access->user->add->value == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Você não possuí acesso para realizar essa operação."
                ]);
            }

            if ( 
                !@$post->external_id || 
                !@$post->user_active || 
                !@$post->user_name || 
                !@$post->user_profile_id ||
                !@$post->user_email ||
                !@$post->user_user ||
                !@$post->user_pass ||
                !@$post->access
            ) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $user = Model::get($commercial,(Object)[
                "tables" => [ "[User]" ],
                "fields" => [ "user_user, user_email" ],
                "filters" => [[ "user_user", "s", "=", $post->user_user ]]
            ]);

            if ( @$user && $user->user_user == $post->user_user ) {
                headerResponse((Object)[
                    "code" => 409,
                    "message" => "O login já está cadastrado."
                ]);
            }

            $user_id = (int)Model::insert($commercial,(Object)[
                "table" => "[User]",
                "fields" => [
                    [ "external_id", "s", $post->external_id ],
                    [ "person_id", "s", @$post->person_id ? $post->person_id : NULL ],
                    [ "user_profile_id", "i", $post->user_profile_id ],
                    [ "user_active", "s", @$post->user_active ? "Y" : "N" ],
                    [ "user_user", "s", $post->user_user ],
                    [ "user_pass", "s", md5($post->user_pass) ],
                    [ "user_name", "s", $post->user_name ],
                    [ "user_email", "s", @$post->user_email ? $post->user_email : NULL ],
                    [ "user_date", "s", date("Y-m-d H:i:s")]
                ]
            ]);

            foreach( $post->access as $access ){
                $access = (Object)$access;
                Model::insert($commercial,(Object)[
                    "table" => "[UserAccess]",
                    "fields" => [
                        [ "user_id", "i", $user_id ],
                        [ "user_access_name", "s", $access->name ],
                        [ "user_access_value", "s", "{$access->value}" ],
                        [ "user_access_data_type", "s", $access->type ],
                        [ "user_access_date", "s", date("Y-m-d H:i:s")]
                    ]
                ]);
            }

            if( @$post->companies ){
                foreach( $post->companies as $company ){
                    $company = (Object)$company;
                    Model::insert($commercial,(Object)[
                        "table" => "[UserCompany]",
                        "fields" => [
                            [ "user_id", "s", $user_id ],
                            [ "company_id", "i", $company->company_id ],
                            [ "user_company_main", "s", $company->user_company_main ],
                            [ "user_company_date", "s", date("Y-m-d H:i:s")]
                        ]
                    ]);
                }
            }

            if( @$post->prices ){
                foreach( $post->prices as $price ){
                    $price = (Object)$price;
                    Model::insert($commercial,(Object)[
                        "table" => "[UserPrice]",
                        "fields" => [
                            [ "user_id", "i", $user_id ],
                            [ "price_id", "s", $price->price_id ],
                            [ "user_price_date", "s", date("Y-m-d H:i:s")]
                        ]
                    ]);
                }
            }

            if( @$post->image ){
                base64toFile( PATH_FILES . "user/", $user_id, $post->image);
            }

            postLog((Object)[
                "parent_id" => $user_id
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Usuário cadastrado com sucesso."
            ]);

        break;

        case "getList":

            $users = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "[User] u",
                    "inner join [UserProfile] up on(u.user_profile_id = up.user_profile_id)"
                ],
                "fields" => [
                    "u.user_id",
                    "u.person_id",
                    "u.user_active",
                    "u.user_name",
                    "up.user_profile_name",
                    "user_login=FORMAT(u.user_login,'yyyy-MM-dd HH:mm:ss')",
                    "user_login_br=u.user_login"
                ],
                "order" => "u.user_name"
            ]);

            foreach( $users as $user ){
                $user->image = getImage((Object)[
                    "image_id" => $user->user_id,
                    "image_dir" => "user"
                ]);
                if (!@$user->image && @$user->person_id ){
                    $user->image = getImage((Object)[
                        "image_id" => $user->person_id,
                        "image_dir" => "person"
                    ]);
                }
            }

            Json::get( $headerStatus[200], $users );

        break;

        case "external":

            $users = Model::getList( $dafel, (Object)[
                "tables" => [ "Usuario" ],
                "fields" => [
                    "user_id=IdUsuario",
                    "user_name=NmUsuario"
                ],
                "filters" => [[ "StAtivo", "s", "=", "S" ]],
                "order" => "NmUsuario"
            ]);

            Json::get( $headerStatus[200], $users );

        break;

        case "userPass":

            if( $login->access->user->user_pass->value == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Você não possuí acesso para realizar essa operação."
                ]);
            }

            if( !@$post->user_id || !@$post->user_new_pass ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "[User]",
                "fields" => [[ "user_pass", "s", md5($post->user_new_pass) ]],
                "filters" => [[ "user_id", "i", "=", $post->user_id ]]
            ]);

            postLog((Object)[
                "parent_id" => $post->user_id
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Senha atualizada com sucesso."
            ]);

        break;

	}

?>