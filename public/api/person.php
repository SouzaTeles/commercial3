<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action )
    {
        case "active":

            if( !@$post->person_id || !@$post->person_category_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $person = Model::get($dafel,(Object)[
                "tables" => [ "Pessoa P (NoLock)", "PessoaCategoria PC (NoLock)" ],
                "fields" => [ "P.IdPessoa", "P.CdChamada", "P.NmPessoa", "P.CdCPF_CGC" ],
                "filters" => [
                    [ "P.IdPessoa = PC.IdPessoa" ],
                    [ "P.IdPessoa", "s", "=", $post->person_id ],
                    [ "PC.IdCategoria", "s", "=", $post->person_category_id ]
                ]
            ]);

            if (!@$person) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "A pessoa não foi localizada."
                ]);
            }

            if( !@$person->CdCPF_CGC ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O cadastro do cliente está sem documento. Verifique."
                ]);
            }

            $people = Model::getList($dafel,(Object)[
                "tables" => [ "Pessoa (NoLock)" ],
                "fields" => [ "CdChamada", "NmPessoa" ],
                "filters" => [
                    [ "IdPessoa", "s", "!=", $post->person_id ],
                    [ "REPLACE(REPLACE(REPLACE(CdCPF_CGC,'.',''),'-',''),'/','')", "s", "=", str_replace( [".", "-", "/"], ["", "", ""], $person->CdCPF_CGC )]
                ]
            ]);

            if( sizeof($people) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O documento do cliente está duplicado. Não será possível ativar o cliente."
                ]);
            }

            Model::update($dafel,(Object)[
                "table" => "PessoaCategoria",
                "fields" => [[ "StAtivo", "s", "S" ]],
                "filters" => [
                    [ "IdCategoria", "s", "=", $post->person_category_id ],
                    [ "IdPessoa", "s", "=", $post->person_id ]
                ]
            ]);

            Model::update($dafel,(Object)[
                "table" => "PessoaComplementar",
                "fields" => [[ "VlLimiteCredito", "d", "0" ]],
                "filters" => [[ "IdPessoa", "s", "=", $post->person_id ]]
            ]);

            postLog((Object)[
                "item_id" => $post->person_id
            ]);

            Json::get( $headerStatus[200] );

        break;

        case "get":

            if( (!@$post->person_id && !@$post->person_code) || !@$post->person_category_id ){
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
                    [ "PC.IdCategoria", "s", "=", $post->person_category_id ],
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

            Json::get( $headerStatus[200], $person );

        break;

        case "getList":

            if( !@$post->person_category_id ){
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
                [ "PC.IdCategoria", "s", "=", $post->person_category_id ],
                [ "PC.StAtivo", "s", "=", ($post->person_active == "Y" ? "S" : NULL) ]
            ];
            $group = [ "P.IdPessoa", "P.CdChamada", "P.NmPessoa", "P.NmCurto", "P.CdCPF_CGC", "P.TpPessoa", "PC.StATivo" ];

            if( $post->person_address == "Y" ){

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
                "top" => 200,
                "tables" => $tables,
                "fields" => $fields,
                "filters" => $filters,
                "group" =>  implode( ", ", $group )
            ]);

            foreach( $people as $person ){
                $person->person_active = $person->person_active == "S" ? "Y" : "N";
                $person->image = getImage((Object)[
                    "image_id" => $person->person_id,
                    "image_dir" => "person"
                ]);
            }

            Json::get( $headerStatus[200], $people );

        break;

        case "typeahead":

            if( !@$post->limit || !@$post->person_name || !@$post->person_category_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $people = Model::getList($dafel,(Object)[
                "top" => $post->limit,
                "join" => 1,
                "tables" => [
                    "Pessoa P",
                    "INNER JOIN PessoaCategoria PC ON(PC.IdPessoa = P.IdPessoa)"
                ],
                "fields" => [
                    "P.IdPessoa",
                    "P.CdChamada",
                    "P.NmPessoa"
                ],
                "filters" => [
                    [ "PC.IdCategoria", "s", "=", $post->person_category_id ],
                    [ "P.NmPessoa", "s", "like", "{$post->person_name}%" ]
                ],
                "order" => "P.NmPessoa"
            ]);

            $data = [];
            foreach( $people as $person ){
                $person->Imagem = getImage((Object)[
                    "image_id" => $person->IdPessoa,
                    "image_dir" => "person"
                ]);
                $data[] = (Object)[
                    "item_id" => $person->IdPessoa,
                    "item_code" => $person->CdChamada,
                    "item_name" => $person->NmPessoa,
                    "item_image" => $person->Imagem,
                    "html" => (
                        "<div class='type-ahead-cover'" . ( @$person->Imagem ? ( " style='background-image:url({$person->Imagem})'" ) : "" ) . "></div>" .
                        "<b>{$person->NmPessoa}</b><br/>" .
                        "Cd. {$person->CdChamada}"
                    )
                ];
            };

            Json::get( $headerStatus[200], $data );

        break;

    }

?>