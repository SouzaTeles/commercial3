<?php

    class StatementStatus
    {
        public $statement_status_id;
        public $statement_status_code;
        public $statement_status_name;
        public $statement_status_min;
        public $statement_status_max;
        public $statement_status_color;
        public $statement_status_period;
        public $statement_status_update;
        public $statement_status_date;

        public function __construct($data)
        {
            $this->statement_status_id = $data->statement_status_id;
            $this->statement_status_code = substr("00000{$data->statement_status_id}",-6);
            $this->statement_status_name = $data->statement_status_name;
            $this->statement_status_min = (int)$data->statement_status_min;
            $this->statement_status_max = (int)$data->statement_status_max;
            $this->statement_status_color = $data->statement_status_color;
            $this->statement_status_period = [ (int)$data->statement_status_min, (int)$data->statement_status_max ];
            $this->statement_status_update = @$data->statement_status_update ? $data->statement_status_update : NULL;
            $this->statement_status_date = $data->statement_status_date;
        }
    }

?>