<?php

    class Service
    {
        public static function Email()
        {
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
        }

        public static function Billing()
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

        public static function Synchronize()
        {
            GLOBAL $commercial, $dafel, $config, $post, $get;

            $data = Model::getList($dafel,(Object)[
                "top" => 100,
                "tables" => [ "Documento D" ],
                "fields" => [
                    "D.IdDocumento",
                    "D.NrDocumento",
                    "D.CdEspecie",
                    "D.StDocumentoCancelado",
                    "D.IdEntidadeOrigem",
                    "IdPedidoDeVenda=(SELECT PVI_DI.IdPedidodeVenda FROM PedidoDeVendaItem_DocumentoItem PVI_DI WHERE PVI_DI.IdDocumentoItem = (SELECT TOP 1 DI.IdDocumentoItem FROM DocumentoItem DI WHERE DI.IdDocumento = D.IdDocumento))"
                ],
                "filters" => [[ "D.IdDocumento", "s", ">", $config->budget->last_document ]],
                "order" => "D.IdDocumento ASC"
            ]);

            if( @$data ){
                $IdDocumento = NULL;
                foreach ($data as $item) {
                    $IdPedidoDeVenda = @$item->IdPedidoDeVenda ? $item->IdPedidoDeVenda : $item->IdEntidadeOrigem;
                    if( @$IdPedidoDeVenda ){
                        $budget = Model::get($commercial,(Object)[
                            "tables" => [ "Budget" ],
                            "fields" => [ "budget_id" ],
                            "filters" => [[ "external_id", "s", "=", $IdPedidoDeVenda ]]
                        ]);
                        if( @$budget ){
                            Model::update($commercial, (Object)[
                                "table" => "Budget",
                                "fields" => [
                                    ["document_id", "s", $item->IdDocumento],
                                    ["document_type", "s", $item->CdEspecie],
                                    ["document_code", "s", $item->NrDocumento],
                                    ["document_canceled", "s", $item->StDocumentoCancelado == "S" ? "Y" : "N"],
                                    ["budget_status", "s", "B"]
                                ],
                                "filters" => [["budget_id", "i", "=", $budget->budget_id]]
                            ]);
                            $post = $item;
                            $get->action = "synchronize";
                            postLog((Object)[
                                "user_id" => 1,
                                "script" => "budget",
                                "parent_id" => $budget->budget_id
                            ]);
                        }
                    }
                    $IdDocumento = $item->IdDocumento;
                }
                if( @$IdDocumento ){
                    Model::update($commercial,(Object)[
                        "table" => "Config",
                        "fields" => [
                            [ "config_value", "s", $IdDocumento ],
                            [ "config_date", "s", date("Y-m-d H:i:s") ]
                        ],
                        "filters" => [[ "config_id", "i", "=", 25 ]]
                    ]);
                }
            }
        }

        public static function clearCredit()
        {
            GLOBAL $dafel, $commercial;

            $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " - 1 year"));
            $people = Model::getList($dafel,(Object)[
                "top" => 3,
                "tables" => [
                    "Pessoa P",
	                "PessoaComplementar PC"
                ],
                "fields" => [
                    "P.IdPessoa",
                    "P.CdChamada",
                    "P.NmPessoa",
                    "PC.VlLimiteCredito",
                    "DtUltimaCompra=CONVERT(VARCHAR(10),(SELECT TOP 1 D.DtEmissao FROM Documento D WHERE D.IdPessoa = P.IdPessoa ORDER BY D.DtEmissao DESC),126)"
                ],
                "filters" => [
                    [ "P.IdPessoa = PC.IdPessoa" ],
                    [ "ISNULL(PC.VlLimiteCredito,0)", "d", ">", 0.01 ],
                    [ "(SELECT TOP 1 D.DtEmissao FROM Documento D WHERE D.IdPessoa = P.IdPessoa ORDER BY D.DtEmissao DESC)", "s", "<", $date ]
                ]
            ]);

            if( @$people ){
                foreach( $people as $person ){
                    Model::update($dafel,(Object)[
                        "table" => "PessoaComplementar",
                        "fields" => [[ "VlLimiteCredito", "d", "0.01" ]],
                        "filters" => [[ "IdPessoa", "s", "=", $person->IdPessoa ]]
                    ]);
                    Model::insert($commercial,(Object)[
                        "table" => "[CreditLog]",
                        "fields" => [
                            [ "person_id", "s", $person->IdPessoa ],
                            [ "origin", "s", "service" ],
                            [ "last_credit_value", "d", $person->VlLimiteCredito ],
                            [ "last_bill_date", "s", $person->DtUltimaCompra ],
                            [ "credit_log_date", "s", date("Y-m-d H:i:s") ],
                        ]
                    ]);
                }
            }
        }

    }

?>