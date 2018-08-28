<?php

    class Modality
    {
        public $modality_id;
        public $nature_id;
        public $modality_code;
        public $modality_description;
        public $modality_type;
        public $modality_delay;
        public $modality_entry;
        public $modality_installment;

        public function __construct( $data )
        {
            $this->modality_id = $data->IdFormaPagamento;
            $this->nature_id = $data->IdNaturezaLancamento;
            $this->modality_code = $data->CdChamada;
            $this->modality_description = $data->DsFormaPagamento;
            $this->modality_type = $data->TpFormaPagamento;
            $this->modality_delay = ( $data->TpFormaPagamento == "A" ? 30 : 1 );
            $this->modality_entry = ( $data->TpFormaPagamento == "D" ? "Y" : "N" );
            $this->modality_installment = @(int)$data->Parcelas ? (int)$data->Parcelas : ( $data->TpFormaPagamento != "A" ? 1 : 0 );
        }

    }

?>