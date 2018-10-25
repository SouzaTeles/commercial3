<?php

    include "../../config/start.php";

    GLOBAL $get, $post, $dafel, $commercial, $headerStatus, $login;

    if( !Session::isUser() )
    {
        if( !@$post->user_user || !@$post->user_pass || !@$post->device_guid ){
            headerResponse((Object)[
                "code" => 417,
                "message" => "Parâmetro POST não encontrado."
            ]);
        }

        $device = Model::get($commercial,(Object)[
            "tables" => [ "device" ],
            "fields" => [
                "device_model",
                "device_active"
            ],
            "filters" => [[ "device_guid", "s", "=", $post->device_guid ]]
        ]);

        if( !@$device ){
            headerResponse((Object)[
                "code" => 404,
                "message" => "Dispositivo não autorizado."
            ]);
        }

        if( $device->device_active == "N" ){
            headerResponse((Object)[
                "code" => 417,
                "message" => "Dispositivo não autorizado."
            ]);
        }

        $login = Model::get($commercial,(Object)[
            "class" => "User",
            "tables" => [ "[User]" ],
            "fields" => [
                "user_id",
                "person_id",
                "external_id",
                "user_profile_id",
                "user_user",
                "user_pass",
                "user_name",
                "user_email",
                "user_active",
                "user_login=FORMAT(user_login,'yyyy-MM-dd HH:mm:ss')",
                "user_update=FORMAT(user_update,'yyyy-MM-dd HH:mm:ss')",
                "user_date=FORMAT(user_date,'yyyy-MM-dd HH:mm:ss')",
            ],
            "filters" => [
                [ "user_user", "s", "=", $post->user_user ],
                [ "user_pass", "s", "=", md5($post->user_pass) ]
            ],
            "gets" => [
                "get_user_company" => 1,
                "get_user_company_erp" => 1,
                "get_user_price" => 1,
                "get_user_price_erp" => 1,
                "get_user_current_session" => 1,
                "get_user_profile" => 1,
                "get_user_seller" => 1,
                "get_user_access" => 1,
                "get_user_person" => 1,
                "get_user_profile_access" => 1
            ]
        ]);

        if( !@$login ){
            headerResponse((Object)[
                "code" => 404,
                "message" => "Usuário não autorizado."
            ]);
        }

        if( $login->user_active == "N" ){
            headerResponse((Object)[
                "code" => 404,
                "message" => "Usuário inativo."
            ]);
        }

        if( $login->user_access->mobile_access == "N" ){
            headerResponse((Object)[
                "code" => 404,
                "message" => "Usuário não autorizado."
            ]);
        }

        $session_id = session_id();
        $login->user_current_session = (Object)[
            "user_session_value" => $session_id,
            "user_session_date" => date("Y-m-d H:i:s")
        ];

        Model::update( $commercial, (Object)[
            "table" => "[User]",
            "fields" => [
                [ "user_login", "s", date("Y-m-d H:i:s") ],
                [ "user_timestamp", "i", strtotime(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s"))) . " + 2 minutes") ]
            ],
            "filters" => [
                [ "user_id", "s", "=", $login->user_id ]
            ],
            "no_update" => 1
        ]);

        Model::insert( $commercial, (Object)[
            "table" => "UserSession",
            "fields" => [
                [ "user_id", "s", $login->user_id ],
                [ "user_session_value", "s", session_id() ],
                [ "user_session_origin", "s", "M" ],
                [ "user_session_app_version", "s", "1.2" ],
                [ "user_session_host_name", "s", $device->device_model ],
                [ "user_session_date", "s", date("Y-m-d H:i:s") ],
            ]
        ]);

        Session::saveSessionUser( $login );
    }

    $login->user_mail = $login->user_email;
    $login->user_seller_id = $login->person_id;
    $login->user_unlock_device = @$login->access ? $login->access->mobile_unlock : $login->user_access->mobile_unlock;
    $login->user_session_expires = @$login->access ? $login->access->only_session : $login->user_access->only_session;
    $login->user_mobile_access = @$login->access ? $login->access->mobile_access : $login->user_access->mobile_access;
    $login->user_max_discount = @$login->access ? $login->access->max_discount : $login->user_access->max_discount;
    $login->user_max_credit_authorization = @$login->access ? ($login->access->credit_authorization == "Y" ? 1 : 0) : ($login->user_access->credit_authorization == "Y" ? 1 : 0);
    $login->user_login = standardize_date($login->user_login);
    $login->user_date = standardize_date($login->user_date);
    $login->person->person_id = $login->person_id;
    $login->user_seller = $login->person;

    foreach( $login->companies as $company ){
        $company->company_erp = Model::get($dafel,(Object)[
            "tables" => [ "EmpresaERP" ],
            "fields" => [
                "company_id=CdEmpresa",
                "company_code=CdEmpresa",
                "company_name=NmEmpresa",
                "company_short_name=NmEmpresaCurto",
                "company_cnpj=NrCGC",
                "company_phone=NrTelefone"
            ],
            "filters" => [[ "CdEmpresa", "i", "=", $company->company_id ]]
        ]);
    }
    $login->user_company = $login->companies;

    foreach( $login->prices as $price ){
        $price->price_erp = clone $price;
    }
    $login->user_price = $login->prices;

    unset($login->prices);
    unset($login->companies);

    Json::get( $headerStatus[200], $login );

?>