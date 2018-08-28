<?php

    class Unidade
    {
        public $IdUnidade;
        public $CdChamada;
        public $CdSigla;
        public $NmUnidade;
        public $TpUnidade;

        public function __construct( $data )
        {
            $this->IdUnidade = $data->IdUnidade;
            $this->CdChamada = @$data->CdChamada ? $data->CdChamada : NULL;
            $this->CdSigla = $data->CdSigla;
            $this->NmUnidade = @$data->NmUnidade ? $data->NmUnidade : NULL;
            $this->TpUnidade = $data->TpUnidade;
        }
    }

?>