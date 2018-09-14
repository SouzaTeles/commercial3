<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $config, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro POST não localizado."
        ]);
    }

    switch( $get->action )
    {

        case "typeahead":

            if( !@$post->district_name ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não localizado."
                ]);
            }

            $post->district_name = removeSpecialChar($post->district_name);

            $districts = Model::getList($dafel,(Object)[
                "tables" => [ "Bairro (NoLock)" ],
                "top" => @$post->limit ? $post->limit : 100,
                "fields" => [ "IdBairro", "CdChamada", "NmBairro" ],
                "filters" => [[ "NmBairro", "s", "LIKE", "{$post->district_name}%" ]],
                "order" => "NmBairro"
            ]);

            $ret = [];
            foreach( $districts as $district ){
                $ret[] = (Object)[
                    "item_id" => $district->IdBairro,
                    "item_name" => removeSpecialChar($district->NmBairro),
                    "html" => (
                        "<b>{$district->NmBairro}</b><br/>" .
                        "<span>Cd: {$district->CdChamada}</span>"
                    )
                ];
            }

            Json::get( $headerStatus[200], $ret );

        break;

    }