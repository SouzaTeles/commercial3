<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    switch( $get->action ) {

        case "getList":

            $prices = Model::getList( $dafel, (Object)[
                "tables" => [ "Preco" ],
                "fields" => [
                    "price_id=IdPreco",
                    "price_code=CdPreco",
                    "price_name=NmPreco"
                ],
                "order" => "CdPreco"
            ]);

            Json::get( $headerStatus[200], $prices );

        break;

        case "getListERP":

            $prices = Model::getList( $dafel, (Object)[
                "tables" => [ "Preco" ],
                "fields" => [ "IdPreco", "CdPreco", "NmPreco" ]
            ]);

            Json::get( $headerStatus[200], $prices );

        break;

    }

?>