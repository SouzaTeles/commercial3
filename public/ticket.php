<?php

    include "../config/start.php";

    GLOBAL $commercial, $post;

    $post->data = (Object)$post->data;
    $post->person = (Object)$post->person;

    $ticket_id = Model::insert($commercial,(Object)[
        "table" => "Ticket",
        "fields" => [
            [ "person_id", "s", $post->person->IdPessoa ],
            [ "company_id", "i", $post->data->company_id ],
            [ "ticket_type_id", "i", $post->data->ticket_type_id ],
            [ "urgency_id", "i", $post->data->urgency_id ],
            [ "ticket_status", "s", "O" ],
            [ "ticket_origin", "s", "W" ],
            [ "ticket_date", "s", date("Y-m-d H:i:s") ]
        ]
    ]);

    $ticket_code = substr("00000{$ticket_id}",-6);

    $post->data->message = "<p>{$post->data->CdChamada} - {$post->data->NmPessoa}</p>" . $post->data->message;
    $ticket_note_id = Model::insert($commercial,(Object)[
        "table" => "TicketNote",
        "fields" => [
            [ "ticket_id", "i", $ticket_id ],
            [ "ticket_note_text", "s", removeSpecialChar($post->data->message) ],
            [ "ticket_date", "s", date("Y-m-d H:i:s") ]
        ]
    ]);

    $to[0] = (Object)[
        "email" => "ti@dafel.com.br",
        "name" => "Ti Dafel"
    ];

    if( @$post->images ){
        $post->images = (Object)$post->images;

        $path = PATH_FILES . "email/" . date("Y/F/d");
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $files = [];
        foreach( $post->images as $key => $image ){
            $fileName = date("YmdHis") + ($key+1);
            $data = explode( ',', $image );
            $extension = explode( ";", explode( "/", $data[0] )[1] )[0];

            $ifp = fopen( "{$path}/{$fileName}.{$extension}", 'wb' );
            fwrite( $ifp, base64_decode( $data[ 1 ] ) );
            fclose( $ifp );

            $files[] = "{$path}/{$fileName}.{$extension}";

            Model::insert($commercial,(Object)[
                "table" => "TicketFile",
                "fields" => [
                    [ "ticket_note_id", "i", $ticket_note_id ],
                    [ "ticket_file_name", "s", "{$fileName}.{$extension}" ],
                    [ "ticket_file_date", "s", date("Y-m-d H:i:s") ]
                ]
            ]);
        }

        email((Object)[
            "origin" => "ticket",
            "parent_id" => $ticket_id,
            "subject" => "Chamado {$ticket_code}",
            "recipient" => $to,
            "files" => $files
        ]);
    }

    echo json_encode((Object)[
        "ticket_id" => $ticket_id,
        "ticket_code" => $ticket_code
    ]);

?>