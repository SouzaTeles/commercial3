<?php

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, x-session-token, System-Version');

    include "../config/start.php";

    GLOBAL $dafel, $commercial, $post;

    $post->data = (Object)$post->data;
    $post->person = (Object)$post->person;
    $post->person->person_id = $post->person->IdPessoa;
    $post->person->person_code = $post->person->CdChamada;
    $post->person->person_name = $post->person->NmPessoa;

    $to[0] = (Object)[
        "email" => "ti@dafel.com.br",
        "name" => "TI DAFEL"
    ];

    $user = Model::get($commercial,(Object)[
        "tables" => [ "[User]" ],
        "fields" => [
            "user_id",
            "user_email",
        ],
        "filters" => [[ "person_id", "s", "=", $post->person->IdPessoa ]]
    ]);

    if( @$user ){
        $to[1] = (Object)[
            "email" => $user->user_email,
            "name" => $post->person->NmPessoa
        ];
    } else{
        $contato = Model::get($dafel,(Object)[
            "tables" => [ "PessoaEndereco_TipoContato" ],
            "fields" => [ "DsContato" ],
            "filters" => [
                [ "IdPessoa", "s", "=", $post->person->IdPessoa ],
                [ "IdTipoContato", "s", "=", "0000000004" ]
            ]
        ]);
        if( @$contato ){
            $to[1] = (Object)[
                "email" => $contato->DsContato,
                "name" => $post->person->NmPessoa
            ];
        }
    }

    $ticket_id = (int)Model::insert($commercial,(Object)[
        "table" => "Ticket",
        "fields" => [
            [ "user_id", "i", @$user ? $user->user_id : NULL ],
            [ "owner_id", "i", 98 ],
            [ "person_id", "s", $post->person->person_id ],
            [ "company_id", "i", $post->data->company_id ],
            [ "ticket_type_id", "i", $post->data->ticket_type_id ],
            [ "urgency_id", "i", $post->data->urgency_id ],
            [ "ticket_status", "s", "O" ],
            [ "ticket_origin", "s", "W" ],
            [ "ticket_update", "s", date("Y-m-d H:i:s") ],
            [ "ticket_date", "s", date("Y-m-d H:i:s") ]
        ]
    ]);

    $ticket_code = substr("00000{$ticket_id}",-6);

    $ticket_note_id = Model::insert($commercial,(Object)[
        "table" => "TicketNote",
        "fields" => [
            [ "ticket_id", "i", $ticket_id ],
            [ "user_id", "i", @$user ? $user->user_id : NULL ],
            [ "owner_id", "i", 98 ],
            [ "urgency_id", "i", $post->data->urgency_id ],
            [ "ticket_status", "s", "O" ],
            [ "ticket_note_text", "s", $post->data->message ],
            [ "ticket_note_date", "s", date("Y-m-d H:i:s") ]
        ]
    ]);

    $files = [];
    if( @$post->images ){
        $post->images = (Object)$post->images;

        $path = PATH_FILES . "email/" . date("Y/F/d/");
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        foreach( $post->images as $key => $image ){

            $fileName = date("YmdHis") + ($key+1);
            $fileName = base64toFile($path, $fileName, $image);
            $files[] = $fileName;

            Model::insert($commercial,(Object)[
                "table" => "TicketFile",
                "fields" => [
                    [ "ticket_note_id", "i", $ticket_note_id ],
                    [ "ticket_file_name", "s", "{$fileName}" ],
                    [ "ticket_file_date", "s", date("Y-m-d H:i:s") ]
                ]
            ]);
        }
    }

    $ticket = (Object)[
        "ticket_id" => $ticket_id,
        "ticket_code" => $ticket_code
    ];

    email((Object)[
        "origin" => "ticket",
        "parent_id" => $ticket_id,
        "subject" => "Chamado {$ticket_code}",
        "recipient" => $to,
        "files" => $files,
        "vars" => [(Object)[
            "key" => "ticket",
            "data" => $ticket
        ]]
    ]);

    echo json_encode($ticket);

?>