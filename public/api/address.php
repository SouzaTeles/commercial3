<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Nenhuma ação informada na requisição."
        ]);
    }

    switch( $get->action )
    {

        case "getList":

            if( !@$post->person_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "Parâmetro GET não localizado."
                ]);
            }

            $address = Model::getList($dafel,(Object)[
                "join" => 1,
                "class" => "PersonAddress",
                "tables" => [
                    "PessoaEndereco PE (NoLock)",
                    "INNER JOIN Cidade C (NoLock) ON(C.IdCidade = PE.IdCidade)",
                    "INNER JOIN Bairro B (NoLock) ON(B.IdBairro = PE.IdBairro)"
                ],
                "fields" => [
                    "PE.IdPessoa",
                    "PE.IdUF",
                    "C.NmCidade",
                    "B.NmBairro",
                    "PE.CdCEP",
                    "PE.CdEndereco",
                    "StATivo=ISNULL(StATivo,'N')",
                    "PE.StEnderecoPrincipal",
                    "PE.TpLogradouro",
                    "PE.NmLogradouro",
                    "PE.NrLogradouro",
                    "PE.DsObservacao"
                ],
                "filters" => [[ "PE.IdPessoa", "s", "=", $post->person_id ]]
            ]);

            Json::get( $headerStatus[200], $address );

        break;

        case "geocode":

            if( !@$post->address_cep ){
                headerResponse((Object)[
                    "code" => 417,
                    "Parâmetro GET não localizado."
                ]);
            }

            $url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyA4we-5aCbXOqPvzcbUJW49x46LXnhwdbY&address={$post->address_cep}";
            $google = file_get_contents($url);
            $google = json_decode($google, TRUE);

            if( !@$google['results'][0] ){
                headerResponse((Object)[
                    "code" => 417,
                    "O google não encontrou as coordenadas para o cep: {$post->address_cep}."
                ]);
            }

            $result = $google['results'][0];
            if( @$result['geometry']['location'] ){
                $address_lat = $result['geometry']['location']['lat'];
                $address_lng = $result['geometry']['location']['lng'];
            } else {
                headerResponse((Object)[
                    "code" => 417,
                    "O google não encontrou as coordenadas para o cep: {$post->address_cep}."
                ]);
            }

            Json::get( $headerStatus[200], (Object)[
                "lat" => $address_lat,
                "lng" => $address_lng
            ]);

        break;

    }