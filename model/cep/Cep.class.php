<?php

    class CEP
    {
        public $cep_code;
        public $uf_id;
        public $city_id;
        public $district_id;
        public $uf_name;
        public $city_name;
        public $district_name;
        public $public_place;
        public $public_place_type;

        public function __construct( $data, $gets )
        {
            $this->cep_code = $data->CdCEP;
            $this->uf_id = $data->IdUF;
            $this->city_id = $data->IdCidade;
            $this->district_id = $data->IdBairro;
            $this->uf_name = $data->NmUF;
            $this->city_name = $data->NmCidade;
            $this->district_name = $data->NmBairro;
            $this->public_place = $data->NmLogradouro;
            $this->public_place_type = $data->TpLogradouro;
        }
    }

?>