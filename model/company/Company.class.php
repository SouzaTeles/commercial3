<?php

    class Company
    {
        public $company_id;
        public $parent_id;
        public $company_active;
        public $company_code;
        public $company_name;
        public $company_short_name;
        public $company_target;
        public $company_update;
        public $company_date;

        public function __construct( $data, $gets=[] )
        {
            $this->company_id = (int)$data->company_id;
            $this->parent_id = $data->parent_id;
            $this->company_active = $data->company_active;
            $this->company_code = substr("0{$data->company_id}",-2);
            $this->company_name = $data->company_name;
            $this->company_short_name = @$data->company_short_name ? $data->company_short_name : NULL;
            $this->company_target = $data->company_target;
            $this->company_color = $data->company_color;
            $this->company_update = @$data->company_update ? $data->company_update : NULL;
            $this->company_date = $data->company_date;

            $this->image = getImage((Object)[
                "image_id" => $data->company_id,
                "image_dir" => "company"
            ]);
        }

        public static function Dashboard($params)
        {
            GLOBAL $commercial, $metas, $commercial;

            $reference = new DateTime($params->reference);

            $data = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "company c",
                    "left join image i on(i.parent_id = c.company_id and i.image_section = 'company')"
                ],
                "fields" => [
                    "i.image_id",
                    "c.parent_id",
                    "c.company_code",
                    "c.company_name",
                    "c.company_short_name",
                    "c.company_color"
                ],
                "filters" => [[ "company_active", "s", "=", "Y" ]]
            ]);

            $ids = []; $companies = [];
            foreach( $data as $key => $company ){
                if( @$company->image_id ){
                    $company->image = URI_EXTERNAL . "files/company/{$company->image_id}_company.png";
                }
                $company_id = (int)$company->company_code;
                $ids[] = $company_id;
                $companies[$company_id] = $company;
            }

            $dataDailyLost = Model::getList($commercial,(Object)[
                "tables" => [ "`order`" ],
                "fields" => [
                    "order_company_id",
                    "count(order_id) as quantity",
                    "sum(order_value_total+order_value_st) as value"
                ],
                "filters" => [
                    [ "order_trash", "s", "=", "N" ],
                    [ "order_status_id", "i", "!=", 1003 ],
                    [ "convert(order_date,date)", "s", "=", $params->reference ]
                ],
                "group" => "order_company_id"
            ]);

            $dataMonthlyLost = Model::getList($commercial,(Object)[
                "tables" => [ "`order`" ],
                "fields" => [
                    "order_company_id",
                    "count(order_id) as quantity",
                    "sum(order_value_total+order_value_st) as value"
                ],
                "filters" => [
                    [ "order_trash", "s", "=", "N" ],
                    [ "order_status_id", "i", "!=", 1003 ],
                    [ "order_date", "s", "between",[$reference->format("Y-m-01"),$params->reference] ]
                ],
                "group" => "order_company_id"
            ]);

            $dailyLost = [];
            foreach( $dataDailyLost as $lost ){
                $lost->value = (float)$lost->value;
                $lost->quantity = (float)$lost->quantity;
                $dailyLost[$lost->order_company_id] = $lost;
            }

            $monthlyLost = [];
            foreach( $dataMonthlyLost as $lost ){
                $lost->value = (float)$lost->value;
                $lost->quantity = (int)$lost->quantity;
                $monthlyLost[$lost->order_company_id] = $lost;
            }

            $data = Model::getList($commercial,(Object)[
                "tables" => [ "billing" ],
                "fields" => [
                    "billing_reference",
                    "parent_id",
                    "SUM(billing_value+billing_st) as billing_value",
                    "SUM(billing_quantity) as billing_quantity",
                    "SUM(billing_cost) as billing_cost"
                ],
                "filters" => [
                    [ "billing_section", "s", "=", "company" ],
                    [ "billing_reference", "s", "between", [$reference->format("Y-m-01"), $params->reference] ]
                ],
                "group" => "billing_reference,parent_id",
                "order" => "billing_reference,1 * parent_id"
            ]);

            $daily = [];
            $billing = [];
            $billingDay = [];
            foreach( $data as $bill ){
                $bill->billing_value = (float)$bill->billing_value;
                $bill->billing_quantity = (int)$bill->billing_quantity;
                $bill->billing_cost = (float)$bill->billing_cost;
                if( $bill->billing_reference == $reference->format("Y-m-d") ){
                    $company = $companies[$bill->parent_id];
                    $lost = @$dailyLost[(int)$company->company_code] ? $dailyLost[(int)$company->company_code] : NULL;
                    $daily[(int)$company->company_code] = (Object)[
                        "image" => $company->image,
                        "parent_id" => $company->parent_id,
                        "company_code" => $company->company_code,
                        "company_name" => $company->company_name,
                        "company_short_name" => $company->company_short_name,
                        "company_color" => $company->company_color,
                        "billing_value" => $bill->billing_value,
                        "billing_quantity" => $bill->billing_quantity,
                        "billing_cost" => $bill->billing_cost,
                        "profit_value" => $bill->billing_value-$bill->billing_cost,
                        "target_value" => 0,
                        "target_percent" => 0,
                        "ticket" => $bill->billing_value/$bill->billing_quantity,
                        "lost_value" => @$lost ? $lost->value : 0,
                        "lost_quantity" => @$lost ? $lost->quantity : 0,
                        "conversion" => @$lost ? (($bill->billing_value/($bill->billing_value+$lost->value))*100) : (@$bill->billing_value?100:0)
                    ];
                }
                if( !@$billing[$bill->parent_id] ){
                    $billing[$bill->parent_id] = (Object)[
                        "parent_id" => $bill->parent_id,
                        "billing_value" => 0,
                        "billing_quantity" => 0,
                        "billing_cost" => 0
                    ];
                }
                $billing[$bill->parent_id]->billing_value += $bill->billing_value;
                $billing[$bill->parent_id]->billing_quantity += $bill->billing_quantity;
                $billing[$bill->parent_id]->billing_cost += $bill->billing_cost;
                $billingDay[$bill->parent_id][$bill->billing_reference] = $bill->billing_value;
            }

            $monthly = [];
            foreach( $billing as $bill ){
                if( @$companies[$bill->parent_id] ) {
                    $company = $companies[$bill->parent_id];
                    $lost = @$monthlyLost[(int)$company->company_code] ? $monthlyLost[(int)$company->company_code] : NULL;
                    $monthly[(int)$company->company_code] = (Object)[
                        "image" => $company->image,
                        "parent_id" => $company->parent_id,
                        "company_code" => $company->company_code,
                        "company_name" => $company->company_name,
                        "company_short_name" => $company->company_short_name,
                        "company_color" => $company->company_color,
                        "billing_value" => $bill->billing_value,
                        "billing_quantity" => $bill->billing_quantity,
                        "billing_cost" => $bill->billing_cost,
                        "profit_value" => $bill->billing_value-$bill->billing_cost,
                        "target_value" => 0,
                        "target_percent" => 0,
                        "ticket" => $bill->billing_value/$bill->billing_quantity,
                        "lost_value" => @$lost ? $lost->value : 0,
                        "lost_quantity" => @$lost ? $lost->quantity : 0,
                        "conversion" => @$lost ? (($bill->billing_value/($bill->billing_value+$lost->value))*100) : (@$bill->billing_value?100:0)
                    ];
                }
            }

            foreach( $daily as $key => $data ){
                if( @$data->parent_id ){
                    if( !@$daily[$data->parent_id] ) {
                        $company = $companies[$data->parent_id];
                        $daily[$data->parent_id] = (Object)[
                            "image" => $company->image,
                            "parent_id" => $company->parent_id,
                            "company_code" => $company->company_code,
                            "company_name" => $company->company_name,
                            "company_short_name" => $company->company_short_name,
                            "company_color" => $company->company_color,
                            "billing_value" => 0,
                            "billing_quantity" => 0,
                            "billing_cost" => 0,
                            "profit_value" => 0,
                            "target_value" => 0,
                            "target_percent" => 0
                        ];
                    }
                    $daily[$data->parent_id]->billing_value += $data->billing_value;
                    $daily[$data->parent_id]->billing_quantity += $data->billing_quantity;
                    $daily[$data->parent_id]->billing_cost += $data->billing_cost;
                    $daily[$data->parent_id]->profit_value += ($data->billing_value-$data->billing_cost);
                    unset($daily[$key]);
                }
            }

            foreach( $monthly as $key => $data ){
                if( @$data->parent_id ){
                    $monthly[$data->parent_id]->billing_value += $data->billing_value;
                    $monthly[$data->parent_id]->billing_quantity += $data->billing_quantity;
                    $monthly[$data->parent_id]->billing_cost += $data->billing_cost;
                    $monthly[$data->parent_id]->profit_value += ($data->billing_value-$data->billing_cost);
                    unset($monthly[$key]);
                }
            }


            $targets = Model::getList( $metas, (Object)[
                "tables" => [ "target" ],
                "fields" => [ "business_code", "target_val" ],
                "filters" => [
                    [ "business_code", "i", "in", $ids ],
                    [ "target_type", "i", "=", "1" ],
                    [ "target_date_start", "s", "=", $reference->format("Y-m-01") ]
                ]
            ]);

            foreach( $targets as $key => $target ){
                $calendar = calendar((Object)[
                    "company_id" => $target->business_code,
                    "reference" => $reference->format("Y-m-d")
                ]);
                if( @$daily[$target->business_code] ){
                    $company = $daily[$target->business_code];
                    $company->target_value = ((float)$target->target_val/$calendar->days);
                    $company->target_percent = ($company->billing_value/$company->target_value)*100;
                    $company->broke = ($company->target_percent >= 100);
                    $company->stars = (int)(($company->target_percent-100)/10);
                }
                if( @$monthly[$target->business_code] ){
                    $company = $monthly[$target->business_code];
                    $company->target_value = (float)$target->target_val;
                    $company->target_percent = ($company->billing_value/$target->target_val)*100;
                    $company->broke = ($company->target_percent >= 100);
                    $company->stars = (int)(($company->target_percent-100)/10);
                    $company->exploitation = ($company->billing_value/($calendar->past*($company->target_value/$calendar->days)))*100;
                    $company->running = 0;
                    $company->today = (Object)[
                        "percent" => ($calendar->past/$calendar->days)*100,
                        "value" => $calendar->past * ($target->target_val/$calendar->days)
                    ];
                    $company->calendar = $calendar;
                    foreach( $billingDay[$target->business_code] as $value ){
                        if( $value >= ((float)$target->target_val/$calendar->days) ){
                            $company->running++;
                        }
                    }
                }
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

        public static function Group($params)
        {
            GLOBAL $commercial, $metas;

            $reference = new DateTime($params->reference);

            $data = Model::getList($commercial,(Object)[
                "tables" => [ "company" ],
                "fields" => [ "company_code" ],
                "filters" => [[ "company_active", "s", "=", "Y" ]]
            ]);

            $ids = [];
            foreach( $data as $key => $company ){
                $ids[] = $company->company_code;
            }

            $target = Model::get( $metas, (Object)[
                "tables" => [ "target" ],
                "fields" => [ "sum(target_val) as target_val" ],
                "filters" => [
                    [ "business_code", "i", "in", $ids ],
                    [ "target_type", "i", "=", "1" ],
                    [ "target_date_start", "s", "=", $reference->format("Y-m-01") ]
                ]
            ]);

            $daily = Model::get($commercial,(Object)[
                "tables" => [ "billing" ],
                "fields" => [
                    "SUM(billing_value+billing_st) as billing_value",
                    "SUM(billing_quantity) as billing_quantity"
                ],
                "filters" => [
                    [ "billing_section", "s", "=", "company" ],
                    [ "billing_reference", "s", "=", $params->reference ]
                ]
            ]);

            $monthly = Model::get($commercial,(Object)[
                "tables" => [ "billing" ],
                "fields" => [
                    "SUM(billing_value+billing_st) as billing_value",
                    "SUM(billing_quantity) as billing_quantity"
                ],
                "filters" => [
                    [ "billing_section", "s", "=", "company" ],
                    [ "billing_reference", "s", "between", [$reference->format("Y-m-01"), $params->reference] ]
                ]
            ]);

            $calendar = calendar((Object)[
                "company_id" => -1,
                "reference" => $reference->format("Y-m-d")
            ]);

            $daily->billing_value = (float)$daily->billing_value;
            $daily->billing_quantity = (int)$daily->billing_quantity;
            $daily->target_value = (float)($target->target_val / $calendar->days);
            $daily->target_percent = ($daily->billing_value / $daily->target_value) * 100;
            $daily->broke = ( (float)$daily->target_percent >= 100 );
            $daily->stars = (int)(($daily->target_percent-100)/10);
            $daily->ticket = $daily->billing_value/($daily->billing_quantity ? $daily->billing_quantity : 1);

            $monthly->billing_value = (float)$monthly->billing_value;
            $monthly->billing_quantity = (int)$monthly->billing_quantity;
            $monthly->target_value = (float)$target->target_val;
            $monthly->target_percent = ($monthly->billing_value / $target->target_val) * 100;
            $monthly->broke = ((float)$monthly->target_percent >= 100);
            $monthly->stars = (int)(($monthly->target_percent-100)/10);
            $monthly->ticket = $monthly->billing_value/($monthly->billing_quantity ? $monthly->billing_quantity : 1);

            $ret = (Object)[
                "daily" => $daily,
                "monthly" => $monthly
            ];

            return $ret;
        }

        public static function BilledERP($params)
        {
            GLOBAL $dafel;

            $data1 = Model::getList( $dafel, (Object)[
                "join" => 1,
                "tables" => [
                    "Documento D(NOLOCK)",
                    "INNER JOIN DocumentoItem DI(NOLOCK) ON (D.IdDocumento = DI.IdDocumento)",
                    "INNER JOIN DocumentoItemValores DIV(NOLOCK) ON (DI.IdDocumentoItem = DIV.IdDocumentoItem)",
                    "INNER JOIN LoteEstoque LE(NOLOCK) ON (D.IdLoteEstoque = LE.IdLoteEstoque)",
                ],
                "fields" => [
                    "LE.CdEmpresa",
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
                "group" => "LE.CdEmpresa, D.DtEmissao"
            ]);

            $data2 = Model::getList( $dafel, (Object)[
                "tables" => [
                    "Documento D",
                    "LoteEstoque LE",
                    "DocumentoItem DI",
                    "Produto P"
                ],
                "fields" => [
                    "DtEmissao=CONVERT(VARCHAR(10),D.DtEmissao,126)",
                    "LE.CdEmpresa",
                    "Custo=DI.QtItem*ISNULL((SELECT TOP 1 HC.VlCusto FROM HistoricoCusto HC WHERE HC.IdProduto = DI.IdProduto AND HC.CdEmpresa = LE.CdEmpresa AND HC.DtReferencia <= D.DtEmissao ORDER BY HC.DtReferencia DESC),0)"
                ],
                "filters" => [
                    [ "D.IdLoteEstoque = LE.IdLoteEstoque" ],
                    [ "D.IdDocumento = DI.IdDocumento" ],
                    [ "DI.IdProduto = P.IdProduto" ],
                    [ "D.IdSistema IS NOT NULL" ],
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $params->operations ]
                ]
            ]);

            $cost = [];
            foreach( $data2 as $data ){
                if( !@$cost[$data->DtEmissao][$data->CdEmpresa] ){
                    $cost[$data->DtEmissao][$data->CdEmpresa] = 0;
                }
                $cost[$data->DtEmissao][$data->CdEmpresa] += $data->Custo;
            }

            foreach( $data1 as $data ){
                $data->Custo = $cost[$data->DtEmissao][$data->CdEmpresa];
            }

            return $data1;
        }

        public static function Synchronize($params)
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
                    [ "billing_section", "s", "=", "company" ],
                    [ "billing_group", "s", "=", "daily" ],
                    [ "billing_reference", "s", "=", $date ]
                ]
            ]);

            foreach( $devs as $dev ){
                Model::insert($commercial, (Object)[
                    "table" => "billing",
                    "fields" => [
                        ["billing_section", "s", "company"],
                        ["parent_id", "s", $dev->CdEmpresa],
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
                        ["billing_section", "s", "company"],
                        ["parent_id", "s", $sale->CdEmpresa],
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