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

            $accounts = Model::getList( $dafel, (Object)[
                "tables" => [ "ContaBancaria" ],
                "fields" => [
                    "IdContaBancaria",
                    "CdChamada",
                    "CdEmpresa",
                    "NrConta",
                    "DsContaBancaria"
                ],
                "filters" => [
                    [ "StATivo", "s", "=", "S" ],
                    [ "CdEmpresa", "s", "=", @$post->company_id ? $post->company_id : NULL ]
                ]
            ]);

            if( !sizeof($accounts) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Nenhuma conta ativa encontrada."
                ]);
            }

            Json::get( $headerStatus[200], $accounts );

        break;

    }

?>