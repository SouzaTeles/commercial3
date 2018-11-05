<?php

    function postLog($params)
    {
        GLOBAL $commercial, $login, $post, $get, $headers, $config;

        $log_id = (int)Model::insert($commercial,(Object)[
            "table" => "[Log]",
            "fields" => [
                [ "user_id", "s", @$params->user_id ? $params->user_id : $login->user_id ],
                [ "log_script", "s", @$params->script ? $params->script : SCRIPT_NAME ],
                [ "log_action", "s", $get->action ],
                [ "log_system_version", "s", $config->system->system_version ],
                [ "log_parent_id", "s", @$params->parent_id ? $params->parent_id : NULL ],
                [ "log_app_version", "s", @$headers["AppVersion"] ? $headers["AppVersion"] : NULL ],
                [ "log_host_ip", "s", @$headers["HostIP"] && $headers["HostIP"] != "null" ? $headers["HostIP"] : NULL ],
                [ "log_host_name", "s", @$headers["HostName"] ? $headers["HostName"] : NULL ],
                [ "log_platform", "s", @$headers["Platform"] ? $headers["Platform"] : NULL ],
                [ "log_date", "s", date("Y-m-d H:i:s") ],
            ]
        ]);

        $pathLog =  PATH_LOG . "post/" . date("Y/F/d") . "/" . (@$params->script ? $params->script : SCRIPT_NAME) . "/" . ( @$get->action ? "{$get->action}/" : "" );
        if( !is_dir($pathLog) ) mkdir($pathLog, 0755, true);
        file_put_contents("{$pathLog}{$log_id}.json" , json_encode((Object)[
            "post" => $post,
            "headers" => $headers
        ]));

        return $log_id;
    }

    function email( $params )
    {
        GLOBAL $commercial, $login, $smarty;

        $date = date("Y-m-d");

        $email_id = Model::insert($commercial,(Object)[
            "table" => "Email",
            "fields" => [
                [ "user_id", "i", @$login->user_id ? $login->user_id : NULL ],
                [ "parent_id", "i", @$params->parent_id ? $params->parent_id : NULL ],
                [ "email_origin", "s", $params->origin ],
                [ "email_subject", "s", removeSpecialChar($params->subject) ],
                [ "email_status", "s", "O" ],
                [ "email_trash", "s", "N" ],
                [ "email_date", "s", $date]
            ]
        ]);

        foreach( $params->recipient as $recipient ){
            Model::insert($commercial,(Object)[
                "table" => "EmailRecipient",
                "fields" => [
                    [ "email_id", "i", $email_id ],
                    [ "email_recipient_email", "s", $recipient->email ],
                    [ "email_recipient_name", "s", @$recipient->name ? $recipient->name : NULL ],
                    [ "email_recipient_date", "s", $date ]
                ]
            ]);
        }

        if( @$params->files ) {
            foreach ($params->files as $file) {
                Model::insert($commercial, (Object)[
                    "table" => "EmailFile",
                    "fields" => [
                        ["email_id", "i", $email_id],
                        ["email_file_name", "s", $file],
                        ["email_file_date", "s", $date]
                    ]
                ]);
            }
        }

        $smarty->assign( "login", $login );

        if( @$params->vars ) {
            foreach( $params->vars as $var ){
                $smarty->assign("{$var->key}", $var->data);
            }
        }

        foreach( get_defined_constants(true)["user"] as $constant => $value ){
            $smarty->assign( $constant, $value );
        }

        ob_start();
        $smarty->display( PATH_TEMPLATES . "email/{$params->origin}.html" );
        $message = ob_get_clean();

        $date = date("Y/F/d");
        $pathLog =  PATH_LOG . "email/{$date}/";
        if( !is_dir($pathLog) ) mkdir($pathLog, 0755, true);

        file_put_contents( "{$pathLog}{$email_id}.html", $message );
    }

    function calendar( $params ){

        GLOBAL $business_days, $business_days_exception;

        $date = explode( "-", $params->reference);
        $past = 0;
        $day = (int)$date[2];
        $year = $date[0];
        $month = $date[1];

        if( @$business_days_exception[$year][$month][$params->company_id] ){
            $days = $business_days_exception[$year][$month][$params->company_id]->count;
        } else {
            $days = $business_days[$year][$month]->count;
        }

        $useless = @$business_days_exception[$year][$month][$params->company_id] ? $business_days_exception[$year][$month][$params->company_id]->days : $business_days[$year][$month]->days;

        for( $d=1; $d<=$day; $d++ ){
            if( !@$useless[$d] ){
                $past++;
            }
        }

        return (Object)[
            "days" => $days,
            "past" => $past
        ];
    }

?>