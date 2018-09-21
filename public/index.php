<?php

    include "../config/start.php";

    GLOBAL $config, $login, $get, $post, $smarty;

    $route = @$get->route ? $get->route : "home";
    define( "ROUTE", $route );

    Session::logout();
    Session::check();

    $data = json_decode(file_get_contents(PATH_DATA."pages.json"));

    if( @$login && $route != "home" && @$login->access->$route->access->value && $login->access->$route->access->value == "N" ){
        headerLocation(URI_PUBLIC);
    }

    $colors = json_decode(file_get_contents(PATH_DATA."colors.json"));
    $colors = getColors($colors);

    $smarty->assign( "data", $data );
    $smarty->assign( "login", $login );
    $smarty->assign( "route", $route );
    $smarty->assign( "config", $config );
    $smarty->assign( "colors", $colors );

    foreach( get_defined_constants(true)["user"] as $constant => $value ){
        $smarty->assign( $constant, $value );
    }

    $smarty->display( PATH_TEMPLATES . "index.html" );

?>