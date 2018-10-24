<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $dafel, $login, $config, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    switch( $get->action )
    {
        case "get":

            if( (!@$post->person_id && !@$post->person_code) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $person = Model::get($dafel,(Object)[
                "class" => "Person",
                "join" => 1,
                "tables" => [
                    "Pessoa P (NoLock)",
                    "INNER JOIN PessoaCategoria PC (NoLock) ON(PC.IdPessoa = P.IdPessoa)",
                    "LEFT JOIN PessoaComplementar PCM (NoLock) ON(PCM.IdPessoa = P.IdPessoa)",
                ],
                "fields" => [
                    "P.IdPessoa",
                    "P.CdChamada",
                    "P.NmPessoa",
                    "P.NmCurto",
                    "P.CdCPF_CGC",
                    "P.TpPessoa",
                    "StATivo=ISNULL(PC.StATivo,'N')",
                    "DtNascimento=CONVERT(VARCHAR(10),PCM.DtNascimento,126)",
                    "PCM.TpSexo",
                    "VlLimiteCredito=ISNULL(PCM.VlLimiteCredito,0)",

                ],
                "filters" => [
                    [ "PC.IdCategoria", "s", "=", $config->person->client_category_id ],
                    [ "P.IdPessoa", "s", "=", @$post->person_id ? $post->person_id : NULL ],
                    [ "P.CdChamada", "s", "=", @$post->person_code ? substr("00000{$post->person_code}", -6) : NULL ]
                ]
            ]);

            if( !@$person ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Pessoa não encontrada."
                ]);
            }

            $person->person_address = [];
            foreach( $person->address as $address ){
                $person->person_address[] = (Object)[
                    "person_address_cep" => $address->address_cep,
                    "person_address_code" => $address->address_code,
                    "person_address_type" => $address->address_type,
                    "person_address_public_place" => $address->address_public_place,
                    "person_address_number" => $address->address_number,
                    "uf" => (Object)[
                        "uf_id" => $address->uf_id
                    ],
                    "city" => (Object)[
                        "uf_id" => $address->uf_id,
                        "city_name" => $address->city_name
                    ],
                    "district" => (Object)[
                        "district_name" => $address->district_name
                    ]
                ];
            }
            unset($person->address);

            Json::get( $headerStatus[200], $person );

        break;

        case "getList":

            if( !@$post->person_name ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $tables = [ "Pessoa P (NoLock)", "PessoaCategoria PC (NoLock)" ];
            $fields = [
                "person_id=P.IdPessoa",
                "person_code=P.CdChamada",
                "person_name=P.NmPessoa",
                "person_short_name=P.NmCurto",
                "person_document=P.CdCPF_CGC",
                "person_type=P.TpPessoa",
                "person_active=PC.StATivo"
            ];
            $filters = [
                [ "P.IdPessoa = PC.IdPessoa" ],
                [ "LEN(P.CdChamada) > 0" ],
                [ "LEN(P.NmPessoa) > 0" ],
                [ "PC.IdCategoria", "s", "=", $config->person->client_category_id ]
            ];
            $group = [ "P.IdPessoa", "P.CdChamada", "P.NmPessoa", "P.NmCurto", "P.CdCPF_CGC", "P.TpPessoa", "PC.StATivo" ];

            if( @$post->person_address && $post->person_address == "Y" ){

                $tables[] = "PessoaEndereco PE (NoLock)";
                $tables[] = "Cidade C (NoLock)";
                $tables[] = "Bairro B (NoLock)";

                $fields[] = "address_cep=PE.CdCEP";
                $fields[] = "address_type=PE.TpLogradouro";
                $fields[] = "address_public_place=PE.NmLogradouro";
                $fields[] = "address_number=PE.NrLogradouro";
                $fields[] = "uf_code=C.IdUF";
                $fields[] = "city_name=C.NmCidade";
                $fields[] = "district_name=B.NmBairro";

                $filters[] = [ "PE.IdBairro = B.IdBairro" ];
                $filters[] = [ "PE.IdCidade = C.IdCidade" ];
                $filters[] = [ "P.IdPessoa = PE.IdPessoa" ];
                $filters[] = [ "PE.StEnderecoPrincipal", "s", "=", "S" ];

                $group = array_merge( $group, [ "PE.CdCEP", "PE.TpLogradouro", "PE.NmLogradouro", "PE.NrLogradouro", "C.IdUF", "C.NmCidade", "B.NmBairro" ] );
            }

            if( @$post->person_contact ){
                $tables[] = "PessoaEndereco_TipoContato PETC (NoLock)";
                $filters[] = [ "P.IdPessoa = PETC.IdPessoa" ];
            }

            if( @$post->person_name )
                $filters[] = [
                    [ "P.CdChamada", "s", "LIKE", @$post->person_name ? "%{$post->person_name}%" : NULL ],
                    [ "P.NmPessoa", "s", "LIKE", @$post->person_name ? "%{$post->person_name}%" : NULL ],
                    [ "P.NmCurto", "s", "LIKE", @$post->person_name ? "%{$post->person_name}%" : NULL ]
                ];

            if( @$post->person_doc )
                $filters[] = ["REPLACE(REPLACE(REPLACE(P.CdCPF_CGC,'.',''),'-',''),'/','')", "s", "LIKE", str_replace([".", "-", "/"], ["", "", ""], $post->person_doc) . "%" ];

            if( @$post->person_contact )
                $filters[] = ["REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(PETC.DsContato,')',''),'(',''),'.',''),'-',''),'/','')", "s", "LIKE", "%" . str_replace( ["(",")","-",".","/"], ["","","","",""], $post->person_contact ) . "%"];

            $people = Model::getList($dafel,(Object)[
                "top" => @$post->limit ? $post->limit : 200,
                "tables" => $tables,
                "fields" => $fields,
                "filters" => $filters,
                "group" =>  implode( ", ", $group )
            ]);

            foreach( $people as $person ){
                $person->person_active = $person->person_active == "S" ? "Y" : "N";
            }

            Json::get( $headerStatus[200], $people );

        break;

    }

?>