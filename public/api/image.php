<?php

    include "../../config/start.php";
    ini_set('memory_limit', -1);

    Session::checkApi();

    GLOBAL $commercial, $site, $login, $dimensions, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não informado."
        ]);
    }

    if( in_array($get->action,["up"]) ){
        postLog();
    }

    switch( $get->action ) {

        case "up":

            if (!@$post->image_id || !@$post->image_dir) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            if (!@$_FILES['file']) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro FILE não informado."
                ]);
            }

            if (sizeof($_FILES['file']['tmp_name']) > 5) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "O Arquivo de imagem ultrapassa o limite de 5Mb."
                ]);
            }

            $images = [];
            $success = 0;
            $quantity = sizeof($_FILES['file']['tmp_name']);
            $path = "files/{$post->image_dir}/";
            $rand = rand(1000,9999);

            foreach ($_FILES['file']['tmp_name'] as $key => $tmp) {

                $name = explode(".", $_FILES['file']['name'][$key]);
                $file = "{$post->image_id}." . end($name);
                if (File::upload($tmp, PATH_PUBLIC . "{$path}{$file}")) {
                    $success++;
                    $images[] = (Object)[
                        "image" => URI_PUBLIC . "{$path}{$file}?{$rand}"
                    ];
                }
            }

            if (!$success) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Falha ao processar a imagem."
                ]);
            }

            Json::get($headerStatus[200], (Object)[
                "message" => "{$success} de {$quantity} imagem(ns) adicionada(s).",
                "success" => $success,
                "images" => $images
            ]);

        break;

        case "del":

            if (!@$post->image_id || !@$post->image_dir) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $path = PATH_FILES . "{$post->image_dir}/{$post->image_id}";
            if (file_exists("{$path}.jpg")) unlink("{$path}.jpg");
            if (file_exists("{$path}.jpeg")) unlink("{$path}.jpeg");
            if (file_exists("{$path}.png")) unlink("{$path}.png");

            Json::get( $headerStatus[200], (Object)[
                "message" => "Imagem removida com sucesso."
            ]);

        break;

    }

?>