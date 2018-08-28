<?php

    class ReportUser
    {
        public $report_user_id;
        public $user_id;
        public $image_id;
        public $user_name;
        public $report_user_modules;

        public function __construct( $data )
        {
            $this->report_user_id = (int)$data->report_user_id;
            $this->user_id = (int)$data->user_id;
            $this->image_id = @$data->image_id ? (int)$data->image_id : NULL;
            $this->user_name = $data->user_name;
            $this->report_user_modules = explode(":",$data->report_user_modules);
        }
    }

?>