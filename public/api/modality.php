<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action )
    {

        case "getList":

            $modalities = Model::getList($dafel,(Object)[
                "join" => 1,
                "class" => "Modality",
                "tables" => [
                    "FormaPagamento FP",
                    "LEFT JOIN FormaPagamentoItem FPI ON (FPI.IdFormaPagamento = FP.IdFormaPagamento AND FPI.CdEmpresa = $post->company_id)"
                ],
                "fields" => [
                    "FP.IdFormaPagamento",
                    "FP.CdChamada",
                    "FP.DsFormaPagamento",
                    "FP.StAtivo",
                    "FP.IdNaturezaLancamento",
                    "FP.TpFormaPagamento",
                    "Parcelas=COUNT(FPI.IdFormaPagamentoItem)"
                ],
                "filters" => [
                    [ "FP.StAtivo", "s", "=", "S" ]
                ],
                "group" => "FP.IdFormaPagamento,FP.CdChamada,FP.DsFormaPagamento,FP.StAtivo,FP.IdNaturezaLancamento,FP.TpFormaPagamento,FPI.NrDiasPrimeiraParcelaVenda",
                "order" => "DsFormaPagamento"
            ]);

            Json::get( $headerStatus[200], $modalities );

        break;

    }