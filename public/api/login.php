<?php

    include "../../config/start.php";

    GLOBAL $commercial, $post, $headerStatus;

    unset($_SESSION["user_id"]);

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
            [ "user_login", "s", date("Y-m-d H:i:s") ],
            [ "user_timestamp", "i", strtotime(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s"))) . " + 2 minutes") ]
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
            [ "user_session_app_version", "s", @$headers["AppVersion"] ? $headers["AppVersion"] : NULL ],
            [ "user_session_host_ip", "s", @$headers["HostIP"] && $headers["HostIP"] != "null" ? $headers["HostIP"] : NULL ],
            [ "user_session_host_name", "s", @$headers["HostName"] ? $headers["HostName"] : NULL ],
            [ "user_session_platform", "s", @$headers["Platform"] ? $headers["Platform"] : NULL ],
            [ "user_session_date", "s", date("Y-m-d H:i:s") ],
        ]
    ]);

    Session::saveSessionUser( $user );

    Json::get( $headerStatus[200], (Object)$_SESSION );

?>