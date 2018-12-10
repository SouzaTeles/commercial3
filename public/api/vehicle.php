<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $commercial, $dafel, $config, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "Parâmetro GET não localizado."
        ]);
    }

    switch( $get->action )
    {

        case "getList":

            $vehicles = Model::getList($commercial,(Object)[
                "tables" => [ "[Vehicle]" ],
                "fields" => [
                    "vehicle_id",
                    "vehicle_uf",
                    "vehicle_plate",
                    "vehicle_model"
                ]
            ]);

            foreach( $vehicles as $vehicle ){
                $vehicle->image = getImage((Object)[
                    "image_dir" => "vehicle",
                    "image_id" => $vehicle->vehicle_id
                ]);
            }

            Json::get($headerStatus[200], $vehicles);

        break;

    }

?>