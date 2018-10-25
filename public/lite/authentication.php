<?php

    include "../../config/start.php";

    GLOBAL $get, $post, $commercial, $headerStatus;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    switch( $_GET["action"] )
    {
        case "register":

            if( !@$post->user_user || !@$post->user_pass || !@$post->device_guid || !@$post->device_device || !@$post->device_model || !@$post->device_brand ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $user = Model::get($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "[User] U",
                    "INNER JOIN [UserAccess] UC ON(UC.user_id = U.user_id AND UC.user_access_name = 'mobile_unlock')"
                ],
                "fields" => [
                    "U.user_id",
                    "U.user_active",
                    "U.user_name",
                    "user_unlock_device=UC.user_access_value"
                ],
                "filters" => [
                    [ "user_user", "s", "=", $post->user_user ],
                    [ "user_pass", "s", "=", md5($post->user_pass) ]
                ]
            ]);

            if( !@$user ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Usuário não encontrado."
                ]);
            }

            if( $user->user_active == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O usuário está inativo."
                ]);
            }

            if( $user->user_unlock_device == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O usuário não possui permissão para desbloqueio do aparelho."
                ]);
            }

            $device_id = (int)Model::insert($commercial,(Object)[
                "table" => "Device",
                "fields" => [
                    [ "device_active", "s", "Y" ],
                    [ "user_id", "i", $user->user_id ],
                    [ "device_guid", "s", $post->device_guid ],
                    [ "device_device", "s", $post->device_device ],
                    [ "device_model", "s", $post->device_model ],
                    [ "device_brand", "s", $post->device_brand ],
                    [ "device_date", "s", date("Y-m-d H:i:s") ]
                ]
            ]);

            postLog((Object)[
                "user_id" => $user->user_id,
                "parent_id" => $device_id
            ]);

            Json::get( $headerStatus[200], (Object)[
                "user_id" => $user->user_id,
                "user_name" => $user->user_name,
                "date" => date("Y-m-d H:i:s")
            ]);

        break;

        case "validate":

            if( !@$post->device_guid ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $device = Model::get($commercial,(Object)[
                "tables" => [ "Device" ],
                "fields" => [ "device_active" ],
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

            Json::get( $headerStatus[200], $device );

        break;

    }

?>