<?php

    class PersonAttribute
    {
        public $attribute_id;
        public $attribute_code;
        public $attribute_classification;
        public $attribute_type;
        public $attribute_name;
        public $attribute_description;
        public $attribute_date;
        public $image;

        public function __construct( $data )
        {
            $this->id = $data->IdPessoa;
            $this->attribute_id = $data->IdCaracteristicaPessoa;
            $this->attribute_code = $data->CdChamada;
            $this->attribute_classification = $data->CdClassificacao;
            $this->attribute_type = $data->TpCaracteristica;
            $this->attribute_name = $data->NmCaracteristicaPessoa;
            $this->attribute_description = @$data->DsObservacao ? $data->DsObservacao : NULL;
            $this->attribute_date = @$data->DtCaracteristica ? $data->DtCaracteristica : NULL;

            $this->image = getImage((Object)[
                "image_id" => $data->IdCaracteristicaPessoa,
                "image_dir" => "attributes"
            ]);
        }
    }

?>