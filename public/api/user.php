<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $smarty, $login, $dimensions, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    if( !in_array($get->action,["loginPass","userPassForm","loginPassForm"])){
        checkAccess();
    }

    if( $get->action == "userPass" && $login->access->user->user_pass->value == "N" ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Você não possuí acesso para realizar essa operação."
        ]);
    }

    if( in_array($get->action,["del","edit","insert","userPass","loginPass"]) ){
        postLog();
    }

    switch( $get->action ){

        case "del":

            if( !@$post->user_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "user",
                "fields" => [[ "user_trash", "s", "Y" ]],
                "filters" => [[ "user_id", "i", "=", $post->user_id ]]
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Usuário removido com sucesso."
            ]);

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
                "tables" => [ "user" ],
                "filters" => [
                    [ "user_trash", "s", "=", "N" ],
                    [ "user_id", "s", "=", $post->user_id ]
                ]
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

            if( !@$post->user_id || !@$post->user_profile_id || !@$post->user_name || !@$post->user_mail ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "user",
                "fields" => [
                    [ "person_id", "s", @$post->person_id ? $post->person_id : NULL ],
                    [ "user_profile_id", "i", $post->user_profile_id ],
                    [ "user_active", "s", $post->user_active ],
                    [ "user_name", "s", $post->user_name ],
                    [ "user_mail", "s", $post->user_mail ]
                ],
                "filters" => [[ "user_id", "s", "=", $post->user_id ]]
            ]);

            Model::delete($commercial,(Object)[
                "table" => "user_company",
                "filters" => [[ "user_id", "s", "=", $post->user_id ]]
            ]);

            if( @$post->companies ){
                foreach( $post->companies as $company ){
                    $company = (Object)$company;
                    Model::insert($commercial,(Object)[
                        "table" => "user_company",
                        "fields" => [
                            [ "user_id", "s", $post->user_id ],
                            [ "company_id", "i", $company->company_id ],
                            [ "user_company_main", "s", $company->user_company_main ],
                        ]
                    ]);
                }
            }

            Model::delete($commercial,(Object)[
                "table" => "user_price",
                "filters" => [[ "user_id", "s", "=", $post->user_id ]]
            ]);

            if( @$post->prices ){
                foreach( $post->prices as $price ){
                    $price = (Object)$price;
                    Model::insert($commercial,(Object)[
                        "table" => "user_price",
                        "fields" => [
                            [ "user_id", "s", $post->user_id ],
                            [ "price_id", "s", $price->price_id ]
                        ]
                    ]);
                }
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "Usuário atualizado com sucesso."
            ]);

        break;

        case "insert":

            if ( !@$post->user_profile_id || !@$post->user_name) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $user = Model::get($commercial,(Object)[
                "tables" => [ "user" ],
                "fields" => [ "user_user, user_mail" ],
                "filters" => [
                    [
                        [ "user_user", "s", "=", $post->user_user ],
                        [ "user_mail", "s", "=", $post->user_mail ]
                    ]
                ]
            ]);

            if ( @$user && $user->user_user == $post->user_user ) {
                headerResponse((Object)[
                    "code" => 409,
                    "message" => "O login já está cadastrado."
                ]);
            }

            if ( @$user && $user->user_mail == $post->user_mail ) {
                headerResponse((Object)[
                    "code" => 409,
                    "message" => "O e-mail já está cadastrado."
                ]);
            }

            $user_id = Model::insert($commercial,(Object)[
                "table" => "user",
                "fields" => [
                    [ "user_id", "s", $post->user_id ],
                    [ "user_profile_id", "i", $post->user_profile_id ],
                    [ "user_active", "s", @$post->user_active ? "Y" : "N" ],
                    [ "user_user", "s", $post->user_user ],
                    [ "user_pass", "s", md5($post->user_pass) ],
                    [ "user_name", "s", $post->user_name ],
                    [ "user_mail", "s", @$post->user_mail ? $post->user_mail : NULL ],
                    [ "user_trash", "s", "N" ]
                ]
            ]);

            if( @$post->companies ){
                foreach( $post->companies as $company ){
                    $company = (Object)$company;
                    Model::insert($commercial,(Object)[
                        "table" => "user_company",
                        "fields" => [
                            [ "user_id", "s", $post->user_id ],
                            [ "company_id", "i", $company->company_id ],
                            [ "user_company_main", "s", $company->user_company_main ],
                        ]
                    ]);
                }
            }

            if( @$post->prices ){
                foreach( $post->prices as $price ){
                    $price = (Object)$price;
                    Model::insert($commercial,(Object)[
                        "table" => "user_price",
                        "fields" => [
                            [ "user_id", "s", $post->user_id ],
                            [ "price_id", "s", $price->price_id ]
                        ]
                    ]);
                }
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "Usuário cadastrado com sucesso."
            ]);

        break;

        case "getList":

            $users = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "user u",
                    "inner join user_profile up on(u.user_profile_id = up.user_profile_id)"
                ],
                "fields" => [
                    "u.user_id",
                    "u.user_active",
                    "u.user_name",
                    "up.user_profile_name",
                    "u.user_login"
                ],
                "filters" => [[ "u.user_trash", "s", "=", "N" ]]
            ]);

            if( !sizeof($users) ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Nenhum usuário encontrado"
                ]);
            }

            foreach( $users as $user ){
                $user->image = getImage((Object)[
                    "image_id" => $user->user_id,
                    "image_dir" => "user"
                ]);
                $user->user_login_br = @$user->user_login ? date_format(date_create($user->user_login),"d/m/Y H:i:s") : NULL;
            }

            Json::get( $headerStatus[200], $users );

        break;

        case "getListERP":

            $users = Model::getList( $dafel, (Object)[
                "tables" => [ "Usuario" ],
                "fields" => [
                    "IdUsuario",
                    "NmUsuario"
                ],
                "filters" => [[ "StAtivo", "s", "=", "S" ]],
                "order" => "NmUsuario"
            ]);

            Json::get( $headerStatus[200], $users );

        break;

        case "userPass":

            if( !@$post->user_id || !@$post->user_pass ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "user",
                "fields" => [[ "user_pass", "s", md5($post->user_pass) ]],
                "filters" => [
                    [ "client_id", "i", "=", $client_id ],
                    [ "user_id", "i", "=", $post->user_id ]
                ]
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Senha atualizada com sucesso."
            ]);

        break;

	    case "loginPass":

            if( !@$post->user_pass || !@$post->user_new_pass ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $user = Model::get($commercial,(Object)[
                "tables" => [ "user" ],
                "fields" => [ "user_id" ],
                "filters" => [
                    [ "user_id", "i", "=", $login->user_id ],
                    [ "user_pass", "s", "=", md5($post->user_pass) ]
                ]
            ]);

            if( !@$user ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "A senha atual está incorreta. Verifique a senha informada."
                ]);
            }

            $user = Model::update($commercial,(Object)[
                "table" => "user",
                "fields" => [[ "user_pass", "s", md5($post->user_new_pass) ]],
                "filters" => [[ "user_id", "i", "=", $login->user_id ]]
            ]);

            Json::get($headerStatus[200],(Object)[
                "message" => "Senha atualizada com sucesso."
            ]);

        break;

        case "userPassForm":

            $smarty->display( PATH_TEMPLATES . "includes/form-user-password.html" );

        break;

        case "loginPassForm":

            $smarty->display( PATH_TEMPLATES . "includes/form-login-password.html" );

        break;

        case "creditAuthorization":
        case "discountItemAuthorization":

            if( !@$post->user_user || !@$post->user_pass || !@$post->data ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $post->data = (Object)$post->data;

            $user = Model::get( $commercial, (Object)[
                "tables" => [ "[User]" ],
                "fields" => [
                    "user_id",
                    "user_name",
                    "user_active",
                    "user_credit_authorization",
                    "user_max_discount=CAST(user_max_discount AS FLOAT)"
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

            if( $get->action == "creditAuthorization" ) {
                if ($user->user_credit_authorization == "N") {
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "O usuário não possui permissão para liberação de crédito."
                    ]);
                }
            } else {
                $user->user_max_discount = (float)$user->user_max_discount;
                $post->data->item_aliquot_discount = (float)$post->data->item_aliquot_discount;
                if ($user->user_max_discount < $post->data->item_aliquot_discount) {
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "Desconto acima do permitido."
                    ]);
                }
            }

            $user->authorization_id = postLog((Object)[
                "user_id" => $user->user_id,
                "item_id" => @$post->data->item_id ? $post->data->item_id : NULL
            ]);

            Json::get( $headerStatus[200], $user );

        break;

	}

?>