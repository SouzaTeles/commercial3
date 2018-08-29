<?php

    class PersonAddressContact
    {
        public $address_contact_type_id;
        public $address_contact_name;
        public $address_code;
        public $address_contact_main;
        public $address_contact_label;
        public $address_contact_value;
        public $address_contact_note;
    
        public function __construct($data)
        {
            $this->address_contact_type_id = $data->IdTipoContato;
            $this->address_contact_name = @$data->PessoaContato ? $data->PessoaContato : NULL;
            $this->address_contact_main = @$data->StContatoPrincipal && $data->StContatoPrincipal == "S" ? "Y" : "N";
            $this->address_code = $data->CdEndereco;
            $this->address_contact_label = $data->NmTipoContato;
            $this->address_contact_value = @$data->DsContato ? $data->DsContato : NULL;
            $this->address_contact_note = @$data->DsObservacao ? $data->DsObservacao : NULL;
        }
    }

?>