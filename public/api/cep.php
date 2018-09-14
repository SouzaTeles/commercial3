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

    switch( $get->action )
    {

        case "get":

            if( !@$post->cep_code ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não localizado."
                ]);
            }

            $cep = Model::get($dafel,(Object)[
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
                    [ "CEP.IdUF = UF.IdUF" ],
                    [ "CEP.IdCidade = C.IdCidade" ],
                    [ "CEP.IdBairro = B.IdBairro" ],
                    [ "CEP.CdCEP", "s", "=", $post->cep_code ]
                ]
            ]);

            if( !@$cep ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "CEP não encontrado."
                ]);
            }

            Json::get( $headerStatus[200], $cep );

        break;

    }