<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $dafel, $login, $config, $headerStatus, $get, $post, $EanCode;

    $EAN = '00A000005R'; //Codigo EAN Principal (Valor fixo Dafel)
    $EanCode;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    switch( $get->action )
    {
        case "get":
            if( !@$post->type){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            switch($post->type){
              //Get pelo codigo do produto
              case 'P':
                $product = Model::get($dafel,(Object)[
                    "join" => 1,
                    "tables" => [
                        "Produto P",
                        "INNER JOIN CodigoProduto cp ON (p.IdProduto = cp.IdProduto)"
                    ],
                    "fields" => [
                        "P.NmProduto",
                        "P.IdProduto",
                        "CP.CdChamada"

                    ],
                    "filters" => [
                        ["cp.StCodigoPrincipal", "s", "=", "S"],
                        ["cp.CdChamada", "s", "=", "{$post->product_code}"]
                    ]
                ]);

                if($product){
                  $EanCode = Model::get($dafel,(Object)[
                    "tables" => [
                        "CodigoProduto CP",
                    ],
                    "fields" => [
                        "CP.CdChamada",
                    ],
                    "filters" => [
                        ["CP.IdTipoCodigoProduto", "s", "=", "{$EAN}"],
                        ["CP.IdProduto", "s", "=", "{$product->IdProduto}"]
                    ]
                  ]);
                } else {
                  headerResponse((Object)[
                      "code" => 417,
                      "message" => "Nenhum produto localizado para o codigo informado, verifique."
                  ]);
                }

                //Busca a Imagem a partir do Id do produto
                $productImage = getImage((Object)[
                    "image_dir" => "product",
                    "image_id"=> $product->IdProduto
                ]);

                $EanCode = ($EanCode ? $EanCode->CdChamada : NULL);


                Json::get($httpStatus[200], (Object)[
                    "product_id" => $product->IdProduto,
                    "product_code" => $product->CdChamada,
                    "product_name" => $product->NmProduto,
                    "product_EAN" => $EanCode,
                    "product_image" => $productImage
                ]);
              break;
              //Get pelo codigo do grupo
              case 'G':
                    $group = Model::get($dafel,(Object)[
                        "join" => 1,
                        "tables" => [
                            "GrupoProduto GP"
                        ],
                        "fields" => [
                            "GP.NmGrupoProduto",
                            "GP.IdGrupoProduto",
                            "GP.CdChamada",
                            "GP.CdClassificacao"

                        ],
                        "filters" => [
                            ["GP.CdChamada", "s", "=", "{$post->product_group_code}"]
                        ]
                    ]);

                    if(!@$group){
                        headerResponse((Object)[
                            "code" => 417,
                            "message" => "Nenhum grupo localizado para o codigo informado, verifique."
                        ]);
                    }
                    else {
                      $productList = Model::get($dafel,(Object)[
                        "tables" => [
                            "GrupoProduto"
                        ],
                        "fields" => [
                            "CdChamada"
                        ],
                        "filters" => [
                            ["CdClassificacao", "s", "like", "{$group->CdClassificacao}.%"]
                        ]
                      ]);

                      //var_dump($productList);

                      if($productList){
                        headerResponse((Object)[
                        "code" => 417,
                        "message" => "Existe(m) subgrupo(s) associado(s) à esse grupo, verifique."
                        ]);
                      } else {

                      }


                      Json::getlist($httpStatus[200], (Object)[
                          "product_group_id" => $group->IdProduto,
                          "product_group_code" => $group->CdChamada,
                          "product_group_name" => $group->NmGrupoProduto
                      ]);
                    }
              break;
            }
        break;
        case "typeahead":
            if( !@$post->item_name || !@$post->limit ){
              headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."

                ]);
            }

            switch($post->type){
              //Get pelo Grupo
              case 'G':
                $groups = Model::getList($dafel,(Object)[
                    "top" => $post->limit,
                    "tables" => [
                        "GrupoProduto (NoLock)",
                    ],
                    "fields" => [
                        "NmGrupoProduto",
                        "IdGrupoProduto",
                        "CdChamada",
                        "CdClassificacao"
                    ],
                    "filters" => [
                             ["NmGrupoProduto", "s", "like", "%{$post->item_name}%"]
                        ]
                ]);

                $items = [];
                foreach( $groups as $group ){
                    $items[] = (Object)[
                        "item_id" => $group->IdGrupoProduto,
                        "item_name" => $group->NmGrupoProduto,
                        "item_code" => $group->CdChamada,
                        "html" => (
                            "<div class='type-ahead-cover'></div>" .
                            "<b>{$group->NmGrupoProduto}</b><br/>".
                            "<span>Cód: {$group->CdChamada}</span>".
                            "<span>Class: {$group->CdClassificacao}</span>"

                        )
                    ];
                }

                Json::get( $headerStatus[200], $items );
              break;
              //Get pelo produto
              case 'P':
                $products = Model::getList($dafel,(Object)[
                    "top" => $post->limit,
                    "tables" => [
                        "vw_produto (NoLock)",
                    ],
                    "fields" => [
                        "CdChamada",
                        "NmProduto",
                        "IdProduto",
                        "CdClassificacao"
                    ],
                    "filters" => [
                             ["NmProduto", "s", "like", "%{$post->item_name}%"]
                        ]
                ]);
                $items = [];
                foreach( $products as $product ){
                    if($product){
                      $EanCode = Model::get($dafel,(Object)[
                        "tables" => [
                            "CodigoProduto CP",
                        ],
                        "fields" => [
                            "CP.CdChamada",
                        ],
                        "filters" => [
                            ["CP.IdTipoCodigoProduto", "s", "=", "{$EAN}"],
                            ["CP.IdProduto", "s", "=", "{$product->IdProduto}"]
                        ]
                      ]);
                    }
                    $EanCode = ($EanCode ? $EanCode->CdChamada : NULL);

                    $productImage = getImage((Object)[
                        "image_dir" => "product",
                        "image_id"=> $product->IdProduto
                    ]);


                    $items[] = (Object)[
                        "item_id" => $product->IdProduto,
                        "item_name" => $product->NmProduto,
                        "item_code" => $product->CdChamada,
                        "item_EAN" => $EanCode,
                        "html" => (
                            // "<div class='type-ahead-cover'></div>" .
                            "<div class='type-ahead-cover'" . (@$productImage ? (" style='background-image:url({$productImage})'") : "") . "></div>" .
                            "<b>{$product->NmProduto}</b><br/>".
                            "<span>Cód: {$product->CdChamada}</span>".
                            "<span>Class: {$product->CdClassificacao}</span>".
                            "<span>Class: {$EanCode}</span>"

                        )
                    ];
                    // $EanCode-CdChamada = NULL;
                }
                Json::get( $headerStatus[200], $items );
          }
        break;
    }
?>
