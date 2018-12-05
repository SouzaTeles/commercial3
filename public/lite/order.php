<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $conn, $dafel, $commercial, $login, $config, $headerStatus, $get, $post;

    switch( $get->action ){

        case "get":

            if (!@$post->order_code ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $budget = Model::get($commercial, (Object)[
                "tables" => [ "Budget" ],
                "fields" => [
                    "order_id=budget_id"
                ],
                "filters" => [
                    [ "budget_trash", "s", "=", "N"],
                    [ "budget_id", "i", "=", (int)$post->order_code ]
                ]
            ]);

            if( !@$budget ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Orçamento não encontrado."
                ]);
            }

            $items = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.BudgetItem BI (NoLock)",
                    "INNER JOIN {$conn->dafel->table}.dbo.Produto P (NoLock) ON(P.IdProduto = BI.product_id)",
                    "INNER JOIN {$conn->dafel->table}.dbo.CodigoProduto CP (NoLock) ON(P.IdProduto = CP.IdProduto AND CP.StCodigoPrincipal = 'S')",
                    "INNER JOIN {$conn->dafel->table}.dbo.Unidade U (NoLock) ON(P.IdUnidade = U.IdUnidade)",
                    "INNER JOIN {$conn->dafel->table}.dbo.Preco PC (NoLock) ON(PC.IdPreco = BI.price_id)",
                ],
                "fields" => [
                    "product_code=CP.CdChamada",
                    "product_name=P.NmProduto",
                    "price_code=PC.CdPreco",
                    "price_name=PC.NmPreco",
                    "budget_item_quantity=CAST(BI.budget_item_quantity AS FLOAT)",
                    "budget_item_value_total=CAST(BI.budget_item_value_total AS FLOAT)",
                    "budget_item_value_unitary=CAST(BI.budget_item_value_unitary AS FLOAT)",
                    "budget_item_aliquot_discount=CAST(BI.budget_item_aliquot_discount AS FLOAT)",
                    "budget_item_value_discount=CAST(BI.budget_item_value_discount AS FLOAT)",
                    "budget_item_value_icms=CAST(BI.budget_item_value_icms AS FLOAT)",
                    "budget_item_value_st=CAST(BI.budget_item_value_st AS FLOAT)",
                    "unit_code=U.CdSigla"
                ],
                "filters" => [[ "BI.budget_id", "i", "=", (int)$post->order_code]]
            ]);

            $budget->order_items = [];
            foreach( $items as $item ){
                $budget->order_items[] = (Object)[
                    "order_item_amount" => (float)$item->budget_item_quantity,
                    "order_item_value_total" => (float)$item->budget_item_value_total,
                    "order_item_value_unitary" => (float)$item->budget_item_value_unitary,
                    "order_item_al_discount" => (float)$item->budget_item_aliquot_discount,
                    "order_item_vl_discount" => (float)$item->budget_item_value_discount,
                    "order_item_value_icms" => (float)$item->budget_item_value_icms,
                    "order_item_value_st" => (float)$item->budget_item_value_st,
                    "product" => (Object)[
                        "product_code" => $item->product_code,
                        "product_name" => $item->product_name,
                        "product_prices" => [(Object)[
                            "price_name" => $item->price_name,
                            "price_value" => (float)$item->budget_item_value_unitary
                        ]],
                        "unit" => (Object)[
                            "unit_initials" => $item->unit_code
                        ]
                    ]
                ];
            }

            Json::get($headerStatus[200], $budget);

        break;

        case "getList":

            $budgets = Model::getList($commercial, (Object)[
                "top" => 100,
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.Budget B",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa P ON(P.IdPessoa = B.client_id)",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa PR ON(PR.IdPessoa = B.seller_id)"
                ],
                "fields" => [
                    "order_id=B.budget_id",
                    "client_code=P.CdChamada",
                    "client_name=P.NmPessoa",
                    "client_short_name=PR.NmCurto",
                    "seller_code=PR.CdChamada",
                    "seller_name=PR.NmPessoa",
                    "seller_short_name=PR.NmCurto",
                    "order_value_st=CAST(B.budget_value_st AS FLOAT)",
                    "order_value_icms=CAST(B.budget_value_icms AS FLOAT)",
                    "order_value_total=CAST(B.budget_value_total AS FLOAT)",
                    "order_date=FORMAT(B.budget_date,'yyyy-MM-dd HH:mm:ss')"
                ],
                "filters" => [
                    ["B.user_id", "s", "=", $login->user_id],
                    ["B.budget_trash", "s", "=", "N"]
                ],
                "group" => "
                    B.budget_id,
                    P.CdChamada,
                    P.NmPessoa,
                    PR.CdChamada,
                    PR.NmPessoa,
                    PR.NmCurto,
                    B.budget_value_st,
                    B.budget_value_icms,
                    B.budget_value_total,
                    B.budget_date
                ",
                "order" => "B.budget_date DESC"
            ]);

            $info = (Object)[
                "value_total" => 0,
                "quantity" => sizeof($budgets)
            ];

            foreach( $budgets as $budget ){
                $budget->order_code = substr("00000{$budget->order_id}",-6);
                $budget->order_value_st = (float)$budget->order_value_st;
                $budget->order_value_icms = (float)$budget->order_value_icms;
                $budget->order_value_total = (float)$budget->order_value_total;
                $info->quantity += $budget->order_value_total+$budget->order_value_st;
                $budget->order_client = (Object)[
                    "person_code" => $budget->client_code,
                    "person_name" => $budget->client_name,
                    "person_shortname" => $budget->client_short_name
                ];
                $budget->order_seller = (Object)[
                    "person_code" => $budget->seller_code,
                    "person_name" => $budget->seller_name,
                    "person_shortname" => $budget->seller_short_name
                ];
            }

            Json::get($headerStatus[200], $budgets, (Object)[
                "quantity" => $info->quantity,
                "value_total" => $info->value_total,
                "average" => number_format($info->value_total / $info->quantity, 2, ",", ".")
            ]);

        break;

        case "insert":

            $post = json_decode(file_get_contents("php://input"));

            if (!@$post->order_company_id) headerResponse((Object)["code" => 417, "message" => "Verifique se a empresa foi informada."]);
            if (!@$post->order_client_id) headerResponse((Object)["code" => 417, "message" => "Verifique se o cliente foi informado."]);
            if (!@$post->order_seller_id) headerResponse((Object)["code" => 417, "message" => "Verifique se o vendedor foi informado ."]);
            if (!@$post->order_address_delivery_code) headerResponse((Object)["code" => 417, "message" => "Verifique se o endereço foi informado."]);
            if (is_null($post->order_value_total)) headerResponse((Object)["code" => 417, "message" => "O valor do pedido nao foi informado."]);
            if (is_null($post->order_al_discount)) headerResponse((Object)["code" => 417, "message" => "A alíquota de desconto do pedido nao foi informada."]);
            if (is_null($post->order_vl_discount)) headerResponse((Object)["code" => 417, "message" => "O valor de desconto do pedido nao foi informado.."]);
            if (is_null($post->order_value_total)) headerResponse((Object)["code" => 417, "message" => "O valor total do pedido nao foi informado."]);
            if (!@$post->order_items || !sizeof($post->order_items)) headerResponse((Object)["code" => 417, "message" => "Nenhum produto informado para o pedido."]);

            $date = date("Y-m-d H:i:s");
            $budget = (Object)[
                "company_id" => $post->order_company_id,
                "client_id" => $post->order_client_id,
                "seller_id" => $post->order_seller_id,
                "address_code" => $post->order_address_delivery_code,
                "address_uf_id" => $post->address_delivery->city->uf_id,
                "budget_value" => $post->order_value_total,
                "budget_aliquot_discount" => $post->order_al_discount,
                "budget_value_discount" => $post->order_vl_discount,
                "budget_value_total" => $post->order_value_total,
                "budget_value_addition" => 0,
                "budget_value_icms" => 0,
                "budget_value_st" => 0,
                "budget_cost" => 0,
                "budget_note" => @$post->order_note ? $post->order_note : NULL,
                "export" => NULL,
                "items" => []
            ];

            foreach( $post->order_items as $item ){
                $item = (Object)$item;
                $product = Model::get($dafel,(Object)[
                    "top" => 1,
                    "tables" => [ "Produto" ],
                    "fields" => [
                        "IdClassificacaoFiscal",
                        "IdCalculoICMS",
                        "CdCFOP",
                        "CdCFOPEntreUF",
                        "VlCusto=ISNULL((SELECT TOP 1 VlCusto FROM HistoricoCusto WHERE IdProduto='{$item->product_id}' AND CdEmpresa={$budget->company_id} ORDER BY DtReferencia DESC),0)"
                    ],
                    "filters" => [[ "IdProduto", "s", "=", $item->product_id ]]
                ]);

                $budget->budget_cost += $product->VlCusto;
                $budget->items[] = [
                    "ncm_id" => $product->IdClassificacaoFiscal,
                    "icms_id" => $product->IdCalculoICMS,
                    "product_cfop" => $product->CdCFOP,
                    "product_cfop_extra" => $product->CdCFOPEntreUF,
                    "price_id" => $item->price_id,
                    "product_id" => $item->product_id,
                    "budget_item_quantity" => $item->order_item_amount,
                    "budget_item_value" => $item->order_item_value,
                    "budget_item_value_unitary" => $item->order_item_value_unitary,
                    "budget_item_aliquot_discount" => $item->order_item_al_discount,
                    "budget_item_value_discount" => $item->order_item_vl_discount,
                    "budget_item_value_total" => $item->order_item_value_total,
                    "budget_item_value_st" => 0,
                    "budget_item_value_icms" => 0,
                    "budget_item_cost" => $product->VlCusto
                ];
            }

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

            Budget::taxes();

            $budget_id = (int)Model::insert($commercial, (Object)[
                "table" => "Budget",
                "fields" => [
                    ["company_id", "i", $budget->company_id],
                    ["user_id", "s", $login->user_id],
                    ["client_id", "s", $budget->client_id],
                    ["seller_id", "s", $budget->seller_id],
                    ["address_code", "s", $budget->address_code],
                    ["budget_value", "d", $budget->budget_value],
                    ["budget_aliquot_discount", "d", $budget->budget_aliquot_discount],
                    ["budget_value_discount", "d", $budget->budget_value_discount],
                    ["budget_value_addition", "d", $budget->budget_value_addition],
                    ["budget_value_icms", "d", $budget->budget_value_icms == "NaN" ? 0 : $budget->budget_value_icms],
                    ["budget_value_st", "d", $budget->budget_value_st == "NaN" ? 0 : $budget->budget_value_st],
                    ["budget_value_total", "d", $budget->budget_value_total],
                    ["budget_note", "s", @$budget->budget_note ? $budget->budget_note : NULL],
                    ["budget_note_document", "s", @$budget->budget_note ? $budget->budget_note : NULL],
                    ["budget_credit", "s", "N"],
                    ["budget_delivery", "s", "N"],
                    ["budget_status", "s", "O" ],
                    ["budget_origin", "s", "M"],
                    ["budget_trash", "s", "N"],
                    ["budget_date", "s", $date],
                    ["budget_cost", "d", $budget->budget_cost]
                ]
            ]);

            foreach ($budget->items as $item) {
                $item = (Object)$item;
                Model::insert($commercial, (Object)[
                    "table" => "BudgetItem",
                    "fields" => [
                        ["budget_id", "i", $budget_id],
                        ["product_id", "s", $item->product_id],
                        ["price_id", "s", $item->price_id],
                        ["budget_item_quantity", "d", $item->budget_item_quantity],
                        ["budget_item_value", "d", $item->budget_item_value],
                        ["budget_item_value_unitary", "d", $item->budget_item_value_unitary],
                        ["budget_item_aliquot_discount", "d", $item->budget_item_aliquot_discount],
                        ["budget_item_value_discount", "d", $item->budget_item_value_discount],
                        ["budget_item_value_total", "d", $item->budget_item_value_total],
                        ["budget_item_value_icms", "d", @$item->budget_item_value_icms ? $item->budget_item_value_icms : "0"],
                        ["budget_item_value_st", "d", @$item->budget_item_value_st ? $item->budget_item_value_st : "0"],
                        ["budget_item_date", "s", $date],
                        ["budget_item_cost", "d", $item->budget_item_cost ]
                    ]
                ]);
            }

            postLog((Object)[
                "parent_id" => $budget_id
            ]);

            Json::get($headerStatus[200], (Object)[
                "order_code" => substr("00000{$budget_id}", -6),
                "budget" => $budget
            ]);

        break;

    }

?>