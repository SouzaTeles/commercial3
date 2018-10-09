<?php

    ini_set('max_execution_time', 12*60*60);
    $inicio = microtime(true);
    include "../config/start.php";

    GLOBAL $dafel, $config;

    $commercial2 = new MySQL((Object)[
        "host" => "localhost",
        "user" => "root",
        "pass" => "",
        "table" => "commercial2"
    ]);

    $orders = Model::getList($commercial2,(Object)[
        "tables" => [ "`order`" ],
        "fields" => [
            "order_id",
            "order_client_id",
            "order_address_delivery_code"
        ],
        "filters" => [
            [ "order_date", "s", ">", "2018-09-28" ],
            [ "order_company_id", "i", "!=", "4" ]
        ]
    ]);

    $params = (Object)[
        "uf_id" => "RJ"
    ];

    $operation = Model::get($dafel,(Object)[
        "tables" => [ "Operacao (NoLock)" ],
        "fields" => [
            "StAtualizaFinanceiro",
            "CDSituacaoTributariaCOFINS",
            "CDSituacaoTributariaPIS",
            "IdCFOPIntraUF",
            "IdCFOPEntreUF",
            "StCalculaICMS",
            "StCalculaSubstTributariaICMS"
        ],
        "filters" => [[ "IdOperacao", "s", "=", $config->budget->operation_id ]]
    ]);

    foreach( $orders as $order ){
        $items = Model::getList($commercial2,(Object)[
            "tables" => [ "order_item" ],
            "filters" => [
                [ "order_id", "i", "=", $order->order_id ],
            ]
        ]);

        foreach( $items as $key => $item ){

            $address = Model::get($dafel,(Object)[
                "tables" => [ "PessoaEndereco" ],
                "fields" => [ "IdUF" ],
                "filters" => [
                    [ "IdPessoa", "s", "=", $order->order_client_id ],
                    [ "CdEndereco", "s", "=", $order->order_address_delivery_code ],
                ]
            ]);

            $product = Model::get($dafel,(Object)[
                ""
            ]);

            $cfop = Model::get($dafel,(Object)[
                "tables" => [ "Produto_Empresa_CFOP (NoLock)" ],
                "fields" => [ "IdCFOPEquivalente" ],
                "filters" => [
                    [ "IdProduto", "s", "=", $item->product_id ],
                    [ "CdEmpresa", "i", "=", $order->order_company_id ],
                    [ "TpOperacao", "s", "=", "V" ],
                    [ "IdUF", "s", "=", $address->IdUF ]
                ]
            ]);

            if( $params->uf_id == $address->IdUF ){
                $cfop_start = "5";
                $cfop_produto = @$item->product_cfop ? $item->product_cfop : NULL;
                $cfop_operacao = $operation->IdCFOPIntraUF;
            } else {
                $cfop_start = "6";
                $cfop_produto = @$item->product_cfop_extra ? $item->product_cfop_extra : NULL;
                $cfop_operacao = $operation->IdCFOPEntreUF;
            }

            if (@$cfop->IdCFOPEquivalente)
                $IdCFOP = "{$cfop_start}.{$cfop->IdCFOPEquivalente}";
            else
                $IdCFOP = @$cfop_produto ? "{$cfop_start}.{$cfop_produto}" : $cfop_operacao;

            $fiscal = Model::get($dafel,(Object)[
                "tables" => ["CalculoICMS_UF (NoLock)"],
                "fields" => [
                    "CdSituacaoTributaria",
                    "StCalculaSubstTributariaICMS",
                    "AlICMS",
                    "AlFCP"
                ],
                "filters" => [
                    [
                        ["CdEmpresa IS NULL"],
                        ["CdEmpresa", "i", "=", $budget->company_id]
                    ],
                    ["IdUFDestino", "s", "=", $budget->address_uf_id],
                    ["IdUF", "s", "=", $params->uf_id],
                    ["IdCFOP", "s", "=", $IdCFOP],
                    ["IdCalculoICMS", "s", "=", $item->icms_id]
                ],
                "order" => "CdEmpresa DESC"
            ]);

            $AlICMS = 0;
            $VlICMS = 0;
            $VlICMSSubstTributaria = 0;
            $VlBaseFCPSubstTributaria = 0;
            $AlFCP = 0;
            $AlFCPSubstTributaria = 0;
            $VlFCP = 0;
            $VlFCPSubstTributaria = 0;
            $VlBaseICMSST = 0;

            if( @$operation->StCalculaICMS && $operation->StCalculaICMS == "S" && @$fiscal->AlICMS ){
                $AlFCP = @$fiscal->AlFCP ? $fiscal->AlFCP : 0;
                $AlICMS = $fiscal->AlICMS - $AlFCP;
                $VlICMS = number_format((($AlICMS/100) * $item->budget_item_value_total), 2, '.', '');
                $VlFCP = number_format((($AlFCP/100) * $item->budget_item_value_total), 2, '.', '');
            }

            if( @$item->ncm_id && @$operation->StCalculaSubstTributariaICMS && $operation->StCalculaSubstTributariaICMS == "S" && $fiscal->StCalculaSubstTributariaICMS == "S" ){

                $ncm = Model::get($dafel,(Object)[
                    "tables" => ["ClassificacaoFiscalItem (NoLock)"],
                    "fields" => ["IdClassificacaoFiscal", "AlLucro", "AlICMSInterna", "AlMVASTInterna", "AlICMSSTInterna"],
                    "filters" => [
                        ["IdClassificacaoFiscal", "s", "=", $item->ncm_id],
                        ["CdEmpresa", "i", "=", $budget->company_id]
                    ]
                ]);

                if( @$ncm->IdClassificacaoFiscal ){

                    $MVA = @$ncm->AlLucro ? $ncm->AlLucro : ( @$ncm->AlMVASTInterna ? $ncm->AlMVASTInterna : 0 );
                    $AlICMSInterna = @$ncm->AlICMSInterna ? $ncm->AlICMSInterna : ( @$ncm->AlICMSSTInterna ? $ncm->AlICMSSTInterna : 0 );
                    if( !@$MVA || !@$AlICMSInterna ){
                        $VlICMSSubstTributaria = 0;
                        $VlFCPSubstTributaria = 0;
                    } else {
                        $VlBaseICMSST = $item->budget_item_value_total * (1+($MVA/100));
                        $VlICMSSubstTributaria = ($VlBaseICMSST * ($AlICMSInterna/100)) - $VlICMS;
                        $VlBaseFCPSubstTributaria = $VlBaseICMSST;
                        $AlFCPSubstTributaria = $AlFCP;
                        $VlFCPSubstTributaria = ($AlFCP/100) * $VlBaseFCPSubstTributaria;
                        if( $VlFCP < 0 ) $VlFCP = 0;
                        $VlFCPSubstTributaria -= $VlFCP;
                        if( $VlFCPSubstTributaria < 0 ) $VlFCPSubstTributaria = 0;
                        $VlICMSSubstTributaria -= $VlFCP;
                        $VlICMSSubstTributaria -= $VlFCPSubstTributaria;
                    }
                }
            }

            $budget->items[$key]["AlICMS"] = $AlICMS;
            $budget->items[$key]["AlFCP"] = $AlFCP;
            $budget->items[$key]["VlICMS"] = $VlICMS;
            $budget->items[$key]["VlFCP"] = $VlFCP;
            $budget->items[$key]["VlBaseFCP"] = $item->budget_item_value_total;
            $budget->items[$key]["CdSituacaoTributaria"] = $fiscal->CdSituacaoTributaria;
            $budget->items[$key]["IdCFOP"] = $IdCFOP;
            $budget->items[$key]["VlICMSSubstTributaria"] = (float)number_format($VlICMSSubstTributaria,2,".","");
            $budget->items[$key]["VlBaseFCPSubstTributaria"] = (float)number_format($VlBaseFCPSubstTributaria,2,".","");
            $budget->items[$key]["AlFCPSubstTributaria"] = (float)number_format($AlFCPSubstTributaria,2,".","");
            $budget->items[$key]["VlFCPSubstTributaria"] = (float)number_format($VlFCPSubstTributaria,2,".","");
            $budget->items[$key]["VlBaseICMSST"] = (float)number_format($VlBaseICMSST,2,".","");

            $budget->items[$key]["budget_item_value_icms"] = $VlICMS + $VlFCP;
            $budget->items[$key]["budget_item_value_st"] = $VlICMSSubstTributaria + $VlFCPSubstTributaria;

            $budget->budget_value_icms += $VlICMS + $VlFCP;
            $budget->budget_value_st += $VlICMSSubstTributaria + $VlFCPSubstTributaria;
        }
    }

    var_dump($orders);

?>