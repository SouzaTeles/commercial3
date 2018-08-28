<?php

    include "../../../config/start.php";

    Session::checkApi();

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    GLOBAL $dafel, $commercial, $headerStatus;

    switch( $get->action )
    {

        case "getList":

            if( !@$post->company_id || !@$post->account_id || !@$post->start_date || !@$post->end_date ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Verifique o formulário preenchido."
                ]);
            }

            $column = [
                "DtCadastro" => "DtCadastro",
                "DtMovimento" => "DtMovimento"
            ];

            $statements = Model::getList( $dafel, (Object)[
                "join" => 1,
                "tables" => [
                    "MovimentoBancario MB",
                    "LEFT JOIN FormaPagamento FP ON(MB.IdFormaPagamento = FP.IdFormaPagamento)",
                    "LEFT JOIN AReceberItemBaixa ARIB ON(ARIB.IdMovimentoBancario = MB.IdMovimentoBancario AND MB.IdUsuario IS NULL)",
	                "LEFT JOIN APagarBaixa APB ON(APB.IdMovimentoBancario = MB.IdMovimentoBancario AND MB.IdUsuario IS NULL)",
                    "LEFT JOIN Usuario U ON(ISNULL(MB.IdUsuario,ISNULL(ARIB.IdUsuario,APB.IdUsuario)) = U.IdUsuario)"
                ],
                "fields" => [
                    "U.NmLogin",
                    "MB.NrTitulo",
                    "MB.NmTitulo",
                    "DsHistorico",
                    "MB.VlMovimento",
                    "FP.DsFormaPagamento",
                    "DtCadastro = CONVERT(VARCHAR(10),MB.DtCadastro,126)",
                    "DtMovimento = CONVERT(VARCHAR(10),MB.DtMovimento,126)"
                ],
                "filters" => [
                    [ "MB.IdContaBancaria", "s", "=", $post->account_id ],
                    [ "MB.{$column[$post->statement_column]}", "s", "between", [ "{$post->start_date} 00:00:00", "{$post->end_date} 23:59:59" ]]
                ]
            ]);

            if( !sizeof($statements) ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Nenhum movimento encontrado."
                ]);
            }

            $status = Model::getList($commercial,(Object)[
                "tables" => [ "statement_status" ],
                "fields" => [
                    "statement_status_name",
                    "statement_status_min",
                    "statement_status_max",
                    "statement_status_color"
                ]
            ]);

            foreach( $statements as $data ){
                $data->VlMovimento = (float)$data->VlMovimento;
                $data->VlMovimentoAbs = abs($data->VlMovimento);
                $data->VlMovimentoBr = number_format( $data->VlMovimento, 2, ",", "." );
                $data->DiasMovimento = countDays( $data->DtMovimento, $data->DtCadastro );
                $data->DtCadastroBr = date_format(date_create($data->DtCadastro),"d/m/Y");
                $data->DtMovimentoBr = date_format(date_create($data->DtMovimento),"d/m/Y");
                $data->VlMovimentoOrdem = ( $data->VlMovimento < 0 ? "_" : "0" ) . substr( "00000000{$data->VlMovimentoAbs}", -10 );
                foreach( $status as $st ){
                    if( $data->DiasMovimento >= $st->statement_status_min && $data->DiasMovimento <= $st->statement_status_max ){
                        $data->Status = $st;
                    }
                }
                if( !@$data->Status ){
                    $data->Status = (Object)[
                        "statement_status_name" => "Não localizado",
                        "statement_status_min" => 9999,
                        "statement_status_max" => 9999,
                        "statement_status_color" => "#000",
                    ];
                }
            }

            Json::get( $headerStatus[200], $statements );

        break;

    }

?>