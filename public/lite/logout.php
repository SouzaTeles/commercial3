<?php

	include "../../config/start.php";

	GLOBAL $headerStatus;

    if( Session::isUser() )
    {
        Session::reset();
        Json::get( $headerStatus[200] );

    } else {
        $headerStatus[404]->description = "Ssssão não encontrada.";
        Json::get( $headerStatus[404] );
    }

?>