<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $config, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action )
    {

        case "getList":

            $banks = Model::getList($dafel,(Object)[
                "class" => "Bank",
                "tables" => [ "Banco (NoLock)" ],
                "fields" => [ "IdBanco", "NmBanco" ],
                "filters" => [[ "IdBanco", "s", "in", $config->bank->authorized ]],
                "order" => "IdBanco"
            ]);

            Json::get( $headerStatus[200], $banks );

        break;

    }