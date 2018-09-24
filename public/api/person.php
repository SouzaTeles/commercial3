<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $headerStatus, $get, $post, $config;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não localizado."
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
                "parent_id" => $post->person_id
            ]);

            Json::get( $headerStatus[200] );

        break;

        case "checkDocument":

            if( !@$post->document ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não localizado."
                ]);
            }

            $data = Model::getList($dafel,(Object)[
                "join" => 1,
                "tables" => [
                    "Pessoa P (NoLock)",
                    "INNER JOIN PessoaCategoria PC (NoLock) ON(PC.IdPessoa = P.IdPessoa)"
                ],
                "fields" => [
                    "person_id=P.IdPessoa",
                    "person_code=P.CdChamada",
                    "person_name=P.NmPessoa",
                    "person_type=P.TpPessoa",
                    "person_active=PC.StAtivo"
                ],
                "filters" => [
                    [ "P.CdCPF_CGC", "s", "=", $post->document ],
                    [ "PC.IdCategoria", "s", "=", $config->person->client_category_id ]
                ]
            ]);

            Json::get( $headerStatus[200], $data );

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

        case "insert":

            if( !@$post->person_name ) headerResponse((Object)[ "code" => 417, "message" => "Nome da Pessoa não informado." ]);
            if( !@$post->person_type ) headerResponse((Object)[ "code" => 417, "message" => "Tipo de pessoa não informada." ]);
            if( !@$post->person_document ) headerResponse((Object)[ "code" => 417, "message" => "CPF ou CNPJ não informado." ]);
            if( !@$post->person_categories ) headerResponse((Object)[ "code" => 417, "message" => "Categoria não informada." ]);

            $address = (Object)$post->address;
            if( !@$address->address_type) headerResponse((Object)[ "code" => 417, "message" => "Tipo do Logradouro não informado." ]);
            if( !@$address->address_public_place) headerResponse((Object)[ "code" => 417, "message" => "Pessoa não informada." ]);
            if( !@$address->address_number) headerResponse((Object)[ "code" => 417, "message" => "Número do endereço não informado." ]);
            if( $address->address_icms_type == 1 && !@$address->address_ie ) headerResponse((Object)[ "code" => 417, "message" => "Inscrição estadual não informada." ]);
            if( !@$address->district_id ) headerResponse((Object)[ "code" => 417, "message" => "Bairro não informado." ]);
            if( !@$address->city_id ) headerResponse((Object)[ "code" => 417, "message" =>  "Cidade não informado." ]);
            if( !@$address->uf_id ) headerResponse((Object)[ "code" => 417, "message" => "UF não informado." ]);
            if( !@$address->address_icms_type ) headerResponse((Object)[ "code" => 417, "message" => "Tipo de contribuição ICMS não informada." ]);
            if( !@$address->address_cep ) headerResponse((Object)[ "code" => 417, "message" => "CEP não informado." ]);

            $post->person_name = removeSpecialChar($post->person_name);
            if( @$post->person_short_name ) $post->person_short_name = removeSpecialChar($post->person_short_name);

            $address->address_public_place = removeSpecialChar($address->address_public_place);
            $address->address_number = removeSpecialChar($address->address_number);
            if( @$address->address_note ) $address->address_note = removeSpecialChar($address->address_note);

            $person = Model::get($dafel,(Object)[
                "tables" => [ "Pessoa (NoLock)" ],
                "fields" => [ "CdChamada", "NmPessoa" ],
                "filters" => [[ "REPLACE(REPLACE(REPLACE(CdCPF_CGC,'.',''),'-',''),'/','')", "s", "=", str_replace([".", "-", "/"], ["", "", ""], $post->person_document) ]]
            ]);

            if (@$person) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => (
                        "O CPF/CNPJ já está cadastrado no seguinte cliente: " .
                        "<b>{$person->CdChamada} - {$person->NmPessoa}</b><br/><br/>" .
                        "Verifique."
                    )
                ]);
            }

            $person = (Object)[
                "IdPessoa" => Model::nextCode($dafel,(Object)[
                    "table" => "Pessoa",
                    "field" => "IdPessoa",
                    "increment" => "S",
                    "base36encode" => 1
                ]),
                "CdChamada" => Model::nextCode($dafel,(Object)[
                    "table" => "Pessoa",
                    "field" => "CdChamada",
                    "increment" => "S"
                ])
            ];

            Model::insert($dafel,(Object)[
                "table" => "Pessoa",
                "fields" => [
                    [ "IdPessoa", "s", $person->IdPessoa ],
                    [ "CdChamada", "s", $person->CdChamada ],
                    [ "TpPessoa", "s", $post->person_type ],
                    [ "CdCPF_CGC", "s", $post->person_document ],
                    [ "NmPessoa", "s", strtoupper(substr($post->person_name,0,50)) ],
                    [ "NmCurto", "s", @$post->person_short_name ? strtoupper(substr($post->person_short_name,0,20)) : NULL ],
                ]
            ]);

            Model::insert($dafel,(Object)[
                "table" => "PessoaComplementar",
                "fields" => [
                    [ "IdPessoa", "s", $person->IdPessoa ],
                    [ "DtNascimento", "s", @$post->person_birth ? $post->person_birth : NULL ],
                    [ "TpSexo", "s", @$post->person_gender ? $post->person_gender : NULL ],
                    [ "DtUltimaAlteracao", "s", date("Y-m-d") ],
                    [ "VlCapitalRegistrado", "d", "0" ],
                    [ "VlCapitalAtual", "d", "0" ],
                    [ "VlCapitalGiro", "d", "0" ],
                    [ "VlEstoque", "d", "0" ],
                    [ "VlFaturamentoAnual", "s", "0" ],
                    [ "AlIRRF", "s", "0" ],
                    [ "NrFuncionarios", "s", "0" ],
                    [ "TpEstabelecimento", "s", "1" ],
                    [ "StTributosContribuicoes", "s", "N" ],
                    [ "StTributosContribQualquerValor", "s", "N" ],
                    [ "StCobraTaxaBancaria", "s", "S" ],
                    [ "StEnvioBoletoAutomatico", "s", "N" ],
                    [ "StPrestadoraServico", "s", "N" ],
                    [ "VlLimiteCredito", "s", "0" ],
                    [ "VlLimiteCreditoParcela", "s", "0" ],
                    [ "StSubstitutoTributario", "s", "0" ],
                    [ "StAdministracaoFederal", "s", "N" ]
                ]
            ]);

            foreach( explode(",", $post->person_categories) as $category_id ){
                $category = (Object)[
                    "IdPessoaCategoria" => Model::nextCode($dafel,(Object)[
                        "table" => "PessoaCategoria",
                        "field" => "IdPessoaCategoria",
                        "increment" => "S",
                        "base36encode" => 1
                    ])
                ];
                Model::insert($dafel,(Object)[
                    "table" => "PessoaCategoria",
                    "fields" => [
                        [ "IdPessoaCategoria", "s", $category->IdPessoaCategoria ],
                        [ "IdPessoa", "s", $person->IdPessoa ],
                        [ "IdCategoria", "s", $category_id ],
                        [ "DtCadastro", "s", date("Y-m-d H:i:s") ],
                        [ "StAtivo", "s", "S" ]
                    ]
                ]);
            }

            Model::insert($dafel,(Object)[
                "table" => "PessoaEndereco",
                "fields" => [
                    [ "IdPessoa", "s", $person->IdPessoa ],
                    [ "CdEndereco", "s", "01" ],
                    [ "StEnderecoPrincipal", "s", "S" ],
                    [ "StEnderecoEntrega", "s", "S" ],
                    [ "StEnderecoCobranca", "s", "S" ],
                    [ "StEnderecoResidencial", "s", "S" ],
                    [ "StEnderecoComercial", "s", "S" ],
                    [ "StEnderecoCorrespondencia", "s", "S" ],
                    [ "StCalculoSuframa", "s", "N" ],
                    [ "NrInscricaoEstadual", "s", substr($address->address_ie,0,20) ],
                    [ "NmLogradouro", "s", strtoupper(substr($address->address_public_place,0,50)) ],
                    [ "NrLogradouro", "s", substr($address->address_number,0,10) ],
                    [ "DsComplemento", "s", @$address->address_reference ? substr($address->address_reference,0,50) : NULL ],
                    [ "TpLogradouro", "s", substr($address->address_type,0,5) ],
                    [ "CdCEP", "s", substr($address->address_cep,0,9) ],
                    [ "IdBairro", "s", $address->district_id ],
                    [ "CdCPF_CGC", "s", $post->person_document ],
                    [ "IdCidade", "s", $address->city_id ],
                    [ "IdUF", "s", $address->uf_id ],
                    [ "NmPessoa", "s", strtoupper(substr($post->person_name,0,50)) ],
                    [ "DsObservacao", "s", @$address->address_note ? strtoupper($address->address_note) : NULL ],
                    [ "StAtivo", "s", "S" ],
                    [ "IdPais", "s", "076" ],
                    //1: contribuinte ICMS, 2: contribuinte ISENTO, 9: não contribuinte
                    [ "TpContribuicaoICMS", "s", $address->address_icms_type ],
                    [ "VlLatitude", "d", @$address->address_lat ? (float)$address->address_lat : NULL ],
                    [ "VlLongitude", "d", @$address->address_lng ? (float)$address->address_lng : NULL ]
                ]
            ]);

            $cep = Model::get($dafel,(Object)[
                "tables" => [ "CEP" ],
                "fields" => [ "CdCEP" ],
                "filters" => [[ "CdCEP", "s", "=", $address->address_cep ]]
            ]);

            if( !@$cep ){
                Model::insert($dafel,(Object)[
                    "table" => "CEP",
                    "fields" => [
                        [ "CdCEP", "s", $address->address_cep, "=" ],
                        [ "NmLogradouro", "s", strtoupper(substr($address->address_public_place,0,50)) ],
                        [ "IdBairro", "s", $address->district_id ],
                        [ "IdCidade", "s", $address->city_id ],
                        [ "IdUF", "s", $address->uf_id ],
                        [ "TpLogradouro", "s", strtoupper(substr($address->address_type,0,50)) ]
                    ]
                ]);
            }

            if( @$address->contacts ){

                $personAddress = (Object)[
                    "IdPessoaEndereco_Contato" => Model::nextCode($dafel,(Object)[
                        "table" => "PessoaEndereco_Contato",
                        "field" => "IdPessoaEndereco_Contato",
                        "increment" => "S",
                        "base36encode" => 1
                    ])
                ];

                Model::insert($dafel,(Object)[
                    "table" => "PessoaEndereco_Contato",
                    "fields" => [
                        [ "IdPessoaEndereco_Contato", "s", $personAddress->IdPessoaEndereco_Contato ],
                        [ "IdPessoa", "s", $person->IdPessoa ],
                        [ "CdEndereco", "s", "01" ],
                        [ "DsContato", "s", strtoupper(substr(explode(" ",$post->person_name)[0],0,50)) ],
                        [ "StContatoPrincipal", "s", "S" ]
                    ]
                ]);

                foreach( $address->contacts as $contact ){
                    $contact = (Object)$contact;
                    if( @$contact->contact_type_id && @$contact->contact_value ){
                        Model::insert($dafel,(Object)[
                            "table" => "PessoaEndereco_TipoContato",
                            "fields" => [
                                [ "IdPessoaEndereco_Contato", "s", $personAddress->IdPessoaEndereco_Contato ],
                                [ "IdTipoContato", "s", $contact->contact_type_id ],
                                [ "IdPessoa", "s", $person->IdPessoa ],
                                [ "CdEndereco", "s", "01" ],
                                [ "DsContato", "s", strtoupper(substr($contact->contact_value,0,50)) ],
                                [ "DsObservacao", "s", @$contact->contact_note ? $contact->contact_note : NULL ]
                            ]
                        ]);
                    }
                }
            }

            postLog((Object)[
                "parent_id" => $person->IdPessoa
            ]);

            Json::get( $headerStatus[200], (Object)[
                "person_id" => $person->IdPessoa,
                "person_code" => $person->CdChamada
            ]);

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
                    [
                        [ "P.NmPessoa", "s", "like", "{$post->person_name}%" ],
                        [ "P.NmCurto", "s", "like", "{$post->person_name}%" ]
                    ]
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

        case "electronWebcam":

            if( !@$post->person_id || !@$post->image ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $file = PATH_FILES . "person/{$post->person_id}.jpeg";
            file_put_contents( $file, pack('H*',$post->image) );

            Json::get( $headerStatus[200], (Object)[
                "image" => URI_FILES . "person/{$post->person_id}.jpeg?" . rand(100000,999999)
            ]);

        break;

    }

?>