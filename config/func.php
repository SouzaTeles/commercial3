<?php

    function postLog()
    {
        GLOBAL $commercial, $login, $post, $get;

        $log_id = Model::insert($commercial,(Object)[
            "table" => "[Log]",
            "fields" => [
                [ "user_id", "s", @$login ? $login->user_id : NULL ],
                [ "log_script", "s", SCRIPT_NAME ],
                [ "log_action", "s", @$get->action ? $get->action : NULL ],
                [ "log_date", "s", date("Y-m-d H:i:s") ]
            ]
        ]);

        $pathLog =  PATH_LOG . "post/" . date("Y/F/d") . "/" . SCRIPT_NAME . "/" . ( @$get->action ? "{$get->action}/" : "" );
        if( !is_dir($pathLog) ) mkdir($pathLog, 0755, true);
        file_put_contents( "{$pathLog}{$log_id}.json" , json_encode($post) );
    }

    function email( $params )
    {
        GLOBAL $commercial, $site, $login, $smarty;

        $email_id = Model::insert($commercial,(Object)[
            "table" => "email",
            "fields" => [
                [ "user_id", "i", @$login->user_id ? $login->user_id : NULL ],
                [ "email_origin", "s", $params->origin ],
                [ "email_subject", "s", $params->subject ],
                [ "email_status", "s", "O" ],
                [ "email_trash", "s", "N" ]
            ]
        ]);

        foreach( $params->recipient as $recipient ){
            Model::insert($commercial,(Object)[
                "table" => "email_recipient",
                "fields" => [
                    [ "email_id", "i", $email_id ],
                    [ "email_recipient_email", "s", $recipient->email ],
                    [ "email_recipient_name", "s", @$recipient->name ? $recipient->name : NULL ]
                ]
            ]);
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