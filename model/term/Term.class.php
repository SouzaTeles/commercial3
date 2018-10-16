<?php

    class Term
    {
        public $term_id;
        public $term_active;
        public $term_code;
        public $term_description;
        public $term_installment;
        public $term_delay;
        public $term_interval;
        public $term_update;
        public $term_date;

        public function __construct( $data, $gets=[] )
        {
            $this->term_id = $data->IdPrazo;
            $this->term_active = $data->StAtivo == "S" ? "Y" : "N";
            $this->term_code = $data->CdChamada;
            $this->term_description = $data->DsPrazo;
            $this->term_installment = (int)$data->NrParcelas;
            $this->term_delay = @$data->NrDias1aParcela ? (int)$data->NrDias1aParcela : ( @$data->NrDiasEntrada ? (int)$data->NrDiasEntrada : 0);
            $this->term_interval = (int)$data->NrDiasEntreParcelas;

            GLOBAL $dafel, $post, $config;

//            if( @$gets["get_entry_modalities"] || @$_POST["get_entry_modalities"] ){
//                $this->entry_modalities = [];
//                $entry_modalities = Model::getList($dafel,(Object)[
//                    "tables" => [ "modality m", "term_modality tm" ],
//                    "fields" => [ "m.modality_id" ],
//                    "filters" => [
//                        [ "tm.modality_id = m.modality_id" ],
//                        [ "tm.term_id", "i", "=", $data->term_id ],
//                        [ "tm.term_modality_type", "s", "=", "E" ],
//                        [ "m.modality_active", "s", "=", "Y" ],
//                        [ "m.modality_trash", "s", "=", "N" ]
//                    ]
//                ]);
//                foreach( $entry_modalities as $modality ){
//                    $this->entry_modalities[] = $modality->modality_id;
//                }
//            }
//
//            if( @$gets["get_parcel_modalities"] || @$_POST["get_parcel_modalities"] ){
//                $this->parcel_modalities = [];
//                $parcel_modalities = Model::getList($dafel,(Object)[
//                    "tables" => [ "modality m", "term_modality tm" ],
//                    "fields" => [ "m.modality_id" ],
//                    "filters" => [
//                        [ "tm.modality_id = m.modality_id" ],
//                        [ "tm.term_id", "i", "=", $data->term_id ],
//                        [ "tm.term_modality_type", "s", "=", "P" ],
//                        [ "m.modality_active", "s", "=", "Y" ],
//                        [ "m.modality_trash", "s", "=", "N" ]
//                    ]
//                ]);
//                foreach( $parcel_modalities as $modality ){
//                    $this->parcel_modalities[] = $modality->modality_id;
//                }
//            }

            if( @$gets["get_term_modalities"] || @$_POST["get_term_modalities"] ){
                $modalities = Model::getList($dafel,(Object)[
                    "join" => 1,
                    "tables" => [
                        "FormaPagamento FP (NoLock)",
                        "INNER JOIN Prazo_FormaPagamento PFP (NoLock) ON (PFP.IdFormaPagamento = FP.IdFormaPagamento)",
                        "LEFT JOIN FormaPagamentoItem FPI ON (FPI.IdFormaPagamento = FP.IdFormaPagamento AND FPI.CdEmpresa = $post->company_id)"
                    ],
                    "fields" => [
                        "modality_id=FP.IdFormaPagamento",
                        "nature_id=FP.IdNaturezaLancamento",
                        "modality_code=FP.CdChamada",
                        "modality_type=FP.TpFormaPagamento",
                        "modality_description=FP.DsFormaPagamento",
                        "term_modality_type=PFP.TpRegistro",
                        "modality_installment=COUNT(FPI.IdFormaPagamentoItem)"
                    ],
                    "filters" => [
                        [ "FP.StAtivo", "s", "=", "S" ],
                        [ "PFP.IdPrazo", "s", "=", $data->IdPrazo ],
                        [ "FP.IdFormaPagamento", "s", "in", $config->budget->authorized_modality_id ],
                    ],
                    "group" => "FP.IdFormaPagamento,FP.IdNaturezaLancamento,FP.CdChamada,FP.TpFormaPagamento,FP.DsFormaPagamento,PFP.TpRegistro"
                ]);
                foreach( $modalities as $modality ){
                    $modality->modality_entry = ( $modality->term_modality_type == "E" ? "Y" : "N" );
                    $modality->modality_delay = ( $modality->modality_type == "A" ? 30 : 1 );
                    $modality->modality_installment = @(int)$modality->modality_installment ? (int)$modality->modality_installment : ( $modality->modality_type != "A" ? 1 : 0 );
                    $modality->image = getImage((Object)[
                        "image_id" => $modality->modality_id,
                        "image_dir" => "modality"
                    ]);
                    if( !@$modality->image ){
                        $modality->image = getImage((Object)[
                            "image_id" => $modality->modality_type,
                            "image_dir" => "modality/type"
                        ]);
                    }
                }
                $this->modalities = $modalities;
            }

            if( @$gets["get_term_modality_link"] || @$_POST["get_term_modality_link"] ){
                $this->term_modality_link = Model::getList($dafel,(Object)[
                    "tables" => [ "term_modality" ],
                    "filters" => [[ "term_id", "i", "=", $data->term_id ]]
                ]);
            }
        }
    }

?>