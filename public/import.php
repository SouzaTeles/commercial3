<?php

    include "../config/start.php";

    GLOBAL $commercial;

    $commercial2 = new MySQL((Object)[
        "host" => "localhost",
        "user" => "root",
        "pass" => "",
        "table" => "commercial2"
    ]);

    $users = Model::getList($commercial2,(Object)[
        "tables" => [ "user" ],
        "order" => "user_id"
    ]);

    foreach( $users as $user ){
        $user->companies = Model::getList($commercial2,(Object)[
            "tables" => [ "user_company" ],
            "filters" => [[ "user_id", "i", "=", $user->user_id ]]
        ]);
        $user->prices = Model::getList($commercial2,(Object)[
            "tables" => [ "user_price" ],
            "filters" => [[ "user_id", "i", "=", $user->user_id ]]
        ]);
        $user_id = Model::insert($commercial,(Object)[
            "table" => "[User]",
            "fields" => [
                [ "external_id", "s", "00A000008J" ],
                [ "user_profile_id", "s", (int)$user->user_profile_id-1000 ],
                [ "user_user", "s", $user->user_user ],
                [ "user_pass", "s", $user->user_pass ],
                [ "user_name", "s", utf8_decode($user->user_name) ],
                [ "user_email", "s", $user->user_mail ],
                [ "user_max_discount", "s", $user->user_max_discount ],
                [ "user_credit_authorization", "s", @$user->user_max_credit_authorization ? "Y" : "N" ],
                [ "user_only_session", "s", $user->user_session_expires ],
                [ "user_mobile_access", "s", $user->user_mobile_access ],
                [ "user_mobile_unlock", "s", $user->user_unlock_device ],
                [ "user_budget_delivery", "s", "N" ],
                [ "user_active", "s", $user->user_active ],
                [ "user_login", "s", @$user->user_login ? $user->user_login : NULL ],
                [ "user_update", "s", @$user->user_update ? $user->user_update : NULL ],
                [ "user_date", "s", $user->user_date ],
            ]
        ]);
        if( @$user->companies ) {
            foreach ($user->companies as $company) {
                Model::insert($commercial, (Object)[
                    "table" => "[UserCompany]",
                    "fields" => [
                        ["user_id", "i", $user_id],
                        ["company_id", "i", $company->company_id],
                        ["user_company_main", "s", $company->user_company_main],
                        ["user_company_date", "s", $company->user_company_date]
                    ]
                ]);
            }
        }
        if( @$user->prices ) {
            foreach ($user->prices as $price) {
                Model::insert($commercial, (Object)[
                    "table" => "[UserPrice]",
                    "fields" => [
                        ["user_id", "i", $user_id],
                        ["price_id", "s", $price->price_id],
                        ["user_price_date", "s", $price->user_price_date]
                    ]
                ]);
            }
        }
    }

?>