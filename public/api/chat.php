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
        case "getList":

            $chat = Model::getList($commercial,(Object)[
                "tables" => [
                    "[User]"
                ],
                "fields" => [
                    "user_id",
                    "person_id",
                    "user_name",
                    "user_timestamp"
                ],
                "filters" => [
                    [ "user_id", "i", "!=", $login->user_id ]
                ]
            ]);

            foreach( $chat as $data ){
                $data->image = getImage((Object)[
                    "image_id" => $data->user_id,
                    "image_dir" => "user"
                ]);
                if (!@$data->image && @$data->person_id ){
                    $data->image = getImage((Object)[
                        "image_id" => $data->person_id,
                        "image_dir" => "person"
                    ]);
                }
                $data->status = @$data->user_timestamp && $data->user_timestamp > time() ? 'on' : 'off';
            }

            Json::get( $headerStatus[200], $chat );

        break;
    }

?>