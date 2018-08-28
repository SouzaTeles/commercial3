<?php

    include "../../../config/start.php";

    Session::checkApi();

    GLOBAL $dafel, $commercial, $headerStatus, $login, $get;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    $login->user_profile->user_profile_access->commission = $login->user_profile->user_profile_access->financial_commission;
    checkAccess();

    if( in_array($get->action,["add","shutdown"]) ){
        postLog();
    }

    switch( $get->action )
    {

        case "add":

            if( !@$post->company_id || !@$post->person_id || !@$post->movement_date || !@$post->release_date || !@$post->movement_value ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Verifique o parâmetro POST."
                ]);
            }

            $IdPessoaContaCorrente = Model::nextCode($dafel, (Object)[
                "table" => "PessoaContaCorrente",
                "field" => "IdPessoaContaCorrente",
                "increment" => "S",
                "base36encode" => 1
            ]);

            Model::insert($dafel,(Object)[
                "table" => "PessoaContaCorrente",
                "fields" => [
                    ["IdPessoaContaCorrente","s",$IdPessoaContaCorrente],
                    ["DtMovimento","s",$post->movement_date],
                    ["IdPessoa","s",$post->person_id],
                    ["VlMovimento","s",$post->movement_value],
                    ["DtLiberado","s",$post->release_date],
                    ["IdPessoaOrigem","s",""],
                    ["StAdiantamento","s","N"],
                    ["DsHistorico","s",@$post->movement_history ? $post->movement_history : NULL],
                    ["CdEmpresa","s",$post->company_id],
                    ["StComporBaseIRRF","s","S"],
                    ["StComporFechamento","s","S"]
                ]
            ]);

            Json::get($headerStatus[200], (Object)[
                "message" => "Movimento cadastrado com sucesso."
            ]);

        break;

        case "getList":

            if( !@$post->start_date || !@$post->end_date ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Verifique o formulário preenchido."
                ]);
            }

            $sellers = Model::getList($commercial,(Object)[
                "tables" => [ "seller" ],
                "fields" => [ "erp_id" ],
                "filters" => [[ "seller_commission", "s", "=", "Y" ]]
            ]);

            $IdPessoa = [];
            foreach( $sellers as $seller ){
                $IdPessoa[] = $seller->erp_id;
            }

            $commissions = Model::getList($dafel,(Object)[
                "join" => 1,
                "tables" => [
                    "PessoaContaCorrente PCC",
                    "LEFT JOIN AReceber AR ON(AR.IdAReceber = PCC.IdEntidadeOrigem)",
                    "INNER JOIN Pessoa P ON(P.IdPessoa = PCC.IdPessoa)"
                ],
                "fields" => [
                    "PCC.IdPessoaContaCorrente",
                    "P.IdPessoa",
                    "P.CdChamada",
                    "P.NmPessoa",
                    "AR.NrTitulo",
                    "PCC.DsHistorico",
                    "PCC.VlMovimento",
                    "DtLiberado = CONVERT(VARCHAR(10),PCC.DtLiberado,126)",
                    "DtEfetivado = CONVERT(VARCHAR(10),PCC.DtEfetivado,126)"
                ],
                "filters" => [
                    [ "PCC.IdPessoa", "s", "=", @$post->person_id ? $post->person_id : NULL ],
                    [ "PCC.CdEmpresa", "s", "=", @$post->company_id ? $post->company_id : NULL ],
                    $post->status == "dropped" ? ( @$post->reference_date ? [ "PCC.DtEfetivado", "s", "=", $post->reference_date ] : [ "PCC.DtEfetivado IS NOT NULL" ] ) : [ "PCC.DtEfetivado IS NULL" ],
                    @$post->reference_date ? NULL : [ "PCC.DtMovimento", "s", "between", [ "{$post->start_date} 00:00:00", "{$post->end_date} 23:59:59" ]],
                    [ "PCC.IdPessoa", "s", "in", $IdPessoa ]
                ]
            ]);

            $sellers = [];
            foreach( $commissions as $data ){
                if( !@$sellers[$data->IdPessoa] ){
                    $sellers[$data->IdPessoa] = (Object)[
                        "IdPessoa" => $data->IdPessoa,
                        "CdChamada" => $data->CdChamada,
                        "NmPessoa" => $data->NmPessoa,
                        "VlMovimento" => 0
                    ];
                }
                $data->Status = "";
                $data->VlMovimento = (float)number_format( $data->VlMovimento, 2, ".", "" );
                $data->VlMovimentoBr = number_format( $data->VlMovimento, 2, ",", "." );
                $sellers[$data->IdPessoa]->VlMovimento += $data->VlMovimento;
            }

            if( $post->status == "open" ){
                $credits = Model::getList($dafel,(Object)[
                    "join" => 1,
                    "tables" => [
                        "Documento D",
                        "INNER JOIN LoteEstoque LE ON(LE.IdLoteEstoque = D.IdLoteEstoque)",
                        "INNER JOIN DocumentoItem DI ON(D.IdDocumento = DI.IdDocumento)",
                        "INNER JOIN DocumentoPagamento DP ON(D.IdDocumento = DP.IdDocumento AND DP.StCartaCredito = 'S')",
                        "INNER JOIN DocumentoItemRepasse DIR ON(DI.IdDocumentoItem = DIR.IdDocumentoItem)",
                        "INNER JOIN Pessoa P ON(P.IdPessoa = DIR.IdPessoa)",
                        "LEFT JOIN AReceber AR ON(AR.IdEntidadeOrigem = DP.IdDocumentoPagamento AND AR.NmEntidadeOrigem = 'DOCUMENTOPAGAMENTO')",
                        "LEFT JOIN PessoaContaCorrente PCC ON(PCC.IdEntidadeOrigem = D.IdDocumento AND PCC.NmEntidadeOrigem = 'CreditoNaVenda')"
                    ],
                    "fields" => [
                        "D.IdDocumento",
                        "LE.CdEmpresa",
                        "D.NrDocumento",
                        "P.IdPessoa",
                        "P.CdChamada",
                        "P.NmPessoa",
                        "VlMovimento = SUM(DIR.VlBaseRepasse * (DIR.AlRepasseDuplicata/100))",
                        "DtLiberado = CONVERT(VARCHAR(10),D.DtEmissao,126)",
                        "VlItens = SUM(DI.VlItem)",
                        "DP.VlTitulo"
                    ],
                    "filters" => [
                        ["AR.IdAReceber IS NULL"],
                        ["PCC.IdPessoaContaCorrente IS NULL"],
                        ["D.IdDocumento = DI.IdDocumento"],
                        ["D.IdDocumento = DP.IdDocumento"],
                        ["LE.IdLoteEstoque = D.IdLoteEstoque"],
                        ["DI.IdDocumentoItem = DIR.IdDocumentoItem"],
                        ["DP.StCartaCredito", "s", "=", "S"],
                        ["D.StDocumentoCancelado", "s", "=", "N"],
                        ["LE.CdEmpresa", "s", "=", @$post->company_id ? $post->company_id : NULL],
                        ["DIR.IdPessoa", "s", "=", @$post->person_id ? $post->person_id : NULL],
                        ["D.DtEmissao", "s", "between", [$post->start_date, $post->end_date]],
                        ["DIR.IdPessoa", "s", "in", $IdPessoa]
                    ],
                    "group" => "D.IdDocumento,LE.CdEmpresa,D.NrDocumento,P.IdPessoa,P.CdChamada,P.NmPessoa,D.DtEmissao,DP.VlTitulo"
                ]);

                foreach( $credits as $credit ){
                    if( !@$sellers[$credit->IdPessoa] ){
                        $sellers[$credit->IdPessoa] = (Object)[
                            "IdPessoa" => $credit->IdPessoa,
                            "NmPessoa" => $credit->NmPessoa,
                            "CdChamada" => $credit->CdChamada,
                            "VlMovimento" => 0
                        ];
                    }
                    $credit->Recalculado = 1;
                    $credit->NrTitulo = "Carta de Crédito";
                    $credit->VlMovimento = (float)$credit->VlMovimento * ($credit->VlTitulo/$credit->VlItens);
                    $credit->VlMovimentoBr = number_format( $credit->VlMovimento, 2, ",", "." );
                    $credit->DsHistorico = "Gerado a partir do documento {$credit->NrDocumento}";
                    $sellers[$credit->IdPessoa]->VlMovimento += $credit->VlMovimento;
                }

                $commissions = array_merge($commissions,$credits);

                $devs = Model::getList($dafel,(Object)[
                    "join" => 1,
                    "tables" => [
                        "Documento D",
                        "INNER JOIN LoteEstoque LE ON(LE.IdLoteEstoque = D.IdLoteEstoque)",
                        "INNER JOIN DocumentoItem DI ON(D.IdDocumento = DI.IdDocumento)",
                        "INNER JOIN DocumentoPagamento DP ON(D.IdDocumento = DP.IdDocumento AND DP.StCartaCredito = 'S')",
                        "INNER JOIN DocumentoItemRepasse DIR ON(DI.IdDocumentoItem = DIR.IdDocumentoItem)",
                        "INNER JOIN Pessoa P ON(P.IdPessoa = DIR.IdPessoa)",
                        "LEFT JOIN AReceber AR ON(AR.IdEntidadeOrigem = DP.IdDocumentoPagamento AND AR.NmEntidadeOrigem = 'DOCUMENTOPAGAMENTO')",
                        "LEFT JOIN PessoaContaCorrente PCC ON(PCC.IdEntidadeOrigem = D.IdDocumento AND PCC.NmEntidadeOrigem = 'CreditoNaDevolucao')"
                    ],
                    "fields" => [
                        "D.IdDocumento",
                        "LE.CdEmpresa",
                        "D.NrDocumento",
                        "P.IdPessoa",
                        "P.CdChamada",
                        "P.NmPessoa",
                        "VlMovimento = SUM(DIR.VlBaseRepasse * (DIR.AlRepasseDuplicata/100))",
                        "DtLiberado = CONVERT(VARCHAR(10),D.DtDevolucao,126)",
                        "VlItens = SUM(DI.VlItem)",
                        "DP.VlTitulo"
                    ],
                    "filters" => [
                        ["AR.IdAReceber IS NULL"],
                        ["PCC.IdPessoaContaCorrente IS NULL"],
                        ["D.IdDocumento = DI.IdDocumento"],
                        ["D.IdDocumento = DP.IdDocumento"],
                        ["LE.IdLoteEstoque = D.IdLoteEstoque"],
                        ["DI.IdDocumentoItem = DIR.IdDocumentoItem"],
                        ["DP.StCartaCredito", "s", "=", "S"],
                        ["D.StDocumentoCancelado", "s", "=", "N"],
                        ["LE.CdEmpresa", "s", "=", @$post->company_id ? $post->company_id : NULL],
                        ["DIR.IdPessoa", "s", "=", @$post->person_id ? $post->person_id : NULL],
                        ["D.DtDevolucao", "s", "between", [$post->start_date, $post->end_date]],
                        ["DIR.IdPessoa", "s", "in", $IdPessoa]
                    ],
                    "group" => "D.IdDocumento,LE.CdEmpresa,D.NrDocumento,P.IdPessoa,P.CdChamada,P.NmPessoa,D.DtDevolucao,DP.VlTitulo"
                ]);

                foreach( $devs as $dev ){
                    if( !@$sellers[$dev->IdPessoa] ){
                        $sellers[$dev->IdPessoa] = (Object)[
                            "IdPessoa" => $dev->IdPessoa,
                            "NmPessoa" => $dev->NmPessoa,
                            "CdChamada" => $dev->CdChamada,
                            "VlMovimento" => 0
                        ];
                    }
                    $dev->Recalculado = 1;
                    $dev->NrTitulo = "Carta de Crédito";
                    $dev->VlMovimento = -(float)$dev->VlMovimento * ($dev->VlTitulo/$dev->VlItens);
                    $dev->VlMovimentoBr = number_format( $dev->VlMovimento, 2, ",", "." );
                    $dev->DsHistorico = "Gerado a partir da devolucao do documento {$dev->NrDocumento}";
                    $sellers[$dev->IdPessoa]->VlMovimento += $dev->VlMovimento;
                }

                $commissions = array_merge($commissions,$devs);
            }

            $clone = [];
            foreach( $sellers as $seller ){
                $seller->VlMovimentoBr = number_format( $seller->VlMovimento, 2, ",", "." );
                $clone[] = $seller;
            }

            Json::get($headerStatus[200], (Object)[
                "commissions" => $commissions,
                "sellers" => $clone
            ]);

        break;

        case "form":

            $smarty->display( PATH_TEMPLATES . "pages/financial/modal/financial-commission-{$post->form}.html" );

        break;

        case "shutdown":

            if( !@$post->company_id || !@$post->reference_date || !@$post->people ){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Verifique o parâmetro POST."
                ]);
            }

            $ret = [];
            foreach( $post->people as $person ){

                $person = (Object)$person;
                if( !@$person->IdPessoaContaCorrente ){
                    $person->IdPessoaContaCorrente = [];
                }

                $news = [];
                if( @$person->Credito ) {
                    foreach ($person->Credito as $credit) {
                        $credit = (Object)$credit;
                        $IdPessoaContaCorrente = Model::nextCode($dafel, (Object)[
                            "table" => "PessoaContaCorrente",
                            "field" => "IdPessoaContaCorrente",
                            "increment" => "S",
                            "base36encode" => 1
                        ]);
                        $news[] = $IdPessoaContaCorrente;
                        //echo "IdPessoaContaCorrente: {$IdPessoaContaCorrente}<br/>";
                        Model::insert($dafel, (Object)[
                            "table" => "PessoaContaCorrente",
                            "fields" => [
                                ["IdPessoaContaCorrente", "s", $IdPessoaContaCorrente],
                                ["IdEntidadeOrigem", "s", $credit->IdDocumento],
                                ["NmEntidadeOrigem", "s", $credit->VlMovimento >= 0 ? "CreditoNaVenda" : "CreditoNaDevolucao"],
                                ["DtMovimento", "s", $credit->Data],
                                ["IdPessoa", "s", $person->IdPessoa],
                                ["VlMovimento", "s", $credit->VlMovimento],
                                ["DtLiberado", "s", $credit->Data],
                                ["IdPessoaOrigem", "s", ""],
                                ["StAdiantamento", "s", "N"],
                                ["DsHistorico", "s", $credit->DsHistorico],
                                ["CdEmpresa", "s", $post->company_id],
                                ["StComporBaseIRRF", "s", "S"],
                                ["StComporFechamento", "s", "S"]
                            ]
                        ]);
                    }
                }

                $IdFechamentoContaCorrente = Model::nextCode($dafel, (Object)[
                    "table" => "FechamentoContaCorrente",
                    "field" => "IdFechamentoContaCorrente",
                    "increment" => "S",
                    "base36encode" => 1
                ]);

                //echo "IdFechamentoContaCorrente: {$IdFechamentoContaCorrente}<br/>";

                Model::insert($dafel, (Object)[
                    "table" => "FechamentoContaCorrente",
                    "fields" => [
                        ["IdFechamentoContaCorrente", "s", $IdFechamentoContaCorrente],
                        ["IdUsuario", "s", $login->erp_id],
                        ["DtFechamento", "s", $post->reference_date],
                        ["VlFechamento", "d", $person->VlMovimento],
                        ["VlAdicional", "d", "0"],
                    ]
                ]);

                $IdAPagar = Model::nextCode($dafel, (Object)[
                    "table" => "APagar",
                    "field" => "IdAPagar",
                    "increment" => "S",
                    "base36encode" => 1
                ]);

                //echo "IdAPagar: {$IdAPagar}<br/>";

                $data = Model::get($dafel, (Object)[
                    "join" => 1,
                    "tables" => [
                        "FechamentoContaCorrente FCC",
                        "INNER JOIN APagar AP ON(AP.IdEntidadeOrigem = FCC.IdFechamentoContaCorrente)"
                    ],
                    "fields" => [
                        "NumeroSequencialPorFechamento = (Count(AP.NrTitulo))"
                    ],
                    "filters" => [
                        ["AP.NmEntidadeOrigem", "s", "=", "ContaCorrente"],
                        ["AP.IdPessoa", "s", "=", $person->IdPessoa],
                        ["Cast(FCC.DtFechamento As Date)", "s", "=", $post->reference_date]
                    ]
                ]);

                $NumeroSequencialPorFechamento = (int)$data->NumeroSequencialPorFechamento + 1;

                //echo "NumeroSequencialPorFechamento: {$NumeroSequencialPorFechamento}<br/>";

                Model::insert($dafel, (Object)[
                    "table" => "FechamentoContaCorrentePessoa",
                    "fields" => [
                        ["IdFechamentoContaCorrente", "s", $IdFechamentoContaCorrente],
                        ["IdPessoa", "s", $person->IdPessoa],
                        ["VlTotalCredito", "d", $person->VlMovimentoPos],
                        ["VlTotalDebito", "d", $person->VlMovimentoNeg],
                        ["VlDependentes", "d", "0"],
                        ["VlBaseIRRF", "d", $person->VlMovimento],
                        ["AlIRRF", "d", "0"],
                        ["VlIRRF", "d", "0"],
                        ["VlBaseINSS", "d", "0"],
                        ["AlINSS", "d", "0"],
                        ["VlINSS", "d", "0"],
                        ["VlCofins", "d", "0"],
                        ["VlPis", "d", "0"],
                        ["VlCSLL", "d", "0"],
                        ["AlCofins", "d", "0"],
                        ["AlPis", "d", "0"],
                        ["AlCSLL", "d", "0"],
                        ["VlBaseCofins", "d", "0"],
                        ["VlBasePis", "d", "0"],
                        ["VlBaseCSLL", "d", "0"],
                        ["VlAdicional", "d", "0"],
                        ["VlOutros", "d", "0"],
                        ["DtVencimento", "s", $post->reference_date],
                        ["IdContaBancaria", "s", ""]
                    ]
                ]);

                $person->IdPessoaContaCorrente = array_merge( $person->IdPessoaContaCorrente, $news );
                Model::update($dafel, (Object)[
                    "table" => "PessoaContaCorrente",
                    "fields" => [
                        ["IdFechamentoContaCorrente", "s", $IdFechamentoContaCorrente],
                        ["DtEfetivado", "s", $post->reference_date]
                    ],
                    "filters" => [
                        ["DtEfetivado IS NULL"],
                        ["IdFechamentoContaCorrente IS NULL"],
                        ["IdPessoa", "s", "=", $person->IdPessoa],
                        ["CdEmpresa", "s", "=", $post->company_id],
                        ["IdPessoaContaCorrente", "s", "in", $person->IdPessoaContaCorrente]
                    ],
                    "top" => sizeof($person->IdPessoaContaCorrente),
                ]);

                $NrTitulo = "{$person->CdChamada}/{$post->reference_date}/{$NumeroSequencialPorFechamento}";
                Model::insert($dafel, (Object)[
                    "table" => "APagar",
                    "fields" => [
                        ["IdAPagar", "s", $IdAPagar],
                        ["CdEmpresa", "s", $post->company_id],
                        ["IdPessoa", "s", $person->IdPessoa],
                        ["IdCategoria", "s", "0000000004"],
                        ["NmTitulo", "s", $person->NmPessoa],
                        ["DtEmissao", "s", date("Y-m-d")],
                        ["NrTitulo", "s", $NrTitulo],
                        ["VlTitulo", "d", $person->VlMovimento],
                        ["DtVencimento", "s", $post->reference_date],
                        ["IdNaturezaLancamento", "s", "00A00000A3"],
                        ["NmEntidadeOrigem", "s", "ContaCorrente"],
                        ["IdEntidadeOrigem", "s", $IdFechamentoContaCorrente],
                        ["StAglutinaTituloEmCheque", "s", "N"],
                        ["IdUsuario", "s", $login->erp_id]
                    ]
                ]);

                //echo "NrTitulo: {$person->CdChamada}/{$post->reference_date}/{$NumeroSequencialPorFechamento}<br/>";

                $ret[] = (Object)[
                    "CdEmpresa" => $post->company_id,
                    "NrTitulo" => $NrTitulo,
                    "VlTitulo" => number_format($person->VlMovimento,2,",","."),
                    "IdPessoa" => $person->IdPessoa,
                    "Pessoa" => "{$person->CdChamada} - {$person->NmPessoa}",
                    "IdFechamentoContaCorrente" => $IdFechamentoContaCorrente
                ];

            }

            Json::get($headerStatus[200], (Object)[
                "message" => "Fechamento de comissão concluído com sucesso!",
                "data" => $ret
            ]);

        break;

    }

?>