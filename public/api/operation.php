<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $site, $login, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    checkAccess();

    if( in_array($get->action,["edit"]) ){
        postLog();
    }

    switch( $get->action ){

        case "edit":

            Model::delete($commercial,(Object)[
                "table" => "operation",
                "filters" => [[ "1=1" ]]
            ]);

            if( @$post->devs ){
                foreach( $post->devs as $id ){
                    Model::insert($commercial,(Object)[
                        "table" => "operation",
                        "fields" => [
                            [ "operation_id", "s", $id ],
                            [ "operation_type", "s", "D" ]
                        ]
                    ]);
                }
            }

            if( @$post->sales ){
                foreach( $post->sales as $id ){
                    Model::insert($commercial,(Object)[
                        "table" => "operation",
                        "fields" => [
                            [ "operation_id", "s", $id ],
                            [ "operation_type", "s", "V" ]
                        ]
                    ]);
                }
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "Operações atualizadas com sucesso."
            ]);

        break;

        case "getList":

            $operations = Model::getList($commercial,(Object)[
                "tables" => [ "operation" ],
                "fields" => [ "operation_id", "operation_type" ]
            ]);

            Json::get( $headerStatus[200], $operations );

        break;

        case "getListERP":

            $operations = Model::getList( $dafel, (Object)[
                "tables" => [ "Operacao" ],
                "fields" => [
                    "IdOperacao",
                    "CdChamada",
                    "NmOperacao",
                    "TpOperacao"
                ],
                "filters" => [
                    [ "TpOperacao", "s", "in", @$post->TpOperacao ? $post->TpOperacao : NULL ]
                ]
            ]);

            Json::get( $headerStatus[200], $operations );

        break;

    }

?>