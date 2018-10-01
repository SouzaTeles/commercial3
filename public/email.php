<?php
    die();
    include "../config/start.php";

    if( !@$_GET["token"] || $_GET["token"] != "BsO6dot0DtnTuqfzoWoTj5FZW4ZfAqxj" ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    GLOBAL $commercial;

    $emails = Model::getList($commercial,(Object)[
        "tables" => [ "Email" ],
        "fields" => [
            "email_id",
            "email_subject",
            "email_date=CONVERT(VARCHAR(10),email_date,126)"
        ],
        "filters" => [
            [ "email_status", "s", "=", "O" ],
            [ "email_trash", "s", "=", "N" ]
        ],
        "order" => "email_id DESC",
        "limit" => 3
    ]);

    if( @$emails ){
        foreach( $emails as $email ){
            $config = Model::get($commercial,(Object)[
                "tables" => [ "EmailAccount" ],
                "fields" => [
                    "email_account_host",
                    "email_account_port",
                    "email_account_smtp",
                    "email_account_user",
                    "email_account_pass"
                ],
                "filters" => [[ "email_account_id", "i", "=", 1 ]]
            ]);
            $email->recipients = Model::getList($commercial,(Object)[
                "tables" => [ "EmailRecipient" ],
                "fields" => [
                    "email=email_recipient_email",
                    "name=email_recipient_name"
                ],
                "filters" => [[ "email_id", "i", "=", $email->email_id ]]
            ]);
            if( @$email->recipients ){
                $date = new DateTime($email->email_date);
                $path = $date->format("Y/F/d");
                $email->message = file_get_contents( PATH_LOG . "email/{$path}/{$email->email_id}.html" );

                $mail = new Mail((Object)[
                    "smtp" => ($config->email_account_smtp == "Y"),
                    "host" => $config->email_account_host,
                    "port" => $config->email_account_port,
                    "user" => $config->email_account_user,
                    "pass" => base64_decode($config->email_account_pass),
                    "debug" => 0
                ]);

                $mail->phpMailer->SetLanguage("br");

                $files = Model::getList($commercial,(Object)[
                    "tables" => [ "EmailFile" ],
                    "fields" => [
                        "file_name=email_file_name",
                        "file_date=CONVERT(VARCHAR(10),email_file_date,126)",
                    ],
                    "filters" => [[ "email_id", "i", "=", $email->email_id ]]
                ]);
                if( @$files ){
                    foreach($files as $file){
                        $date = DateTime::createFromFormat('Y-m-d', $file->file_date);
                        $path = PATH_FILES . "email/{$date->format("Y/F/d")}/{$file->file_name}";
                        $mail->phpMailer->AddAttachment($path);
                    }
                }

                $ret = $mail->send((Object)[
                    "from" => (Object)[
                        "email" => $config->email_account_user,
                        "name" => "Commercial"
                    ],
                    "recipients" => $email->recipients,
                    "subject" => $email->email_subject,
                    "message" => $email->message
                ]);

                Model::insert($commercial,(Object)[
                    "table" => "EmailLog",
                    "fields" => [
                        [ "email_id", "i", $email->email_id ],
                        [ "email_log_message", "s", ( @$ret->sent ? "Mensagem enviada com sucesso." : $ret->ErrorInfo ) ],
                        [ "email_log_date", "s", date("Y-m-d H:i:s") ]
                    ]
                ]);

                Model::update($commercial,(Object)[
                    "table" => "Email",
                    "fields" => [
                        [ "email_status", "s", ( @$ret->sent ? "S" : "E" )],
                        [ "email_update", "s", date("Y-m-d H:i:s") ]
                    ],
                    "filters" => [[ "email_id", "i", "=", $email->email_id ]]
                ]);
            }
        }
    }

?>