<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $dafel, $headerStatus, $get, $post, $config, $login;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não localizado."
        ]);
    }

    switch($get->action){

        case "getList":

            if( !@$post->person_id || !@$post->instance_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não localizado."
                ]);
            }

            $credits = Model::getList($dafel,(Object)[
                "class" => "PersonCredit",
                "tables" => [
                    "APagar AP (NoLock)",
                    "FormaPagamento FP (NoLock)"
                ],
                "fields" => [
                    "AP.IdAPagar",
                    "AP.IdPessoa",
                    "FP.IdFormaPagamento",
                    "AP.CdEmpresa",
                    "AP.NrTitulo",
                    "FP.DsFormaPagamento",
                    "VlTitulo = (ISNULL(AP.VlTitulo, 0)-ISNULL(AP.VlIRRF, 0)-ISNULL(AP.VlPIS, 0)-ISNULL(AP.VlCOFINS, 0)- ISNULL(AP.VlCSLL, 0)-ISNULL(AP.VlINSS, 0)-ISNULL(AP.VlISS, 0)- ISNULL(AP.VlPIS_COFINS_CSLL, 0)- ISNULL(AP.VlOutros, 0))",
                    "VlUtilizado = ISNULL(( SELECT Sum(ISNULL(APB.VlBaixa, 0)) FROM APagarBaixa APB WHERE APB.IdAPagar = AP.IdAPagar GROUP BY APB.IdAPagar ),0) + ISNULL(( SELECT Sum(ISNULL(LAPB.VlBaixa, 0)) FROM LoteAPagarBaixa LAPB WHERE LAPB.IdAPagar = AP.IdAPagar AND ( NOT EXISTS( SELECT AB.IdAPagarBaixa FROM APagarBaixa AB WHERE ( AB.IdAPagarBaixa = LAPB.IdAPagarBaixa ))) GROUP BY LAPB.IdAPagar ),0)",
                    "AP.DtEmissao",
                    "AP.DsObservacao",
                    "Empenhado = (SELECT Name FROM TempDB..sysObjects WHERE Name like '##CCredito$%' AND (SUBSTRING(Name,23,10) <> '{$post->instance_id}' AND (SUBSTRING(Name,12,10) = AP.IdApagar)))"
                ],
                "filters" => [
                    [ "AP.DtExclusao IS NULL" ],
                    [ "AP.DtBaixa IS NULL" ],
                    [ "FP.IdFormaPagamento = AP.IdFormaPagamento" ],
                    [ "AP.IdPessoa", "s", "=", $post->person_id ],
                    [
                        [ "AP.IdNaturezaLancamento", "s", "=", $config->credit->entry_id  ],
                        [ "(SELECT COUNT(*) FROM ApagarItem APIB WHERE APIB.IdAPagar = AP.IdAPagar AND APIB.IdNaturezaLancamento = '{$config->credit->entry_id }') = 1" ]
                    ]
                ]
            ]);

            Json::get( $headerStatus[200], $credits );

        break;

        case "pawn":

            if( !@$post->payable_id || !@$post->instance_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não localizado."
                ]);
            }

            $data = (Object)[
                "id" => $post->payable_id,
                "table" => "##CCredito\${$post->payable_id}\${$post->instance_id}\${$login->external_id}\${$config->credit->pawn_system}\$M",
                "description" => "Inclusao do Commercial",
                "date" => date("Y-m-d H:i:s"),
                "instance_id" => $post->instance_id,
                "login_id" => $login->user_id,
                "login_name" => $login->user_name
            ];
            file_put_contents(PATH_ROOT . "public/credit/new/{$post->payable_id}.json", json_encode($data));

            Json::get($headerStatus[200]);

        break;

        case "redeem":

            if( !@$post->payable || !@$post->instance_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não localizado."
                ]);
            }

            foreach( $post->payable as $payable_id ){
                $data = (Object)[
                    "id" => $payable_id,
                    "table" => "##CCredito\${$payable_id}\${$post->instance_id}\${$login->external_id}\${$config->credit->pawn_system}\$M",
                    "description" => "Inclusao do Commercial",
                    "date" => date("Y-m-d H:i:s"),
                    "instance_id" => $post->instance_id,
                    "login_id" => $login->user_id,
                    "login_name" => $login->user_name
                ];
                file_put_contents(PATH_ROOT . "public/credit/del/{$payable_id}.json", json_encode($data));
            }

            Json::get($headerStatus[200]);

        break;
    }

?>