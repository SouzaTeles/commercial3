<?php

    class Unit
    {
        public $unit_id;
        public $unit_code;
        public $unit_name;
        public $unit_type;

        public function __construct( $data )
        {
            $this->unit_id = $data->unit_id;
            $this->unit_code = $data->unit_code;
            $this->unit_name = $data->unit_name;
            $this->unit_type = $data->unit_type;
        }
    }

?>