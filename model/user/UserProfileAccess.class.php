<?php

    class UserProfileAccess
    {
        public $user_profile_access_id;
        public $user_profile_access_module;
        public $user_profile_access_name;
        public $user_profile_access_value;
        public $user_profile_access_data_type;
        public $user_profile_access_date;

        public function __construct($data)
        {
            $this->user_profile_access_id = (int)$data->user_profile_access_id;
            $this->user_profile_access_module = $data->user_profile_access_module;
            $this->user_profile_access_name = $data->user_profile_access_name;
            $this->user_profile_access_value = $data->user_profile_access_value;
            $this->user_profile_access_data_type = $data->user_profile_access_data_type;
            $this->user_profile_access_date = $data->user_profile_access_date;
        }

        public static function treeAccess($l_access)
        {
            $user_access = json_decode( file_get_contents( PATH_DATA . "access.json" ));

            foreach ($l_access as $access) {
                $module = "{$access->user_profile_access_module}";
                $object = "{$access->user_profile_access_name}";
                if (!@$user_access->$module){
                    echo "Chave do banco de dados não encontrada: {$module}";
                    die(var_dump($user_access));
                }
                if (!@$user_access->$module->$object) {
                    $user_access->$module->$object = new StdClass();
                }
                $user_access->$module->$object->value = $access->user_profile_access_value;
                $user_access->$module->$object->data_type = $access->user_profile_access_data_type;
            }

            return $user_access;
        }

        public static function insert($user_profile_id)
        {
            GLOBAL $commercial, $post;

            foreach( $post->user_profile_access as $module => $access ){
                foreach( $access as $name => $data ){
                    if( is_array($data) ){
                        $data = (Object)$data;
                        if( $data->data_type != "bool" || ( $data->data_type == "bool" && $data->value == "Y" )) {
                            Model::insert($commercial, (Object)[
                                "table" => "user_profile_access",
                                "fields" => [
                                    ["user_profile_id", "i", $user_profile_id],
                                    ["user_profile_access_module", "s", $module],
                                    ["user_profile_access_name", "s", $name],
                                    ["user_profile_access_value", "s", $data->value],
                                    ["user_profile_access_data_type", "s", $data->data_type]
                                ]
                            ]);
                        }
                    }
                }
            }
        }
    }

?>