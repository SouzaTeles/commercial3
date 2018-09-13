<?php

    class PersonAddress
    {
        public $uf_id;
        public $city_id;
        public $district_id;
        public $address_cep;
        public $address_code;
        public $address_active;
        public $address_main;
        public $address_delivery;
        public $address_ie;
        public $address_type;
        public $address_public_place;
        public $address_number;
        public $address_reference;
        public $address_note;
        public $address_icms_type;
        public $city_name;
        public $district_name;

        public function __construct($data, $params)
        {
            $this->uf_id = @$data->IdUF ? $data->IdUF : NULL;
            $this->city_id = @$data->IdCidade ? $data->IdCidade : NULL;
            $this->district_id = @$data->IdBairro ? $data->IdBairro : NULL;
            $this->city_name = $data->NmCidade;
            $this->district_name = $data->NmBairro;
            $this->address_cep = $data->CdCEP;
            $this->address_code = @$data->CdEndereco ? $data->CdEndereco : NULL;
            $this->address_active = $data->StATivo == "S" ? "Y" : "N";
            $this->address_main = $data->StEnderecoPrincipal == "S" ? "Y" : "N";
            $this->address_delivery = @$data->StEnderecoEntrega ? ($data->StEnderecoEntrega == "S" ? "Y" : "N") : NULL;
            $this->address_ie = @$data->NrInscricaoEstadual ? $data->NrInscricaoEstadual : ( @$data->TpContribuicaoICMS && $data->TpContribuicaoICMS == 2 ? "ISENTO" : NULL );
            $this->address_type = $data->TpLogradouro;
            $this->address_public_place = $data->NmLogradouro;
            $this->address_number = $data->NrLogradouro;
            $this->address_reference = @$data->DsComplemento ? $data->DsComplemento : NULL;
            $this->address_note = @$data->DsObservacao ? $data->DsObservacao : NULL;
            $this->address_icms_type = @$data->TpContribuicaoICMS ? $data->TpContribuicaoICMS : NULL;

            GLOBAL $dafel;

            if( @$params["get_address_contact"] || @$_POST["get_address_contact"] )
            {
                $this->contacts = Model::getList($dafel,(Object)[
                    "top" => 1,
                    "class" => "PersonAddressContact",
                    "tables" => [
                        "PessoaEndereco_Contato PEC (NoLock)",
                        "PessoaEndereco_TipoContato PETC (NoLock)",
                        "TipoContato TP (NoLock)"
                    ],
                    "fields" => [
                        "PETC.IdPessoa",
                        "TP.IdTipoContato",
                        "PessoaContato = PEC.DsContato",
                        "PEC.StContatoPrincipal",
                        "PETC.CdEndereco",
                        "TP.NmTipoContato",
                        "PETC.DsContato",
                        "PETC.DsObservacao"
                    ],
                    "filters" => [
                        [ "PEC.IdPessoaEndereco_Contato = PETC.IdPessoaEndereco_Contato" ],
                        [ "PETC.IdTipoContato = TP.IdTipoContato" ],
                        [ "PETC.IdPessoa", "s", "=", $data->IdPessoa ],
                        [ "PETC.CdEndereco", "s", "=", $data->CdEndereco ]
                    ]
                ]);
            }
        }
    }

?>