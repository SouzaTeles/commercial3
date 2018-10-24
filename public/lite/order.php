<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $dafel, $commercial, $login, $config, $headerStatus, $get, $post;

    switch( $get->action ){

        case "get":

            if (!@$post->order_id && !@$post->order_code) {
                headerResponse(417, $errorMessage["parameter-post"]);
            }

            $_POST["get_product_stock"] = 1;

            $l_user_price = Model::getList((Object)[
                "class" => "UserPrice",
                "tables" => ["user_price"],
                "filters" => [["user_id", "i", "=", $login->user_id]]
            ]);

            $login->allowed_prices = [];
            foreach ($l_user_price as $user_price) {
                $login->allowed_prices[] = $user_price->price_id;
            }

            $order = Model::get((Object)[
                "class" => "Order",
                "tables" => ["`order`"],
                "filters" => [
                    ["order_id", "i", "=", @$post->order_id ? $post->order_id : NULL],
                    ["order_code", "s", "=", @$post->order_code ? $post->order_code : NULL]
                ]
            ]);

            if (!@$order) {
                headerResponse(404, $errorMessage["order"]["not-found"]);
            }

            Json::get($headerStatus[200], $order);

        break;

        case "getList":

            $start_date = (@$post->start_date ? substr($post->start_date, 0, 10) : "2017-01-01") . ' 00:00:00';
            $end_date = (@$post->end_date ? substr($post->end_date, 0, 10) : date('Y-m-d')) . ' 23:59:59';

            $l_order = Model::getList((Object)[
                "class" => "Order",
                "tables" => ["`order`"],
                "filters" => [
                    ["order_user_id", "i", "=", $login->user_id],
                    ["order_seller_id", "s", "=", @$post->order_seller_id ? $post->order_seller_id : NULL],
                    ["order_trash", "s", "=", "N"],
                    [
                        ["order_update", "s", "between", [$start_date, $end_date]],
                        ["order_date", "s", "between", [$start_date, $end_date]]
                    ]
                ],
                "order" => ORDER_ORDER,
                "limit" => @$post->limit ? $post->limit : 100
            ]);

            if (!sizeof($l_order)) {
                headerResponse(404, $errorMessage["order"]["not-found"]);
            }

            $info = (Object)[
                "quantity" => sizeof($l_order),
                "value_total" => 0
            ];

            foreach ($l_order as $order) {
                $info->value_total += $order->order_value_total;
            }

            $person_id = [];
            foreach ($l_order as $order) {
                $person_id[$order->order_client_id] = $order->order_client_id;
                $person_id[$order->order_seller_id] = $order->order_seller_id;
            }

            $data = Api::getData((Object)[
                "action" => "getFullList",
                "script" => "person",
                "data" => [
                    "person_id" => implode(",", $person_id)
                ]
            ]);

            $person = (Array)$data;
            foreach ($l_order as $order) {
                $order->order_client = @$person[$order->order_client_id] ? $person[$order->order_client_id] : (Object)[];
                $order->order_seller = @$person[$order->order_seller_id] ? $person[$order->order_seller_id] : (Object)[];
            }

            Json::get($headerStatus[200], $l_order, (Object)[
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
            if (!@$post->order_items || !sizeof($post->items)) headerResponse((Object)["code" => 417, "message" => "Nenhum produto informado para o pedido."]);

            $date = date("Y-m-d H:i:s");
            $budget = (Object)[
                "company_id" => $post->order_company_id,
                "client_id" => $post->order_client_id,
                "seller_id" => $post->order_seller_id,
                "address_code" => $post->order_address_delivery_code,
                "address_uf_id" => $post->address_delivery->uf_id,
                "budget_value" => $post->order_value_total,
                "budget_aliquot_discount" => $post->order_al_discount,
                "budget_value_discount" => $post->order_vl_discount,
                "budget_value_total" => $post->order_value_discount,
                "budget_value_addition" => 0,
                "budget_value_icms" => 0,
                "budget_value_st" => 0,
                "budget_cost" => 0,
                "items" => []
            ];

            foreach( $post->order_items as $item ){
                $item = (Object)$item;
                $cost = Model::get($dafel,(Object)[
                    "top" => 1,
                    "tables" => [ "HistoricoCusto" ],
                    "fields" => [ "VlCusto" ],
                    "filters" => [
                        [ "IdProduto", "s", "=", $item->product_id ],
                        [ "CdEmpresa", "i", "=", $budget->company_id ]
                    ],
                    "order" => "DtReferencia DESC"
                ]);
                $budget_item_cost = @$cost && @$cost->VlCusto ? (float)number_format($cost->VlCusto,2,".","") : 0;
                $budget->budget_cost += $budget_item_cost;
                $budget->items[] = (Object)[
                    "product_id" => $item->product_id,
                    "price_id" => $item->price_id,
                    "budget_item_quantity" => $item->budget_item_quantity,
                    "budget_item_value" => $item->budget_item_value,
                    "budget_item_value" => $item->budget_item_value,
                    "budget_item_cost" => $budget_item_cost
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
                    ["budget_note", "s", @$budget->budget_note ? utf8_decode($budget->budget_note) : NULL],
                    ["budget_note_document", "s", @$budget->budget_note_document ? utf8_decode($budget->budget_note_document) : NULL],
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

            $ret = (Object)[
                "budget_id" => $budget_id,
                "budget_status" => @$budget->export ? "L" : "O",
                "budget_code" => substr("00000{$budget_id}", -6),
                "budget_title" => ( @$budget->external_id ? ( $budget->external_type == "D" ? "Dav" : "Pedido" ) : "Orçamento" ),
                "external_id" => $budget->external_id,
                "external_type" => $budget->external_type,
                "external_code" => $budget->external_code,
                "post" => $post
            ];

            postLog((Object)[
                "parent_id" => $budget_id
            ]);

            Json::get($headerStatus[200], $ret);

        break;

    }

?>