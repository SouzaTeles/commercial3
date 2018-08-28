<?php

    class Bank
    {
        public $bank_id;
        public $bank_name;
        public $agencies;

        public function __construct( $data )
        {
            $this->bank_id = $data->IdBanco;
            $this->bank_name = $data->NmBanco;

            GLOBAL $dafel;

            $this->agencies = Model::getList($dafel,(Object)[
                "tables" => [ "Agencia (NoLock)" ],
                "fields" => [
                    "agency_id=IdAgencia",
                    "agency_code=CdChamada",
                    "agency_number=NrAgencia",
                    "agency_name=NmAgencia"
                ],
                "filters" => [[ "IdBanco", "s", "=", $data->IdBanco ]]
            ]);
        }

    }

?>