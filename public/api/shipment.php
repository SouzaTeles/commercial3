<?php

include "../../config/start.php";

Session::checkApi();

GLOBAL $dafel, $login, $config, $headerStatus, $get, $post;

if( !@$get->action ){
    headerResponse((Object)[
        "code" => 417,
        "message" => "Parâmetro GET não encontrado."
    ]);
}


switch( $get->action )
{
    case "getList":

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
                //[ "M.StMapaCarregamento", "s", "=", "A"],
                //[ "M.CdChamada", "s", "=", $post->shipment_code],
                [ "M.DtReferencia", "s", "between", ["{$post->start_date} 00:00:00", "{$post->end_date} 23:59:59"]]
               // [ "M.CdChamada", "s", "=", $post->company_id],

            ]
        ]);

        if( !sizeof($documents) ){
            headerResponse((Object)[
                "code" => 404,
                "message" => "Nenhum mapa foi localizado para os filtros informados. Verifique."
            ]);
        }

        $ret = [];
        foreach($documents as $document){
            $ret[] = (Object)[
                "shipment_status" => $document->StMapaCarregamento,
                "shipment_code" => $document->CdChamada,
                "shipment_name" => $document->DsMapaCarregamento,
                "shipment_date" => $document->DtReferencia,
                "shipment_driver" => $document->NmMotorista,
                "driver_image" => getImage((Object)[
                "image_id" => "00A000000J.jpeg", //$document->IdVendedor,
                "image_dir" => "person",
            ])
            ];
        }
        Json::get($httpStatus[200], $ret);
        break;

    case "get": $documents = Model::getList($dafel,(Object)[
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
}
?>