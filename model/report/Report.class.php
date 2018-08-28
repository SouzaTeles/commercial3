<?php

    class Report
    {
        public $report_id;
        public $report_active;
        public $report_name;
        public $report_description;
        public $report_period;
        public $report_days;
        public $report_time;
        public $report_update;
        public $report_date;

        public function __construct( $data )
        {
            $this->report_id = (int)$data->report_id;
            $this->report_active = $data->report_active;
            $this->report_name = $data->report_name;
            $this->report_description = @$data->report_description ? $data->report_description : NULL;
            $this->report_period = $data->report_period;
            $this->report_days = @$data->report_days ? explode(":",$data->report_days) : [];
            $this->report_time = $data->report_time;
            $this->report_update = $data->report_update;
            $this->report_date = $data->report_date;

            GLOBAL $commercial;

            $this->users = Model::getList($commercial,(Object)[
                "join" => 1,
                "class" => "ReportUser",
                "tables" => [
                    "report_user ru",
                    "inner join user u on(ru.user_id = u.user_id)",
                    "left join image i on(i.parent_id = u.user_id and i.image_section = 'user')"
                ],
                "fields" => [
                    "ru.report_user_id",
                    "u.user_id",
                    "i.image_id",
                    "u.user_name",
                    "ru.report_user_modules"
                ],
                "filters" => [
                    [ "report_id", "i", "=", $data->report_id ]
                ]
            ]);
        }
    }

?>