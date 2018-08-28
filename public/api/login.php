<?php

    include "../../config/start.php";

    if( !Session::isUser() )
    {
        if( !@$post->user_user || !@$post->user_pass ){
            headerResponse((Object)[
                "code" => 417,
                "message" => "Parâmetro POST não encontrado"
            ]);
        }

        $user = Model::get( $commercial, (Object)[
            "class" => "User",
            "tables" => [ "[User]" ],
            "fields" => [
                "user_id",
                "person_id",
                "user_profile_id",
                "user_user",
                "user_pass",
                "user_name",
                "user_email",
                "user_discount=CAST(user_discount AS FLOAT)",
                "user_credit",
                "user_active",
                "user_trash",
                "user_login",
                "user_update=FORMAT(user_update,'yyyy-MM-dd HH:mm:ss')",
                "user_date=FORMAT(user_date,'yyyy-MM-dd HH:mm:ss')",
            ],
            "filters" => [
                [ "user_user", "s", "=", $post->user_user ],
                [ "user_pass", "s", "=", md5($post->user_pass) ]
            ]
        ]);

        if( !@$user ){
            headerResponse((Object)[
                "code" => 404,
                "message" => "Login e/ou senha incorretos."
            ]);
        }

        if( $user->user_active == "N" ){
            Session::reset();
            headerResponse((Object)[
                "code" => 401,
                "message" => "O usuário está inativo."
            ]);
        }

        Model::update( $commercial, (Object)[
            "table" => "[User]",
            "fields" => [
                [ "user_login", "s", date("Y-m-d H:i:s") ]
            ],
            "filters" => [
                [ "user_id", "s", "=", $user->user_id ]
            ],
            "no_update" => 1
        ]);

        Model::insert( $commercial, (Object)[
            "table" => "UserSession",
            "fields" => [
                [ "user_id", "s", $user->user_id ],
                [ "user_session_value", "s", session_id() ],
                [ "user_session_origin", "s", "D" ],
                [ "user_session_date", "s", date("Y-m-d H:i:s") ],
            ]
        ]);

        Session::saveSessionUser( $user );
    }

    Json::get( $headerStatus[200], (Object)$_SESSION );

?>