<?php

	error_reporting(E_ALL);
    ini_set('memory_limit', '-1');

    session_name("COMMERCIAL3");
    session_start();

    //setcookie(session_name(),session_id(),time()+3600);

    date_default_timezone_set("America/Sao_Paulo");
    //date_default_timezone_set("America/Araguaina");

    include "config.php";
    include "func.php";

    loadClass();
    $conn = json_decode(file_get_contents( PATH_DATA . "conn.json" ));

    $dafel = new MSSQL($conn->dafel);
    $commercial = new MSSQL($conn->commercial);
    $metas = new MySQL($conn->metas);
    $compass = new MySQL($conn->compass);

    $smarty = new Smarty();

    $get = (Object)$_GET;
    $post = (Object)$_POST;
    $headers = getallheaders();
    if( !Session::isUser() && @$get->user_id && @$get->user_session ){
        $session = Model::get($commercial,(Object)[
            "top" => 1,
            "tables" => [ "UserSession" ],
            "fields" => [ "user_id" ],
            "filters" => [
                [ "user_id", "i", "=", $get->user_id ],
                [ "user_session_value", "s", "=", $get->user_session ],
                [ "CONVERT(VARCHAR(10),user_session_date,126)", "s", "=", date("Y-m-d") ]
            ]
        ]);
        if( @$session ){
            $_SESSION["user_id"] = $get->user_id;
            $_SESSION["user_session_id"] = $get->user_session;
        }
    }

    Session::set();
    $device = Session::device();

    if( Session::isUser() ){
        $login = Model::get($commercial,(Object)[
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
            "filters" => [[ "user_id", "i", "=", $_SESSION["user_id"] ]],
            "gets" => [
                "get_user_price" => 1,
                "get_user_access" => 1,
                "get_user_person" => 1,
                "get_user_company" => 1,
                "get_user_profile" => 1,
                "get_user_last_session" => 1,
                "get_user_profile_access" => 1
            ]
        ]);

        if( @$login ){
            $login->user_current_session = (Object)$_SESSION;
            $login->access = (Object)array_merge(
                (Array)$login->user_access,
                (Array)$login->user_profile->user_profile_access
            );
            unset($login->user_access);
            unset($login->user_profile->user_profile_access);
            Model::update($commercial,(Object)[
                "table" => "[User]",
                "fields" => [[ "user_timestamp", "i", strtotime(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s"))) . " + 2 minutes") ]],
                "filters" => [[ "user_id", "i", "=", $login->user_id ]]
            ]);
        }
    }

    $config = Config::getList();

?>