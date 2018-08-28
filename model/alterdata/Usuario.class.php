<?php

    class Usuario
    {
        public $IdUsuario;
        public $NmLogin;
        public $NmUsuario;

        public function __construct( $data )
        {
            $this->IdUsuario = $data->IdUsuario;
            $this->NmLogin = $data->NmLogin;
            $this->NmUsuario = $data->NmUsuario;
        }
    }

?>