<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $conn, $commercial, $dafel, $config, $login, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    switch( $get->action )
    {
        case "getJson":

            if( !@$post->log_id || !@$post->log_script || !@$post->log_action || !@$post->log_date ) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $date = DateTime::createFromFormat('Y-m-d H:i:s', $post->log_date);

            $file = PATH_LOG . "post/{$date->format("Y/F/d")}/{$post->log_script}/{$post->log_action}/{$post->log_id}.json";
            if( !file_exists($file) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O arquivo json não foi encontrado. Contate o setor de TI."
                ]);
            }

            $json = json_decode(file_get_contents($file));
            if( @$json->post->user_pass ) $json->post->user_pass = "******";

            Json::get( $headerStatus[200], $json );

        break;

        case "getList":

            if( !@$post->log_parent_id || !@$post->log_script ) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $audit = Model::getList($commercial,(Object)[
                "tables" => [ "[Log] L", "[User] U" ],
                "fields" => [
                    "L.log_id",
                    "U.user_id",
                    "U.user_name",
                    "L.log_script",
                    "L.log_action",
                    "log_date=FORMAT(L.log_date,'yyyy-MM-dd HH:mm:ss')",
                    "log_date_br=L.log_date"
                ],
                "filters" => [
                    [ "U.user_id = L.user_id" ],
                    [ "L.log_parent_id", "s", "=", $post->log_parent_id ],
                    [ "L.log_script", "s", "=", $post->log_script ]
                ]
            ]);

            Json::get( $headerStatus[200], $audit );

        break;
    }

?>