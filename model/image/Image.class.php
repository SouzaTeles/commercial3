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
        public $post_id;
        public $person_id;
        public $image_start_date;
        public $image_end_date;
        public $image_active;

        public function __construct( $data )
        {
            $this->image_id = (int)$data->image_id;
            $this->image_main = $data->image_main;
            $this->image_name = @$data->image_name ? $data->image_name : NULL;
            $this->image_description = @$data->image_description ? $data->image_description : NULL;
            $this->image_update = @$data->image_update ? $data->image_update : NULL;
            $this->image_date = $data->image_date;
            $this->image_uri = URI_PUBLIC . "files/{$data->image_section}/" . ( @$data->parent_id && !in_array($data->image_section,["plan","product_category","user"]) ? "{$data->parent_id}/" : "" ). "{$data->image_id}_";
            $this->post_id = @$data->post_id ? $data->post_id : NULL;
            $this->post_id = @$data->post_id ? $data->post_id : NULL;
            $this->image_start_date = @$data->image_start_date ? $data->image_start_date : NULL;
            $this->image_end_date = @$data->image_end_date ? $data->image_end_date : NULL;
            $this->image_active = $data->image_active;
        }
    }

?>