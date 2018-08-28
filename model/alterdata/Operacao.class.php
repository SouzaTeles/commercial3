<?php

    class Operacao
    {
        public $IdOperacao;
        public $CdChamada;
        public $NmOperacao;
        public $TpOperacao;

        public function __construct( $data )
        {
            $this->IdOperacao = $data->IdOperacao;
            $this->CdChamada = $data->CdChamada;
            $this->NmOperacao = $data->NmOperacao;
            $this->TpOperacao = $data->TpOperacao;
        }
    }

?>