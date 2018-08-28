<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $site, $login, $headerStatus, $get;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    checkAccess();

    if( in_array($get->action,["trigger"]) ){
        postLog();
    }

    $site_id = @$site ? $site->site_id : ( @$post->site_id ? $post->site_id : NULL );

    switch( $get->action ){

        case "config":

            $config = Model::get($commercial,(Object)[
                "tables" => [ "email_config" ],
                "filters" => [[ "email_config_id", "i", "=", 1 ]]
            ]);

            $config->email_config_pass = base64_decode($config->email_config_pass);

            Json::get( $headerStatus[200], $config );

        break;

        case "edit":

            if( !@$post->email_config_id || !@$post->email_config_smtp || !@$post->email_config_host || !@$post->email_config_port || !@$post->email_config_user || !@$post->email_config_pass ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "email_config",
                "fields" => [
                    [ "email_config_host", "s", $post->email_config_host ],
                    [ "email_config_port", "i", $post->email_config_port ],
                    [ "email_config_smtp", "s", $post->email_config_smtp ],
                    [ "email_config_user", "s", $post->email_config_user ],
                    [ "email_config_pass", "s", base64_encode($post->email_config_pass) ],
                ],
                "filters" => [[ "email_config_id", "i", "=", $post->email_config_id ]]
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => "Configurações atualizadas com sucesso!"
            ]);

        break;

        case "getList":

            $data = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "email e",
                    "inner join email_recipient er on(er.email_id = e.email_id)"
                ],
                "fields" => [
                    "e.email_id",
                    "er.email_recipient_email",
                    "er.email_recipient_name",
                    "e.email_origin",
                    "e.email_subject",
                    "e.email_status",
                    "e.email_date",
                ],
                "filters" => [[ "e.email_trash", "s", "=", "N" ]]
            ]);

            $sent = [];
            foreach( $data as $email ){
                if( !@$sent[$email->email_id] ){
                    $sent[$email->email_id] = (Object)[
                        "email_id" => $email->email_id,
                        "email_origin" => $email->email_origin,
                        "email_subject" => $email->email_subject,
                        "email_status" => $email->email_status,
                        "email_date" => $email->email_date,
                        "email_date_br" => date_format(date_create($email->email_date),"d/m/Y H:i:s"),
                        "recipients" => []
                    ];
                }
                $sent[$email->email_id]->recipients[] = "{$email->email_recipient_name} : {$email->email_recipient_email}";
            }

            $ret = [];
            foreach( $sent as $email ){
                $ret[] = $email;
            }

            Json::get( $headerStatus[200], $sent );

        break;

        case "log":

            if (!@$post->email_id) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $data = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "email_log el",
                    "left join user u on(el.user_id = u.user_id)" ],
                "fields" => [
                    "el.email_id",
                    "u.user_name",
                    "el.email_log_message",
                    "el.email_log_date"
                ],
                "filters" => [[ "el.email_id", "i", "=", $post->email_id ]]
            ]);

            foreach( $data as $log ){
                $log->email_log_date_br = date_format(date_create($log->email_log_date),"d/m/Y H:i:s");
            }

            Json::get( $headerStatus[200], $data );

        break;
        
        case "test":

            if( !@$post->config || !@$post->recipient ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);   
            }
            
            $post->config = (Object)$post->config;
            $recipient = $post->recipient["recipient"];

            if( !@$post->config->email_config_smtp || !@$post->config->email_config_host || !@$post->config->email_config_port || !@$post->config->email_config_user || !@$post->config->email_config_pass ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $mail = new Mail((Object)[
                "smtp" => ( $post->config->email_config_smtp == "Y" ),
                "host" => $post->config->email_config_host,
                "port" => $post->config->email_config_port,
                "user" => $post->config->email_config_user,
                "pass" => $post->config->email_config_pass,
                "debug" => 0
            ]);

            $subject = "Mensagem de Teste";
            $from = (Object)[
                "email" => $post->config->email_config_user,
                "name" => "Compass"
            ];
            $recipients = [(Object)[
                "email" => $recipient,
                "name" => $recipient
            ]];
            $message = "<p>Olá! </p><p>Se você recebeu essa mensagem, isso significa que o teste de configuração de e-mail funcionou!</p>";

            $ret = $mail->send((Object)[
                "from" => $from,
                "recipients" => $recipients,
                "subject" => $subject,
                "message" => $message
            ]);

            if( !$ret->sent ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não foi possível validar as configurações de E-mail. Verifique e tente novamente."
                ]);
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "<b>Configurações validadas com sucesso!</b><br/>Confirme o recebimento da mensagem de teste na caixa de entrada do e-mail {$recipient}."
            ]);
            
        break;

        case "trigger":

            if (!@$post->email_id) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $email = Model::get($commercial,(Object)[
                "tables" => [ "email" ],
                "filters" => [
                    [ "email_id", "i", "=", $post->email_id ],
                    [ "site_id", "i", "=", $site_id ]
                ]
            ]);

            if( !@$email ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Mensagem não encontrada."
                ]);
            }

            $site_email = Model::get($commercial,(Object)[
                "tables" => [ "site_email" ],
                "filters" => [[ "site_id", "i", "=", $email->site_id ]]
            ]);

            $site = Model::get($commercial,(Object)[
                "tables" => [ "site" ],
                "fields" => [ "site_name" ],
                "filters" => [[ "site_id", "i", "=", $email->site_id ]]
            ]);

            if( !@$site || !@$site_email ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "As configurações de envio não foram localizadas."
                ]);
            }

            $email->recipients = Model::getList($commercial,(Object)[
                "tables" => [ "email_recipient" ],
                "fields" => [
                    "email_recipient_email as email",
                    "email_recipient_name as name"
                ],
                "filters" => [[ "email_id", "i", "=", $post->email_id ]]
            ]);

            if( !@$email->recipients ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Nenhum destinatário foi localizado."
                ]);
            }

            $date = new DateTime($email->email_date);
            $path = $date->format("Y/F/d");
            $email->message = file_get_contents( PATH_LOG . "email/{$path}/{$email->email_id}.html" );

            $mail = new Mail((Object)[
                "smtp" => ($site_email->site_email_smtp == "Y"),
                "host" => $site_email->site_email_host,
                "port" => $site_email->site_email_port,
                "user" => $site_email->site_email_user,
                "pass" => base64_decode($site_email->site_email_pass),
                "debug" => 0
            ]);

            $mail->phpMailer->SetLanguage("br");

            $ret = $mail->send((Object)[
                "from" => (Object)[
                    "email" => $site_email->site_email_user,
                    "name" => $site->site_name
                ],
                "recipients" => $email->recipients,
                "subject" => $email->email_subject,
                "message" => $email->message
            ]);

            Model::insert($commercial,(Object)[
                "table" => "email_log",
                "fields" => [
                    [ "email_id", "i", $email->email_id ],
                    [ "user_id", "i", $login->user_id ],
                    [ "email_log_message", "s", ( @$ret->sent ? "Mensagem enviada com sucesso." : $ret->ErrorInfo ) ]
                ]
            ]);

            Model::update($commercial,(Object)[
                "table" => "email",
                "fields" => [[ "email_status", "s", ( @$ret->sent ? "S" : "E" )]],
                "filters" => [[ "email_id", "i", "=", $email->email_id ]]
            ]);

            Json::get( $headerStatus[200], (Object)[
                "message" => ( @$ret->sent ? "A mensagem foi enviada com sucesso." : "Não foi possivel enviar a mensagem." )
            ]);

        break;

    }

?>