<?php

    include "../config/start.php";

    GLOBAL $config, $login, $get, $post, $smarty;

    $route = @$get->route ? $get->route : "home";
    define( "ROUTE", $route );

    Session::logout();
    Session::check();

    $data = json_decode(file_get_contents(PATH_DATA."pages.json"));

    if( @$login ){
        $login->access = $login->user_profile->user_profile_access;
        foreach( $data as $p1 ) {
            if (!in_array($p1->key, ["login", "home"])) {
                if (@$p1->pages) {
                    foreach ($p1->pages as $p2) {
                        $key = $p2->key;
                        if (!@$login->access->$key) {
                            echo "Chave do json não encontrada: {$key}";
                            die(var_dump($login->access));
                        }
                        $p2->access = $login->access->$key->access->value;
                    }
                }
                $key = $p1->key;
                if (!@$login->access->$key) {
                    echo "Chave do json não encontrada: {$key}";
                    die(var_dump($login->access));
                }
                $p1->access = $login->access->$key->access->value;
            }
        }
        unset($login->user_profile->user_profile_access);
    }

    if( @$login && $route != "home" && @$login->access->$route->access->value && $login->access->$route->access->value == "N" ){
        headerLocation(URI_PUBLIC);
    }

    $colors = json_decode(file_get_contents(PATH_DATA."colors.json"));
    $colors = getColors($colors);

    $_SESSION["colors"] = $colors;

    $smarty->assign( "data", $data );
    $smarty->assign( "login", $login );
    $smarty->assign( "route", $route );
    $smarty->assign( "config", $config );
    $smarty->assign( "colors", $colors );
    $smarty->assign( "user_profile_access", json_decode(file_get_contents(PATH_DATA."access.json")) );

    foreach( get_defined_constants(true)["user"] as $constant => $value ){
        $smarty->assign( $constant, $value );
    }

    $smarty->display( PATH_TEMPLATES . "index.html" );

?>