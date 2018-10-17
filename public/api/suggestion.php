<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action ) {

        case "insert":

            if(
                !@$post->company_id ||
                !@$post->suggestion_message
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não localizado."
                ]);
            }

            $suggestion_id = Model::insert($commercial,(Object)[
                "table" => "Suggestion",
                "fields" => [
                    [ "company_id", "s", @$post->company_id ? $post->company_id : NULL ],
                    [ "person_name", "s", @$post->person_name ? $post->person_name : NULL ],
                    [ "suggestion_message", "s", $post->suggestion_message ],
                    [ "app_version", "s", @$headers["AppVersion"] ? $headers["AppVersion"] : NULL ],
                    [ "host_ip", "s", @$headers["HostIP"] && $headers["HostIP"] != "null" ? $headers["HostIP"] : NULL ],
                    [ "host_name", "s", @$headers["HostName"] ? $headers["HostName"] : NULL ],
                    [ "host_platform", "s", @$headers["Platform"] ? $headers["Platform"] : NULL ],
                    [ "suggestion_date", "s", date("Y-m-d H:i:s") ]
                ]
            ]);

            $to = [
                (Object)[
                    "email" => "alessandro@dafel.com.br",
                    "name" => "Alessandro Menezes"
                ],
                (Object)[
                    "email" => "adriano@dafel.com.br",
                    "name" => "Adriano Machado"
                ]
            ];

            $path = PATH_FILES . "email/" . date("Y/F/d");
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            email((Object)[
                "recipient" => $to,
                "origin" => "suggestion",
                "subject" => "Caixa de sugestão",
                "parent_id" => $suggestion_id
            ]);

            Json::get($headerStatus[200]);

        break;

    }

?>