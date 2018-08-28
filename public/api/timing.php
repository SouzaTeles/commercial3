<?php

    include "../../config/start.php";
    Session::checkApi();

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    GLOBAL $commercial, $site, $login, $headerStatus;

    checkAccess();

    if( !@$post->start_date || !@$post->end_date ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro POST não encontrado."
        ]);
    }

    $operations = Model::getList($commercial,(Object)[
        "tables" => [ "operation" ]
    ]);

    $devOperations = [];
    $saleOperations = [];
    foreach( $operations as $operation ){
        if( $operation->operation_type == "D" ){
            $devOperations[] = $operation->erp_id;
        } else {
            $saleOperations[] = $operation->erp_id;
        }
    }

    $begin = new DateTime($post->start_date);
    $end = new DateTime($post->end_date);
    $begin->modify('-1 day');
    $end->modify('+1 day');

    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($begin,$interval,$end);

    switch( $get->action ){

        case "company":

            foreach( $period as $dt ){

                $date = $dt->format("Y-m-d");
                Company::Synchronize((Object)[
                    "date" => $date,
                    "devOperations" => $devOperations,
                    "saleOperations" => $saleOperations,
                ]);

            }

            Json::get($headerStatus[200], (Object)[
                "message" => "Informações sincronizadas com sucesso!"
            ]);

        break;

        case "seller":

            foreach( $period as $dt ){

                $date = $dt->format("Y-m-d");

                Seller::Synchronize((Object)[
                    "date" => $date,
                    "devOperations" => $devOperations,
                    "saleOperations" => $saleOperations,
                ]);

            }

            Json::get($headerStatus[200], (Object)[
                "message" => "Informações sincronizadas com sucesso!"
            ]);

        break;

        case "getList":

//            $billing = Model::getList($commercial,(Object)[
//                "tables" => [ "billing" ]
//            ]);

            Json::get( $headerStatus[200], [] );

        break;

    }

?>