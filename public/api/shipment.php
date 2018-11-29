<?php

    include "../../config/start.php";

    Session::checkApi();

    GLOBAL $conn, $dafel, $commercial, $login, $config, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    switch( $get->action )
    {

        case "getList":

            $documents = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "Shipment S (NoLock)",
                    "INNER JOIN ShipmentDocument SD (NoLock) ON(SD.shipment_id = S.shipment_id)",
                    "INNER JOIN Person P (NoLock) ON(P.person_id = S.driver_id)"
                ],
                "fields" => [
                    "S.shipment_id",
                    "S.external_code",
                    "S.shipment_status",
                    "driver_name=P.person_name",
                    "shipment_reference=FORMAT(S.shipment_reference,'yyyy-MM-dd HH:mm:ss')",
                    "shipment_reference_br=FORMAT(S.shipment_reference,'dd/MM/yyyy')",
                    "shipment_date=FORMAT(S.shipment_date,'yyyy-MM-dd HH:mm:ss')",
                    "shipment_date_br=S.shipment_date",
                    "weight=SUM(SD.shipment_document_weight)",
                    "distance=SUM(SD.shipment_document_distance)",
                    "duration=SUM(SD.shipment_document_duration)",
                ],
                "filters" => [
                    [ "S.company_id", "i", "=", $post->company_id],
                    [ "S.shipment_date", "s", "between", ["{$post->start_date} 00:00:00", "{$post->end_date} 23:59:59"]],
                    [ "S.external_code", "s", "=", @$post->shipment_code ? $post->shipment_code : NULL ]
                ],
                "group" => "S.shipment_id,S.external_code,S.shipment_status,P.person_name,S.shipment_reference,S.shipment_date"
            ]);

            foreach($documents as $document){
                $document->weight = (float)$document->weight;
                $document->distance = (int)$document->distance;
                $document->duration = (int)$document->duration;
                $document->weight_order = substr("0000000000" . number_format($document->weight,2,"",""),-10);
                $document->distance_order = substr("0000000000{$document->distance}",-10);
                $document->duration_order = substr("0000000000{$document->duration}",-10);
            }
            
            Json::get($httpStatus[200], $documents);
            
        break;

        case "get":

            $documents = Model::getList($dafel,(Object)[
                "tables" => [
                    "MapaCarregamento M (NoLock)"
                ],
                "fields" => [
                    "M.StMapaCarregamento",
                    "M.CdChamada",
                    "M.DsMapaCarregamento",
                    "M.DtReferencia",
                    "M.NmMotorista"
                ],
                "filters" => [
                    [ "M.CdChamada", "s", "=", $post->shipment_code]
                ]
            ]);

            //var_dump($documents); vazio

            $ret = [];
            foreach($documents as $document){
                $ret[] = (Object)[
                    "shipment_status" => $document->StMapaCarregamento,
                    "shipment_code" => $document->CdChamada,
                    "shipment_name" => $document->DsMapaCarregamento,
                    "shipment_date" => $document->DtReferencia,
                    "shipment_driver" => $document->NmMotorista
                ];
            }

            //var_dump($ret); vazio
            Json::get($httpStatus[200], $ret);
            break;

        case "addSingle":

            if(
                !@$post->company_id ||
                !@$post->vehicle ||
                !@$post->vehicle["vehicle_id"] ||
                !@$post->vehicle["vehicle_uf"] ||
                !@$post->vehicle["vehicle_plate"] ||
                !@$post->driver ||
                !@$post->driver["person_id"] ||
                !@$post->driver["person_name"] ||
                !@$post->routes ||
                !@$post->helpers ||
                !@$post->documents ||
                !@$post->reference
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $shipment = (Object)[
                "IdMapaCarregamento" => Model::nextCode($dafel,(Object)[
                    "table" => "MapaCarregamento",
                    "field" => "IdMapaCarregamento",
                    "increment" => "S",
                    "base36encode" => 1
                ]),
                "CdChamada" => Model::nextCode($dafel,(Object)[
                    "table" => "MapaCarregamento",
                    "field" => "CdChamada",
                    "increment" => "S"
                ])
            ];

            $shipment->CdChamada = substr("000000{$shipment->CdChamada}",-6);

            Model::insert($dafel,(Object)[
                "table" => "MapaCarregamento",
                "fields" => [
                    [ "IdMapaCarregamento", "s", $shipment->IdMapaCarregamento ],
                    [ "CdChamada", "s", $shipment->CdChamada ],
                    [ "DsMapaCarregamento", "s", @$post->description ? $post->description : NULL ],
                    [ "DtReferencia", "s", $post->reference ],
                    [ "NmMotorista", "s", substr($post->driver["person_name"],0,50) ],
                    [ "NmAjudante1", "s", substr($post->helpers[0]["person_name"],0,50) ],
                    [ "NmAjudante2", "s", @$post->helpers[1] ? substr($post->helpers[1]["person_name"],0,50) : NULL ],
                    [ "TpSituacaoMapa", "s", "A" ],
                    [ "TpOrigemMapaCarregamento", "s", "D" ],
                    [ "CdPlacaVeiculo", "s", $post->vehicle["vehicle_plate"] ],
                    [ "IdUFPlacaVeiculo", "s", $post->vehicle["vehicle_uf"] ]
                ]
            ]);

            $shipment_id = (int)Model::insert($commercial,(Object)[
                "table" => "Shipment",
                "fields" => [
                    [ "external_id", "s", $shipment->IdMapaCarregamento ],
                    [ "external_code", "s", $shipment->CdChamada ],
                    [ "company_id", "i", $post->company_id ],
                    [ "driver_id", "i", $post->driver["person_id"] ],
                    [ "vehicle_id", "i", $post->vehicle["vehicle_id"] ],
                    [ "shipment_status", "s", "A" ],
                    [ "shipment_trash", "s", "N" ],
                    [ "shipment_description", "s", @$post->description ? $post->description : NULL ],
                    [ "shipment_reference", "s", $post->reference ],
                    [ "shipment_date", "s", date("Y-m-d H:i:s") ]
                ]
            ]);

            $documents = [];
            foreach( $post->documents as $key => $document ){
                $document = (Object)$document;
                Model::insert($commercial,(Object)[
                    "table" => "ShipmentDocument",
                    "fields" => [
                        [ "shipment_id", "i", $shipment_id ],
                        [ "document_id", "s", $document->document_id ],
                        [ "shipment_document_weight", "d", $document->weight ],
                        [ "shipment_document_distance", "i", $document->distance ],
                        [ "shipment_document_duration", "i", $document->duration ],
                        [ "shipment_document_order", "i", $key+1 ],
                        [ "shipment_document_date", "s", date("Y-m-d H:i:s") ],
                    ]
                ]);
                $documents[] = $document->document_id;
            }

            if( sizeof($documents) ){
                Model::update($dafel,(Object)[
                    "top" => sizeof($documents),
                    "table" => "Documento",
                    "fields" => [[ "IdMapaCarregamento", "s", $shipment->IdMapaCarregamento ]],
                    "filters" => [
                        [ "IdMapaCarregamento IS NULL" ],
                        [ "IdDocumento", "s", "in", $documents ]
                    ]
                ]);
            }

            foreach( $post->routes as $route ){
                Model::insert($commercial,(Object)[
                    "table" => "ShipmentRoute",
                    "fields" => [
                        [ "shipment_id", "i", $shipment_id ],
                        [ "route_id", "i", $route["route_id"] ],
                        [ "shipment_route_date", "s", date("Y-m-d H:i:s") ]
                    ]
                ]);
            }

            foreach( $post->helpers as $helper ){
                Model::insert($commercial,(Object)[
                    "table" => "ShipmentHelper",
                    "fields" => [
                        [ "shipment_id", "i", $shipment_id ],
                        [ "person_id", "i", $helper["person_id"] ],
                        [ "shipment_helper_date", "s", date("Y-m-d H:i:s") ]
                    ]
                ]);
            }

            Json::get( $headerStatus[200], (Object)[
                "shipment_id" => $shipment_id,
                "shipment_code" => substr("00000{$shipment_id}",-6),
                "external_id" => $shipment->IdMapaCarregamento,
                "external_code" => $shipment->CdChamada
            ]);

        break;

        case "distanceMatrix":

            $data = [];
            for( $i=0; $i<sizeof($post->points)-1; $i++ ){
                $origin = urlencode(removeSpecialChar($post->points[$i]));
                $destiny = urlencode(removeSpecialChar($post->points[$i+1]));
                $google = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&key=AIzaSyA4we-5aCbXOqPvzcbUJW49x46LXnhwdbY&model=driving&origins={$origin}&destinations={$destiny}");
                $google = json_decode($google, TRUE);
                //Json::get( $headerStatus[200], $google["rows"][0]["elements"][0]["distance"]["value"] );
                $data[] = (Object)[
                    "distance" =>  @$google["rows"] && $google["rows"][0]["elements"][0] ? $google["rows"][0]["elements"][0]["distance"]["value"] : 0,
                    "duration" => @$google["rows"] && $google["rows"][0]["elements"][0] ? $google["rows"][0]["elements"][0]["duration"]["value"] : 0
                ];
            }

            Json::get( $headerStatus[200], $data );

        break;

        case "dataMap":

            if( !@$post->company_id || !@$post->shipment_id ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $company = Model::get($commercial,(Object)[
                "join" => "1",
                "tables" => [
                    "{$conn->commercial->table}.dbo.Company C",
                    "INNER JOIN {$conn->dafel->table}.dbo.EmpresaERP E ON(E.CdEmpresa = C.company_id)"
                ],
                "fields" => [
                    "C.company_id",
                    "C.company_name",
                    "C.company_short_name",
                    "address_public_place=E.DsEndereco",
                    "address_number=E.NrLogradouro",
                    "address_cep=E.NrCEP",
                    "district_name=E.NmBairro",
                    "city_name=E.NmCidade",
                    "uf_id=E.CdUF",
                    "latitude=C.company_latitude",
                    "longitude=C.company_longitude"
                ],
                "filters" => [[ "C.company_id", "i", "=", $post->company_id ]]
            ]);

            $points = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.ShipmentDocument SD (NoLock)",
                    "INNER JOIN {$conn->dafel->table}.dbo.Documento D (NoLock) ON(D.IdDocumento = SD.document_id)",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa P (NoLock) ON(P.IdPessoa = D.IdPessoa)",
                    "INNER JOIN {$conn->dafel->table}.dbo.PessoaEndereco PE (NoLock) ON(PE.IdPessoa = P.IdPessoa AND PE.CdEndereco = D.CdEnderecoEntrega)",
                    "INNER JOIN {$conn->dafel->table}.dbo.Bairro B (NoLock) ON(B.IdBairro = PE.IdBairro)",
                    "INNER JOIN {$conn->dafel->table}.dbo.Cidade C (NoLock) ON(C.IdCidade = PE.IdCidade)"
                ],
                "fields" => [
                    "document_code=D.NrDocumento",
                    "person_code=P.CdChamada",
                    "person_name=P.NmPessoa",
                    "address_code=PE.CdEndereco",
                    "address_type=PE.TpLogradouro",
                    "address_public_place=PE.NmLogradouro",
                    "address_number=PE.NrLogradouro",
                    "address_cep=PE.CdCEP",
                    "district_name=B.NmBairro",
                    "city_name=C.NmCidade",
                    "uf_id=C.IdUF",
                    "latitude=PE.VlLatitude",
                    "longitude=PE.VlLongitude"
                ],
                "filters" => [[ "SD.shipment_id", "i", "=", $post->shipment_id ]],
                "order" => "SD.shipment_document_order"
            ]);

            $url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyA4we-5aCbXOqPvzcbUJW49x46LXnhwdbY&address=";
            foreach( $points as $point ){
                if( !@$point->latitude || !@$point->longitude ){
                    $google = NULL;
                    if( @$point->address_cep ){
                        $url2 = $url.urlencode(removeSpecialChar("{$point->city_name} - {$point->uf_id}, {$point->address_public_place}, {$point->district_name}"));
                        $google = file_get_contents($url2);
                        $google = json_decode($google, TRUE);
                        $result = $google['results'][0];
                        if( @$result['geometry']['location'] ){
                            $point->latitude = $result['geometry']['location']['lat'];
                            $point->longitude = $result['geometry']['location']['lng'];
                            $point->url = $url2;
                        }
                    }
                    if( !@$google ){
                        $url2 = "{$url}{$point->address_cep}";
                        $google = file_get_contents($url2);
                        $google = json_decode($google, TRUE);
                        if( @$google['results'][0] ){
                            $result = $google['results'][0];
                            if( @$result['geometry']['location'] ){
                                $point->latitude = $result['geometry']['location']['lat'];
                                $point->longitude = $result['geometry']['location']['lng'];
                                $point->url = $url2;
                            }
                        }
                    }
                }
                if( !@$point->latitude || !@$point->longitude ){
                    $point->latitude = 0;
                    $point->longitude = 0;
                }
            }

            $vehicle = Model::get($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "Shipment S",
                    "INNER JOIN Vehicle V ON(V.vehicle_id = S.vehicle_id)",
                    "INNER JOIN Person P ON(P.person_id = S.driver_id)"
                ],
                "fields" => [
                    "V.vehicle_id",
                    "V.vehicle_model",
                    "V.vehicle_plate",
                    "P.person_name",
                ],
                "filters" => [[ "S.shipment_id", "i", "=", $post->shipment_id ]]
            ]);

            $vehicle->latitude = -22.404852;
            $vehicle->longitude = -42.9641182;

            Json::get( $headerStatus[200], (Object)[
                "company" => $company,
                "points" => $points,
                "vehicle" => $vehicle
            ]);

        break;
    }
?>