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

            if( !@$post->term_id && !@$post->term_code ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $term = Model::get($dafel,(Object)[
                "class" => "Term",
                "tables" => [ "Prazo" ],
                "fields" => [
                    "IdPrazo",
                    "CdChamada",
                    "DsPrazo",
                    "NrParcelas",
                    "NrDiasEntrada",
                    "NrDias1aParcela",
                    "NrDiasEntreParcelas",
                    "StAtivo",
                    "AlEntrada"
                ],
                "filters" => [
                    [ "IdPrazo", "s", "=", @$post->term_id ? $post->term_id : NULL ],
                    [ "CdChamada", "s", "=", @$post->term_code ? substr("00000{$post->term_code}", -6) : NULL ]
                ]
            ]);

            if( !@$term ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Prazo não encontrado."
                ]);
            }

            Json::get( $headerStatus[200], $term );

        break;

        case "typeahead":

            if( !@$post->term_description || !@$post->limit ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $terms = Model::getList($dafel,(Object)[
                "top" => $post->limit,
                "tables" => [ "Prazo" ],
                "fields" => [
                    "IdPrazo",
                    "CdChamada",
                    "DsPrazo"
                ],
                "filters" => [
                    [ "StAtivo = 'S'" ],
                    [ "DsPrazo", "s", "LIKE", @$post->term_description ? "%".str_replace("[","[[]",$post->term_description)."%" : NULL ]
                ],
                "order" => "DsPrazo"
            ]);

            $items = [];
            foreach( $terms as $term ){
                $items[] = (Object)[
                    "item_id" => $term->IdPrazo,
                    "item_name" => $term->DsPrazo,
                    "html" => (
                        "<b>{$term->DsPrazo}</b><br/>" .
                        "<span>Cód: {$term->CdChamada}</span>"
                    )
                ];
            }

            Json::get( $headerStatus[200], $items );

        break;

    }

?>