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
        case "add":

            if( !@$post->to_id || !@$post->message_type || !@$post->message_text ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $date = date("Y-m-d H:i:s");
            $post->message_text = strip_tags($post->message_text);
            $message_id = (int)Model::insert($commercial,(Object)[
                "table" => "[Message]",
                "fields" => [
                    ["to_id", "s", $post->to_id],
                    ["from_id", "s", $login->user_id],
                    ["message_status", "s", "O"],
                    ["message_type", "s", $post->message_type],
                    ["message_text", "s", $post->message_text],
                    ["message_date", "s", $date],
                ]
            ]);

            Json::get($headerStatus[200],(Object)[
                "message_date" => $date,
                "message_status" => "O",
                "message_id" => $message_id,
                "message_text" => $post->message_text,
            ]);

        break;

        case "getList":

            $chat = Model::getList($commercial,(Object)[
                "tables" => [
                    "[User] U"
                ],
                "fields" => [
                    "U.user_id",
                    "U.person_id",
                    "U.user_name",
                    "U.user_timestamp",
                    "message_status=(SELECT TOP 1 M1.message_status FROM [Message] M1 WHERE ((M1.from_id={$login->user_id} AND M1.to_id=U.user_id) OR (M1.to_id={$login->user_id} AND M1.from_id=U.user_id)) ORDER BY M1.message_date DESC)",
                    "message_text=(SELECT TOP 1 M2.message_text FROM [Message] M2 WHERE ((M2.from_id={$login->user_id} AND M2.to_id=U.user_id) OR (M2.to_id={$login->user_id} AND M2.from_id=U.user_id)) ORDER BY M2.message_date DESC)",
                    "message_date=(SELECT TOP 1 M3.message_date FROM [Message] M3 WHERE ((M3.from_id={$login->user_id} AND M3.to_id=U.user_id) OR (M3.to_id={$login->user_id} AND M3.from_id=U.user_id)) ORDER BY M3.message_date DESC)",
                ],
                "filters" => [
                    [ "U.user_id", "i", "!=", $login->user_id ],
                    [ "U.user_active", "s", "=", "Y" ]
                ],
                "order" => "(SELECT TOP 1 M4.message_date FROM [Message] M4 WHERE ((M4.from_id={$login->user_id} AND M4.to_id=U.user_id) OR (M4.to_id={$login->user_id} AND M4.from_id=U.user_id)) ORDER BY M4.message_date DESC) DESC, U.user_timestamp DESC"
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

        case "getMessages":

            if( !@$post->user_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro GET não encontrado."
                ]);
            }

            $messages = Model::getList($commercial,(Object)[
                "top" => 10,
                "tables" => [ "[Message]" ],
                "fields" => [
                    "message_id",
                    "to_id",
                    "from_id",
                    "message_text",
                    "message_date",
                    "message_status"
                ],
                "filters" => [
                    [
                        ["to_id={$post->user_id} and from_id={$login->user_id}"],
                        ["to_id={$login->user_id} and from_id={$post->user_id}"]
                    ]
                ],
                "order" => "message_date desc"
            ]);

            Json::get($headerStatus[200],$messages);

        break;
    }

?>