<?php

    include "../config/start.php";

    $path =  PATH_LOG . "error/" . date("Y/F/d/");
    if( !is_dir($path) ) mkdir($path, 0755, true);

    $file = session_id() . date("His");
    file_put_contents( "{$path}{$file}.json" , json_encode($post->params) );
    file_put_contents( "{$path}{$file}.html" , $post->response );

?>