<?php

    class ContaBancaria
    {
        public $IdContaBancaria;
        public $CdChamada;
        public $CdEmpresa;
        public $NrConta;
        public $DsContaBancaria;

        public function __construct( $data )
        {
            $this->IdContaBancaria = $data->IdContaBancaria;
            $this->CdChamada = $data->CdChamada;
            $this->CdEmpresa = $data->CdEmpresa;
            $this->NrConta = $data->NrConta;
            $this->DsContaBancaria = $data->DsContaBancaria;
        }

    }

?>