<?php

    include "../config/start.php";

    GLOBAL $commercial, $config, $login, $get, $post, $smarty;

    define( "ROUTE", "window" );
    Session::check();

    $colors = json_decode(file_get_contents(PATH_DATA."colors.json"));
    $colors = getColors($colors);

    $smarty->assign( "login", $login );
    $smarty->assign( "config", $config );
    $smarty->assign( "colors", $colors );

    $smarty->assign( "module", $get->module );
    $smarty->assign( "action", $get->action );
    $smarty->assign( "pages", json_decode(file_get_contents(PATH_DATA."pages.json")) );
    $smarty->assign( "profile", json_decode(file_get_contents(PATH_DATA."profile.json")) );
    $smarty->assign( "theme", @$_SESSION["theme"] ? $_SESSION["theme"] : "" );

    if( $get->module == "budget" && $get->action == "print" && @$get->budget_id ){
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
                "budget_cost=CAST(budget_cost AS FLOAT)",
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
                [ "budget_id", "i", "=", $get->budget_id ]
            ],
            "gets" => [
                "get_budget_items" =>  1,
                "get_budget_person" =>  1,
                "get_person_address" =>  1,
                "get_address_contact" =>  1,
                "get_budget_address" =>  1,
                "get_budget_seller" =>  1,
                "get_budget_payments" =>  1,
                "get_budget_company" =>  1,
                "get_budget_term" => 1
            ]
        ]);
        $company = Model::get($commercial,(Object)[
            "tables" => [ "Company" ],
            "fields" => [
                "company_id",
                "company_st",
                "company_consumer_id",
                "company_budget_message"
            ],
            "filters" => [[ "company_id", "i", "=", $budget->company_id ]]
        ]);
        $company->image = getImage((Object)[
            "image_id" => $company->company_id,
            "image_dir" => "company"
        ]);
        $smarty->assign( "budget", $budget );
        $smarty->assign( "company", $company );
    }

    foreach( get_defined_constants(true)["user"] as $constant => $value ){
        $smarty->assign( $constant, $value );
    }

    $smarty->display( PATH_TEMPLATES . "window.html" );

?>