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

            $routes = Model::getList($commercial,(Object)[
                "tables" => [ "[Route]" ],
                "fields" => [
                    "route_id",
                    "route_name"
                ]
            ]);

            Json::get($headerStatus[200], $routes);

        break;

    }

?>