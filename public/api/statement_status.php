<?php

    include "../../config/start.php";

    Session::checkApi();

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "messagem" => "Parâmetro GET não encontrado."
        ]);
    }

    GLOBAL $commercial, $headerStatus;

    switch( $get->action ) {

        case "del":

            if( !@$post->statement_status_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::delete($commercial, (Object)[
                "table" => "statement_status",
                "filters" => [[ "statement_status_id", "i", "=", $post->statement_status_id ]]
            ]);

            Json::get($headerStatus[200],(Object)[
                "message" => "Status removido com sucesso."
            ]);

        break;

        case "get":

            if( !@$post->statement_status_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $st = Model::get($commercial, (Object)[
                "class" => "StatementStatus",
                "tables" => ["statement_status"],
                "filters" => [[ "statement_status_id", "i", "=", $post->statement_status_id ]]
            ]);

            if (!@$st) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Status não encontrado."
                ]);
            }

            Json::get($headerStatus[200], $st);

        break;

        case "edit":

            if( !@$post->statement_status_id || !@$post->statement_status_name || !isset($post->statement_status_min) || !isset($post->statement_status_max) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "statement_status",
                "fields" => [
                    [ "statement_status_name", "s", $post->statement_status_name ],
                    [ "statement_status_min", "i", $post->statement_status_min ],
                    [ "statement_status_max", "i", $post->statement_status_max ],
                    [ "statement_status_color", "s", $post->statement_status_color ]
                ],
                "filters" => [[ "statement_status_id", "i", "=", $post->statement_status_id ]]
            ]);

            Json::get($headerStatus[200],(Object)[
                "message" => "Status {$post->statement_status_code} editado com sucesso."
            ]);

        break;

        case "insert":

            if( !@$post->statement_status_name || !isset($post->statement_status_min) || !isset($post->statement_status_max) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Verifique o formuálio preenchido."
                ]);
            }

            $statement_status_id = Model::insert($commercial,(Object)[
                "table" => "statement_status",
                "fields" => [
                    [ "statement_status_name", "s", $post->statement_status_name ],
                    [ "statement_status_color", "s", $post->statement_status_color ],
                    [ "statement_status_min", "i", $post->statement_status_min ],
                    [ "statement_status_max", "i", $post->statement_status_max ]
                ]
            ]);

            $statement_status_code = substr("00000{$statement_status_id}",-6);
            Json::get($headerStatus[200], (Object)[
                "message" => "Status {$statement_status_code} cadastrado com sucesso!"
            ]);

        break;

        case "getList":

            $status = Model::getList($commercial, (Object)[
                "tables" => ["statement_status"],
                "fields" => [
                    "statement_status_id",
                    "statement_status_name",
                    "statement_status_min",
                    "statement_status_max",
                    "statement_status_color"
                ]
            ]);

            if (!sizeof($status)) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Nenhum status encontrado."
                ]);
            }

            foreach( $status as $st ){
                $st->statement_status_code = substr("00000{$st->statement_status_id}",-6);
            }

            Json::get($headerStatus[200], $status);

        break;

    }

?>