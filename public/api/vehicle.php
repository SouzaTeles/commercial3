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
        //  var_dump($post);
            $vehicles = Model::getList($commercial,(Object)[
                "join" => 2,
                "tables" => [ 
                    "Vehicle V",
                    "INNER JOIN Maker M ON (V.maker_id = M.maker_id)",
                    "INNER JOIN VehicleType VT ON (V.vehicle_type_id = VT.vehicle_type_id)"
                ],
                "fields" => [
                    "V.vehicle_id",
                    "V.vehicle_uf",
                    "V.vehicle_plate",
                    "V.vehicle_model",
                    "M.maker_name",
                    "V.vehicle_year",
                    "VT.vehicle_type_name",
                    "V.vehicle_capacity_kg"
                ],
                "filters" => [
                    ["V.vehicle_year", "s", "=", @$post->vehicle_year ? $post->vehicle_year : NULL ],
                    ["V.vehicle_type_id", "s", "=", @$post->vehicle_type ? $post->vehicle_type : NULL ],
                    ["M.maker_id", "s", "=", @$post->maker_id ? $post->maker_id : NULL ]
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

        case "add":
        Model::insert($commercial,(Object)[
            "top" => 1,
            "table" =>
                "Vehicle",
            "fields" => [
                ["vehicle_plate", "s", $post->vehicle_plate],
                ["vehicle_uf", "s", $post->vehicle_uf],
                ["vehicle_model", "s", $post->vehicle_model],
                ["vehicle_year", "s", $post->vehicle_year],
                ["vehicle_type_id", "s", $post->vehicle_type],
                ["maker_id", "s", $post->maker_id_],
                ["vehicle_capacity_kg", "s", $post->vehicle_capacity_kg],
                ["vehicle_capacity_m3", "s", $post->vehicle_capacity_m3],
                ["vehicle_tare", "s", $post->vehicle_tare],
                ["vehicle_axis", "s", $post->vehicle_axis],
                ["vehicle_chassis", "s", $post->vehicle_chassis],
                ["vehicle_renavam", "s", $post->vehicle_renavam],
                ["vehicle_crlv", "s", $post->vehicle_crlv],
                ["vehicle_fuel", "s", $post->vehicle_fuel_],
                ["vehicle_date", "s", date("Y-m-d H:i:s")]      
            ],
          ]);

          Json::get($headerStatus[200], "Veículo cadastrado com sucesso.");
          
        break;

        case "getPlate":
            $vehicle = Model::get($commercial,(Object)[
                "join" => 2,
                "tables" => [
                    "Vehicle V",
                    "inner join maker M on (v.maker_id = m.maker_id)",
                    "inner join vehicletype VT on (vt.vehicle_type_id = v.vehicle_type_id)",

                ],
                "fields" => [
                    "V.vehicle_id",
                    "V.vehicle_plate",
                    "V.vehicle_model",
                    "M.maker_name",
                    "V.vehicle_year",
                    "VT.vehicle_type_name",
                    "V.vehicle_capacity_kg"

                ],
                "filters" => [
                    ["v.vehicle_plate", "s", "=", $post->vehicle_plate],
                ]
            ]);
            switch($post->query_type){
                case 'V':
                    if($vehicle){
                        Json::get($headerStatus[200], $vehicle);
                    } else {
                        Json::get($headerStatus[417], "Veículo não localizado.");
                    }
                break;
                case 'P':
                    if($vehicle){
                        Json::get($headerStatus[417], $vehicle);;
                    } else{
                        Json::get($headerStatus[200], "Ok");
                    }
                break;
            }



            
        break;

        case "getOptions":
        $maker = Model::getList($commercial,(Object)[
            "tables" => [ 
                "Maker M",
            ],
            "fields" => [
                "M.maker_name",
                "M.maker_id"
            ],
        ]);
        $vehicle_type = Model::getList($commercial,(Object)[
            "tables" => [ 
                "VehicleType VT",
            ],
            "fields" => [
                "VT.vehicle_type_name",
                "VT.vehicle_type_id"
            ],
        ]);
        $options['vehicle_type'] = $vehicle_type;
        $options['maker'] = $maker;
        Json::get($headerStatus[200], $options);
        
        break;

        case "get":
        $vehicle = Model::get($commercial,(Object)[
            "join" => 2,
            "tables" => [
                "Vehicle V",
                "inner join maker M on (v.maker_id = m.maker_id)",
                "inner join vehicleType vt on (vt.vehicle_type_id = v.vehicle_type_id)"
            ],
            "fields" => [
                "V.vehicle_id",
                "V.vehicle_plate",
                "V.vehicle_model",
                "M.maker_name",
                "V.vehicle_year",
                "VT.vehicle_type_name",
                "V.vehicle_capacity_kg"

            ],
            "filters" => [
                ["v.vehicle_id", "s", "=", $post->vehicle_id],
            ]
        ]);

        if($vehicle){
            Json::get($headerStatus[200], $vehicle);
        } else {
            Json::get($headerStatus[417], "Veículo não localizado.");
        }
        break;
    }
?>