<?php

    class Config
    {
        public static function getList()
        {
            GLOBAL $commercial;

            $configs = Model::getList($commercial,(Object)[
                "tables" => [ "Config" ],
                "fields" => [
                    "config_id",
                    "config_category",
                    "config_name",
                    "config_value"
                ]
            ]);

            $data = [];
            foreach( $configs as $config ){
                if(
                    ($config->config_category == "person" && $config->config_name == "attributes") ||
                    ($config->config_category == "bank" && $config->config_name == "authorized" )
                ){
                    $config->config_value = explode(":", $config->config_value);
                }
                $data[$config->config_category][$config->config_name] = $config->config_value;
            }

            $data = (Object)$data;
            foreach( $data as $key => $d ){
                $data->$key = (Object)$d;
            }

            $data->uri = (Object)[
                "uri" => URI,
                "uri_lib" => URI_LIB,
                "uri_public" => URI_PUBLIC,
                "uri_public_api" => URI_PUBLIC_API
            ];

            return $data;
        }

    }

?>