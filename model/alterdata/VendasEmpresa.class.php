<?php

    class VendasEmpresa
    {
        public static function getBill( $params )
        {
            GLOBAL $dafel;

            $data = Model::getList( $dafel, (Object)[
                "tables" => [
                    "Documento D(NOLOCK)",
                    "INNER JOIN DocumentoItem DI(NOLOCK) ON (D.IdDocumento = DI.IdDocumento)",
                    "INNER JOIN DocumentoItemValores DIV(NOLOCK) ON (DI.IdDocumentoItem = DIV.IdDocumentoItem)",
                    "INNER JOIN LoteEstoque LE(NOLOCK) ON (D.IdLoteEstoque = LE.IdLoteEstoque)",
                ],
                "fields" => array_merge([
                    "LE.CdEmpresa",
                    "VlVenda=SUM(ISNULL(DI.VlItem,0)+ISNULL(D.VlAcrescimo,0)-ISNULL(D.VlDesconto,0))",
                    "QtDocumento=COUNT(DISTINCT D.IdDocumento)"
                ],(@$params->fields ? $params->fields : [])),
                "filters" => [
                    [ "D.IdSistema IS NOT NULL" ],
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $params->operations ]
                ],
                "group" => "LE.CdEmpresa" . ( @$params->group ? ",{$params->group}" : "" ),
                "join" => 1
            ]);

            return $data;
        }

        public static function getBilling( $params )
        {
            GLOBAL $dafel, $constants, $business_days, $business_days_exception;

            $data = Model::getList( $dafel, (Object)[
                "tables" => [
                    "Operacao OP(NOLOCK) INNER JOIN",
                    "Documento D(NOLOCK) ON (OP.IdOperacao = D.IdOperacao) INNER JOIN",
                    "DocumentoItem DI(NOLOCK) ON (D.IdDocumento = DI.IdDocumento) INNER JOIN",
                    "DocumentoItemValores DIV(NOLOCK) ON (DI.IdDocumentoItem = DIV.IdDocumentoItem) INNER JOIN",
                    "LoteEstoque LE(NOLOCK) ON (D.IdLoteEstoque = LE.IdLoteEstoque)",
                ],
                "fields" => [
                    "LE.CdEmpresa",
                    "VlVenda = ISNULL(ROUND(SUM((DI.VlItem*((100-ISNULL(D.AlDesconto,0))/100)+ISNULL(DIV.VlICMSSubstTributaria,0))),2),0)",
                    "QtDocumento = COUNT(DISTINCT D.IdDocumento)"
                ],
                "filters" => [
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $constants->operations->sale ]
                ],
                "group" => "LE.CdEmpresa",
                "join" => 1
            ]);

            $sales = [];
            foreach( $data as $d ){
                $sales[$d->CdEmpresa] = $d;
            }

            $data = Model::getList( $dafel, (Object)[
                "tables" => [
                    "Operacao OP(NOLOCK) INNER JOIN",
                    "Documento D(NOLOCK) ON (OP.IdOperacao = D.IdOperacao) INNER JOIN",
                    "DocumentoItem DI(NOLOCK) ON (D.IdDocumento = DI.IdDocumento) INNER JOIN",
                    "DocumentoItemValores DIV(NOLOCK) ON (DI.IdDocumentoItem = DIV.IdDocumentoItem) INNER JOIN",
                    "LoteEstoque LE(NOLOCK) ON (D.IdLoteEstoque = LE.IdLoteEstoque)",
                ],
                "fields" => [
                    "LE.CdEmpresa",
                    "VlVenda = ISNULL(ROUND(SUM((DI.VlItem*((100-ISNULL(D.AlDesconto,0))/100)+ISNULL(DIV.VlICMSSubstTributaria,0))),2),0)",
                    "QtDocumento = COUNT(DISTINCT D.IdDocumento)"
                ],
                "filters" => [
                    [ "D.StDocumentoCancelado", "s", "=", "N" ],
                    [ "D.DtEmissao", "s", "between", [ $params->dtStart, $params->dtEnd ] ],
                    [ "D.IdOperacao", "s", "in", $constants->operations->dev ]
                ],
                "group" => "LE.CdEmpresa",
                "join" => 1
            ]);

            $returns = [];
            foreach( $data as $d ){
                $returns[$d->CdEmpresa] = $d;
            }

            if( @$sales[30] ){
                if( !@$sales[3] ) $sales[3] = (Object)[ "VlVenda" => 0, "QtDocumento" => 0 ];
                $sales[3]->VlVenda += $sales[30]->VlVenda;
                $sales[3]->QtDocumento += $sales[30]->QtDocumento;
                unset($sales[30]);
            }
            if( @$returns[30] ){
                if( !@$returns[3] ) $returns[3] = (Object)[ "VlVenda" => 0, "QtDocumento" => 0 ];
                $returns[3]->VlVenda += $returns[30]->VlVenda;
                $returns[3]->QtDocumento += $returns[30]->QtDocumento;
                unset($returns[30]);
            }

            $ret = [];
            unset($constants->companies[30]);
            foreach( $constants->companies as $company ){
                if( !@$sales[$company->id] ) $sales[$company->id] = (Object)[ "VlVenda" => 0, "QtDocumento" => 0 ];
                if( !@$returns[$company->id] ) $returns[$company->id] = (Object)[ "VlVenda" => 0, "QtDocumento" => 0 ];
                if( !@$sales[$company->id]->VlVenda ) $sales[$company->id]->VlVenda = 0;
                if( !@$returns[$company->id]->VlVenda ) $returns[$company->id]->VlVenda = 0;
                if( !@$sales[$company->id]->QtDocumento ) $sales[$company->id]->QtDocumento = 0;
                if( !@$returns[$company->id]->QtDocumento ) $returns[$company->id]->QtDocumento = 0;

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

                $billed = (float)$sales[$company->id]->VlVenda - (float)$returns[$company->id]->VlVenda;
                $target_value = @$params->companies[$company->id]->target_val ? $params->companies[$company->id]->target_val : 0;
                $percent = 0;
                if( @$target_value ){
                    $target_value = $target_value / ($params->target == "daily" ? $days : 1);
                    $percent = (100 * $billed) / $target_value > 0 ? (100 * $billed) / $target_value : 0;
                }

                $ret[] = (Object)[
                    "code" => $company->code,
                    "name" => $company->name,
                    "color" => $company->color,
                    "billed" => $billed,
                    "documents" => (int)$sales[$company->id]->QtDocumento + $returns[$company->id]->QtDocumento,
                    "target" => (Object)[
                        "percent" => $percent,
                        "value" => $target_value,
                        "stars" => (int)(($percent-100)/10),
                        "bar" => (Object)[
                            "target" => $target_value >= $billed ? 100 : (100*$target_value)/$billed,
                            "billed" => $target_value < $billed ? 100 : (100*$billed)/$target_value,
                            "average" => (Object)[
                                "percent" => (100*$past*($target_value/$days))/(@$target_value ? $target_value : 1) > 0 ? (100*$past*($target_value/$days))/(@$target_value ? $target_value : 1) : 0,
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