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

    switch( $get->action ) {

        case "edit":

            if (
                !@$post->company_id ||
                !@$post->company_active ||
                !@$post->company_target ||
                !@$post->company_st ||
                !@$post->company_credit ||
                !@$post->company_color ||
                !@$post->company_name ||
                !@$post->company_short_name ||
                !@$post->delivery_days ||
                !@$post->company_consumer_id ||
                !@$post->company_budget_message ||
                !@$post->company_latitude ||
                !@$post->company_longitude
            ){
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
                    ["company_target", "s", $post->company_target],
                    ["company_st", "s", $post->company_st],
                    ["company_credit", "s", $post->company_credit],
                    ["company_color", "s", $post->company_color],
                    ["company_name", "s", $post->company_name],
                    ["company_short_name", "s", $post->company_short_name],
                    ["delivery_days", "i", $post->delivery_days],
                    ["company_consumer_id", "s", $post->company_consumer_id],
                    ["company_budget_message", "s", $post->company_budget_message],
                    ["company_latitude", "s", $post->company_latitude],
                    ["company_longitude", "s", $post->company_longitude],
                    ["company_update", "s", date("Y-m-d H:m:i")]
                ],
                "filters" => [["company_id", "i", "=", $post->company_id]]
            ]);

            postLog((Object)[
                "parent_id" => $post->company_id
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

            $company = Model::get($commercial, (Object)[
                "class" => "Company",
                "tables" => ["company"],
                "fields" => [
                    "company_id",
                    "parent_id",
                    "company_active",
                    "company_name",
                    "company_short_name",
                    "company_color",
                    "company_target",
                    "company_consumer_id",
                    "company_update",
                    "company_date",
                    "company_budget_message",
                    "company_st",
                    "company_credit",
                    "delivery_days",
                    "company_latitude",
                    "company_longitude"
                ],
                "filters" => [["company_id", "i", "=", $post->company_id]]
            ]);

            if (!@$company) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Pessoa não encontrada."
                ]);
            }

            Json::get($headerStatus[200], $company);

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
                "tables" => [
                    "EmpresaERP ERP"
                ],
                "fields" => [
                    "ERP.CdEmpresa",
                    "ERP.NmEmpresa",
                    "ERP.NmEmpresaCurto"
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

            if(
                !@$post->company_id ||
                !@$post->company_active ||
                !@$post->company_target ||
                !@$post->company_st ||
                !@$post->company_credit ||
                !@$post->company_color ||
                !@$post->company_name ||
                !@$post->company_short_name ||
                !@$post->delivery_days ||
                !@$post->company_consumer_id ||
                !@$post->company_budget_message ||
                !@$post->company_latitude ||
                !@$post->company_longitude
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $company_id = Model::insert($commercial, (Object)[
                "table" => "Company",
                "fields" => [
                    ["company_id", "i", $post->company_id],
                    ["parent_id", "i", @$post->parent_id ? $post->parent_id : NULL],
                    ["company_active", "s", $post->company_active],
                    ["company_target", "s", $post->company_target],
                    ["company_st", "s", $post->company_st],
                    ["company_credit", "s", $post->company_credit],
                    ["company_color", "s", $post->company_color],
                    ["company_name", "s", $post->company_name],
                    ["company_short_name", "s", $post->company_short_name],
                    ["delivery_days", "i", $post->delivery_days],
                    ["company_consumer_id", "s", $post->company_consumer_id],
                    ["company_budget_message", "s", $post->company_budget_message],
                    ["company_latitude", "s", $post->company_latitude],
                    ["company_longitude", "s", $post->company_longitude],
                    ["company_date", "s", date("Y-m-d H:m:i")]
                ]
            ]);

            if( @$post->image ){
                base64toFile( PATH_FILES . "company/", $post->company_id, $post->image);
            }

            postLog((Object)[
                "parent_id" => $post->company_id
            ]);

            Json::get($headerStatus[200], (Object)[
                "message" => "Empresa cadastrada com sucesso."
            ]);

        break;

        case "external":

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

    }

?>