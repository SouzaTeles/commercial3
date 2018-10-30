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
                    "product_classification=P.CdClassificacao",
                    "parent_id=P.IdProdutoOrigem",
                    "conversion=P.FtConversaoUnidade"
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

            if( @$post->product_code ){
                foreach( $products as $product ){
                    if( (int)$product->product_code == (int)$post->product_code ) {
                        Json::get( $headerStatus[200], new Product($product) );
                    }
                }
                foreach( $products as $product ){
                    if( $product->product_code_provider == $post->product_code ) {
                        Json::get( $headerStatus[200], new Product($product) );
                    }
                }
                foreach( $products as $product ){
                    if( $product->product_code_bar == $post->product_code ) {
                        Json::get( $headerStatus[200], new Product($product) );
                    }
                }
            }

            Json::get( $headerStatus[200], new Product($products[0]) );

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
                    "image" => getImage((Object)[
                        "image_id" => $product->IdProduto,
                        "image_dir" => "product"
                    ]),
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

        case "getInfo":

            if( !@$post->product_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $info = [];
            $data = Model::getList($dafel,(Object)[
                "join" => 1,
                "tables" => [
                    "Produto P (NoLock)",
	                "INNER JOIN Produto_Empresa (NoLock) PE ON(P.IdProduto = PE.IdProduto)"
                ],
                "fields" => [
                    "PE.CdEmpresa",
                    "PE.StAtivoVenda",
                    "PE.StAtivoCompra",
                    "QtPedidoCompra=ISNULL(PE.QtPedidoCompra,0)",
                    "QtEstoque=(CASE WHEN P.IdProdutoOrigem IS NULL THEN ( SELECT TOP 1 QtEstoque FROM EstoqueEmpresa WHERE IdProduto = P.IdProduto AND CdEmpresa = PE.CdEmpresa ORDER BY DtReferencia DESC ) ELSE ( ( SELECT TOP 1 QtEstoque FROM EstoqueEmpresa WHERE IdProduto = P.IdProdutoOrigem AND CdEmpresa = PE.CdEmpresa ORDER BY DtReferencia DESC ) * P.FtConversaoUnidade ) END)"
                ],
                "filters" => [[ "P.IdProduto", "s", "=", $post->product_id ]]
            ]);

            foreach( $data as $d ){
                $info[] = (Object)[
                    "company_code" => substr("0{$d->CdEmpresa}",-2),
                    "sale_active" => $d->StAtivoVenda == "S" ? "Y" : "N",
                    "buy_active" => $d->StAtivoCompra == "S" ? "Y" : "N",
                    "bought" => (float)$d->QtPedidoCompra,
                    "stock" => (float)$d->QtEstoque
                ];
            }

            Json::get( $headerStatus[200], $info );

        break;

        case "getInfoBuy":

            if( !@$post->product_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $items = Model::getList($dafel,(Object)[
                "join" => 1,
                "tables" => [
                    "PedidoDeCompra PC (NoLock)",
	                "INNER JOIN PedidoDeCompraItem PCI (NoLock) ON(PCI.IdPedidoDeCompra = PC.IdPedidoDeCompra)",
	                "LEFT JOIN Pessoa PF (NoLock) ON(PF.IdPessoa = PC.IdPessoaFornecedor)"
                ],
                "fields" => [
                    "CdPedido=PC.CdChamada",
                    "CdPessoa=PF.CdChamada",
                    "PF.NmPessoa",
                    "DtEmissao=CONVERT(VARCHAR(10),PC.DtEmissao,126)",
                    "DtEntrega=CONVERT(VARCHAR(10),PC.DtEntrega,126)",
                    "PCI.QtPedida",
                    "PCI.QtAtendida",
                    "PCI.StPedidoDeCompraItem"
                ],
                "filters" => [
                    [ "PC.CdEmpresa", "s", "=", $post->company_id ],
                    [ "PCI.IdProduto", "s", "=", $post->product_id ],
                    [ "PCI.StPedidoDeCompraItem", "s", "in", ['G','A','P'] ]
                ]
            ]);

            $ret = [];
            foreach( $items as $item ){
                $ret[] = (Object)[
                    "budget_code" => $item->CdPedido,
                    "budget_date" => $item->DtEmissao,
                    "budget_delivery" => $item->DtEntrega,
                    "provider_code" => $item->CdPessoa,
                    "provider_name" => $item->NmPessoa,
                    "required" => (float)$item->QtPedida,
                    "attended" => (float)$item->QtAtendida,
                    "status" => $item->StPedidoDeCompraItem
                ];
            }

            Json::get( $headerStatus[200], $ret );

        break;

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

        case "typeahead":

            if( !@$post->product_name || !@$post->limit || !@$post->company_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $products = Model::getList($dafel,(Object)[
                "join" => 1,
                "top" => $post->limit,
                "tables" => [
                    "Produto P (NoLock)",
                    "INNER JOIN CodigoProduto CP (NoLock) ON(P.IdProduto = CP.IdProduto AND CP.StCodigoPrincipal = 'S')",
                    "INNER JOIN Produto_Empresa PE (NoLock) ON(P.IdProduto = PE.IdProduto)",
                    "INNER JOIN Unidade U ON(P.IdUnidade = U.IdUnidade)"
                ],
                "fields" => [
                    "P.IdProduto",
                    "CP.CdChamada",
                    "P.NmProduto",
                    "P.CdClassificacao",
                    "StAtivoVenda = ISNULL(PE.StAtivoVenda,'S')",
                    "P.IdProdutoOrigem",
                    "P.FtConversaoUnidade",
                    "Preco=( SELECT TOP 1 VlPreco FROM HistoricoPreco WHERE IdProduto = P.IdProduto AND CdEmpresa = '{$post->company_id}' AND IdPreco = '{$config->product->price_id}' ORDER BY DtReferencia DESC)",
                    "Estoque=ISNULL(( CASE WHEN P.IdProdutoOrigem IS NULL THEN ( SELECT TOP 1 QtEstoque FROM EstoqueEmpresa WHERE IdProduto = P.IdProduto AND CdEmpresa = {$post->company_id} ORDER BY DtReferencia DESC ) ELSE ( ( SELECT TOP 1 QtEstoque FROM EstoqueEmpresa WHERE IdProduto = P.IdProdutoOrigem AND CdEmpresa = {$post->company_id} ORDER BY DtReferencia DESC ) * P.FtConversaoUnidade ) END ),0)",
                    "U.CdSigla",
                    "U.TpUnidade"
                ],
                "filters" => [
                    [ "PE.CdEmpresa", "i", "=", $post->company_id ],
                    [ "ISNULL(PE.StAtivoVenda,'S')", "s", "=", "S" ],
                    [
                        [ "P.CdClassificacao", "s", "like", "%{$post->product_name}%" ],
                        [ "P.NmProduto", "s", "like", "%{$post->product_name}%" ]
                    ],
                    [ "(SELECT TOP 1 VlPreco FROM HistoricoPreco WHERE IdProduto = P.IdProduto AND CdEmpresa = '{$post->company_id}' AND IdPreco = '{$config->product->price_id}' ORDER BY DtReferencia DESC) IS NOT NULL" ],
                ]
            ]);

            $items = [];
            foreach( $products as $product ){
                $product->Preco = number_format($product->Preco,2,",",".");
                $product->Class = (float)$product->Estoque < 0 ? "danger" : "info";
                $product->Estoque = number_format($product->Estoque,($product->TpUnidade == "F" ? 3 : 0),",",".");
                $items[] = (Object)[
                    "item_id" => $product->IdProduto,
                    "item_name" => $product->NmProduto,
                    "html" => (
                        //"<div class='type-ahead-cover'></div>" .
                        "<b>{$product->NmProduto}</b><br/>" .
                        "<span>Cód: {$product->CdChamada}</span>" .
                        "<span>Class: {$product->CdClassificacao}</span>" .
                        "<span class='{$product->Class}'>Estoque: {$product->Estoque}({$product->CdSigla})</span>" .
                        "<span>Preço: R$ {$product->Preco}</span>"
                    )
                ];
            }

            Json::get( $headerStatus[200], $items );

        break;

    }

?>