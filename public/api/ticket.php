<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $headerStatus, $get, $post, $login;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action ){

        case "new":

            $post->data = (Object)$post->data;
            $post->person = (Object)$post->person;

            $to[0] = (Object)[
                "email" => "alessandro@dafel.com.br",
                "name" => "TI DAFEL"
            ];

            $user = Model::get($commercial,(Object)[
                "tables" => [ "[User]" ],
                "fields" => [
                    "user_id",
                    "user_email",
                ],
                "filters" => [[ "person_id", "s", "=", $post->person->person_id ]]
            ]);

            if( @$user ){
                $to[1] = (Object)[
                    "email" => $user->user_email,
                    "name" => $post->person->person_name
                ];
            } else{
                $contato = Model::get($dafel,(Object)[
                    "tables" => [ "PessoaEndereco_TipoContato" ],
                    "fields" => [ "DsContato" ],
                    "filters" => [
                        [ "person_id", "s", "=", $post->person->person_id ],
                        [ "IdTipoContato", "s", "=", "0000000004" ]
                    ]
                ]);
                if( @$contato ){
                    $to[1] = (Object)[
                        "email" => $contato->DsContato,
                        "name" => $post->person->person_name
                    ];
                }
            }

            $ticket_id = (int)Model::insert($commercial,(Object)[
                "table" => "Ticket",
                "fields" => [
                    [ "user_id", "i", $login->user_id ],
                    [ "company_id", "i", $post->data->company_id ],
                    [ "ticket_type_id", "i", $post->data->ticket_type_id ],
                    [ "urgency_id", "i", $post->data->urgency_id ],
                    [ "ticket_status", "s", "O" ],
                    [ "ticket_origin", "s", "D" ],
                    [ "ticket_date", "s", date("Y-m-d H:i:s") ]
                ]
            ]);

            $ticket_code = substr("00000{$ticket_id}",-6);

            $post->data->message = "<p>{$post->person->person_code} - {$post->person->person_name}</p>" . $post->data->message;
            $ticket_note_id = Model::insert($commercial,(Object)[
                "table" => "TicketNote",
                "fields" => [
                    [ "ticket_id", "i", $ticket_id ],
                    [ "user_id", "i", $login->user_id ],
                    [ "ticket_note_text", "s", removeSpecialChar($post->data->message) ],
                    [ "ticket_note_date", "s", date("Y-m-d H:i:s") ]
                ]
            ]);

            $files = [];
            if( @$post->images ){
                $post->images = (Object)$post->images;

                $path = PATH_FILES . "email/" . date("Y/F/d");
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

            Json::get($headerStatus[200], $ticket );

        break;

        case "getList":

            if( !@$post->start_date || !@$post->end_date ){
                headerResponse((Object)[
                    "code" => 417,
                    "Parâmetro POST não localizado."
                ]);
            }

            $tickets = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "Ticket T",
                    "LEFT JOIN [User] U ON(U.user_id = T.user_id)",
                    "LEFT JOIN [User] O ON(O.user_id = T.user_id)"
                ],
                "fields" => [
                    "T.ticket_id",
                    "U.user_id",
                    "U.user_name",
                    "owner_id=O.user_id",
                    "owner_name=O.user_name",
                    "T.owner_id",
                    "T.company_id",
                    "T.ticket_type_id",
                    "T.urgency_id",
                    "T.ticket_status",
                    "T.ticket_origin",
                    "ticket_update=FORMAT(T.ticket_update,'yyyy-MM-dd HH:mm:ss')"
                ],
                "filters" => [
                    [ "T.company_id", "i", "=", @$post->company_id ? $post->company_id : NULL ],
                    [ "T.ticket_date", "s", "between", [$post->start_date,$post->end_date]]
                ]
            ]);

            Json::get($headerStatus[200],$tickets);

        break;

    }