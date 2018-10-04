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
                        [ "CP.CdChamada", "s", "=", substr("00000{$post->product_code}",( strlen($post->product_code) > 6 ? -(strlen($post->product_code)) : -6)) ],
                    ]
                ]);
                // var_dump($product);
;
                if(!@$product->CdChamada){
                  $productList = Model::getlist($dafel, (Object)[
                    "top" => 50,
                    "join" => 1,
                    "tables" => [
                      "Produto P",
                      "inner join CodigoProduto CP (nolock) on (P.IdProduto = Cp.IdProduto)"
                    ],
                    "fields" => [
                      "CP.CdChamada",
                      "P.NmProduto",
                      "P.IdProduto",
                      "P.CdClassificacao",
                    ],
                    "filters" => [
                      ["CP.CdChamada", "s",  "like", "{$post->product_code}%"],
                      ["CP.StCodigoPrincipal", "s",  "=", "S"]
                    ]
                  ]);
                  // var_dump($productList);
                  Json::get($httpStatus[200], $productList);
                  // foreach($productList as $product){
                  // }
                  return;
                }


                //   });
                // }

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
                        [ "GP.CdChamada", "s", "=", substr("00000{$post->product_group_code}",( strlen($post->product_group_code) > 6 ? -(strlen($post->product_group_code)) : -6)) ],
                        ]
                    ]);

                    if(!@$group){
                        headerResponse((Object)[
                            "code" => 417,
                            "message" => "Nenhum grupo localizado para o codigo informado, verifique."
                        ]);
                    }
                    else {
                      $productGroupList = Model::get($dafel,(Object)[
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

                 if($productGroupList){
                       headerResponse((Object)[
                       "code" => 417,
                       "message" => "Existe(m) subgrupo(s) associado(s) à esse grupo, verifique."
                       ]);
                     } else {
                         $productList = Model::getlist($dafel,(Object)[
                           "tables" => [
                               "vw_Produto"
                           ],
                           "fields" => [
                               "IdProduto",
                               "CdChamada",
                               "NmProduto",

                           ],
                           "filters" => [
                               ["IdGrupoProduto", "s", "=", "{$group->IdGrupoProduto}"]
                           ]
                         ]);
                        // var_dump($productList);
                        $ret = [];
                        $ret2=[];
                        foreach($productList as $product){
                            $ret[] = (Object)[
                              "product_id" => $product->IdProduto,
                              "product_code" => $product->CdChamada,
                              "product_name" => $product->NmProduto

                            ];
                        }
                        $ret2[] = (object)[
                          "group_info" => (object)[
                            "product_group_code" =>$group->CdChamada,
                            "product_group_name" =>$group->NmGrupoProduto
                          ],
                          "product_info" =>$ret
                        ];

                        Json::get($httpStatus[200], $ret2);
                  }

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
                    "join" => 1,
                    "tables" => [
                        "Produto P (NoLock)",
                        "inner join CodigoProduto CP (nolock) on (P.IdProduto = Cp.IdProduto)"
                    ],
                    "fields" => [
                      "CP.CdChamada",
                      "P.NmProduto",
                      "P.IdProduto",
                      "P.CdClassificacao"
                    ],
                    "filters" => [
                      ["P.NmProduto", "s", "like", "%{$post->item_name}%"],
                      ["CP.StCodigoPrincipal", "s", "=", 'S']
                    ]
                ]);
                $itens = [];
                foreach( $products as $product){
                    if($product){
                      // var_dump($product->IdProduto);
                      // var_dump($EAN);
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
                    // var_dump($EanCode);
                    $EanCode = ($EanCode ? $EanCode->CdChamada : NULL);

                    $productImage = getImage((Object)[
                        "image_dir" => "product",
                        "image_id"=> $product->IdProduto
                    ]);


                    $itens[] = (Object)[
                        "product_id" => $product->IdProduto,
                        "product_name" => $product->NmProduto,
                        "product_code" => $product->CdChamada,
                        "product_EAN" => $EanCode,
                        "product_image" =>@$productImage,
                        "html" => (
                            // "<div class='type-ahead-cover'></div>" .
                            "<div class='type-ahead-cover'" . (@$productImage ? (" style='background-image:url({$productImage})'") : "") . "></div>" .
                            "<b>{$product->NmProduto}</b><br/>".
                            "<span>Cód: {$product->CdChamada}</span>".
                            "<span>Class: {$product->CdClassificacao}</span>".
                            "<span>Class: {$EanCode}</span>"

                        )
                    ];
                    // var_dump($itens);
                    // $EanCode-CdChamada = NULL;
                }
                Json::get( $headerStatus[200], $itens);
          }
        break;
        case "up":
        //echo $post->product_id;
        // var_dump($post);
        // echo "[LOG]Entrou no UP/n";;
          if( !@$post->product_id){
            headerResponse((Object)[
                  "code" => 417,
                  "message" => "Parâmetro POST não encontrado."
              ]);
          } else {

            switch(@$post->product_img_act){
              case 'I':
              if(@$post->product_image64){
                $path = PATH_FILES . "\product" . $post->product_id;
                if (file_exists("{$path}.jpg")) unlink("{$path}.jpg");
                if (file_exists("{$path}.jpeg")) unlink("{$path}.jpeg");
                if (file_exists("{$path}.png")) unlink("{$path}.png");
                base64toFile(PATH_FILES . "\product", $post->product_id, $post->product_image64);
                Json::get($httpStatus[200], ("foi foi foi foi foi"));
              }
              break;

              case 'R':
              $path = PATH_FILES . "\product" . $post->product_id;
              if (file_exists("{$path}.jpg")) unlink("{$path}.jpg");
              if (file_exists("{$path}.jpeg")) unlink("{$path}.jpeg");
              if (file_exists("{$path}.png")) unlink("{$path}.png");
              break;
            }


            // echo "[LOG]Entrou no IF/n";
            if(@$post->product_EAN){
              // echo "entrou";
              $eanTemp = Model::get($dafel,(Object)[
                "top" => 1,
                "tables" => [
                    "CodigoProduto"
                ],
                "fields" => [
                    "CdChamada"
                ],
                "filters" => [
                    ["IdTipoCodigoProduto", "s", "=", $EAN],
                    ["IdProduto", "s", "=", $post->product_id]
                ]
              ]);

              if(@$eanTemp->CdChamada){
                //Update
                // echo "Teste";
                Model::update($dafel,(Object)[
                  "top" => 1,
                  "table" =>
                      "CodigoProduto",
                  "fields" => [
                      ["CdChamada", "s", $post->product_EAN],
                  ],
                  "filters" => [
                      ["IdTipoCodigoProduto", "s", "=", $EAN],
                      ["IdProduto", "s", "=", $post->product_id]
                  ]
                ]);
              }
              else {
                //Insert
                $query = Model::nextCode($dafel,(Object)[
                        "table" => "CodigoProduto",
                        "field" => "IdCodigoProduto",
                        "increment" => "S",
                        "base36encode" => 1
                    ]);

                // var_dump($query);

                Model::insert($dafel,(Object)[
                  "top" => 1,
                  "table" =>
                      "CodigoProduto",
                  "fields" => [
                      ["CdChamada", "s", $post->product_EAN],
                      ["IdCodigoProduto", "s", $query],
                      ["IdProduto", "s", $post->product_id],
                      ["IdTipoCodigoProduto", "s", $EAN],
                    	["StCodigoPrincipal", "s", 'N']
                  ],
                ]);

              }
            }
            Json::get($headerStatus[200], (Object)[
                  "code" => 200,
                  "message" => "Cadastro efetuado com sucesso."
              ]);
          }
        break;
    }
?>
