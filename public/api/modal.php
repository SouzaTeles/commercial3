<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $get, $smarty;

    if( !@$get->modal ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    if( !file_exists(PATH_TEMPLATES."modal/{$get->modal}.html") ){
        headerResponse((Object)[
            "code" => 404,
            "message" => "O arquivo de template não foi encontrado."
        ]);
    }

    foreach( get_defined_constants(true)["user"] as $constant => $value ){
        $smarty->assign( $constant, $value );
    }

    $smarty->display( PATH_TEMPLATES . "modal/{$get->modal}.html" );

?>