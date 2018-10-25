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

            if( !@$post->company_id || ( !@$post->product_id && !@$post->product_code )){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $_POST["get_unit"] = 1;
            $_POST["get_product_prices"] = 1;

            $products = Model::getList($dafel,(Object)[
                "join" => 1,
                "tables" => [
                    "Produto P (NoLock)",
                    "INNER JOIN CodigoProduto CP (NoLock) ON(P.IdProduto = CP.IdProduto AND CP.StCodigoPrincipal = 'S')",
                    "LEFT JOIN CodigoProduto EAN (NoLock) ON(P.IdProduto = EAN.IdProduto AND EAN.IdTipoCodigoProduto = '{$config->product->code_ean_id}')",
                    "LEFT JOIN CodigoProduto CPF (NoLock) ON(P.IdProduto = CPF.IdProduto AND CPF.IdTipoCodigoProduto = '{$config->product->code_provider_id}')",
                    "INNER JOIN Produto_Empresa PE (NoLock) ON(P.IdProduto = PE.IdProduto)"
                ],
                "fields" => [
                    "ncm_id=P.IdClassificacaoFiscal",
                    "icms_id=P.IdCalculoICMS",
                    "unit_id=P.IdUnidade",
                    "product_id=P.IdProduto",
                    "product_code=CP.CdChamada",
                    "product_code_bar=EAN.CdChamada",
                    "product_code_provider=CPF.CdChamada",
                    "product_name=P.NmProduto",
                    "product_active=ISNULL(PE.StAtivoVenda,'S')",
                    "product_discount=ISNULL(P.AlDesconto,0)",
                    "product_commission=ISNULL(P.AlRepasseDuplicata,0)",
                    "product_weight_net=ISNULL(P.VlPesoLiquido,0)",
                    "product_weight_gross=ISNULL(P.VlPesoBruto,0)",
                    "product_cfop=P.CdCFOP",
                    "product_cfop_extra=P.CdCFOPEntreUF",
                    "product_classification=P.CdClassificacao"
                ],
                "filters" => [
                    [ "PE.CdEmpresa", "i", "=", $post->company_id ],
                    [ "P.IdProduto", "s", "=", @$post->product_id ? $post->product_id : NULL ],
                    @$post->product_code ? [
                        [ "CP.CdChamada", "s", "=", substr("00000{$post->product_code}",( strlen($post->product_code) > 6 ? -(strlen($post->product_code)) : -6)) ],
                        [ "EAN.CdChamada", "s", "=", $post->product_code ],
                        [ "CPF.CdChamada", "s", "=", $post->product_code ],
                    ] : NULL
                ]
            ]);

            if( !sizeof($products) ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "O produto não foi localizado para a empresa informada."
                ]);
            }

            $product = NULL;
            if( @$post->product_code ){
                foreach( $products as $product ){
                    if( (int)$product->product_code == (int)$post->product_code ) {
                        $product = new Product($product);
                    }
                }
                if( !@$product ) {
                    foreach ($products as $product) {
                        if ($product->product_code_provider == $post->product_code) {
                            $product = new Product($product);
                        }
                    }
                }
                if( !@$product ) {
                    foreach ($products as $product) {
                        if ($product->product_code_bar == $post->product_code) {
                            $product = new Product($product);
                        }
                    }
                }
            }

            if( !@$product ) {
                $product = new Product($products[0]);
            }

            $product->product_ean = $product->product_code_bar;
            $product->product_provider_code = $product->product_code_provider;
            if( @$product->stock ){
                $product->stock->product_stock = $product->stock->stock_value;
                $product->stock->product_stock_date = standardize_date($product->stock->stock_date);
            }
            $product->unit->unit_initials = $product->unit->unit_code;
            $product->unit->unit_format = $product->unit->unit_type;
            foreach($product->prices as $price){
                $price->price_date = standardize_date($price->price_date);
            }
            $product->product_prices = $product->prices;

            Json::get( $headerStatus[200], $product );

        break;

        case "getList":

            if( !@$post->product_name || !@$post->company_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $products = Model::getList($dafel,(Object)[
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
                    [ "ISNULL(PE.StAtivoVenda,'S')", "s", "=", ( @$post->product_active && $post->product_active == "true" ? "S" : NULL ) ],
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
                    "unit" => (Object)[
                        "unit_initials" => $product->CdSigla,
                        "unit_format" => $product->TpUnidade
                    ],
                    "stock" => (Object)[
                        "product_stock" => (float)$product->VlEstoque
                    ],
                    "product_prices" => [(Object)[
                        "price_value" => (float)$product->VlPreco
                    ]]
                ];
            }

            Json::get( $headerStatus[200], $ret );

        break;
    }

?>