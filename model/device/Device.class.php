<?php

    class Device
    {
        public $device_id;
        public $device_active;
        public $user_id;
        public $device_guid;
        public $device_device;
        public $device_model;
        public $device_brand;
        public $device_update;
        public $device_date;

        public function __construct($data, $params)
        {
            $this->device_id = (int)$data->device_id;
            $this->device_active = $data->device_active;
            $this->user_id = $data->user_id;
            $this->device_guid = $data->device_guid;
            $this->device_device = $data->device_device;
            $this->device_model = $data->device_model;
            $this->device_active = $data->device_active;
            $this->device_device = $data->device_device;
            $this->device_brand = $data->device_brand;
            $this->device_update = @$data->device_update ? $data->device_update : NULL;
            $this->device_date = $data->device_date;
        }
    }

?>