<?php

    class Ticket
    {
        public $ticket_id;
        public $user_id;
        public $owner_id;
        public $company_id;
        public $ticket_type_id;
        public $urgency_id;
        public $ticket_code;
        public $ticket_status;
        public $ticket_origin;
        public $ticket_host_ip;
        public $ticket_host_name;
        public $ticket_host_platform;
        public $ticket_update;
        public $ticket_date;

        public function __construct($data)
        {
            $this->ticket_id = (int)$data->ticket_id;
            $this->user_id = @$data->user_id ? (int)$data->user_id : NULL;
            $this->owner_id = @$data->owner_id ? (int)$data->owner_id : NULL;
            $this->company_id = (int)$data->company_id;
            $this->ticket_type_id = (int)$data->ticket_type_id;
            $this->urgency_id = (int)$data->urgency_id;
            $this->ticket_code = substr("00000{$data->ticket_id}",-6);
            $this->ticket_status = $data->ticket_status;
            $this->ticket_origin = $data->ticket_origin;
            $this->ticket_host_ip = @$data->ticket_host_ip ? $data->ticket_host_ip : NULL;
            $this->ticket_host_name = @$data->ticket_host_name ? $data->ticket_host_name : NULL;
            $this->ticket_host_platform = @$data->ticket_host_platform ? $data->ticket_host_platform : NULL;
            $this->ticket_update = @$data->ticket_update ? $data->ticket_update : NULL;
            $this->ticket_date = $data->ticket_date;

            GLOBAL $commercial;

            if( @$_POST["get_ticket_user"] && @$data->user_id ){
                $this->user = Model::get($commercial,(Object)[
                    "class" => "User",
                    "tables" => [ "[User]" ],
                    "fields" => [
                        "user_id",
                        "person_id",
                        "user_name",
                        "user_email"
                    ],
                    "filters" => [[ "user_id", "i", "=", $data->user_id ]]
                ]);

            }

            if( @$_POST["get_ticket_owner"] && @$data->owner_id ){
                $this->owner = Model::get($commercial,(Object)[
                    "class" => "User",
                    "tables" => [ "[User]" ],
                    "fields" => [
                        "user_id",
                        "person_id",
                        "user_name",
                        "user_email"
                    ],
                    "filters" => [[ "user_id", "i", "=", $data->owner_id ]]
                ]);
            }

            if( @$_POST["get_ticket_notes"] ){
                $this->notes = [];
                $notes = Model::getList($commercial,(Object)[
                    "join" => 1,
                    "tables" => [
                        "TicketNote TN",
                        "LEFT JOIN [User] U ON(U.user_id = TN.user_id)",
                        "LEFT JOIN [User] O ON(O.user_id = TN.owner_id)"
                    ],
                    "fields" => [
                        "TN.ticket_note_id",
                        "U.user_id",
                        "U.user_name",
                        "user_person_id=U.person_id",
                        "owner_id=O.user_id",
                        "owner_name=O.user_name",
                        "owner_person_id=O.person_id",
                        "TN.ticket_status",
                        "TN.ticket_note_text",
                        "TN.ticket_note_host_ip",
                        "TN.ticket_note_host_name",
                        "TN.ticket_note_host_platform",
                        "TN.ticket_note_date"
                    ],
                    "filters" => [[ "TN.ticket_id", "i", "=", $data->ticket_id ]]
                ]);
                foreach( $notes as $note ){
                    $note->user_image = getImage((Object)[
                        "image_id" => $note->user_id,
                        "image_dir" => "user"
                    ]);
                    if (!@$note->user_image && @$note->user_person_id ){
                        $note->user_image = getImage((Object)[
                            "image_id" => $note->user_person_id,
                            "image_dir" => "person"
                        ]);
                    }
                    $note->owner_image = getImage((Object)[
                        "image_id" => $note->owner_id,
                        "image_dir" => "user"
                    ]);
                    if (!@$note->owner_image && @$note->owner_person_id ){
                        $note->owner_image = getImage((Object)[
                            "image_id" => $note->owner_person_id,
                            "image_dir" => "person"
                        ]);
                    }
                    $note->files = [];
                    $files = Model::getList($commercial,(Object)[
                        "tables" => [ "TicketFile" ],
                        "fields" => [
                            "ticket_file_name",
                            "ticket_file_date=CONVERT(VARCHAR(10),ticket_file_date,126)"
                        ],
                        "filters" => [[ "ticket_note_id", "i", "=", $note->ticket_note_id ]]
                    ]);
                    if( @$files ) {
                        foreach ($files as $file) {
                            $date = DateTime::createFromFormat('Y-m-d', $file->ticket_file_date);
                            $file->url = URI_FILES . "email/{$date->format("Y/F/d")}/{$file->ticket_file_name}";
                        }
                        $note->files = $files;
                    }
                }
                $this->notes = $notes;
            }
        }
    }

    ?>