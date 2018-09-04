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