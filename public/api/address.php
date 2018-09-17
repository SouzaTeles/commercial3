<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Nenhuma ação informada na requisição."
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
                    "PE.DsObservacao",
                    "VlLatitude=CAST(PE.VlLatitude AS FLOAT)",
                    "VlLongitude=CAST(PE.VlLongitude AS FLOAT)"
                ],
                "filters" => [[ "PE.IdPessoa", "s", "=", $post->person_id ]]
            ]);

            Json::get( $headerStatus[200], $address );

        break;

        case "geocode":

            if( !@$post->address_cep ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro GET não localizado."
                ]);
            }

            $url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyA4we-5aCbXOqPvzcbUJW49x46LXnhwdbY&address={$post->address_cep}";
            $google = file_get_contents($url);
            $google = json_decode($google, TRUE);

            if( !@$google['results'][0] ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O google não encontrou as coordenadas para o cep: {$post->address_cep}."
                ]);
            }

            $result = $google['results'][0];
            if( !@$result['geometry']['location'] ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O google não encontrou as coordenadas para o cep: {$post->address_cep}."
                ]);
            }

            $address_lat = $result['geometry']['location']['lat'];
            $address_lng = $result['geometry']['location']['lng'];

            Json::get( $headerStatus[200], (Object)[
                "lat" => $address_lat,
                "lng" => $address_lng
            ]);

        break;

        case "main":

            if( !@$post->person_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($dafel,(Object)[
                "table" => "PessoaEndereco",
                "fields" => [[ "StEnderecoPrincipal", "s", "N" ]],
                "filters" => [[ "IdPessoa", "s", "=", $post->person_id ]]
            ]);

            Model::update($dafel,(Object)[
                "table" => "PessoaEndereco",
                "fields" => [[ "StEnderecoPrincipal", "s", "S" ]],
                "filters" => [
                    [ "IdPessoa", "s", "=", $post->person_id ],
                    [ "CdEndereco", "s", "=", $post->address_code ]
                ]
            ]);

            postLog((Object)[
                "parent_id" => $post->person_id
            ]);

            Json::get($headerStatus[200],(Object)[
                "message" => "Endereço atualizado com sucesso!"
            ]);

        break;

        case "getContactTypes":

            $contactTypes = Model::getList($dafel,(Object)[
                "tables" => [ "TipoContato (NoLock)" ],
                "fields" => [
                    "contact_type_id=IdTipoContato",
                    "contact_type_code=CdChamada",
                    "contact_type_name=NmTipoContato"
                ],
                "order" => "CdPrioridade"
            ]);

            Json::get( $headerStatus[200], $contactTypes );

        break;

        case "getAddressTypes":

            $addressTypes = Model::getList($dafel,(Object)[
                "tables" => [ "InformacaoGeral (NoLock)" ],
                "fields" => [ "address_type=DsInformacaoGeral" ],
                "filters" => [
                    [ "LEN(DsInformacaoGeral) > 0" ],
                    [ "NmCombo", "s", "=", "Tipo Logradouro" ]
                ]
            ]);

            Json::get( $headerStatus[200], $addressTypes );

        break;

    }