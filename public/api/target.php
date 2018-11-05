<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $metas, $commercial, $dafel, $headerStatus, $get, $post;

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
                "dynamic_percent" => 0
            ];

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
                $ret->month_result += (float)$day->billing_value;
                if( $day->billing_reference == date("Y-m-d") ){
                    $ret->daily_result = (float)$day->billing_value;
                }
            }

            $ret->month_percent = 100*($ret->month_result/(@$ret->month_value ? $ret->month_value : 1));
            $ret->month_percent = (float)number_format($ret->month_percent,2,".","");

            $ret->daily_value = ($ret->month_value/(@$ret->days ? $ret->days : 1));
            $ret->daily_value = (float)number_format($ret->daily_value,2,".","");

            $ret->daily_percent = 100*($ret->daily_result/(@$ret->daily_value ? $ret->daily_value : 1));
            $ret->daily_percent = (float)number_format($ret->daily_percent,2,".","");

            $days = $ret->days - $ret->past + 1;
            $ret->dynamic_value = ($ret->month_value-$ret->month_result)/(@$days ? $days : 1);
            $ret->dynamic_value = (float)number_format($ret->dynamic_value,2,".","");

            $ret->dynamic_percent = 100*($ret->daily_result/(@$ret->dynamic_value ? $ret->dynamic_value : 1));
            $ret->dynamic_percent = (float)number_format($ret->dynamic_percent,2,".","");

            Json::get($headerStatus[200],$ret);

        break;

    }

?>