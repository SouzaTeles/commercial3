<?php

    include "../../config/start.php";
    Session::checkApi();

    GLOBAL $dafel, $commercial, $site, $login, $headerStatus, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    switch( $get->action ){

        case "dropAudit":

            $drops = Model::getList($dafel,(Object)[
                "join" => 1,
                "tables" => [
                    "DAFEL.dbo.AReceberItemBaixa ARIB1",
                    "INNER JOIN DAFEL.dbo.Usuario U (NoLock) ON(U.IdUsuario = ARIB1.IdUsuario)",
                    "INNER JOIN DAFEL.dbo.AReceberItem ARI1 (NoLock) ON(ARIB1.IdAreceberItem = ARI1.IdAreceberItem)",
                    "INNER JOIN DAFEL.dbo.AReceber AR (NoLock) ON(AR.IdAreceber = ARI1.IdAreceber)",
                    "INNER JOIN DAFEL.dbo.FormaPagamento FP (NoLock) ON(FP.IdFormaPagamento = AR.IdFormaPagamento)",
                    "INNER JOIN DAFEL.dbo.AReceberItem ARI2 (NoLock) ON(ARI2.IdAreceber = AR.IdAreceber)",
                    "INNER JOIN DAFEL.dbo.AReceberItemBaixa ARIB2 (NoLock) ON(ARIB2.IdAReceberItem = ARI2.IdAReceberItem)",
                ],
                "fields" => [
                    "title_id=AR.IdAreceber",
                    "company_id=AR.CdEmpresa",
                    "title_code=AR.NrTitulo",
                    "title_value=CAST(AR.VlTitulo AS FLOAT)",
                    "title_drop_date=ARIB1.DtBaixa",
                    "title_drop_date_br=ARIB1.DtBaixa",
                    "title_drop_value=CAST(ARIB1.VlBaixaItem AS FLOAT)",
                    "title_drop_process=ARIB1.DtProcessamento",
                    "title_drop_process_br=ARIB1.DtProcessamento",
                    "title_drop_value_total=CAST(SUM(ARIB2.VlBaixaItem) AS FLOAT)",
                    "user_name=U.NmLogin",
                    "modality_description=FP.DsFormaPagamento"
                ],
                "filters" => [
                    [ "ARIB1.DtBaixa", "s", "between", ["{$post->drop_start_date} 00:00:00","{$post->drop_end_date} 23:59:59"]],
                    [ "ARIB1.DtProcessamento", "s", "between", ["{$post->process_start_date} 00:00:00","{$post->process_end_date} 23:59:59"]],
                    [
                        ["ARI1.IdTipoMovCobranca IS NULL"],
                        ["ARI1.IdTipoMovCobranca", "s", "!=", "00A0000004"]
                    ],
                    [ "AR.NrTitulo", "s", "NOT LIKE", @$post->not_ren ? "%REN%" : NULL],
                    [ "AR.CdEmpresa", "s", "in", @$post->companies ? $post->companies : NULL],
                    [ "AR.NmTitulo", "s", "not in", ["VISA","CREDICARD","CIELO"]],
                    [ "ARIB1.IdTipoBaixa", "s", "in", ["00A0000001","00A0000002","00A0000005"]],
                    [ "ARIB1.IdFormaPagamento", "s", "not in", ["00A0000002","00A000000N","00A000001J","00A000001K"]]
                ],
                "group" => implode(",",[
                    "AR.IdAreceber",
                    "AR.CdEmpresa",
                    "AR.NrTitulo",
                    "AR.VlTitulo",
                    "ARIB1.DtBaixa",
                    "ARIB1.VlBaixaItem",
                    "ARIB1.DtProcessamento",
                    "U.NmLogin",
                    "FP.DsFormaPagamento"
                ]),
                "having" => @$post->only_diff ? "AR.VlTitulo-SUM(ARIB2.VlBaixaItem) >= 0.01" : NULL
            ]);

            foreach( $drops as $drop ){
                $drop->title_value = (float)$drop->title_value;
                $drop->title_drop_value = (float)$drop->title_drop_value;
                $drop->title_drop_value_total = (float)$drop->title_drop_value_total;
                $drop->diff = $drop->title_value - $drop->title_drop_value_total;
                $drop->diff_order = substr("0000000000" . number_format($drop->diff,2,"",""),-10);
                $drop->title_value_order = substr("0000000000" . number_format($drop->title_value,2,"",""),-10);
                $drop->title_drop_value_order = substr("0000000000" . number_format($drop->title_drop_value,2,"",""),-10);
                $drop->title_drop_value_total_order = substr("0000000000" . number_format($drop->title_drop_value_total,2,"",""),-10);
            }

            Json::get($headerStatus[200], $drops);

        break;

    }

?>