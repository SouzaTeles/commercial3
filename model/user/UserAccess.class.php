<?php

    class UserAccess
    {
        public $user_access_id;
        public $user_access_name;
        public $user_access_value;
        public $user_access_data_type;

        public function __construct($data)
        {
            $this->user_access_id = (int)$data->user_access_id;
            $this->user_access_name = $data->user_access_name;
            $this->user_access_value = $data->user_access_value;
            $this->user_access_data_type = $data->user_access_data_type;
        }

        public static function treeAccess($l_access)
        {
            $user_access = json_decode( file_get_contents( PATH_DATA . "user.json" ));

            foreach ($l_access as $access) {
                $name = "{$access->user_access_name}";
                if (!@$user_access->$name){
                    echo "Chave do banco de dados não encontrada: {$name}";
                    var_dump($user_access);
                    die();
                }
                if (!@$user_access->$name) {
                    $user_access->$name = new StdClass();
                }
                if( $access->user_access_data_type == "float" ){
                    $access->user_access_value = (float)$access->user_access_value;
                }
                if( $access->user_access_data_type == "int" ){
                    $access->user_access_value = (int)$access->user_access_value;
                }
                $user_access->$name = $access->user_access_value;
            }

            return $user_access;
        }
    }

?>