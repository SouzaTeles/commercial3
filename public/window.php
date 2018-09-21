<?php

    include "../config/start.php";

    GLOBAL $config, $login, $get, $post, $smarty;

    define( "ROUTE", "window" );
    Session::check();

    $colors = json_decode(file_get_contents(PATH_DATA."colors.json"));
    $colors = getColors($colors);

    $smarty->assign( "login", $login );
    $smarty->assign( "config", $config );
    $smarty->assign( "colors", $colors );

    $smarty->assign( "module", $get->module );
    $smarty->assign( "action", $get->action );

    foreach( get_defined_constants(true)["user"] as $constant => $value ){
        $smarty->assign( $constant, $value );
    }

    $smarty->display( PATH_TEMPLATES . "window.html" );

?>