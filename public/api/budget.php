<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $conn, $commercial, $dafel, $config, $login, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    checkAccess();

    switch( $get->action ) {

        case "delivery":

            if( !@$post->budget_id || !@$post->budget_delivery || !@$post->budget_delivery_date ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O ID do pedido não foi informado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "Budget",
                "fields" => [
                    [ "budget_delivery", "s", $post->budget_delivery ],
                    [ "budget_delivery_date", "s", $post->budget_delivery_date ],
                    [ "budget_note_document", "s", @$post->budget_note_document ? $post->budget_note_document : NULL ]
                ],
                "filters" => [[ "budget_id", "i", "=", $post->budget_id ]]
            ]);

            postLog((Object)[
                "parent_id" => $post->budget_id
            ]);

            Json::get($headerStatus[200]);

        break;

        case "edit":

            if (!@$post->budget_id) headerResponse((Object)["code" => 417, "message" => "O ID do pedido não foi informado."]);
            if (!@$post->company_id) headerResponse((Object)["code" => 417, "message" => "Verifique se a empresa foi informada."]);
            if (!@$post->client_id) headerResponse((Object)["code" => 417, "message" => "Verifique se o cliente foi informado."]);
            if (!@$post->seller_id) headerResponse((Object)["code" => 417, "message" => "Verifique se o vendedor foi informado ."]);
            if (!@$post->address_code) headerResponse((Object)["code" => 417, "message" => "Verifique se o endereço foi informado."]);
            if (is_null($post->budget_value)) headerResponse((Object)["code" => 417, "message" => "O valor do pedido nao foi informado."]);
            if (is_null($post->budget_aliquot_discount)) headerResponse((Object)["code" => 417, "message" => "A alíquota de desconto do pedido nao foi informada."]);
            if (is_null($post->budget_value_discount)) headerResponse((Object)["code" => 417, "message" => "O valor de desconto do pedido nao foi informado.."]);
            if (is_null($post->budget_value_addition)) headerResponse((Object)["code" => 417, "message" => "O valor de acréscimo do pedido nao foi informado.."]);
            if (is_null($post->budget_value_total)) headerResponse((Object)["code" => 417, "message" => "O valor total do pedido nao foi informado."]);
            if (!@$post->budget_credit) headerResponse((Object)["code" => 417, "message" => "A informação de crédito do pedido nao foi informada."]);
            if (!@$post->budget_delivery) headerResponse((Object)["code" => 417, "message" => "A informação de entrega do pedido nao foi informada."]);
            if (!@$post->items || !sizeof($post->items)) headerResponse((Object)["code" => 417, "message" => "Nenhum produto informado para o pedido."]);

            $budget = $post;
            $date = date("Y-m-d H:i:s");
            $budget_id = $budget->budget_id;
            $budget->credit = (Object)$budget->credit;

            if( @$budget->export ){
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
                $seller = Model::get($dafel,(Object)[
                    "tables" => [ "Representante (NoLock)" ],
                    "fields" => [
                        "AlComissaoFaturamento",
                        "AlComissaoDuplicata",
                        "StComissao",
                        "TpComissao"
                    ],
                    "filters" => [[ "IdPessoaRepresentante", "s", "=", $budget->seller_id ]]
                ]);
                Budget::taxes();
                Budget::export();
            }

            $budget_origin = "D";
            $payment_icon = NULL;
            if( @$budget->payments ){
                $payment_icon = sizeof($budget->payments) > 1 ? "SEVERAL" : $budget->payments[0]["modality_id"];
            }
            else if( $budget->credit->value > 0 ){
                $payment_icon = $config->credit->modality_id;
            }

            Model::update($commercial, (Object)[
                "table" => "Budget",
                "fields" => [
                    ["client_id", "s", $budget->client_id],
                    ["seller_id", "s", $budget->seller_id],
                    ["address_code", "s", $budget->address_code],
                    ["term_id", "s", @$budget->term_id ? $budget->term_id : NULL],
                    ["external_id", "s", @$budget->external_id ? $budget->external_id : NULL],
                    ["external_type", "s", @$budget->external_type ? $budget->external_type : NULL],
                    ["external_code", "s", @$budget->external_code ? $budget->external_code : NULL],
                    ["budget_value", "d", $budget->budget_value],
                    ["budget_aliquot_discount", "d", $budget->budget_aliquot_discount],
                    ["budget_value_discount", "d", $budget->budget_value_discount],
                    ["budget_value_addition", "d", $budget->budget_value_addition],
                    ["budget_value_icms", "d", $budget->budget_value_icms],
                    ["budget_value_st", "d", $budget->budget_value_st],
                    ["budget_value_total", "d", $budget->budget_value_total],
                    ["budget_note", "s", @$budget->budget_note ? $budget->budget_note : NULL],
                    ["budget_note_document", "s", @$budget->budget_note_document ? $budget->budget_note_document : NULL],
                    ["budget_payment_icon","s", $payment_icon],
                    ["budget_credit", "s", $budget->budget_credit],
                    ["budget_delivery", "s", $budget->budget_delivery],
                    ["budget_status", "s", @$budget->export ? "L" : "O" ],
                    ["budget_delivery_date", "s", @$budget->budget_delivery_date ? $budget->budget_delivery_date : NULL],
                    ["budget_update", "s", $date]
                ],
                "filters" => [[ "budget_id", "i", "=", $budget_id ]]
            ]);

            $items = [];
            foreach ($budget->items as $item) {
                $item = (Object)$item;
                $fields = [
                    ["external_id", "s", @$item->external_id ? $item->external_id : NULL],
                    ["price_id", "s", $item->price_id],
                    ["budget_item_quantity", "d", $item->budget_item_quantity],
                    ["budget_item_value", "d", $item->budget_item_value],
                    ["budget_item_value_unitary", "d", $item->budget_item_value_unitary],
                    ["budget_item_aliquot_discount", "d", $item->budget_item_aliquot_discount],
                    ["budget_item_value_discount", "d", $item->budget_item_value_discount],
                    ["budget_item_value_total", "d", $item->budget_item_value_total],
                    ["budget_item_value_icms", "d", @$item->budget_item_value_icms ? $item->budget_item_value_icms : "0"],
                    ["budget_item_value_st", "d", @$item->budget_item_value_st ? $item->budget_item_value_st : "0"],
                ];
                if( @$item->budget_item_id ) {
                    $items[] = $item->budget_item_id;
                    $fields[] = ["budget_item_update", "s", $date];
                    Model::update($commercial, (Object)[
                        "table" => "BudgetItem",
                        "fields" => $fields,
                        "filters" => [[ "budget_item_id", "i", "=", $item->budget_item_id ]]
                    ]);
                } else {
                    $fields[] = ["budget_id", "i", $budget_id];
                    $fields[] = ["product_id", "s", $item->product_id];
                    $fields[] = ["budget_item_date", "s", $date];
                    $items[] = (int)Model::insert($commercial, (Object)[
                        "table" => "BudgetItem",
                        "fields" => $fields
                    ]);
                }
            }

            if( $budget->credit->value > 0 ){
                $payment_id = Model::insert($commercial, (Object)[
                    "table" => "BudgetPayment",
                    "fields" => [
                        ["budget_id", "i", $budget_id],
                        ["modality_id", "s", $config->credit->modality_id],
                        ["external_id", "s", $budget->credit->external_id],
                        ["budget_payment_value", "d", $budget->credit->value],
                        ["budget_payment_installment", "i", 1],
                        ["budget_payment_entry", "s", "N"],
                        ["budget_payment_credit", "s", "Y"],
                        ["budget_payment_deadline", "s", date("Y-m-d")],
                        ["budget_payment_date", "s", date("Y-m-d")]
                    ]
                ]);
                foreach( $budget->credit->payable as $credit ){
                    $credit = (Object)$credit;
                    Model::insert($commercial, (Object)[
                        "table" => "BudgetPaymentCredit",
                        "fields" => [
                            ["budget_payment_id", "i", $payment_id],
                            ["payable_id", "s", $credit->payable_id],
                            ["payable_value", "s", $credit->payable_value],
                            ["budget_payment_credit_date", "s", date("Y-m-d H:i:s")]
                        ]
                    ]);
                }
            }

            $payments = [];
            if( @$budget->payments ){
                foreach( $budget->payments as $payment ){
                    $payment = (Object)$payment;
                    $fields = [
                        ["modality_id", "s", $payment->modality_id],
                        ["external_id", "s", @$payment->external_id ? $payment->external_id : NULL],
                        ["bank_id", "s", @$payment->bank_id ? $payment->bank_id : NULL],
                        ["agency_id", "s", @$payment->agency_id ? $payment->agency_id : NULL],
                        ["agency_code", "s", @$payment->agency_code ? $payment->agency_code : NULL],
                        ["check_number", "s", @$payment->check_number ? $payment->check_number : NULL],
                        ["budget_payment_value", "d", $payment->budget_payment_value],
                        ["budget_payment_installment", "d", $payment->budget_payment_installment],
                        ["budget_payment_entry", "s", $payment->budget_payment_entry],
                        ["budget_payment_credit", "s", "N"],
                        ["budget_payment_deadline", "s", $payment->budget_payment_deadline]
                    ];
                    if( @$payment->budget_payment_id ){
                        $payments[] = $payment->budget_payment_id;
                        $fields[] = ["budget_payment_update", "s", $date];
                        Model::update($commercial, (Object)[
                            "table" => "BudgetPayment",
                            "fields" => $fields,
                            "filters" => [[ "budget_payment_id", "i", "=", $payment->budget_payment_id ]]
                        ]);
                    } else {
                        $fields[] = ["budget_id", "i", $budget_id];
                        $fields[] = ["budget_payment_date", "s", $date];
                        $payments[] = (int)Model::insert($commercial, (Object)[
                            "table" => "BudgetPayment",
                            "fields" => $fields
                        ]);
                    }
                }
            }

            Model::delete($commercial,(Object)[
                "top" => 99,
                "table" => "BudgetItem",
                "filters" => [
                    [ "budget_id", "i", "=", $budget_id ],
                    [ "budget_item_id", "i", "not in", $items ]
                ]
            ]);

            Model::delete($commercial,(Object)[
                "top" => 99,
                "table" => "BudgetPayment",
                "filters" => [
                    [ "budget_id", "i", "=", $budget_id ],
                    [ "budget_payment_credit", "s", "=", "N" ],
                    [ "budget_payment_id", "i", "not in", sizeof($payments) ? $payments : NULL ]
                ]
            ]);

            postLog((Object)[
                "parent_id" => $budget_id
            ]);

            Json::get($headerStatus[200], (Object)[
                "budget_id" => $budget_id,
                "budget_code" => substr("00000{$budget_id}", -6),
                "budget_title" => ( @$budget->external_id ? ( $budget->external_type == "D" ? "Dav" : "Pedido" ) : "Orçamento" ),
                "external_id" => $budget->external_id,
                "external_type" => $budget->external_type,
                "external_code" => $budget->external_code
            ]);

        break;

        case "get":

            if (!@$post->budget_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $budget = Model::get($commercial, (Object)[
                "class" => "Budget",
                "tables" => [ "Budget" ],
                "fields" => [
                    "budget_id",
                    "company_id",
                    "user_id",
                    "client_id",
                    "seller_id",
                    "address_code",
                    "term_id",
                    "external_id",
                    "external_type",
                    "external_code",
                    "document_id",
                    "document_type",
                    "document_code",
                    "document_canceled",
                    "budget_value=CAST(budget_value AS FLOAT)",
                    "budget_aliquot_discount=CAST(budget_aliquot_discount AS FLOAT)",
                    "budget_value_discount=CAST(budget_value_discount AS FLOAT)",
                    "budget_value_addition=CAST(budget_value_addition AS FLOAT)",
                    "budget_value_icms=CAST(budget_value_icms AS FLOAT)",
                    "budget_value_st=CAST(budget_value_st AS FLOAT)",
                    "budget_value_total=CAST(budget_value_total AS FLOAT)",
                    "budget_note",
                    "budget_note_document",
                    "budget_credit",
                    "budget_delivery",
                    "budget_status",
                    "budget_origin",
                    "budget_trash",
                    "budget_delivery_date=FORMAT(budget_delivery_date,'yyyy-MM-dd')",
                    "budget_update=FORMAT(budget_update,'yyyy-MM-dd HH:mm:ss')",
                    "budget_date=FORMAT(budget_date,'yyyy-MM-dd HH:mm:ss')"
                ],
                "filters" => [
                    [ "budget_trash", "s", "=", "N"],
                    [ "budget_id", "i", "=", $post->budget_id ]
                ]
            ]);

            if( !@$budget ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Orçamento não encontrado."
                ]);
            }

            Json::get($headerStatus[200], $budget);

        break;

        case "getDelivery":

            if( !@$post->budget_id ) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $delivery = Model::get($commercial, (Object)[
                "tables" => [ "Budget" ],
                "fields" => [
                    "budget_delivery",
                    "budget_note_document",
                    "budget_delivery_date=FORMAT(budget_delivery_date,'yyyy-MM-dd')"
                ],
                "filters" => [[ "budget_id", "i", "=", $post->budget_id ]]
            ]);

            Json::get($headerStatus[200], $delivery);

         break;

        case "getList":

            if (!@$post->company_id || !@$post->start_date || !@$post->end_date) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $budgets = Model::getList($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.Budget B",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa P ON(P.IdPessoa = B.client_id)",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa PR ON(PR.IdPessoa = B.seller_id)"
                ],
                "fields" => [
                    "B.budget_id",
                    "B.external_id",
                    "B.external_type",
                    "B.external_code",
                    "B.document_id",
                    "B.document_type",
                    "B.document_code",
                    "B.document_canceled",
                    "B.client_id",
                    "client_code=P.CdChamada",
                    "client_name=P.NmPessoa",
                    "B.seller_id",
                    "seller_code=PR.CdChamada",
                    "seller_name=PR.NmPessoa",
                    "seller_short_name=PR.NmCurto",
                    "budget_value_st=CAST(B.budget_value_st AS FLOAT)",
                    "budget_value_total=CAST(B.budget_value_total AS FLOAT)",
                    "B.budget_origin",
                    "B.budget_status",
                    "B.budget_delivery",
                    "B.budget_payment_icon",
                    "budget_date=FORMAT(B.budget_date,'yyyy-MM-dd HH:mm:ss')"
                ],
                "filters" => [
                    ["B.company_id", "i", "=", $post->company_id],
                    ["B.seller_id", "s", "=", @$post->seller_id ? $post->seller_id : NULL],
                    ["B.budget_date", "s", "between", ["{$post->start_date} 00:00:00", "{$post->end_date} 23:59:59"]]
                ],
                "group" => "B.budget_id,B.external_id,B.external_type,B.external_code,B.document_id,B.document_type,B.document_code,B.document_canceled,B.client_id,P.CdChamada,P.NmPessoa,B.seller_id,PR.CdChamada,PR.NmPessoa,PR.NmCurto,B.budget_value_st,B.budget_value_total,B.budget_origin,B.budget_status,B.budget_delivery,B.budget_payment_icon,B.budget_date"
            ]);

            $ret = [];
            foreach ($budgets as $budget) {
                $icon = NULL;
                if( @$budget->budget_payment_icon ){
                    if( $budget->budget_payment_icon == "SEVERAL" ){
                        $icon = URI_FILES . "modality/several.png";
                    } else {
                        $icon = getImage((Object)[
                            "image_id" => $budget->budget_payment_icon,
                            "image_dir" => "modality"
                        ]);
                    }

                }
                $ret[] = (Object)[
                    "budget" => (Object)[
                        "id" => (int)$budget->budget_id,
                        "value_st" => (float)$budget->budget_value_st,
                        "code" => substr("000000{$budget->budget_id}",-6),
                        "value_total" => (float)($budget->budget_value_total+$budget->budget_value_st),
                        "value_total_order" => substr("0000000000" . number_format((float)($budget->budget_value_total+$budget->budget_value_st),2,"",""),-10),
                        "type" => @$budget->external_type ? $budget->external_type : "B",
                        "origin" => $budget->budget_origin,
                        "status" => $budget->budget_status,
                        "delivery" => $budget->budget_delivery,
                        "date" => $budget->budget_date,
                        "date_formatted" => date_format(date_create($budget->budget_date),"d/m/Y"),
                        "payment" => $budget->budget_payment_icon,
                        "icon" => $icon
                    ],
                    "external" => (Object)[
                        "id" => $budget->external_id,
                        "type" => $budget->external_type,
                        "code" => $budget->external_code
                    ],
                    "document" => (Object)[
                        "id" => $budget->document_id,
                        "code" => $budget->document_code,
                        "type" => $budget->document_type,
                        "canceled" => $budget->document_canceled,
                    ],
                    "person" => (Object)[
                        "id" => $budget->client_id,
                        "code" => $budget->client_code,
                        "name" => $budget->client_name,
                        "image" => getImage((Object)[
                            "image_id" => $budget->client_id,
                            "image_dir" => "person",
                        ])
                    ],
                    "seller" => (Object)[
                        "id" => $budget->seller_id,
                        "code" => $budget->seller_code,
                        "name" => $budget->seller_name,
                        "short_name" => $budget->seller_short_name,
                        "image" => getImage((Object)[
                            "image_id" => $budget->seller_id,
                            "image_dir" => "person",
                        ])
                    ]
                ];
            }

            Json::get($headerStatus[200], $ret);

        break;

        case "insert":

            if (!@$post->company_id) headerResponse((Object)["code" => 417, "message" => "Verifique se a empresa foi informada."]);
            if (!@$post->client_id) headerResponse((Object)["code" => 417, "message" => "Verifique se o cliente foi informado."]);
            if (!@$post->seller_id) headerResponse((Object)["code" => 417, "message" => "Verifique se o vendedor foi informado ."]);
            if (!@$post->address_code) headerResponse((Object)["code" => 417, "message" => "Verifique se o endereço foi informado."]);
            if (is_null($post->budget_value)) headerResponse((Object)["code" => 417, "message" => "O valor do pedido nao foi informado."]);
            if (is_null($post->budget_aliquot_discount)) headerResponse((Object)["code" => 417, "message" => "A alíquota de desconto do pedido nao foi informada."]);
            if (is_null($post->budget_value_discount)) headerResponse((Object)["code" => 417, "message" => "O valor de desconto do pedido nao foi informado.."]);
            if (is_null($post->budget_value_addition)) headerResponse((Object)["code" => 417, "message" => "O valor de acréscimo do pedido nao foi informado.."]);
            if (is_null($post->budget_value_total)) headerResponse((Object)["code" => 417, "message" => "O valor total do pedido nao foi informado."]);
            if (!@$post->budget_credit) headerResponse((Object)["code" => 417, "message" => "A informação de crédito do pedido nao foi informada."]);
            if (!@$post->budget_delivery) headerResponse((Object)["code" => 417, "message" => "A informação de entrega do pedido nao foi informada."]);
            if (!@$post->items || !sizeof($post->items)) headerResponse((Object)["code" => 417, "message" => "Nenhum produto informado para o pedido."]);

            $budget = $post;
            $date = date("Y-m-d H:i:s");
            $budget->credit = (Object)$budget->credit;

            if( $budget->credit->value > 0 ){
                foreach( $budget->credit->payable as $credit ){
                    $credit = (Object)$credit;
                    $table = Model::get($dafel,(Object)[
                        "tables" => [ "TempDB..sysObjects" ],
                        "fields" => [ "Name" ],
                        "filters" => [
                            [ "SUBSTRING(Name,12,10)", "s", "=", $credit->payable_id ],
                            [ "SUBSTRING(Name,23,10)", "s", "=", $budget->instance_id ]
                        ]
                    ]);
                    if( !@$table->Name ){
                        headerResponse((Object)[
                            "code" => 417,
                            "message" => "O crédito não foi empenhado. Contate o setor de TI."
                        ]);
                    }
                }
            }

            if( @$budget->export ){
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
                $seller = Model::get($dafel,(Object)[
                    "tables" => [ "Representante (NoLock)" ],
                    "fields" => [
                        "AlComissaoFaturamento",
                        "AlComissaoDuplicata",
                        "StComissao",
                        "TpComissao"
                    ],
                    "filters" => [[ "IdPessoaRepresentante", "s", "=", $budget->seller_id ]]
                ]);
                Budget::taxes();
                Budget::export();
            }

            $budget_origin = "D";
            $payment_icon = NULL;
            if( @$budget->payments ){
                $payment_icon = sizeof($budget->payments) > 1 ? "SEVERAL" : $budget->payments[0]["modality_id"];
            }
            else if( $budget->credit->value > 0 ){
                $payment_icon = $config->credit->modality_id;
            }

            $budget_id = (int)Model::insert($commercial, (Object)[
                "table" => "Budget",
                "fields" => [
                    ["company_id", "i", $budget->company_id],
                    ["user_id", "s", $login->user_id],
                    ["client_id", "s", $budget->client_id],
                    ["seller_id", "s", $budget->seller_id],
                    ["address_code", "s", $budget->address_code],
                    ["term_id", "s", @$budget->term_id ? $budget->term_id : NULL],
                    ["external_id", "s", @$budget->external_id ? $budget->external_id : NULL],
                    ["external_type", "s", @$budget->external_type ? $budget->external_type : NULL],
                    ["external_code", "s", @$budget->external_code ? $budget->external_code : NULL],
                    ["budget_value", "d", $budget->budget_value],
                    ["budget_aliquot_discount", "d", $budget->budget_aliquot_discount],
                    ["budget_value_discount", "d", $budget->budget_value_discount],
                    ["budget_value_addition", "d", $budget->budget_value_addition],
                    ["budget_value_icms", "d", $budget->budget_value_icms],
                    ["budget_value_st", "d", $budget->budget_value_st],
                    ["budget_value_total", "d", $budget->budget_value_total],
                    ["budget_note", "s", @$budget->budget_note ? $budget->budget_note : NULL],
                    ["budget_note_document", "s", @$budget->budget_note_document ? $budget->budget_note_document : NULL],
                    ["budget_payment_icon","s",$payment_icon],
                    ["budget_credit", "s", $budget->budget_credit],
                    ["budget_delivery", "s", $budget->budget_delivery],
                    ["budget_status", "s", @$budget->export ? "L" : "O" ],
                    ["budget_origin", "s", $budget_origin],
                    ["budget_trash", "s", "N"],
                    ["budget_delivery_date", "s", @$budget->budget_delivery_date ? $budget->budget_delivery_date : NULL],
                    ["budget_date", "s", $date]
                ]
            ]);

            foreach ($budget->items as $item) {
                $item = (Object)$item;
                Model::insert($commercial, (Object)[
                    "table" => "BudgetItem",
                    "fields" => [
                        ["budget_id", "i", $budget_id],
                        ["external_id", "s", @$item->external_id ? $item->external_id : NULL],
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
                        ["budget_item_date", "s", $date]
                    ]
                ]);
            }

            if( $budget->credit->value > 0 ){
                $payment_id = Model::insert($commercial, (Object)[
                    "table" => "BudgetPayment",
                    "fields" => [
                        ["budget_id", "i", $budget_id],
                        ["modality_id", "s", $config->credit->modality_id],
                        ["external_id", "s", $budget->credit->external_id],
                        ["budget_payment_value", "d", $budget->credit->value],
                        ["budget_payment_installment", "i", 1],
                        ["budget_payment_entry", "s", "N"],
                        ["budget_payment_credit", "s", "Y"],
                        ["budget_payment_deadline", "s", date("Y-m-d")],
                        ["budget_payment_date", "s", date("Y-m-d")]
                    ]
                ]);
                foreach( $budget->credit->payable as $credit ){
                    $credit = (Object)$credit;
                    Model::insert($commercial, (Object)[
                        "table" => "BudgetPaymentCredit",
                        "fields" => [
                            ["budget_payment_id", "i", $payment_id],
                            ["payable_id", "s", $credit->payable_id],
                            ["payable_value", "s", $credit->payable_value],
                            ["budget_payment_credit_date", "s", date("Y-m-d H:i:s")]
                        ]
                    ]);
                }
            }

            if (@$budget->payments) {
                foreach ($budget->payments as $payment) {
                    $payment = (Object)$payment;
                    Model::insert($commercial, (Object)[
                        "table" => "BudgetPayment",
                        "fields" => [
                            ["budget_id", "i", $budget_id],
                            ["modality_id", "s", $payment->modality_id],
                            ["external_id", "s", @$payment->external_id ? $payment->external_id : NULL],
                            ["bank_id", "s", @$payment->bank_id ? $payment->bank_id : NULL],
                            ["agency_id", "s", @$payment->agency_id ? $payment->agency_id : NULL],
                            ["agency_code", "s", @$payment->agency_code ? $payment->agency_code : NULL],
                            ["check_number", "s", @$payment->check_number ? $payment->check_number : NULL],
                            ["budget_payment_value", "d", $payment->budget_payment_value],
                            ["budget_payment_installment", "i", $payment->budget_payment_installment],
                            ["budget_payment_entry", "s", $payment->budget_payment_entry],
                            ["budget_payment_credit", "s", "N"],
                            ["budget_payment_deadline", "s", $payment->budget_payment_deadline],
                            ["budget_payment_date", "s", $date]
                        ]
                    ]);
                }
            }

            if( @$budget->authorization ){
                Model::update($commercial,(Object)[
                    "table" => "[Log]",
                    "fields" => [[ "log_parent_id", "s", $budget_id ]],
                    "filters" => [[ "log_id", "i", "in", $budget->authorization ]]
                ]);
            }

            $ret = (Object)[
                "budget_id" => $budget_id,
                "budget_code" => substr("00000{$budget_id}", -6),
                "budget_title" => ( @$budget->external_id ? ( $budget->external_type == "D" ? "Dav" : "Pedido" ) : "Orçamento" ),
                "external_id" => $budget->external_id,
                "external_type" => $budget->external_type,
                "external_code" => $budget->external_code
            ];

            postLog((Object)[
                "parent_id" => $budget_id
            ]);

            Json::get($headerStatus[200], $ret);

        break;

        case "recover":

            if (!@$post->budget_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $budget = Model::get($commercial,(Object)[
                "tables" => [ "Budget" ],
                "fields" => [
                    "budget_id",
                    "external_id",
                    "budget_status",
                    "budget_credit"
                ],
                "filters" => [[ "budget_id", "i", "=", $post->budget_id ]]
            ]);

            if( !@$budget ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Orçamento não encontrado."
                ]);
            }

            $order = Model::get($dafel,(Object)[
                "tables" => [ "PedidoDeVenda" ],
                "fields" => [ "StPedidoDeVenda" ],
                "filters" => [[ "IdPedidoDeVenda", "s", "=", $budget->external_id ]]
            ]);

            if( !@$order ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O pedido não foi encontrado no ERP."
                ]);
            }

            if( $order->StPedidoDeVenda == "T" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não será possivel editar o pedido, pois o mesmo está sendo faturado."
                ]);
            }

            if( $budget->budget_credit == "Y" ){
                $credits = Model::getList($commercial,(Object)[
                    "tables" => [
                        "Budget B",
                        "BudgetPayment BP",
                        "BudgetPaymentCredit BPC"
                    ],
                    "fields" => [
                        "BP.budget_payment_id",
                        "BP.external_id",
                        "BPC.payable_id",
                        "BPC.payable_value"
                    ],
                    "filters" => [
                        [ "B.budget_id = BP.budget_id" ],
                        [ "BP.budget_payment_id = BPC.budget_payment_id" ],
                        [ "BP.budget_payment_credit", "s", "=", "Y" ],
                        [ "B.budget_id", "i", "=", $budget->budget_id ]
                    ]
                ]);

                $payable = [];
                $IdEntidadeOrigem = NULL;
                $budget_payment_id = NULL;
                foreach( $credits as $credit ){
                    $payable[] = $credit->payable_id;
                    $IdEntidadeOrigem = $credit->external_id;
                    $budget_payment_id = $credit->budget_payment_id;
                }

                if( !@$IdEntidadeOrigem || !@$budget_payment_id ){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "Não foi possível recuperar o orçamento. Contate o setor de TI."
                    ]);
                }

                $drops = Model::getList($dafel,(Object)[
                    "tables" => [ "APagarBaixa" ],
                    "fields" => [ "IdAPagarBaixa", "IdLoteAPagar" ],
                    "filters" => [
                        [ "IdEntidadeOrigem", "s", "=", $IdEntidadeOrigem ],
                        [ "IdAPagar", "s", "in", $payable ]
                    ]
                ]);

                if( sizeof($drops) != sizeof($payable) ){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "Não foi possível recuperar o orçamento. Uma ou mais Baixas do crédito não foram encontradas."
                    ]);
                }

                $payableDrop=[];
                $payableLot=[];
                foreach( $drops as $drop ){
                    $payableDrop[] = $drop->IdAPagarBaixa;
                    $payableLot[] = $drop->IdLoteAPagar;
                }

                Model::delete($dafel,(Object)[
                    "table" => "LoteAPagar",
                    "filters" => [[ "IdLoteAPagar", "s", "in", $payableLot ]],
                    "top" => sizeof($payableLot)
                ]);

                Model::delete($dafel,(Object)[
                    "table" => "APagarBaixa",
                    "filters" => [
                        [ "IdEntidadeOrigem", "s", "=", $IdEntidadeOrigem ],
                        [ "IdAPagarBaixa", "s", "in", $payableDrop ]
                    ],
                    "top" => sizeof($payableDrop)
                ]);

                Model::update($dafel,(Object)[
                    "table" => "APagar",
                    "fields" => [[ "DtBaixa", "s", NULL ]],
                    "filters" => [[ "IdAPagar", "s", "in", $payable ]],
                    "top" => sizeof($payable)
                ]);

                Model::delete($commercial,(Object)[
                    "table" => "BudgetPayment",
                    "filters" => [
                        ["budget_id", "i", "=", $budget->budget_id],
                        ["budget_payment_id", "i", "=", $budget_payment_id]
                    ]
                ]);

                Model::delete($commercial,(Object)[
                    "table" => "BudgetPaymentCredit",
                    "filters" => [["budget_payment_id", "i", "=", $budget_payment_id]]
                ]);
            }

            Model::update($dafel,(Object)[
                "table" => "PedidoDeVenda",
                "fields" => [[ "StPedidoDeVenda", "s", "X" ]],
                "filters" => [[ "IdPedidoDeVenda", "s", "=", $budget->external_id ]]
            ]);

            Model::update($commercial,(Object)[
                "table" => "Budget",
                "fields" => [[ "budget_status", "s", "O" ]],
                "filters" => [[ "budget_id", "s", "=", $budget->budget_id ]]
            ]);

            postLog((Object)[
                "parent_id" => $budget->budget_id
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Orçamento recuperado com sucesso!"
            ]);

        break;

        case "creditAuthorization":
        case "discountItemAuthorization":

            if( !@$post->user_user || !@$post->user_pass || !@$post->data ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $post->data = (Object)$post->data;

            $user = Model::get( $commercial, (Object)[
                "tables" => [ "[User]" ],
                "fields" => [
                    "user_id",
                    "user_name",
                    "user_active",
                    "user_credit_authorization",
                    "user_max_discount=CAST(user_max_discount AS FLOAT)"
                ],
                "filters" => [
                    [ "user_user", "s", "=", $post->user_user ],
                    [ "user_pass", "s", "=", md5($post->user_pass) ]
                ]
            ]);

            if( !@$user ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Login e/ou senha incorretos."
                ]);
            }

            if( $user->user_active == "N" ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O usuário está inativo."
                ]);
            }

            if( $get->action == "creditAuthorization" ) {
                if ($user->user_credit_authorization == "N") {
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "O usuário não possui permissão para liberação de crédito."
                    ]);
                }
            } else {
                $user->user_max_discount = (float)$user->user_max_discount;
                $post->data->item_aliquot_discount = (float)$post->data->item_aliquot_discount;
                if ($user->user_max_discount < $post->data->item_aliquot_discount) {
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "Desconto acima do permitido."
                    ]);
                }
            }

            $user->authorization_id = postLog((Object)[
                "user_id" => $user->user_id
            ]);

            Json::get( $headerStatus[200], $user );

        break;

    }

?>