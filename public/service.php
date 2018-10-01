<?php

    include "../config/start.php";
    
    if( !@$_GET["token"] || $_GET["token"] != "r0zUBn6o7tbggzZQXCusGT2DUPJ4wHF3" ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    GLOBAL $commercial;

    $services = Model::getList($commercial,(Object)[
        "tables" => [ "service" ],
        "fields" => [
            "service_id",
            "service_key",
            "service_period",
            "service_days",
            "service_time",
            "service_last_run"
        ],
        "filters" => [[ "service_active", "s", "=", "Y" ]]
    ]);

    foreach( $services as $service ){
        $params = (Object)[
            "period" => $service->service_period,
            "days" => $service->service_days,
            "time" => $service->service_time,
            "last" => "2019-01-01 12:00:00"//$service->service_last_run
        ];
        if( run($params) ){
            $function = "";
            foreach(explode("-",$service->service_key) as $key ){
                $function .= ucfirst($key);
            }
            Service::$function();
            Model::update($commercial,(Object)[
                "debug" => 0,
                "no_update" => 1,
                "table" => "service",
                "fields" => [[ "service_last_run", "s", date("Y-m-d H:i:s") ]],
                "filters" => [[ "service_id", "i", "=", $service->service_id ]]
            ]);
        }
    }

//    $reports = Model::getList($commercial,(Object)[
//        "tables" => [ "report" ],
//        "fields" => [
//            "report_id",
//            "report_period",
//            "report_name",
//            "report_days",
//            "report_time",
//            "report_last_run"
//        ],
//        "filters" => [
//            [ "report_active", "s", "=", "Y" ],
//            [ "report_trash", "s", "=", "N" ]
//        ]
//    ]);
//
//    foreach( $reports as $report ){
//        $params = (Object)[
//            "period" => $report->report_period,
//            "days" => $report->report_days,
//            "time" => $report->report_time,
//            "last" => $report->report_last_run
//        ];
//        if( run($params) ){
//            $users = Model::getList($commercial,(Object)[
//                "tables" => [ "report_user ru", "user u" ],
//                "fields" => [ "u.user_id", "u.user_mail", "u.user_name", "ru.report_user_modules" ],
//                "filters" => [
//                    [ "ru.user_id = u.user_id" ],
//                    [ "ru.report_id", "i", "=", $report->report_id ]
//                ]
//            ]);
//            if( sizeof($users)) {
//                foreach ($users as $user) {
//                    $user->token = md5(uniqid(rand(), true));
//                    $data = (Object)[
//                        "report_id" => $report->report_id,
//                        "user_id" => $user->user_id
//                    ];
//                    Model::insert($commercial, (Object)[
//                        "table" => "token",
//                        "fields" => [
//                            ["token_value", "s", $user->token],
//                            ["token_data", "s", json_encode($data)]
//                        ]
//                    ]);
//                    email((Object)[
//                        "origin" => "report",
//                        "subject" => $report->report_name,
//                        "recipient" => [(Object)[
//                            "email" => $user->user_mail,
//                            "name" => $user->user_name
//                        ]],
//                        "vars" => [(Object)[
//                            "key" => "user",
//                            "data" => $user
//                        ], (Object)[
//                            "key" => "report",
//                            "data" => $report
//                        ]]
//                    ]);
//                }
//            }
//            Model::update($commercial,(Object)[
//                "table" => "report",
//                "fields" => [[ "report_last_run", "s", date("Y-m-d H:i:s") ]],
//                "filters" => [[ "report_id", "i", "=", $report->report_id ]]
//            ]);
//        }
//    }

?>