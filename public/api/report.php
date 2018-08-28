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

        case "add":

            if( !@$post->report_active || !@$post->report_name || !@$post->report_period || !@$post->report_time ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $report_id = Model::insert($commercial,(Object)[
                "table" => "report",
                "fields" => [
                    [ "report_active", "s", $post->report_active ],
                    [ "report_name", "s", $post->report_name ],
                    [ "report_description", "s", @$post->report_description ? $post->report_description : NULL ],
                    [ "report_period", "s", $post->report_period ],
                    [ "report_days", "s", @$post->report_days ? implode(":",$post->report_days) : NULL ],
                    [ "report_time", "s", $post->report_time ],
                    [ "report_trash", "s", "N" ]
                ],
                "filters" => [[ "report_id", "i", "=", $post->report_id ]]
            ]);

            if( @$post->users ) {
                foreach ($post->users as $user) {
                    $user = (Object)$user;
                    Model::insert($commercial, (Object)[
                        "table" => "report_user",
                        "fields" => [
                            ["report_id", "i", $report_id],
                            ["user_id", "i", $user->user_id],
                            ["report_user_modules", "s", implode(":", $user->report_user_modules)]
                        ]
                    ]);
                }
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "Relatório adicionado com sucesso."
            ]);

        break;

        case "del":

            if (!@$post->report_id) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $report = Model::get($commercial,(Object)[
                "class" => "Report",
                "tables" => ["report"],
                "filters" => [["report_id", "i", "=", $post->report_id]]
            ]);

            if (!@$report) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Relatório não encontrada."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "report",
                "fields" => [[ "report_trash", "s", "Y" ]],
                "filters" => [[ "report_id", "i", "=", $post->report_id ]]
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Relatório removido com sucesso."
            ]);

        break;

        case "edit":

            if( !@$post->report_id || !@$post->report_active || !@$post->report_name || !@$post->report_period || !@$post->report_time ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "report",
                "fields" => [
                    [ "report_active", "s", $post->report_active ],
                    [ "report_name", "s", $post->report_name ],
                    [ "report_description", "s", @$post->report_description ? $post->report_description : NULL ],
                    [ "report_period", "s", $post->report_period ],
                    [ "report_days", "s", @$post->report_days ? implode(":",$post->report_days) : NULL ],
                    [ "report_time", "s", $post->report_time ]
                ],
                "filters" => [[ "report_id", "i", "=", $post->report_id ]]
            ]);

            $reports = [];
            foreach( $post->users as $user ){
                $user = (Object)$user;
                if( @$user->report_user_id ){
                    $reports[] = (int)$user->report_user_id;
                    Model::update($commercial,(Object)[
                        "table" => "report_user",
                        "fields" => [[ "report_user_modules", "s", implode(":",$user->report_user_modules)]],
                        "filters" => [[ "report_user_id", "i", "=", $user->report_user_id ]]
                    ]);
                } else {
                    $reports[] = Model::insert($commercial,(Object)[
                        "table" => "report_user",
                        "fields" => [
                            [ "report_id", "i", $post->report_id ],
                            [ "user_id", "i", $user->user_id ],
                            [ "report_user_modules", "s", implode(":",$user->report_user_modules) ]
                        ]
                    ]);
                }
            }

            Model::delete($commercial,(Object)[
                "table" => "report_user",
                "filters" => [
                    [ "report_id", "i", "=", $post->report_id ],
                    [ "report_user_id", "i", "not in", $reports ]
                ]
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Relatório atualizado com sucesso."
            ]);

        break;

        case "get":

            if (!@$post->report_id) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $report = Model::get($commercial,(Object)[
                "class" => "Report",
                "tables" => ["report"],
                "filters" => [["report_id", "i", "=", $post->report_id]]
            ]);

            if (!@$report) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Relatório não encontrada."
                ]);
            }

            Json::get($headerStatus[200], $report);

        break;

        case "getList":

            $reports = Model::getList($commercial,(Object)[
                "tables" => [ "report" ],
                "fields" => [
                    "report_id",
                    "report_active",
                    "report_name",
                    "report_last_run"
                ],
                "filters" => [[ "report_trash", "s", "=", "N" ]]
            ]);

            foreach( $reports as $report ){
                $report->report_code = substr("00000{$report->report_id}",-6);
                $report->report_last_run_br = @$report->report_last_run ? date_format(date_create($report->report_last_run),"d/m/Y H:i:s") : NULL;
            }

            Json::get( $headerStatus[200], $reports );

        break;

        case "getModules":

            $modules = Model::getList($commercial,(Object)[
                "tables" => [ "report_module" ],
                "fields" => [
                    "report_module_id",
                    "report_module_name"
                ]
            ]);

            Json::get( $headerStatus[200], $modules );

        break;

        case "trigger":

            if( !@$post->report_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $report = Model::get($commercial,(Object)[
                "tables" => [ "report" ],
                "fields" => [ "report_id", "report_active", "report_name" ],
                "filters" => [[ "report_id", "i", "=", $post->report_id ]]
            ]);

            if( !@$report ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Relatório não localizado."
                ]);
            }

            if( $report->report_active == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O Relatório não está ativo."
                ]);
            }

            $users = Model::getList($commercial,(Object)[
                "tables" => [ "report_user ru", "user u" ],
                "fields" => [ "u.user_id", "u.user_mail", "u.user_name", "ru.report_user_modules" ],
                "filters" => [
                    [ "ru.user_id = u.user_id" ],
                    [ "ru.report_id", "i", "=", $report->report_id ]
                ]
            ]);

            if( !sizeof($users) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O Relatório não possui usuários de destino."
                ]);
            }

            foreach( $users as $user ){
                $user->token = md5(uniqid(rand(),true));
                $data = (Object)[
                    "report_id" => $report->report_id,
                    "user_id" => $user->user_id
                ];
                Model::insert($commercial,(Object)[
                    "table" => "token",
                    "fields" => [
                        [ "token_value", "s", $user->token ],
                        [ "token_data", "s", json_encode($data) ]
                    ]
                ]);
                email((Object)[
                    "origin" => "report",
                    "subject" => $report->report_name,
                    "recipient" => [(Object)[
                        "email" => $user->user_mail,
                        "name" => $user->user_name
                    ]],
                    "vars" => [(Object)[
                        "key" => "user",
                        "data" => $user
                    ],(Object)[
                        "key" => "report",
                        "data" => $report
                    ]]
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "report",
                "fields" => [[ "report_last_run", "s", date("Y-m-d H:i:s") ]],
                "filters" => [[ "report_id", "i", "=", $report->report_id ]]
            ]);

            Json::get($headerStatus[200], (Object)[
                "message" => "Relatório processado com sucesso!"
            ]);

        break;

    }

?>