<?php

    class Image
    {
        public $image_id;
        public $image_name;
        public $image_description;
        public $image_main;
        public $image_order;
        public $image_update;
        public $image_date;
    
        public function __construct( $data )
        {
            $this->image_id = (int)$data->image_id;
            $this->image_main = $data->image_main;
            $this->image_name = @$data->image_name ? $data->image_name : NULL;
            $this->image_description = @$data->image_description ? $data->image_description : NULL;
            $this->image_update = @$data->image_update ? $data->image_update : NULL;
            $this->image_date = $data->image_date;
            $this->image_uri = URI_PUBLIC . "files/{$data->image_section}/" . ( @$data->parent_id && !in_array($data->image_section,["plan","product_category","user"]) ? "{$data->parent_id}/" : "" ). "{$data->image_id}_";
            $this->image_section = $data->image_section;
        }
    }

?>