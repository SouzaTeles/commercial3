<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $metas, $get, $headerStatus;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action )
    {

        case "dashboardCompanies":

            $companies = Company::Dashboard((Object)[
                "reference" => @$post->reference ? $post->reference : date("Y-m-d")
            ]);

            Json::get( $headerStatus[200], $companies );

        break;

        case "dashboardGroups":

            $groups = Company::Group((Object)[
                "reference" => @$post->reference ? $post->reference : date("Y-m-d")
            ]);

            Json::get( $headerStatus[200], $groups );

        break;

        case "dashboardSellers":

            $sellers = Seller::Dashboard((Object)[
                "reference" => @$post->reference ? $post->reference : date("Y-m-d")
            ]);

            Json::get( $headerStatus[200], $sellers );

        break;

        case "billCompany":

            $grouping = [
                "daily" => (Object)[
                    "column" => "billing_reference",
                    "interval" => "day",
                    "format" => "Y-m-d",
                    "label" => "d/m"
                ],
                "monthly" => (Object)[
                    "column" => "DATE_FORMAT(billing_reference,'%Y-%m')",
                    "interval" => "month",
                    "format" => "Y-m",
                    "label" => "m/y"
                ],
                "yearly" => (Object)[
                    "column" => "DATE_FORMAT(billing_reference,'%Y')",
                    "interval" => "year",
                    "format" => "Y",
                    "label" => "Y"
                ]
            ];

            $group = $grouping[$post->grouping];

            $data = Model::getList($commercial,(Object)[
                "tables" => [ "company" ],
                "fields" => [
                    "erp_id",
                    "parent_id",
                    "company_code",
                    "company_color",
                    "company_short_name"
                ],
                "filters" => [
                    [ "company_active", "s", "=", "Y" ],
                    [ "company_id", "i", "in", @$post->company_id ? $post->company_id : NULL ]
                ]
            ]);

            $companies = [];
            foreach( $data as $company ){
                $companies[$company->erp_id] = (Object)[
                    "erp_id" => $company->erp_id,
                    "parent_id" => $company->parent_id,
                    "company_code" => $company->company_code,
                    "company_color" => $company->company_color,
                    "company_short_name" => $company->company_short_name,
                    "billing_value" => [],
                    "billing_quantity" => [],
                    "billing_discount" => [],
                    "billing_cost" => [],
                    "billing_icms" => [],
                    "billing_st" => []
                ];
            }

            $data = Model::getList($commercial,(Object)[
                "tables" => [ "billing" ],
                "fields" => [
                    "{$group->column} as billing_reference",
                    "parent_id",
                    "SUM(billing_value+billing_st) as billing_value",
                    "SUM(billing_quantity) as billing_quantity",
                    "SUM(billing_discount) as billing_discount",
                    "SUM(billing_cost) as billing_cost",
                    "SUM(billing_icms) as billing_icms",
                    "SUM(billing_st) as billing_st"
                ],
                "filters" => [
                    [ "billing_section", "s", "=", "company" ],
                    [ "billing_reference", "s", "between", [ $post->start_date, $post->end_date ] ]
                ],
                "group" => "{$group->column}
                , parent_id"
            ]);

            $billing = [];
            foreach( $data as $bill ){
                $bill->billing_value = (float)$bill->billing_value;
                $bill->billing_quantity = (int)$bill->billing_quantity;
                $bill->billing_discount = (int)$bill->billing_discount;
                $bill->billing_cost = (int)$bill->billing_cost;
                $bill->billing_icms = (int)$bill->billing_icms;
                $bill->billing_st = (int)$bill->billing_st;
                $billing[$bill->billing_reference][$bill->parent_id] = clone $bill;
            }

            $begin = new DateTime($post->start_date);
            $end = new DateTime($post->end_date);
            if( $post->grouping == "daily" ) $end->modify('+1 day');

            $interval = DateInterval::createFromDateString("1 {$group->interval}");
            $period = new DatePeriod($begin,$interval,$end);

            $categories = [];
            $carai = 1;
            foreach( $period as $dt ){
                $date = $dt->format($group->format);
                if( $post->grouping != "daily" || $dt->format("w") ) {
                    $categories[] = $dt->format($group->label);
                    foreach ($companies as $company) {
                        if (@$billing[$date][$company->erp_id]) {
                            $company->billing_value[] = $billing[$date][$company->erp_id]->billing_value;
                            $company->billing_quantity[] = $billing[$date][$company->erp_id]->billing_quantity;
                            $company->billing_discount[] = $billing[$date][$company->erp_id]->billing_discount;
                            $company->billing_cost[] = $billing[$date][$company->erp_id]->billing_cost;
                            $company->billing_icms[] = $billing[$date][$company->erp_id]->billing_icms;
                            $company->billing_st[] = $billing[$date][$company->erp_id]->billing_st;
                        } else {
                            $company->billing_value[] = 0;
                            $company->billing_quantity[] = 0;
                            $company->billing_discount[] = 0;
                            $company->billing_cost[] = 0;
                            $company->billing_icms[] = 0;
                            $company->billing_st[] = 0;
                        }
                    }
                }
            }

            foreach( $companies as $key => $company ){
                if( @$company->parent_id ){
                    if( !@$companies[$company->parent_id] ) {
                        $company->erp_id = $companies[$company->parent_id]->erp_id;
                        $company->company_code = $companies[$company->parent_id]->company_code;
                        $company->company_short_name = $companies[$company->parent_id]->company_short_name;
                        $companies[$company->parent_id] = clone $company;
                    } else {
                        for( $i=0; $i<sizeof($categories); $i++ ){
                            $companies[$company->parent_id]->billing_value[$i] += $company->billing_value[$i];
                            $companies[$company->parent_id]->billing_quantity[$i] += $company->billing_quantity[$i];
                            $companies[$company->parent_id]->billing_discount[$i] += $company->billing_discount[$i];
                            $companies[$company->parent_id]->billing_cost[$i] += $company->billing_cost[$i];
                            $companies[$company->parent_id]->billing_icms[$i] += $company->billing_icms[$i];
                            $companies[$company->parent_id]->billing_st[$i] += $company->billing_st[$i];
                        }
                    }
                    unset($companies[$key]);
                }
            }

            Json::get( $headerStatus[200], (Object)[
                "categories" => $categories,
                "companies" => $companies
            ]);

        break;

    }

?>