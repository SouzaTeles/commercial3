<?php

	class PersonCreditPawn
	{
		public $user_name;
		public $system_name;
		public $description;

		public function __construct( $data )
		{
		    $this->user_name = $data->NmUsuario;
			$this->system_name = $data->NmSistema;
            $this->description = $data->Descricao;
		}
	}

?>