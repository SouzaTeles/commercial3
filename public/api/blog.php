<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Nenhuma ação informada na requisição."
        ]);
    }

    switch( $get->action )
    {
        case "get":

            $url = "http://intranet.dafel.com.br/blog/commercial.php?action=get&token=r0zUBn6o7tbggzZQXCusGT2DUPJ4wHF3&post_id={$post->post_id}";
            $post = file_get_contents($url);
            $post = json_decode($post, TRUE);

            Json::get( $headerStatus[200], $post );

        break;
    }

?>