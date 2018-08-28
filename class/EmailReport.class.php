<?php

    class EmailReport
    {
        public static function Company($params)
        {
            GLOBAL $smarty;

            $companies = Company::Dashboard((Object)[
                "reference" => $params->reference
            ]);

            usort( $companies->daily, function( $a, $b ){
                return $a->target_percent < $b->target_percent;
            });

            usort( $companies->monthly, function( $a, $b ){
                return $a->target_percent < $b->target_percent;
            });

            $smarty->assign( "companies", $companies );

        }

        public static function CompanyGroup($params)
        {
            GLOBAL $commercial, $metas, $smarty;

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
                    [ "section", "s", "=", "company" ],
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
                    [ "section", "s", "=", "company" ],
                    [ "billing_reference", "s", "between", [$reference->format("Y-m-01"), $params->reference] ]
                ]
            ]);

            $calendar = calendar((Object)[
                "company_id" => -1,
                "reference" => $reference->format("Y-m-d")
            ]);

            $daily->target_value = ($target->target_val / $calendar->days);
            $daily->target_percent = number_format(($daily->billing_value / $daily->target_value) * 100, 2, ".", "");
            $daily->broke = ( (float)$daily->target_percent >= 100 );
            $daily->stars = (int)(($daily->target_percent-100)/10);

            $monthly->target_value = $target->target_val;
            $monthly->target_percent = number_format(($monthly->billing_value / $target->target_val) * 100, 2, ".", "");
            $monthly->broke = ((float)$monthly->target_percent >= 100);
            $monthly->stars = (int)(($monthly->target_percent-100)/10);

            $ret = (Object)[
                "daily" => $daily,
                "monthly" => $monthly
            ];

            $smarty->assign( "group", $ret );
        }

        public static function SellerTarget($params)
        {
            GLOBAL $smarty;

            $sellers = Seller::Dashboard((Object)[
                "reference" => $params->reference
            ]);

            usort( $sellers->daily, function( $a, $b ){
                return $a->target_percent < $b->target_percent;
            });

            usort( $sellers->monthly, function( $a, $b ){
                return $a->target_percent < $b->target_percent;
            });

            $smarty->assign( "sellers", $sellers );
        }
    }

?>