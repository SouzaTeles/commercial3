<?php

    include "../../config/start.php";

    if( !@$get->action ){
        headerResponse( 417, $errorMessage["parameter-get"] );
    }

    $period = explode( " - ", $post->period );
    $period = (Object)[
        "start" => DateTime::createFromFormat( 'd/m/Y', $period[0] ),
        "end" => DateTime::createFromFormat( 'd/m/Y', $period[1] )
    ];

    switch( $get->action )
    {

        case "report":

            $data = ModelSql::getList((Object)[
                "class" => "Shopping",
                "tables" => [
                    "Documento D",
                    "INNER JOIN Operacao O ON(D.IdOperacao = O.IdOperacao)",
                    "INNER JOIN Usuario U ON(D.IdUsuario = U.IdUsuario)",
                    "INNER JOIN Pessoa F ON(D.IdPessoa = F.IdPessoa)",
                    "INNER JOIN DocumentoItem DI ON(DI.IdDocumento = D.IdDocumento)",
                    "INNER JOIN Produto P ON(DI.IdProduto = P.IdProduto)",
                    "INNER JOIN GrupoProduto GP ON(P.IdGrupoProduto = GP.IdGrupoProduto)",
                    "INNER JOIN Unidade UN ON(UN.IdUnidade = P.IdUnidade)",
                    "INNER JOIN CodigoProduto CP ON(CP.IdProduto = P.IdProduto AND CP.StCodigoPrincipal = 'S')",
                    "INNER JOIN LoteEstoque LE ON(LE.IdLoteEstoque = D.IdLoteEstoque)",
                    "INNER JOIN EmpresaERP E ON(E.CdEmpresa = LE.CdEmpresa)"
                ],
                "fields" => [
                    "LE.CdEmpresa",
                    "U.IdUsuario",
                    "P.IdProduto",
                    "GP.IdGrupoProduto",
                    "F.IdPessoa",
                    "U.NmLogin",
                    "U.NmUsuario",
                    "CdProduto = CP.CdChamada",
                    "P.NmProduto",
                    "GP.CdClassificacao",
                    "GP.NmGrupoProduto",
                    "CdPessoa = F.CdChamada",
                    "F.NmPessoa",
                    "UN.CdSigla",
                    "Quantidade = SUM(DI.QtItem)",
                    "Valor = SUM(DI.VlItem)",
                    "D.DtReferencia"
                ],
                "filters" => [
                    [ "O.TpOperacao", "s", "C" ],
                    [ "LE.CdEmpresa", "i", "in", @$post->company ? $post->company : NULL ],
                    [ "U.IdUsuario", "s", "in", @$post->users ? $post->users : NULL ],
                    [ "GP.IdGrupoProduto", "s", "in", @$post->groups ? $post->groups : NULL ],
                    [ "D.IdOperacao", "s", "in", @$post->operations ? $post->operations : NULL ],
                    [ "D.DtReferencia", "s", "between", [ $period->start->format("Y-m-d"), $period->end->format("Y-m-d") ]]
                ],
                "group" => "LE.CdEmpresa, U.IdUsuario, P.IdProduto, GP.IdGrupoProduto, F.IdPessoa, U.NmLogin, U.NmUsuario, CP.CdChamada, P.NmProduto, GP.CdClassificacao, GP.NmGrupoProduto, F.CdChamada, F.NmPessoa, UN.CdSigla, D.DtReferencia",
                "join" => 1
            ]);

            $data = Shopping::group($data);

            file_put_contents( PATH_TMP . session_id() . ".json", json_encode($data) );

            Json::get( $headerStatus[200], $data );

        break;

    }

?>