<?php

    include "../../../config/start.php";

    Session::checkApi();

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    GLOBAL $dafel, $metas, $headerStatus;

    switch( $get->action )
    {

        case "getDashboard":

            $data = Model::getList( $metas, (Object)[
                "tables" => [ "target" ],
                "fields" => [ "business_code", "target_val" ],
                "filters" => [
                    [ "target_type", "i", "=", "1" ],
                    [ "target_date_start", "s", "=", date("Y-m-01") ]
                ]
            ]);

            $companies = [];
            foreach( $data as $key => $company ){
                $company->teste = "@";
                $companies[$company->business_code] = $company;
                unset($data[$key]);
            }

            $lost = (Object)[
                "daily" => Model::getList($commercial,(Object)[
                    "tables" => [ "`order`" ],
                    "fields" => [
                        "order_company_id",
                        "count(order_id) as quantity",
                        "sum(order_value_total+order_value_st) as value"
                    ],
                    "filters" => [
                        ["order_trash","s","=","N"],
                        ["order_status_id","i","!=",1003],
                        [
                            ["order_date","s","between",[date("Y-m-d")." 00:00:00",date("Y-m-d")." 23:59:59"]],
                            ["order_update","s","between",[date("Y-m-d")." 00:00:00",date("Y-m-d")." 23:59:59"]]
                        ]
                    ],
                    "group" => "order_company_id"
                ]),
                "monthly" => Model::getList($commercial,(Object)[
                    "tables" => [ "`order`" ],
                    "fields" => [
                        "order_company_id",
                        "count(order_id) as quantity",
                        "sum(order_value_total+order_value_st) as value"
                    ],
                    "filters" => [
                        ["order_trash","s","=","N"],
                        ["order_status_id","i","!=",1003],
                        [
                            ["order_date","s","between",[date("Y-m-01")." 00:00:00",date("Y-m-d")." 23:59:59"]],
                            ["order_update","s","between",[date("Y-m-01")." 00:00:00",date("Y-m-d")." 23:59:59"]]
                        ]
                    ],
                    "group" => "order_company_id"
                ])
            ];

            $sellers = Model::getList( $metas, (Object)[
                "tables" => [ "seller s", "target t" ],
                "fields" => [ "s.idERP", "s.business_code", "t.target_val" ],
                "filters" => [
                    [ "s.seller_id = t.seller_id" ],
                    [ "t.target_type", "i", "=", "2" ],
                    [ "s.seller_active", "s", "=", "Y" ],
                    [ "t.target_date_start", "s", "=", date("Y-m-01") ]
                ]
            ]);

            $people_id = [];
            foreach( $sellers as $key => $seller ){
                $people_id[] = $seller->idERP;
                $sellers[$seller->idERP] = $seller;
                unset($sellers[$key]);
            }

            $dashboard = (Object)[
                "daily" => (Object)[
                    "companies" => VendasEmpresa::getBilling((Object)[
                        "target" => "daily",
                        "companies" => $companies,
                        "dtStart" => date("Y-m-d"),
                        "dtEnd" => date("Y-m-d")
                    ]),
                    "sellers" => VendasRepresentante::getBilling((Object)[
                        "target" => "daily",
                        "sellers" => $sellers,
                        "people_id" => $people_id,
                        "dtStart" => date("Y-m-d"),
                        "dtEnd" => date("Y-m-d")
                    ])
                ],
                "monthly" => (Object)[
                    "companies" => VendasEmpresa::getBilling((Object)[
                        "target" => "monthly",
                        "companies" => $companies,
                        "dtStart" => date("Y-m-01"),
                        "dtEnd" => date("Y-m-d")
                    ]),
                    "sellers" => VendasRepresentante::getBilling((Object)[
                        "target" => "monthly",
                        "sellers" => $sellers,
                        "people_id" => $people_id,
                        "dtStart" => date("Y-m-01"),
                        "dtEnd" => date("Y-m-d")
                    ])
                ]
            ];

            $daily = [];
            foreach( $lost->daily as $data ){
                $daily[$data->order_company_id] = $data;
            }

            $monthly = [];
            foreach( $lost->monthly as $data ){
                $monthly[$data->order_company_id] = $data;
            }

            foreach( $dashboard->daily->companies as $company ){
                $company->lost = @$daily[(int)$company->code] ? $daily[(int)$company->code] : NULL;
            }

            foreach( $dashboard->monthly->companies as $company ){
                $company->lost = @$monthly[(int)$company->code] ? $monthly[(int)$company->code] : NULL;
            }

            Json::get( $headerStatus[200], $dashboard );

        break;

        case "getCompaniesDaily":

            $data = Model::getList( $metas, (Object)[
                "tables" => [ "target" ],
                "fields" => [ "business_code", "target_val" ],
                "filters" => [
                    [ "target_type", "i", "=", "1" ],
                    [ "target_date_start", "s", "=", date("Y-m-01") ]
                ]
            ]);

            $companies = [];
            foreach( $data as $key => $company ){
                $companies[$company->business_code] = $company;
                unset($data[$key]);
            }

            $daily = VendasEmpresa::getBilling((Object)[
                "target" => "daily",
                "companies" => $companies,
                "dtStart" => date("Y-m-d"),
                "dtEnd" => date("Y-m-d")
            ]);

            $lost = Model::getList($commercial,(Object)[
                "tables" => [ "`order`" ],
                "fields" => [
                    "order_company_id",
                    "count(order_id) as quantity",
                    "sum(order_value_total+order_value_st) as value"
                ],
                "filters" => [
                    ["order_trash","s","=","N"],
                    ["order_status_id","i","!=",1003],
                    [
                        ["order_date","s","between",[date("Y-m-d")." 00:00:00",date("Y-m-d")." 23:59:59"]],
                        ["order_update","s","between",[date("Y-m-d")." 00:00:00",date("Y-m-d")." 23:59:59"]]
                    ]
                ],
                "group" => "order_company_id"
            ]);

            $data = [];
            foreach( $lost as $l ){
                $data[$l->order_company_id] = $l;
            }

            foreach( $daily as $company ){
                $company->lost = @$data[(int)$company->code] ? $data[(int)$company->code] : NULL;
            }

            Json::get( $headerStatus[200], $daily );

        break;

        case "getCompaniesMonthly":

            $data = Model::getList( $metas, (Object)[
                "tables" => [ "target" ],
                "fields" => [ "business_code", "target_val" ],
                "filters" => [
                    [ "target_type", "i", "=", "1" ],
                    [ "target_date_start", "s", "=", date("Y-m-01") ]
                ]
            ]);

            $companies = [];
            foreach( $data as $key => $company ){
                $companies[$company->business_code] = $company;
                unset($data[$key]);
            }

            $monthly = VendasEmpresa::getBilling((Object)[
                "target" => "monthly",
                "companies" => $companies,
                "dtStart" => date("Y-m-01"),
                "dtEnd" => date("Y-m-d")
            ]);

            $lost = Model::getList($commercial,(Object)[
                "tables" => [ "`order`" ],
                "fields" => [
                    "order_company_id",
                    "count(order_id) as quantity",
                    "sum(order_value_total+order_value_st) as value"
                ],
                "filters" => [
                    ["order_trash","s","=","N"],
                    ["order_status_id","i","!=",1003],
                    [
                        ["order_date","s","between",[date("Y-m-01")." 00:00:00",date("Y-m-d")." 23:59:59"]],
                        ["order_update","s","between",[date("Y-m-01")." 00:00:00",date("Y-m-d")." 23:59:59"]]
                    ]
                ],
                "group" => "order_company_id"
            ]);

            $data = [];
            foreach( $lost as $l ){
                $data[$l->order_company_id] = $l;
            }

            foreach( $monthly as $company ){
                $company->lost = @$data[(int)$company->code] ? $data[(int)$company->code] : NULL;
            }

            Json::get( $headerStatus[200], $monthly );

        break;

        case "getSellersDaily":

            $sellers = Model::getList( $metas, (Object)[
                "tables" => [ "seller s", "target t" ],
                "fields" => [ "s.idERP", "s.business_code", "t.target_val" ],
                "filters" => [
                    [ "s.seller_id = t.seller_id" ],
                    [ "t.target_type", "i", "=", "2" ],
                    [ "s.seller_active", "s", "=", "Y" ],
                    [ "t.target_date_start", "s", "=", date("Y-m-01") ]
                ]
            ]);

            $people_id = [];
            foreach( $sellers as $key => $seller ){
                $people_id[] = $seller->idERP;
                $sellers[$seller->idERP] = $seller;
                unset($sellers[$key]);
            }

            $daily = VendasRepresentante::getBilling((Object)[
                "target" => "daily",
                "sellers" => $sellers,
                "people_id" => $people_id,
                "dtStart" => date("Y-m-d"),
                "dtEnd" => date("Y-m-d")
            ]);

            Json::get( $headerStatus[200], $daily );

        break;

        case "getSellersMonthly":

            $sellers = Model::getList( $metas, (Object)[
                "tables" => [ "seller s", "target t" ],
                "fields" => [ "s.idERP", "s.business_code", "t.target_val" ],
                "filters" => [
                    [ "s.seller_id = t.seller_id" ],
                    [ "t.target_type", "i", "=", "2" ],
                    [ "s.seller_active", "s", "=", "Y" ],
                    [ "t.target_date_start", "s", "=", date("Y-m-01") ]
                ]
            ]);

            $people_id = [];
            foreach( $sellers as $key => $seller ){
                $people_id[] = $seller->idERP;
                $sellers[$seller->idERP] = $seller;
                unset($sellers[$key]);
            }

            $monthly = VendasRepresentante::getBilling((Object)[
                "target" => "monthly",
                "sellers" => $sellers,
                "people_id" => $people_id,
                "dtStart" => date("Y-m-01"),
                "dtEnd" => date("Y-m-d")
            ]);

            Json::get( $headerStatus[200], $monthly );

        break;

    }

?>