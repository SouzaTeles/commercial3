<?php

    include "../../../config/start.php";

    Session::checkApi();

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    GLOBAL $dafel, $headerStatus;

    switch( $get->action )
    {

        case "getList":

            $users = Model::getList( $dafel, (Object)[
                "tables" => [ "Usuario" ],
                "fields" => [
                    "IdUsuario",
                    "NmUsuario"
                ],
                "filters" => [[ "StAtivo", "s", "=", "S" ]],
                "order" => "NmUsuario"
            ]);

            Json::get( $headerStatus[200], $users );

        break;

    }

?>