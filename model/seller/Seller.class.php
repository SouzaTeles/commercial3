<?php

    class Seller
    {
        public $seller_id;
        public $company_id;
        public $seller_type;
        public $seller_code;
        public $seller_active;
        public $seller_name;
        public $seller_email;
        public $seller_target;
        public $seller_commission;
        public $seller_update;
        public $seller_date;

        public function __construct($data)
        {
            $this->seller_id = $data->seller_id;
            $this->company_id = $data->company_id;
            $this->seller_type = $data->seller_type;
            $this->seller_code = $data->seller_code;
            $this->seller_active = $data->seller_active;
            $this->seller_name = $data->seller_name;
            $this->seller_email = $data->seller_email;
            $this->seller_target = $data->seller_target;
            $this->seller_commission = $data->seller_commission;
            $this->seller_update = @$data->seller_update ? $data->seller_update : NULL;
            $this->seller_date = $data->seller_date;

            $this->image = getImage((Object)[
                "image_id" => $data->seller_id,
                "image_dir" => "person"
            ]);
        }

        public static function Dashboard($params)
        {
            GLOBAL $commercial, $metas, $commercial;

            $reference = new DateTime($params->reference);

            $data1 = Model::getList($metas,(Object)[
                "tables" => [ "seller s", "target t" ],
                "fields" => [ "s.IdERP", "t.target_val" ],
                "filters" => [
                    [ "s.seller_id = t.seller_id" ],
                    [ "t.target_type", "i", "=", "2" ],
                    [ "t.target_date_start", "s", "=", $reference->format("Y-m-01") ]
                ]
            ]);
            
            $ids = [];
            $targets = [];
            foreach( $data1 as $data ){
                $ids[] = $data->IdERP;
                $targets[$data->IdERP] = $data;
            }

            $dataDailyLost = Model::getList($commercial,(Object)[
                "tables" => [ "`order`" ],
                "fields" => [
                    "order_seller_id",
                    "count(order_id) as quantity",
                    "sum(order_value_total+order_value_st) as value"
                ],
                "filters" => [
                    [ "order_trash", "s", "=", "N" ],
                    [ "order_status_id", "i", "!=", 1003 ],
                    [ "convert(order_date,date)", "s", "=", $params->reference ]
                ],
                "group" => "order_seller_id"
            ]);

            $dataMonthlyLost = Model::getList($commercial,(Object)[
                "tables" => [ "`order`" ],
                "fields" => [
                    "order_seller_id",
                    "count(order_id) as quantity",
                    "sum(order_value_total+order_value_st) as value"
                ],
                "filters" => [
                    [ "order_trash", "s", "=", "N" ],
                    [ "order_status_id", "i", "!=", 1003 ],
                    [ "order_date", "s", "between",[$reference->format("Y-m-01"),$params->reference] ]
                ],
                "group" => "order_seller_id"
            ]);

            $dailyLost = [];
            foreach( $dataDailyLost as $lost ){
                $lost->value = (float)$lost->value;
                $lost->quantity = (float)$lost->quantity;
                $dailyLost[$lost->order_seller_id] = $lost;
            }

            $monthlyLost = [];
            foreach( $dataMonthlyLost as $lost ){
                $lost->value = (float)$lost->value;
                $lost->quantity = (float)$lost->quantity;
                $monthlyLost[$lost->order_seller_id] = $lost;
            }

            $data = Model::getList($commercial,(Object)[
                "tables" => [ "billing b", "seller s", "company c" ],
                "fields" => [
                    "b.billing_reference",
                    "b.parent_id",
                    "c.company_code",
                    "s.seller_code",
                    "s.seller_name",
                    "SUM(b.billing_value+billing_st) as billing_value",
                    "SUM(b.billing_quantity) as billing_quantity",
                    "SUM(b.billing_cost) as billing_cost"
                ],
                "filters" => [
                    [ "b.parent_id = s.erp_id" ],
                    [ "s.company_id = c.company_id" ],
                    [ "b.billing_section", "s", "=", "seller" ],
                    [ "b.parent_id", "s", "in", $ids ],
                    [ "b.billing_reference", "s", "between", [$reference->format("Y-m-01"),$params->reference] ]
                ],
                "group" => "b.billing_reference, b.parent_id, c.company_code, s.seller_code, s.seller_name"
            ]);

            $daily = [];
            $billing = [];
            $billingDay = [];
            foreach( $data as $bill ){
                $bill->billing_cost = (float)$bill->billing_cost;
                $bill->billing_value = (float)$bill->billing_value;
                $bill->billing_quantity = (int)$bill->billing_quantity;
                if( $bill->billing_reference == $reference->format("Y-m-d") ){
                    $calendar = calendar((Object)[
                        "company_id" => (int)$bill->company_code,
                        "reference" => $reference->format("Y-m-d")
                    ]);
                    $target = $targets[$bill->parent_id];
                    $lost = @$dailyLost[$bill->parent_id] ? $dailyLost[$bill->parent_id] : NULL;
                    $bill->target_value = $target->target_val ? ($target->target_val/$calendar->days) : 0;
                    $bill->target_percent = $target->target_val ? ($bill->billing_value / $bill->target_value) * 100 : 0;
                    $bill->broke = ($bill->target_percent >= 100);
                    $bill->ticket = $bill->billing_value / (@$bill->billing_quantity ? $bill->billing_quantity : 1);
                    $bill->stars = (int)(($bill->target_percent - 100) / 10);
                    $bill->lost_value = @$lost ? $lost->value : 0;
                    $bill->lost_quantity = @$lost ? $lost->value : 0;
                    $bill->profit_value = ($bill->billing_value - $bill->billing_cost);
                    $bill->conversion = @$lost ? (($bill->billing_value / ($bill->billing_value + $lost->value)) * 100) : (@$bill->billing_value ? 100 : 0);
                    $daily[] = clone $bill;
                }
                if( !@$billing[$bill->parent_id] ){
                    $billing[$bill->parent_id] = clone $bill;
                    $billing[$bill->parent_id]->parent_id = $bill->parent_id;
                    $billing[$bill->parent_id]->billing_cost = 0;
                    $billing[$bill->parent_id]->billing_value = 0;
                    $billing[$bill->parent_id]->billing_quantity = 0;
                    $billing[$bill->parent_id]->profit_value = 0;
                }
                $billing[$bill->parent_id]->billing_cost += $bill->billing_cost;
                $billing[$bill->parent_id]->billing_value += $bill->billing_value;
                $billing[$bill->parent_id]->billing_quantity += $bill->billing_quantity;
                $billing[$bill->parent_id]->profit_value += ($bill->billing_value-$bill->billing_cost);
                $billingDay[$bill->parent_id][$bill->billing_reference] = $bill->billing_value;
            }

            $monthly = [];
            foreach( $billing as $bill ){
                $calendar = calendar((Object)[
                    "company_id" => (int)$bill->company_code,
                    "reference" => $reference->format("Y-m-d")
                ]);
                $target = $targets[$bill->parent_id];
                $lost = @$monthlyLost[$bill->parent_id] ? $monthlyLost[$bill->parent_id] : NULL;
                $bill->target_value = $target->target_val;
                $bill->target_percent = ($bill->billing_value/$bill->target_value)*100;
                $bill->broke = ($bill->target_percent >= 100);
                $bill->ticket = $bill->billing_value/($bill->billing_quantity?$bill->billing_quantity:1);
                $bill->stars = (int)(($bill->target_percent-100)/10);
                $bill->lost_value = @$lost ? $lost->value : 0;
                $bill->lost_quantity = @$lost ? $lost->quantity : 0;
                $bill->conversion = @$lost ? (($bill->billing_value/($bill->billing_value+$lost->value))*100) : (@$bill->billing_value?100:0);
                $bill->today = (Object)[
                    "percent" => ($calendar->past/$calendar->days)*100,
                    "value" => $calendar->past * ($target->target_val/$calendar->days)
                ];
                $bill->calendar = $calendar;
                $bill->exploitation = ($bill->billing_value/($calendar->past*($target->target_val/$calendar->days)))*100;
                $bill->running = 0;
                foreach( $billingDay[$bill->parent_id] as $value ){
                    if( $value >= ($target->target_val/$calendar->days) ){
                        $bill->running++;
                    }
                }
                $monthly[] = clone $bill;
            }

            $dailyRet = [];
            foreach( $daily as $data ){
                $dailyRet[] = $data;
            }

            $monthlyRet = [];
            foreach( $monthly as $data ){
                $monthlyRet[] = $data;
            }

            return (Object)[
                "daily" => $dailyRet,
                "monthly" => $monthlyRet
            ];
        }

        public static function BilledERP( $params )
        {
            GLOBAL $dafel;

            $data1 = Model::getList( $dafel, (Object)[
                "join" => 1,
                "tables" => [
                    "Documento D (NoLock)",
                    "INNER JOIN DocumentoItem DI (NoLock) on ( D.IdDocumento = DI.IdDocumento )",
                    "INNER JOIN DocumentoItemValores DIV (NoLock) on ( DI.IdDocumentoItem = DIV.IdDocumentoItem )",
                    "INNER JOIN DocumentoItemRepasse DIR (NoLock) on ( DI.IdDocumentoItem = DIR.IdDocumentoItem )",
                ],
                "fields" => [
                    "DIR.IdPessoa",
                    "VlVenda=SUM(ISNULL(DI.VlItem,0)+ISNULL(D.VlAcrescimo,0)-ISNULL(D.VlDesconto,0))",
                    "QtDocumento=COUNT(DISTINCT D.IdDocumento)",
                    "VlDesconto=SUM(ISNULL(DIV.VLDescontoItem,0))",
                    "VLICMS=SUM(ISNULL(DIV.VlICMS,0))",
                    "VlST=SUM(ISNULL(DIV.VlICMSSubstTributaria,0))",
                    "DtEmissao=CONVERT(VARCHAR(10),D.DtEmissao,126)"
                ],
                "filters" => [
                    [ "D.IdSistema IS NOT NULL" ],
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $params->operations ]
                ],
                "group" => "DIR.IdPessoa, D.DtEmissao"
            ]);

            $data2 = Model::getList( $dafel, (Object)[
                "tables" => [
                    "Documento D",
                    "LoteEstoque LE",
                    "DocumentoItem DI",
                    "DocumentoItemRepasse DIR",
                    "Produto P"
                ],
                "fields" => [
                    "DtEmissao=CONVERT(VARCHAR(10),D.DtEmissao,126)",
                    "DIR.IdPessoa",
                    "Custo=DI.QtItem*ISNULL((SELECT TOP 1 HC.VlCusto FROM HistoricoCusto HC WHERE HC.IdProduto = DI.IdProduto AND HC.CdEmpresa = LE.CdEmpresa AND HC.DtReferencia <= D.DtEmissao ORDER BY HC.DtReferencia DESC),0)"
                ],
                "filters" => [
                    [ "D.IdLoteEstoque = LE.IdLoteEstoque" ],
                    [ "D.IdDocumento = DI.IdDocumento" ],
                    [ "DI.IdDocumentoItem = DIR.IdDocumentoItem" ],
                    [ "DI.IdProduto = P.IdProduto" ],
                    [ "D.IdSistema IS NOT NULL" ],
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $params->operations ]
                ]
            ]);

            $cost = [];
            foreach( $data2 as $data ){
                if( !@$cost[$data->DtEmissao][$data->IdPessoa] ){
                    $cost[$data->DtEmissao][$data->IdPessoa] = 0;
                }
                $cost[$data->DtEmissao][$data->IdPessoa] += $data->Custo;
            }

            foreach( $data1 as $data ){
                $data->Custo = $cost[$data->DtEmissao][$data->IdPessoa];
            }

            return $data1;
        }

        public static function Synchronize( $params )
        {
            GLOBAL $commercial;

            $date = $params->date;
            $devOperations = $params->devOperations;
            $saleOperations = $params->saleOperations;

            $devs = self::BilledERP((Object)[
                "operations" => $devOperations,
                "dtStart" => $date,
                "dtEnd" => $date
            ]);
            $sales = self::BilledERP((Object)[
                "operations" => $saleOperations,
                "dtStart" => $date,
                "dtEnd" => $date
            ]);

            Model::delete($commercial,(Object)[
                "table" => "billing",
                "filters" => [
                    [ "billing_section", "s", "=", "seller" ],
                    [ "billing_group", "s", "=", "daily" ],
                    [ "billing_reference", "s", "=", $date ]

                ]
            ]);

            foreach( $devs as $dev ){
                Model::insert($commercial, (Object)[
                    "table" => "billing",
                    "fields" => [
                        ["billing_section", "s", "seller"],
                        ["parent_id", "s", $dev->IdPessoa],
                        ["billing_group", "s", "daily"],
                        ["billing_operation", "s", "D"],
                        ["billing_value", "d", -$dev->VlVenda],
                        ["billing_quantity", "d", -$dev->QtDocumento],
                        ["billing_discount", "d", -$dev->VlDesconto],
                        ["billing_cost", "d", -$dev->Custo],
                        ["billing_icms", "d", -$dev->VLICMS],
                        ["billing_st", "d",-$dev->VlST],
                        ["billing_reference", "s", $dev->DtEmissao]
                    ]
                ]);
            }

            foreach( $sales as $sale ){
                Model::insert($commercial, (Object)[
                    "table" => "billing",
                    "fields" => [
                        ["billing_section", "s", "seller"],
                        ["parent_id", "s", $sale->IdPessoa],
                        ["billing_group", "s", "daily"],
                        ["billing_operation", "s", "V"],
                        ["billing_value", "d", $sale->VlVenda],
                        ["billing_quantity", "d", $sale->QtDocumento],
                        ["billing_discount", "d", $sale->VlDesconto],
                        ["billing_cost", "d", $sale->Custo],
                        ["billing_icms", "d", $sale->VLICMS],
                        ["billing_st", "d", $sale->VlST],
                        ["billing_reference", "s", $sale->DtEmissao]
                    ]
                ]);
            }
        }

    }

?>