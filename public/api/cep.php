<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $config, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action ) {

        case "get":

            if (!@$post->cep_code) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não localizado."
                ]);
            }

            $cep = Model::get($dafel, (Object)[
                "class" => "CEP",
                "tables" => [
                    "CEP CEP (NoLock)",
                    "UF UF (NoLock)",
                    "Cidade C (NoLock)",
                    "Bairro B (NoLock)"
                ],
                "fields" => [
                    "CEP.CdCEP",
                    "UF.IdUF",
                    "C.IdCidade",
                    "B.IdBairro",
                    "UF.NmUF",
                    "C.NmCidade",
                    "B.NmBairro",
                    "CEP.NmLogradouro",
                    "CEP.TpLogradouro"
                ],
                "filters" => [
                    ["CEP.IdUF = UF.IdUF"],
                    ["CEP.IdCidade = C.IdCidade"],
                    ["CEP.IdBairro = B.IdBairro"],
                    ["CEP.CdCEP", "s", "=", $post->cep_code]
                ]
            ]);

            if (!@$cep) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "CEP não encontrado."
                ]);
            }

            if( @$cep->cep_code && strlen($cep->cep_code) == 9){
                $url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyA4we-5aCbXOqPvzcbUJW49x46LXnhwdbY&address={$cep->cep_code}";
                $google = file_get_contents($url);
                $google = json_decode($google, TRUE);
                if (@$google['results'][0]) {
                    $result = $google['results'][0];
                    if (@$result['geometry']['location']) {
                        $cep->address_lat = $result['geometry']['location']['lat'];
                        $cep->address_lng = $result['geometry']['location']['lng'];
                    }
                }
            }

            Json::get( $headerStatus[200], $cep );

        break;

        case "getList":

            $cep = Model::getList($dafel,(Object)[
                "class" => "CEP",
                "top" => 100,
                "tables" => [
                    "CEP CEP (NoLock)",
                    "UF UF (NoLock)",
                    "Cidade C (NoLock)",
                    "Bairro B (NoLock)"
                ],
                "fields" => [
                    "CEP.CdCEP",
                    "UF.IdUF",
                    "C.IdCidade",
                    "B.IdBairro",
                    "UF.NmUF",
                    "C.NmCidade",
                    "B.NmBairro",
                    "CEP.NmLogradouro",
                    "CEP.TpLogradouro"
                ],
                "filters" => [
                    [ "CEP.IdUF = UF.IdUF" ],
                    [ "CEP.IdCidade = C.IdCidade" ],
                    [ "CEP.IdBairro = B.IdBairro" ],
                    [ "CEP.CdCEP", "s", "=", @$post->cep_code ? $post->cep_code : NULL ],
                    [ "UF.IdUF", "s", "=", @$post->uf_id ? $post->uf_id : NULL ],
                    [ "UF.NmUF", "s", "LIKE", @$post->uf_name ? "{$post->uf_name}%" : NULL ],
                    [ "C.IdCidade", "s", "=", @$post->city_id ? $post->city_id : NULL ],
                    [ "C.NmCidade", "s", "LIKE", @$post->city_name ? removeSpecialChar($post->city_name) . "%" : NULL ],
                    [ "B.IdBairro", "s", "=", @$post->district_id ? $post->district_id : NULL ],
                    [ "B.NmBairro", "s", "LIKE", @$post->district_name ? removeSpecialChar($post->district_name) . "%" : NULL ],
                    [ "CEP.NmLogradouro", "s", "LIKE", @$post->public_place ? "{$post->public_place}%" : NULL ]
                ]
            ]);

            Json::get( $headerStatus[200], $cep );

        break;

    }