<?php

    class Service
    {
        public static function email()
        {
            GLOBAL $commercial;

            $emails = Model::getList($commercial,(Object)[
                "tables" => [ "email" ],
                "filters" => [
                    [ "email_status", "s", "=", "O"],
                    [ "email_trash", "s", "=", "N" ]
                ],
                "order" => "email_id DESC",
                "limit" => 3
            ]);

            if( @$emails ){
                foreach( $emails as $email ){
                    $config = Model::get($commercial,(Object)[
                        "tables" => [ "email_config" ],
                        "filters" => [[ "email_config_id", "i", "=", 1 ]]
                    ]);
                    $email->recipients = Model::getList($commercial,(Object)[
                        "tables" => [ "email_recipient" ],
                        "fields" => [
                            "email_recipient_email as email",
                            "email_recipient_name as name"
                        ],
                        "filters" => [[ "email_id", "i", "=", $email->email_id ]]
                    ]);
                    if( @$email->recipients ){
                        $date = new DateTime($email->email_date);
                        $path = $date->format("Y/F/d");
                        $email->message = file_get_contents( PATH_LOG . "email/{$path}/{$email->email_id}.html" );
                        $mail = new Mail((Object)[
                            "smtp" => ($config->email_config_smtp == "Y"),
                            "host" => $config->email_config_host,
                            "port" => $config->email_config_port,
                            "user" => $config->email_config_user,
                            "pass" => base64_decode($config->email_config_pass),
                            "debug" => 0
                        ]);
                        $mail->phpMailer->SetLanguage("br");
                        $ret = $mail->send((Object)[
                            "from" => (Object)[
                                "email" => $config->email_config_user,
                                "name" => "Commercial"
                            ],
                            "recipients" => $email->recipients,
                            "subject" => $email->email_subject,
                            "message" => $email->message
                        ]);
                        Model::insert($commercial,(Object)[
                            "table" => "email_log",
                            "fields" => [
                                [ "email_id", "i", $email->email_id ],
                                [ "email_log_message", "s", ( @$ret->sent ? "Mensagem enviada com sucesso." : $ret->ErrorInfo ) ]
                            ]
                        ]);
                        Model::update($commercial,(Object)[
                            "table" => "email",
                            "fields" => [[ "email_status", "s", ( @$ret->sent ? "S" : "E" )]],
                            "filters" => [[ "email_id", "i", "=", $email->email_id ]]
                        ]);
                    }
                }
            }
        }

        public static function billing()
        {
            GLOBAL $commercial;

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

            $begin = new DateTime(date("Y-m-d"));
            $end = new DateTime(date("Y-m-d"));
            $begin->modify('-1 day');
            $end->modify('+1 day');

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin,$interval,$end);

            foreach( $period as $dt ) {
                $date = $dt->format("Y-m-d");
                Company::Synchronize((Object)[
                    "date" => $date,
                    "devOperations" => $devOperations,
                    "saleOperations" => $saleOperations,
                ]);
                Seller::Synchronize((Object)[
                    "date" => $date,
                    "devOperations" => $devOperations,
                    "saleOperations" => $saleOperations,
                ]);
            }
        }

    }

?>