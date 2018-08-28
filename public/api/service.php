<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $site, $login, $headerStatus;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    checkAccess();

    if( in_array($get->action,["edit"]) ){
        postLog();
    }

    switch( $get->action ){

        case "edit":

            if( !@$post->service_id || !@$post->service_active || !@$post->service_name || !@$post->service_period || !@$post->service_time ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "service",
                "fields" => [
                    [ "service_active", "s", $post->service_active ],
                    [ "service_name", "s", $post->service_name ],
                    [ "service_description", "s", @$post->service_description ? $post->service_description : NULL ],
                    [ "service_period", "s", $post->service_period ],
                    [ "service_days", "s", @$post->service_days ? implode(":",$post->service_days) : NULL ],
                    [ "service_time", "s", $post->service_time ]
                ],
                "filters" => [[ "service_id", "i", "=", $post->service_id ]]
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Serviço atualizado com sucesso."
            ]);

        break;

        case "get":

            if (!@$post->service_id) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $service = Model::get($commercial,(Object)[
                "tables" => ["service"],
                "filters" => [["service_id", "i", "=", $post->service_id]]
            ]);

            if (!@$service) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Relatório não encontrada."
                ]);
            }

            $service->service_days = @$service->service_days ? explode(":",$service->service_days) : [];

            Json::get($headerStatus[200], $service);

        break;

        case "getList":

            $services = Model::getList($commercial,(Object)[
                "tables" => [ "service" ],
                "fields" => [
                    "service_id",
                    "service_active",
                    "service_name",
                    "service_last_run"
                ]
            ]);

            foreach( $services as $service ){
                $service->service_last_run_br = @$service->service_last_run ? date_format(date_create($service->service_last_run),"d/m/Y H:i:s") : NULL;
            }

            Json::get( $headerStatus[200], $services );

        break;

        case "getModules":

            $modules = Model::getList($commercial,(Object)[
                "tables" => [ "service_module" ],
                "fields" => [
                    "service_module_id",
                    "service_module_name"
                ]
            ]);

            Json::get( $headerStatus[200], $modules );

        break;

        case "trigger":

            if( !@$post->service_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $service = Model::get($commercial,(Object)[
                "tables" => [ "service" ],
                "fields" => [ "service_id", "service_key", "service_active" ],
                "filters" => [[ "service_id", "i", "=", $post->service_id ]]
            ]);

            if( $service->service_active == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O serviço está inativo."
                ]);
            }

            $function = "";
            foreach(explode("-",$service->service_key) as $key ){
                $function .= ucfirst($key);
            }
            Service::$function();

            Model::update($commercial,(Object)[
                "table" => "service",
                "fields" => [[ "service_last_run", "s", date("Y-m-d H:i:s") ]],
                "filters" => [[ "service_id", "i", "=", $service->service_id ]]
            ]);

            Json::get($headerStatus[200], (Object)[
                "message" => "Serviço executado com sucesso!"
            ]);

        break;

    }

?>