<?php

    class VendasRepresentante
    {
        public static function getBill( $params )
        {
            GLOBAL $dafel;

            $data = Model::getList( $dafel, (Object)[
                "tables" => [
                    "Documento D (NoLock)",
                    "INNER JOIN DocumentoItem DI (NoLock) on ( D.IdDocumento = DI.IdDocumento )",
                    "INNER JOIN DocumentoItemValores DIV (NoLock) on ( DI.IdDocumentoItem = DIV.IdDocumentoItem )",
                    "INNER JOIN DocumentoItemRepasse DIR (NoLock) on ( DI.IdDocumentoItem = DIR.IdDocumentoItem )",
                ],
                "fields" => array_merge([
                    "DIR.IdPessoa",
                    "VlVenda=SUM(ISNULL(DI.VlItem,0)+ISNULL(D.VlAcrescimo,0)-ISNULL(D.VlDesconto,0))",
                    "QtDocumento=COUNT(DISTINCT D.IdDocumento)"
                ],(@$params->fields ? $params->fields : [])),
                "filters" => [
                    [ "D.IdSistema IS NOT NULL" ],
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $params->operations ]
                ],
                "group" => "DIR.IdPessoa" . ( @$params->group ? ",{$params->group}" : "" ),
                "join" => 1
            ]);

            return $data;
        }

        public static function getBilling( $params )
        {
            GLOBAL $dafel, $constants, $business_days, $business_days_exception;

            if( !@$params->people_id ){
                return [];
            }

            $data = Model::getList( $dafel, (Object)[
                "tables" => [
                    "Documento D (NoLock)",
                    "INNER JOIN DocumentoItem DI (NoLock) on ( D.IdDocumento = DI.IdDocumento )",
                    "INNER JOIN DocumentoItemValores DIV (NoLock) on ( DI.IdDocumentoItem = DIV.IdDocumentoItem )",
                    "INNER JOIN DocumentoItemRepasse DIR (NoLock) on ( DI.IdDocumentoItem = DIR.IdDocumentoItem )",
                    "INNER JOIN Pessoa P (NoLock) on( P.IdPessoa = DIR.IdPessoa )"
                ],
                "fields" => [
                    "P.IdPessoa",
                    "P.CdChamada",
                    "P.NmPessoa",
                    "VlVenda = ISNULL(SUM(DI.VlItem),0)+ISNULL(SUM(DIV.VlICMSSubstTributaria),0)",
                    "QtDocumento = COUNT(DISTINCT D.IdDocumento)"
                ],
                "filters" => [
                    [ "P.IdPessoa", "s", "in", $params->people_id ],
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $constants->operations->sale ]
                ],
                "group" => "P.IdPessoa, P.CdChamada, P.NmPessoa",
                "join" => 1
            ]);

            $sellers = [];

            $sales = [];
            foreach( $data as $d ){
                $sales[$d->IdPessoa] = $d;
                $sellers[$d->IdPessoa] = $d;
            }

            $data = Model::getList( $dafel, (Object)[
                "tables" => [
                    "Documento D (NoLock)",
                    "INNER JOIN DocumentoItem DI (NoLock) on ( D.IdDocumento = DI.IdDocumento )",
                    "INNER JOIN DocumentoItemValores DIV (NoLock) on ( DI.IdDocumentoItem = DIV.IdDocumentoItem )",
                    "INNER JOIN DocumentoItemRepasse DIR (NoLock) on ( DI.IdDocumentoItem = DIR.IdDocumentoItem )",
                    "INNER JOIN Pessoa P (NoLock) on( P.IdPessoa = DIR.IdPessoa )"
                ],
                "fields" => [
                    "P.IdPessoa",
                    "P.CdChamada",
                    "P.NmPessoa",
                    "VlVenda = ISNULL(SUM(DI.VlItem),0)+ISNULL(SUM(DIV.VlICMSSubstTributaria),0)",
                    "QtDocumento = COUNT(DISTINCT D.IdDocumento)"
                ],
                "filters" => [
                    [ "P.IdPessoa", "s", "in", $params->people_id ],
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $constants->operations->dev ]
                ],
                "group" => "P.IdPessoa, P.CdChamada, P.NmPessoa",
                "join" => 1
            ]);

            $returns = [];
            foreach( $data as $d ){
                $returns[$d->IdPessoa] = $d;
                $sellers[$d->IdPessoa] = $d;
            }

            $ret = [];
            foreach( $sellers as $key => $seller ){
                if( !@$sales[$key] ) $sales[$key] = (Object)[ "VlVenda" => 0, "QtDocumento" => 0 ];
                if( !@$returns[$key] ) $returns[$key] = (Object)[ "VlVenda" => 0, "QtDocumento" => 0 ];
                if( !@$sales[$key]->VlVenda ) $sales[$key]->VlVenda = 0;
                if( !@$returns[$key]->VlVenda ) $returns[$key]->VlVenda = 0;
                if( !@$sales[$key]->QtDocumento ) $sales[$key]->QtDocumento = 0;
                if( !@$returns[$key]->QtDocumento ) $returns[$key]->QtDocumento = 0;

                $company = $constants->companies[$params->sellers[$seller->IdPessoa]->business_code];

                $date = explode( "-", $params->dtStart);
                $past = 1;
                $year = $date[0];
                $month = $date[1];
                if( @$business_days_exception[$year][$month][$company->id] ){
                    $days = $business_days_exception[$year][$month][$company->id]->count;
                } else {
                    $days = $business_days[$year][$month]->count;
                }
                if( $params->target == "monthly" ){
                    $useless = @$business_days_exception[$year][$month][$company->id] ? $business_days_exception[$year][$month][$company->id]->days : $business_days[$year][$month]->days;
                    for( $d=2; $d<=date("d"); $d++ ){
                        if( !@$useless[$d] ){
                            $past++;
                        }
                    }
                }

                $target_value = $params->sellers[$seller->IdPessoa]->target_val/($params->target == "daily" ? $days : 1);
                $billed = (float)$sales[$key]->VlVenda - (float)$returns[$key]->VlVenda;
                $percent = (100*$billed)/$target_value > 0 ? (100*$billed)/$target_value : 0;
                $ret[] = (Object)[
                    "id" => $seller->IdPessoa,
                    "code" => $seller->CdChamada,
                    "name" => $seller->NmPessoa,
                    "billed" => $billed,
                    "documents" => (int)$sales[$key]->QtDocumento + $returns[$key]->QtDocumento,
                    "company" => $company,
                    "target" => (Object)[
                        "value" => $target_value,
                        "percent" => $percent,
                        "stars" => (int)(($percent-100)/10),
                        "bar" => (Object)[
                            "target" => $target_value >= $billed ? 100 : (100*$target_value)/$billed,
                            "billed" => $target_value < $billed ? 100 : (100*$billed)/$target_value,
                            "average" => (Object)[
                                "percent" => (100*$past*($target_value/$days))/$target_value > 0 ? (100*$past*($target_value/$days))/$target_value : 0,
                                "value" => $past*($target_value/$days)
                            ]
                        ]
                    ]
                ];
            }

            usort( $ret, function( $a, $b ){
                return $a->billed < $b->billed;
            });

            return $ret;
        }
    }

?>