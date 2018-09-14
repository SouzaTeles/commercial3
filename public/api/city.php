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

        case "typeahead":

            if( !@$post->city_name ){
                headerResponse((Object)[
                    "code" => 417,
                    "Parâmetro POST não localizado."
                ]);
            }

            $post->city_name = removeSpecialChar($post->city_name);

            $cities = Model::getList($dafel,(Object)[
                "tables" => [ "Cidade (NoLock)" ],
                "top" => @$post->limit ? $post->limit : 100,
                "fields" => [
                    "IdCidade",
                    "IdUF",
                    "CdChamada",
                    "NmCidade",
                    "CdIBGE",
                    "CdDDD"
                ],
                "filters" => [[ "NmCidade", "s", "LIKE", "{$post->city_name}%" ]],
                "order" => "NmCidade"
            ]);

            $ret = [];
            foreach( $cities as $city ){
                $city->NmCidade = removeSpecialChar($city->NmCidade);
                $ret[] = (Object)[
                    "uf_id" => $city->IdUF,
                    "item_id" => $city->IdCidade,
                    "item_name" => "{$city->NmCidade} - {$city->IdUF}",
                    "city_code" => $city->CdChamada,
                    "city_ibge" => $city->CdIBGE,
                    "city_ddd" => $city->CdDDD,
                    "html" => (
                        "<b>{$city->NmCidade}</b><br/>" .
                        "<span>UF: {$city->IdUF}</span>" .
                        ( @$city->CdIBGE ? " / <span>IBGE: {$city->CdIBGE}</span>" : "" ) .
                        ( @$city->CdDDD ? " / <span>DDD: {$city->CdDDD}</span>" : "" )
                    )
                ];
            }

            Json::get( $headerStatus[200], $ret );

        break;

    }