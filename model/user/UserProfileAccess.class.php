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
            $profile_access = json_decode( file_get_contents( PATH_DATA . "profile.json" ));

            foreach ($l_access as $access) {
                $module = "{$access->user_profile_access_module}";
                $name = "{$access->user_profile_access_name}";
                if (!@$profile_access->$module){
                    echo "Chave do banco de dados não encontrada: {$module}";
                    var_dump($profile_access);
                    die();
                }
                $profile_access->$module->$name = new StdClass();
                $type = $access->user_profile_access_data_type;
                $value = $access->user_profile_access_value;
                if( $type == "float" ){
                    $value = (float)$value;
                }
                if( $type == "int" ){
                    $value = (int)$value;
                }
                $profile_access->$module->$name->value = $value;
                unset($profile_access->$module->name);
            }

            return $profile_access;
        }
    }

?>