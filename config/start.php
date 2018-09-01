<?php

	error_reporting(E_ALL);
    ini_set('memory_limit', '-1');

	session_name("COMMERCIAL3");
	session_start();
	date_default_timezone_set("America/Sao_Paulo");

    include "config.php";
    include "func.php";

    loadClass();

    $conn = json_decode(file_get_contents( PATH_DATA . "conn.json" ));

    $dafel = new MSSQL($conn->dafel);
    $commercial = new MSSQL($conn->commercial);

    $smarty = new Smarty();

    $get = (Object)$_GET;
    $post = (Object)$_POST;
    $headers = getallheaders();

    if( Session::isUser() ){
        $login = Model::get($commercial,(Object)[
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
            "filters" => [[ "user_id", "s", "=", $_SESSION["user_id"] ]],
            "gets" => [
                "get_user_price" => 1,
                "get_user_person" => 1,
                "get_user_company" => 1,
                "get_user_profile" => 1,
                "get_user_last_session" => 1,
                "get_user_profile_access" => 1
            ]
        ]);
        $login->user_current_session = (Object)$_SESSION;
    }

    $config = Config::getList();

?>