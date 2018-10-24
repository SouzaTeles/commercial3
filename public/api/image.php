<?php

    include "../../config/start.php";
    ini_set('memory_limit', -1);

    Session::checkApi();

    GLOBAL $conn, $commercial, $site, $login, $dimensions, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não informado."
        ]);
    }

    $maxWidthThumb = WIDTH_THUMB_MAX;
    $maxHeightThumb = HEIGHT_THUMB_MAX;

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
            $rand = rand(1000,9999);

            foreach ($_FILES['file']['tmp_name'] as $key => $tmp) {

                $name = explode(".", $_FILES['file']['name'][$key]);
                $file = "{$post->image_id}." . end($name);
                if( File::upload( $tmp, PATH_FILES . "{$post->image_dir}/{$file}")) {
                    $success++;
                    $images[] = (Object)[
                        "image" => URI_PUBLIC . "files/{$post->image_dir}/{$file}?{$rand}"
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

        case "add":

            if( !@$post->image_section ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Imagem não suportada.<br/>Verifique o tamanho do arquivo.<br/><br/>Dimensões máximas: {$maxWidthThumb}px por {$maxHeightThumb}px."
                ]);
            }

            if( !@$_FILES['file'] ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro FILE não informado."
                ]);
            }

            $images = [];
            $success = 0;
            $quantity = sizeof($_FILES['file']['tmp_name']);
            $parent_id = @$post->parent_id ? $post->parent_id : NULL;
            $path = "files/{$post->image_section}/" . ( @$parent_id && !@$post->image_root ? "{$parent_id}/" : "" );

            foreach( $_FILES['file']['tmp_name'] as $key => $file ){
                if( in_array($_FILES['file']['type'][$key],["image/jpeg","image/jpg","image/png"]) ) {
                    $image_id = (int)Model::insert( $commercial, (Object)[
                        "table" => "Image",
                        "fields" => [
                            [ "parent_id", "i", $parent_id ],
                            [ "image_section", "s", $post->image_section ],
                            [ "image_main", "s", "N" ],
                            [ "image_order", "s", "0" ],
                            [ "image_date", "s", date("Y-m-d H:i:s") ]
                        ]
                    ]);
                    if( File::processImage((Object)[
                        "path" => PATH_PUBLIC . $path,
                        "file" => $file,
                        "image_id" => $image_id,
                        "dimensions" => $dimensions[$post->image_section],
                        "extension" => $_FILES['file']['type'][$key],
                        "stretch" => @$post->image_stretch,
                        "in" => @$post->image_in,
                        "quality" => @$post->quality ? $post->quality : NULL
                    ])){
                        $success++;
                        $rand = rand(1000,9999);
                        $images[] = (Object)[
                            "image_id" => $image_id,
                            "image_main" => "N",
                            "image_large" => URI_PUBLIC . "{$path}{$image_id}_large.jpg?{$rand}",
                            "image_small" => URI_PUBLIC . "{$path}{$image_id}_small.jpg?{$rand}"
                        ];
                    } else {
                        Model::delete($commercial, (Object)[
                            "table" => "Image",
                            "filters" => [["image_id", "i", "=", $image_id]]
                        ]);
                    }
                }
            }

            if( !$success ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Falha ao processar as imagens.<br/>Verifique o tamanho e formato dos arquivos.<br/><br/>Dimensões máximas: {$maxWidthThumb}px por {$maxHeightThumb}px."
                ]);
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "{$success} de {$quantity} Imagens adicionadas.",
                "success" => $success,
                "images" => $images
            ]);

        break;

        case "del":

            if( !@$post->image_id || !@$post->image_dir ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $image = Model::get( $commercial,(Object)[
                "tables" => [ "[Image]" ],
                "fields" => [ "image_id", "image_section" ],
                "filters" => [[ "image_id", "i", "=", $post->image_id ]]
            ]);

            if( @$image ){
                Model::delete( $commercial,(Object)[
                    "table" => "[Image]",
                    "filters" => [[ "image_id", "i", "=", $image->image_id ]]
                ]);
                $large = PATH_FILES . "{$image->image_section}/{$post->image_id}_large.jpg";
                $small = PATH_FILES . "{$image->image_section}/{$post->image_id}_small.jpg";
                if (file_exists($large)) unlink($large);
                if (file_exists($small)) unlink($small);
            } else {
                $path = PATH_FILES . "{$post->image_dir}/{$post->image_id}";
                if (file_exists("{$path}.jpg")) unlink("{$path}.jpg");
                if (file_exists("{$path}.jpeg")) unlink("{$path}.jpeg");
                if (file_exists("{$path}.png")) unlink("{$path}.png");
            }

            Json::get( $headerStatus[200], (Object)[
                "message" => "Imagem removida com sucesso."
            ]);

        break;

        case "edit":

            if( !@$post->image_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            Model::update( $commercial,(Object)[
                "table" => "[Image]",
                "fields" => [
                    [ "image_active", "s", $post->image_active ],
                    [ "post_id", "i", @$post->post_id ? $post->post_id : NULL ],
                    [ "person_id", "s", @$post->person_id ? $post->person_id : NULL ],
                    [ "image_start_date", "s", @$post->image_start_date ? $post->image_start_date : NULL ],
                    [ "image_end_date", "s", @$post->image_end_date ? $post->image_end_date : NULL ],
                    [ "image_link", "s", @$post->image_link ? $post->image_link : NULL ],
                    [ "image_name", "s", @$post->image_name ? utf8_decode($post->image_name) : NULL ],
                    [ "image_description", "s", @$post->image_description ? utf8_decode($post->image_description) : NULL ],
                    [ "image_update", "s", date("Y-m-d H:i:s") ]
                ],
                "filters" => [[ "image_id", "i", "=", $post->image_id ]]
            ]);

            Json::get( $headerStatus[200] );

        break;

        case "getList":

            if( !@$post->image_section ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $images = Model::getList( $commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.[Image] I (NOLOCK)",
                    "LEFT JOIN {$conn->dafel->table}.dbo.Pessoa P (NOLOCK) ON(I.person_id = P.IdPessoa)",
                ],
                "fields" => [
                    "I.image_id",
                    "I.parent_id",
                    "I.image_main",
                    "I.image_name",
                    "I.image_link",
                    "I.image_section",
                    "I.image_description",
                    "I.post_id",
                    "I.person_id",
                    "image_start_date=CONVERT(VARCHAR(10),image_start_date,126)",
                    "image_end_date=CONVERT(VARCHAR(10),image_end_date,126)",
                    "I.image_active",
                    "person_code=P.CdChamada",
                    "person_name=P.NmPessoa",
                    "person_short_name=P.NmCurto"
                ],
                "filters" => [
                    [ "I.image_section", "s", "=", $post->image_section ],
                    [ "I.parent_id", "i", "=", @$post->parent_id ? $post->parent_id : NULL ],
                    [ "I.image_active", "s", "=", @$post->image_active ? $post->image_active : NULL ],
                    @$post->image_start_date ? [
                        [ "I.image_start_date IS NULL" ],
                        [ "I.image_start_date", "s", "<=", date("Y-m-d") ]
                    ] : NULL,
                    @$post->image_end_date ? [
                        [ "I.image_end_date IS NULL" ],
                        [ "I.image_end_date", "s", ">=", date("Y-m-d") ]
                    ] : NULL,
                ],
                "order" => "I.image_order"
            ]);

            $root = [];
            $rand = rand(1000,9999);
            foreach( $images as $image ){
                $uri = URI_FILES . "{$image->image_section}/" . ( @$image->parent_id && !in_array($image->image_section,$root) ? "{$image->parent_id}/" : "" ). "{$image->image_id}_";
                $image->image_large = "{$uri}large.jpg?{$rand}";
                $image->image_small = "{$uri}small.jpg?{$rand}";
                $image->person_image = getImage((Object)[
                    "image_id" => $image->person_id,
                    "image_dir" => "person"
                ]);
            }

            Json::get( $headerStatus[200], $images );

        break;

        case "sortable":

            if( !@$post->image_section || !@$post->images ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            foreach( $post->images as $image ){
                $image = (Object)$image;
                Model::update( $commercial,(Object)[
                    "table" => "[Image]",
                    "fields" => [[ "image_order", "i", $image->image_order ]],
                    "filters" => [[ "image_id", "i", "=", $image->image_id ]]
                ]);
            }

            Json::get($headerStatus[200]);

        break;

    }

?>