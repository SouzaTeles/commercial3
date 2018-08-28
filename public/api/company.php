<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $login, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    if( in_array($get->action,["del","edit","insert"]) ){
        checkAccess();
    }

    if( in_array($get->action,["del","edit","insert"]) ){
        postLog();
    }

    switch( $get->action ) {

        case "del":

            if (!@$post->company_id) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $users = Model::get($commercial,(Object)[
                "tables" => [ "user_company" ],
                "fields" => [ "user_company_id" ],
                "filters" => [[ "company_id", "i", "=", $post->company_id ]]
            ]);

            if( @$users ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Existem usuários vinculados a empresa. A exclusão não será permitida."
                ]);
            }

            Model::delete($commercial,(Object)[
                "table" => "company",
                "filters" => [[ "company_id", "i", "=", $post->company_id ]]
            ]);

            $path = PATH_FILES . "company/{$post->company_id}";
            if (file_exists("{$path}.jpg")) unlink("{$path}.jpg");
            if (file_exists("{$path}.jpeg")) unlink("{$path}.jpeg");
            if (file_exists("{$path}.png")) unlink("{$path}.png");

            Json::get($headerStatus[200], (Object)[
                "message" => "Empresa removida com sucesso."
            ]);

        break;

        case "edit":

            if (!@$post->company_id || !@$post->company_active || !@$post->company_code || !@$post->company_name || !@$post->company_target) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial, (Object)[
                "table" => "company",
                "fields" => [
                    ["parent_id", "i", @$post->parent_id ? $post->parent_id : NULL],
                    ["company_active", "s", $post->company_active],
                    ["company_short_name", "s", @$post->company_short_name ? $post->company_short_name : NULL],
                    ["company_target", "s", $post->company_target],
                    ["company_color", "s", $post->company_color]
                ],
                "filters" => [["company_id", "i", "=", $post->company_id]]
            ]);

            Json::get($headerStatus[200], (Object)[
                "message" => "Empresa atualizada com sucesso."
            ]);

        break;

        case "get":

            if (!@$post->company_id) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $person = Model::get($commercial, (Object)[
                "class" => "Company",
                "tables" => ["company"],
                "filters" => [["company_id", "i", "=", $post->company_id]]
            ]);

            if (!@$person) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Pessoa não encontrada."
                ]);
            }

            Json::get($headerStatus[200], $person);

        break;

        case "getList":

            $companies = Model::getList($commercial, (Object)[
                "join" => 1,
                "tables" => [ "company" ],
                "fields" => [
                    "company_id",
                    "parent_id",
                    "company_active",
                    "company_name",
                    "company_short_name",
                    "company_color"
                ],
                "filters" => [
                    ( @$post->parent ? [ "parent_id IS NULL" ] : NULL ),
                    [ "company_active", "s", "=", @$post->company_active ? $post->company_active : NULL ]
                ],
                "order" => "company_id"
            ]);

            foreach( $companies as $company ){
                $company->image = getImage((Object)[
                    "image_id" => $company->company_id,
                    "image_dir" => "company"
                ]);
                $company->company_code = substr("0{$company->company_id}", -2);
            }

            Json::get($headerStatus[200], $companies);

        break;

        case "getListERP":

            $companies = Model::getList( $dafel, (Object)[
                "tables" => [ "EmpresaERP" ],
                "fields" => [
                    "CdEmpresa",
                    "NmEmpresa",
                    "NmEmpresaCurto"
                ]
            ]);

            if( !sizeof($companies) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Nenhuma empresa encontrada."
                ]);
            }

            Json::get( $headerStatus[200], $companies );

        break;

        case "insert":

            if (!@$post->company_id || !@$post->company_active || !@$post->company_code || !@$post->company_name || !@$post->company_target) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $company_id = Model::insert($commercial, (Object)[
                "table" => "company",
                "fields" => [
                    ["company_id", "s", $post->company_id],
                    ["company_active", "s", $post->company_active],
                    ["company_name", "s", $post->company_name],
                    ["company_short_name", "s", @$post->company_short_name ? $post->company_short_name : NULL],
                    ["company_target", "s", $post->company_target],
                    ["company_color", "s", $post->company_color]
                ]
            ]);

            Json::get($headerStatus[200], (Object)[
                "message" => "Empresa cadastrado com sucesso."
            ]);

        break;

    }

?>