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

        case "edit":

            if( !@$post->person_id) headerResponse((Object)[ "code" => 417, "message" => "Pessoa não informada." ]);
            if( !@$post->address_code) headerResponse((Object)[ "code" => 417, "message" => "Código não informado." ]);
            if( !@$post->address_type) headerResponse((Object)[ "code" => 417, "message" => "Tipo do Logradouro não informado." ]);
            if( !@$post->address_public_place) headerResponse((Object)[ "code" => 417, "message" => "Pessoa não informada." ]);
            if( !@$post->address_number) headerResponse((Object)[ "code" => 417, "message" => "Número do endereço não informado." ]);
            if( $post->address_icms_type == 1 && !@$post->address_ie ) headerResponse((Object)[ "code" => 417, "message" => "Inscrição estadual não informada." ]);
            if( !@$post->district_id ) headerResponse((Object)[ "code" => 417, "message" => "Bairro não informado." ]);
            if( !@$post->city_id ) headerResponse((Object)[ "code" => 417, "message" =>  "Cidade não informado." ]);
            if( !@$post->uf_id ) headerResponse((Object)[ "code" => 417, "message" => "UF não informado." ]);
            if( !@$post->address_icms_type ) headerResponse((Object)[ "code" => 417, "message" => "Tipo de contribuição ICMS não informada." ]);
            if( !@$post->address_cep ) headerResponse((Object)[ "code" => 417, "message" => "CEP não informado." ]);

            $person = Model::get($dafel,(Object)[
                "tables" => [ "Pessoa (NoLock)" ],
                "fields" => [ "IdPessoa", "CdChamada", "NmPessoa", "CdCPF_CGC" ],
                "filters" => [[ "IdPessoa", "s", "=", $post->person_id ]]
            ]);

            Model::update($dafel,(Object)[
                "table" => "PessoaEndereco",
                "fields" => [
                    [ "StEnderecoEntrega", "s", @$post->address_main ? "S" : "N" ],
                    [ "NrInscricaoEstadual", "s", substr($post->address_ie,0,20) ],
                    [ "NmLogradouro", "s", strtoupper(substr($post->address_public_place,0,50)) ],
                    [ "NrLogradouro", "s", substr($post->address_number,0,10) ],
                    [ "DsComplemento", "s", @$post->address_reference ? substr($post->address_reference,0,50) : NULL ],
                    [ "TpLogradouro", "s", substr($post->address_type,0,5) ],
                    [ "CdCEP", "s", substr($post->address_cep,0,9) ],
                    [ "CdCPF_CGC", "s", $person->CdCPF_CGC ],
                    [ "IdCidade", "s", $post->city_id ],
                    [ "IdUF", "s", $post->uf_id ],
                    [ "NmPessoa", "s", $person->NmPessoa ],
                    [ "DsObservacao", "s", @$post->address_note ? strtoupper($post->address_note) : NULL ],
                    [ "TpContribuicaoICMS", "s", $post->address_icms_type ],
                    [ "VlLatitude", "d", @$post->address_lat ? (float)$post->address_lat : NULL ],
                    [ "VlLongitude", "d", @$post->address_lng ? (float)$post->address_lng : NULL ]
                ],
                "filters" => [
                    [ "IdPessoa", "s", "=", $person->IdPessoa ],
                    [ "CdEndereco", "s", "=", $post->address_code ]
                ]
            ]);

            $cep = Model::get($dafel,(Object)[
                "tables" => [ "CEP" ],
                "fields" => [ "CdCEP" ],
                "filters" => [[ "CdCEP", "s", "=", $post->address_cep ]]
            ]);

            if (!@$cep) {
                Model::insert($dafel,(Object)[
                    "table" => "CEP",
                    "fields" => [
                        [ "CdCEP", "s", $post->address_cep, "=" ],
                        [ "NmLogradouro", "s", strtoupper(substr($post->address_public_place,0,50)) ],
                        [ "IdBairro", "s", $post->district_id ],
                        [ "IdCidade", "s", $post->city_id ],
                        [ "IdUF", "s", $post->uf_id ],
                        [ "TpLogradouro", "s", strtoupper(substr($post->address_type,0,50)) ]
                    ]
                ]);
            }

            $updated = [];
            if( @$post->contacts ){

                $dataContacts = Model::getList($dafel,(Object)[
                    "tables" => [
                        "PessoaEndereco_Contato PEC",
                        "PessoaEndereco_TipoContato PETC"
                    ],
                    "fields" => [
                        "PEC.IdPessoaEndereco_Contato",
                        "PETC.IdTipoContato"
                    ],
                    "filters" => [
                        [ "PETC.IdPessoaEndereco_Contato = PEC.IdPessoaEndereco_Contato" ],
                        [ "PEC.IdPessoa", "s", "=", $person->IdPessoa ],
                        [ "PEC.CdEndereco", "s", "=", $post->address_code ]
                    ]
                ]);

                $oldContacts = [];
                $IdPessoaEndereco_Contato = NULL;
                foreach( $dataContacts as $item ){
                    $oldContacts[] = $item->IdTipoContato;
                    $IdPessoaEndereco_Contato = $item->IdPessoaEndereco_Contato;
                }

                foreach( $post->contacts as $contact ){
                    $contact = (Object)$contact;
                    $key = array_search($contact->address_contact_type_id,$oldContacts);
                    if( $key === FALSE ){
                        Model::insert($dafel,(Object)[
                            "table" => "PessoaEndereco_TipoContato",
                            "fields" => [
                                [ "IdPessoaEndereco_Contato", "s", $IdPessoaEndereco_Contato ],
                                [ "IdTipoContato", "s", $contact->address_contact_type_id ],
                                [ "IdPessoa", "s", $person->IdPessoa ],
                                [ "CdEndereco", "s", $post->address_code ],
                                [ "DsContato", "s", strtoupper(substr(removeSpecialChar($contact->address_contact_value),0,50)) ]
                            ]
                        ]);
                    } else {
                        unset($oldContacts[$key]);
                        Model::update($dafel,(Object)[
                            "table" => "PessoaEndereco_TipoContato",
                            "fields" => [[ "DsContato", "s", strtoupper(substr($contact->address_contact_value,0,50)) ]],
                            "filters" => [
                                [ "IdPessoaEndereco_Contato", "s", "=", $IdPessoaEndereco_Contato ],
                                [ "IdTipoContato", "s", "=", $contact->address_contact_type_id ],
                                [ "IdPessoa", "s", "=", $person->IdPessoa ],
                                [ "CdEndereco", "s", "=", $post->address_code ]
                            ]
                        ]);
                    }
                }
                if( sizeof($oldContacts)) {
                    Model::delete($dafel, (Object)[
                        "top" => 99,
                        "table" => "PessoaEndereco_TipoContato",
                        "filters" => [
                            ["IdTipoContato", "s", "in", $oldContacts],
                            ["IdPessoa", "s", "=", $person->IdPessoa],
                            ["CdEndereco", "s", $post->address_code],
                            ["IdPessoaEndereco_Contato", "s", "=", $IdPessoaEndereco_Contato]
                        ]
                    ]);
                }
            }

            postLog((Object)[
                "parent_id" => $person->IdPessoa
            ]);

            Json::get( $headerStatus[200] );
            
        break;

        case "insert":

            if( !@$post->person_id) headerResponse((Object)[ "code" => 417, "message" => "Pessoa não informada." ]);
            if( !@$post->address_type) headerResponse((Object)[ "code" => 417, "message" => "Tipo do Logradouro não informado." ]);
            if( !@$post->address_public_place) headerResponse((Object)[ "code" => 417, "message" => "Pessoa não informada." ]);
            if( !@$post->address_number) headerResponse((Object)[ "code" => 417, "message" => "Número do endereço não informado." ]);
            if( $post->address_icms_type == 1 && !@$post->address_ie ) headerResponse((Object)[ "code" => 417, "message" => "Inscrição estadual não informada." ]);
            if( !@$post->district_id ) headerResponse((Object)[ "code" => 417, "message" => "Bairro não informado." ]);
            if( !@$post->city_id ) headerResponse((Object)[ "code" => 417, "message" =>  "Cidade não informado." ]);
            if( !@$post->uf_id ) headerResponse((Object)[ "code" => 417, "message" => "UF não informado." ]);
            if( !@$post->address_icms_type ) headerResponse((Object)[ "code" => 417, "message" => "Tipo de contribuição ICMS não informada." ]);
            if( !@$post->address_cep ) headerResponse((Object)[ "code" => 417, "message" => "CEP não informado." ]);

            $person = Model::get($dafel,(Object)[
                "tables" => [ "Pessoa (NoLock)" ],
                "fields" => [ "IdPessoa", "CdChamada", "NmPessoa", "CdCPF_CGC" ],
                "filters" => [[ "IdPessoa", "s", "=", $post->person_id ]]
            ]);

            if( !@$person ){
                headerResponse((Object)[ "code" => 417, "message" => "O Id da pessoa não foi encontrado." ]);
            }

            $post->address_public_place = removeSpecialChar($post->address_public_place);
            $post->address_number = removeSpecialChar($post->address_number);
            if( @$post->address_note ) $post->address_note = removeSpecialChar($post->address_note);

            $code = Model::get($dafel,(Object)[
                "tables" => [ "PessoaEndereco" ],
                "fields" => [ "CdEndereco = MAX(CdEndereco)" ],
                "filters" => [[ "IdPessoa", "s", "=", $person->IdPessoa ]]
            ]);

            $address_code = 1;
            if( @$code ){
                $address_code = (int)$code->CdEndereco + 1;
            }
            $address_code = substr( "0{$address_code}", - 2 );

            Model::insert($dafel,(Object)[
                "table" => "PessoaEndereco",
                "fields" => [
                    [ "IdPessoa", "s", $person->IdPessoa ],
                    [ "CdEndereco", "s", $address_code ],
                    [ "StEnderecoPrincipal", "s", "N" ],
                    [ "StEnderecoEntrega", "s", "N" ],
                    [ "StEnderecoCobranca", "s", "N" ],
                    [ "StEnderecoResidencial", "s", "N" ],
                    [ "StEnderecoComercial", "s", "N" ],
                    [ "StEnderecoCorrespondencia", "s", "N" ],
                    [ "StCalculoSuframa", "s", "N" ],
                    [ "NrInscricaoEstadual", "s", substr($post->address_ie,0,20) ],
                    [ "NmLogradouro", "s", strtoupper(substr($post->address_public_place,0,50)) ],
                    [ "NrLogradouro", "s", substr($post->address_number,0,10) ],
                    [ "DsComplemento", "s", @$post->address_reference ? substr($post->address_reference,0,50) : NULL ],
                    [ "TpLogradouro", "s", substr($post->address_type,0,5) ],
                    [ "CdCEP", "s", substr($post->address_cep,0,9) ],
                    [ "IdBairro", "s", $post->district_id ],
                    [ "CdCPF_CGC", "s", $person->CdCPF_CGC ],
                    [ "IdCidade", "s", $post->city_id ],
                    [ "IdUF", "s", $post->uf_id ],
                    [ "NmPessoa", "s", $person->NmPessoa ],
                    [ "DsObservacao", "s", @$post->address_note ? strtoupper(removeSpecialChar($post->address_note)) : NULL ],
                    [ "StAtivo", "s", "S" ],
                    [ "IdPais", "s", "076" ],
                    //1: contribuinte ICMS, 2: contribuinte ISENTO, 9: não contribuinte
                    [ "TpContribuicaoICMS", "s", $post->address_icms_type ],
                    [ "VlLatitude", "d", @$post->address_lat ? (float)$post->address_lat : NULL ],
                    [ "VlLongitude", "d", @$post->address_lng ? (float)$post->address_lng : NULL ]
                ]
            ]);

            $cep = Model::get($dafel,(Object)[
                "tables" => [ "CEP" ],
                "fields" => [ "CdCEP" ],
                "filters" => [[ "CdCEP", "s", "=", $post->address_cep ]]
            ]);

            if( !@$cep ){
                Model::insert($dafel,(Object)[
                    "table" => "CEP",
                    "fields" => [
                        [ "CdCEP", "s", $post->address_cep, "=" ],
                        [ "NmLogradouro", "s", strtoupper(substr($post->address_public_place,0,50)) ],
                        [ "IdBairro", "s", $post->district_id ],
                        [ "IdCidade", "s", $post->city_id ],
                        [ "IdUF", "s", $post->uf_id ],
                        [ "TpLogradouro", "s", strtoupper(substr($post->address_type,0,50)) ]
                    ]
                ]);
            }

            if( @$post->contacts ){

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
                        [ "CdEndereco", "s", $address_code ],
                        [ "DsContato", "s", $person->NmPessoa ],
                        [ "StContatoPrincipal", "s", "S" ]
                    ]
                ]);

                foreach( $post->contacts as $contact ){
                    $contact = (Object)$contact;
                    if( @$contact->contact_type_id && @$contact->contact_value ){
                        Model::insert($dafel,(Object)[
                            "table" => "PessoaEndereco_TipoContato",
                            "fields" => [
                                [ "IdPessoaEndereco_Contato", "s", $personAddress->IdPessoaEndereco_Contato ],
                                [ "IdTipoContato", "s", $contact->contact_type_id ],
                                [ "IdPessoa", "s", $post->person_id ],
                                [ "CdEndereco", "s", $address_code ],
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
                "address_code" => $address_code
            ]);

        break;

        break;

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
                    "C.IdCidade",
                    "C.NmCidade",
                    "B.IdBairro",
                    "B.NmBairro",
                    "PE.CdCEP",
                    "PE.CdEndereco",
                    "StATivo=ISNULL(StATivo,'N')",
                    "PE.StEnderecoPrincipal",
                    "PE.TpLogradouro",
                    "PE.TpContribuicaoICMS",
                    "PE.NmLogradouro",
                    "PE.NrLogradouro",
                    "PE.DsObservacao",
                    "PE.NrInscricaoEstadual",
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