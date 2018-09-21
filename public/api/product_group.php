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
                }

                $productImage = getImage((Object)[
                    "image_dir" => "products",
                    "image_id"=> $product->IdProduto
                ]);

                Json::get($httpStatus[200], (Object)[
                    "product_id" => $product->IdProduto,
                    "product_code" => $product->CdChamada,
                    "product_name" => $product->NmProduto,
                    "product_EAN" => $EanCode,
                    "product_image" => $productImage
                ]);
              break;
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

                    if( !@$group){
                        headerResponse((Object)[
                            "code" => 417,
                            "message" => "Nenhum resultado para o codigo informado, verifique."
                        ]);
                    }
                    else {
                      $productList = Model::get($dafel,(Object)[
                        "tables" => [
                            "GrupoProduto GP"
                        ],
                        "fields" => [
                            "GP.IdGrupoProduto"
                        ],
                        "filters" => [
                            ["GP.CdClassificacao", "s", "like", "{$group->CdClassificacao}.%"]
                        ]
                      ]);

                      if($productList){
                        headerResponse((Object)[
                        "code" => 417,
                        "message" => "Existem um ou mais subgrupos associados à esse grupo, verifique."
                        ]);
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
  //  }
        case "typeahead":
            if( !@$post->item_name || !@$post->limit ){
              headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."

                ]);
            }

            switch($post->type){
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
                    $items[] = (Object)[
                        "item_id" => $product->IdProduto,
                        "item_name" => $product->NmProduto,
                        "item_code" => $product->CdChamada,
                        "item_EAN" => $EanCode,
                        "html" => (
                            "<div class='type-ahead-cover'></div>" .
                            "<b>{$product->NmProduto}</b><br/>".
                            "<span>Cód: {$product->CdChamada}</span>".
                            "<span>Class: {$product->CdClassificacao}</span>".
                            "<span>Class: {$EanCode}</span>"

                        )
                    ];
                    $EanCode = NULL;
                }
                Json::get( $headerStatus[200], $items );
          }
        break;
    }
?>

