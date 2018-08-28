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
                    "PE.NrLogradouro"
                ],
                "filters" => [[ "PE.IdPessoa", "s", "=", $post->person_id ]]
            ]);

            Json::get( $headerStatus[200], $address );

        break;

    }