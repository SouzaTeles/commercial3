<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $metas, $commercial, $dafel, $headerStatus, $get, $post, $login;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action ) {

        case "dashboard":

            $ret = (Object)[
                "days" => 0,
                "past" => 0,
                "month_value" => 0,
                "month_percent" => 0,
                "month_result" => 0,
                "daily_value" => 0,
                "daily_percent" => 0,
                "daily_result" => 0,
                "dynamic_value" => 0,
                "dynamic_percent" => 0,
                "resume" => (Object)[
                    "daily_target_broken_value" => 0,
                    "daily_target_broken_percent" => 0,
                    "budget_billed_value" => 0,
                    "budget_open_value" => 0,
                    "budget_total_value" => 0,
                    "conversion" => 0,
                    "discount_count" => 0,
                    "discount_total" => 0,
                    "discount_average" => 0,
                    "exploitation" => 0
                ]
            ];

            if( @$login->person_id ){

                $budgets = Model::getList($commercial,(Object)[
                    "tables" => [ "Budget" ],
                    "fields" => [
                        "budget_status",
                        "budget_value=CAST(sum(budget_value_total+budget_value_st) AS FLOAT)"
                    ],
                    "filters" => [
                        [ "seller_id", "s", "=", $login->person_id ],
                        [ "budget_trash", "s", "=", "N" ],
                        [ "budget_status", "s", "!=", "C" ],
                        [ "budget_date", "s", "between", [date("Y-m-01")." 00:00:00",date("Y-m-d")." 23:59:59"] ]
                    ],
                    "group" => "budget_status"
                ]);

                if(@$budgets){
                    foreach($budgets as $budget){
                        $ret->resume->budget_total_value += $budget->budget_value;
                        if($budget->budget_status == "B"){
                            $ret->resume->budget_billed_value += $budget->budget_value;
                        } else{
                            $ret->resume->budget_open_value += $budget->budget_value;
                        }
                    }
                    $ret->resume->conversion = 100*($ret->resume->budget_billed_value/$ret->resume->budget_total_value);
                    $ret->resume->conversion = (float)number_format($ret->resume->conversion,2,".","");
                }

                $discounts = Model::get($commercial,(Object)[
                    "join" => 1,
                    "tables" => [
                        "Budget B (NoLock)",
	                    "INNER JOIN BudgetItem BI (NoLock) ON(BI.budget_id = B.budget_id)"
                    ],
                    "fields" => [
                        "count=COUNT(BI.budget_item_id)",
                        "total=CAST(ISNULL(SUM(BI.budget_item_aliquot_discount),0) AS FLOAT)"
                    ],
                    "filters" => [
                        [ "B.seller_id", "s", "=", $login->person_id ],
                        [ "B.budget_date", "s", "between", [date("Y-m-01")." 00:00:00",date("Y-m-d")." 23:59:59"] ]
                    ]
                ]);

                $ret->resume->discount_count = (int)$discounts->count;
                $ret->resume->discount_total = (float)$discounts->total;
                $ret->resume->discount_average = $ret->resume->discount_total/(@$ret->resume->discount_count ? $ret->resume->discount_count : 1);
                $ret->resume->discount_average = (float)number_format($ret->resume->discount_average,2,".","");
                $ret->resume->discount_percent = 100*($ret->resume->discount_average/50);
                $ret->resume->discount_percent = (float)number_format($ret->resume->discount_percent,2,".","");

                $data = Model::get($metas,(Object)[
                    "join" => 1,
                    "tables" => [
                        "seller s",
                        "inner join target t on(t.seller_id = s.seller_id)"
                    ],
                    "fields" => [
                        "s.business_code",
                        "t.target_val"
                    ],
                    "filters" => [
                        [ "s.idERP", "s", "=", $login->person_id ],
                        [ "t.target_date_start", "s", "=", date("Y-m-01") ]
                    ]
                ]);

                if( @$data->target_val ){
                    $ret->month_value = (float)$data->target_val;
                    $calendar = calendar((Object)[
                        "company_id" => (int)$data->business_code,
                        "reference" => date("Y-m-d")
                    ]);
                    if( @$calendar ){
                        $ret->days = $calendar->days;
                        $ret->past = $calendar->past;
                    }
                }

                $data = Model::getList($compass,(Object)[
                    "tables" => [ "billing b", "seller s", "company c" ],
                    "fields" => [
                        "b.billing_reference",
                        "SUM(b.billing_value+billing_st) as billing_value"
                    ],
                    "filters" => [
                        [ "b.parent_id = s.erp_id" ],
                        [ "s.company_id = c.company_id" ],
                        [ "b.billing_section", "s", "=", "seller" ],
                        [ "b.parent_id", "s", "=", $login->person_id ],
                        [ "b.billing_reference", "s", "between", [date("Y-m-01"),date("Y-m-d")] ]
                    ],
                    "group" => "b.billing_reference"
                ]);

                foreach( $data as $day ){
                    $day->billing_value = (float)$day->billing_value;
                    $ret->month_result += $day->billing_value;
                    if( $day->billing_reference == date("Y-m-d") ){
                        $ret->daily_result = (float)$day->billing_value;
                    }
                }

                $ret->daily_value = ($ret->month_value/(@$ret->days ? $ret->days : 1));
                $ret->daily_value = (float)number_format($ret->daily_value,2,".","");

                $ret->month_percent = 100*($ret->month_result/(@$ret->month_value ? $ret->month_value : 1));
                $ret->month_percent = (float)number_format($ret->month_percent,2,".","");

                $ret->daily_percent = 100*($ret->daily_result/(@$ret->daily_value ? $ret->daily_value : 1));
                $ret->daily_percent = (float)number_format($ret->daily_percent,2,".","");

                $days = $ret->days - $ret->past + 1;
                $ret->dynamic_value = ($ret->month_value-$ret->month_result)/(@$days ? $days : 1);
                $ret->dynamic_value = (float)number_format($ret->dynamic_value,2,".","");

                $ret->dynamic_percent = 100*($ret->daily_result/(@$ret->dynamic_value ? $ret->dynamic_value : 1));
                $ret->dynamic_percent = (float)number_format($ret->dynamic_percent,2,".","");

                $target_value = ($ret->past*($ret->month_value/$ret->days));
                $ret->resume->exploitation = @$ret->month_value ? 100*$ret->month_result/($target_value?$target_value:1) : 0;
                $ret->resume->exploitation = (float)number_format($ret->resume->exploitation,2,".","");

                foreach( $data as $day ){
                    $day->billing_value = (float)$day->billing_value;
                    $date = DateTime::createFromFormat('Y-m-d', $day->billing_reference);
                    if( $date->format("w") != 6 && $day->billing_value >= $ret->daily_value ){
                        $ret->resume->daily_target_broken_value++;
                    }
                }

                $ret->resume->daily_target_broken_percent = 100*($ret->resume->daily_target_broken_value/(@$ret->days ? $ret->days : 1));
                $ret->resume->daily_target_broken_percent = (float)number_format($ret->resume->daily_target_broken_percent,2,".","");

            }

            Json::get($headerStatus[200],$ret);

        break;

    }

?>