/*
        case "getList":

            if( !@$post->product_group_name || !@$post->limit ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

          /  $products = Model::getList($dafel,(Object)[
                "top" => 200,
                "join" => 1,
                "tables" => [
                    "Produto P (NoLock)",
                    "INNER JOIN CodigoProduto CP (NoLock) ON(P.IdProduto = CP.IdProduto AND CP.StCodigoPrincipal = 'S')",
                    "LEFT JOIN CodigoProduto EAN (NoLock) ON(P.IdProduto = EAN.IdProduto AND EAN.IdTipoCodigoProduto = '{$config->product->code_ean_id}')",
                    "LEFT JOIN CodigoProduto CPF (NoLock) ON(P.IdProduto = CPF.IdProduto AND CPF.IdTipoCodigoProduto = '{$config->product->code_provider_id}')",
                    "INNER JOIN Produto_Empresa PE (NoLock) ON(P.IdProduto = PE.IdProduto)",
                    "INNER JOIN Unidade U ON(P.IdUnidade = U.IdUnidade)"
                ],
                "fields" => [
                    "P.IdProduto",
                    "CP.CdChamada",
                    "P.NmProduto",
                    "P.CdClassificacao",
                    "StAtivoVenda=ISNULL(PE.StAtivoVenda,'S')",
                    "VlPreco=(SELECT TOP 1 VlPreco FROM HistoricoPreco WHERE IdProduto = P.IdProduto AND CdEmpresa = '{$post->company_id}' AND IdPreco = '{$config->product->price_id}' ORDER BY DtReferencia DESC)",
                    "VlEstoque=(CASE WHEN P.IdProdutoOrigem IS NULL THEN ( SELECT TOP 1 QtEstoque FROM EstoqueEmpresa WHERE IdProduto = P.IdProduto AND CdEmpresa = {$post->company_id} ORDER BY DtReferencia DESC ) ELSE ( ( SELECT TOP 1 QtEstoque FROM EstoqueEmpresa WHERE IdProduto = P.IdProdutoOrigem AND CdEmpresa = {$post->company_id} ORDER BY DtReferencia DESC ) * P.FtConversaoUnidade ) END)",
                    "U.CdSigla",
                    "U.TpUnidade"
                ],
                "filters" => [
                    [ "PE.CdEmpresa", "i", "=", $post->company_id ],
                    [ "ISNULL(PE.StAtivoVenda,'S')", "s", "=", @$post->product_active ? "S" : NULL ],
                    @$post->product_name ? [
                        [ "CP.CdChamada", "s", "like", "%{$post->product_name}%" ],
                        [ "EAN.CdChamada", "s", "like", "%{$post->product_name}%" ],
                        [ "CPF.CdChamada", "s", "like", "%{$post->product_name}%" ],
                        [ "P.CdClassificacao", "s", "like", "%{$post->product_name}%" ],
                        [ "P.NmProduto", "s", "like", "%{$post->product_name}%" ]
                    ] : NULL,
                    [ "(SELECT TOP 1 VlPreco FROM HistoricoPreco WHERE IdProduto = P.IdProduto AND CdEmpresa = '{$post->company_id}' AND IdPreco = '{$config->product->price_id}' ORDER BY DtReferencia DESC) IS NOT NULL" ],
                ],
                "group" => "P.IdProduto, P.IdUnidade, PE.CdEmpresa, CP.CdChamada, P.NmProduto, P.CdClassificacao, ISNULL(PE.StAtivoVenda,'S'), IdProdutoOrigem, FtConversaoUnidade, U.CdSigla, U.TpUnidade",
            ]);

            $ret = [];
            foreach( $products as $product ){
                $ret[] = (Object)[
                    "product_id" => $product->IdProduto,
                    "product_code" => $product->CdChamada,
                    "product_name" => $product->NmProduto,
                    "product_active" => $product->StAtivoVenda == "S" ? "Y" : "N",
                    "product_classification" => $product->CdClassificacao,
                    "product_price" => (float)$product->VlPreco,
                    "product_stock" => (float)$product->VlEstoque,
                    "unit_code" => $product->CdSigla,
                    "unit_type" => $product->TpUnidade
                ];
            }

            Json::get( $headerStatus[200], $ret );

        break;
*/

/*
        case "complement":

            if( !@$post->product_id || !@$post->company_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $prices = [];
            $allowed = [];

            foreach( $login->prices as $price ){
                $allowed[] = $price->price_id;
            }

            $history = Model::getList($dafel,(Object)[
                "distinct" => 1,
                "tables" => [ "HistoricoPreco (NoLock)" ],
                "fields" => [ "price_id=IdPreco" ],
                "filters" => [
                    [ "ISNULL(VlPreco,0)", "d", ">", 0 ],
                    [ "IdProduto", "s", "=", $post->product_id ],
                    [ "CdEmpresa", "i", "=", $post->company_id ],
                    [ "IdPreco", "s", "in", $allowed  ]
                ],
                "order" => "IdPreco"
            ]);

            $prices = [];
            foreach( $history as $price ){
                $hp = Model::get($dafel,(Object)[
                    "top" => 1,
                    "tables" => [ "HistoricoPreco HP (NoLock)", "Preco P (NoLock)" ],
                    "fields" => [
                        "product_id=HP.IdProduto",
                        "price_id=HP.IdPreco",
                        "price_value=CAST(HP.VlPreco AS FLOAT)",
                        "price_code=P.CdPreco",
                        "price_name=P.NmPreco",
                        "price_date=CONVERT(VARCHAR(10),HP.DtReferencia,126)"
                    ],
                    "filters" => [
                        [ "HP.IdPreco = P.IdPreco" ],
                        [ "HP.IdProduto", "s", "=", $post->product_id ],
                        [ "HP.IdPreco", "s", "=", $price->price_id ],
                        [ "HP.CdEmpresa", "i", "=", $post->company_id ]
                    ],
                    "order" => "HP.DtReferencia DESC"
                ]);
                $hp->price_value = (float)number_format($hp->price_value,2,".","");
                $prices[] = $hp;
            }

            Json::get( $headerStatus[200], (Object)[
                "prices" => $prices
            ]);

        break;
*/
