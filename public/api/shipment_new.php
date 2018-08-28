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
    case "get":

        $document = Model::get($dafel,(Object)[
            "tables" => [
                "Documento D (NoLock)"
            ],
            "fields" => [
                "D.IdDocumento",
                "D.CdChaveAcessoNFEletronica",
                "D.NrDocumento",
                "D.StDocumentoCancelado",
                "D.IdMapaCarregamento",
                "D.DtEmissao"
            ],
            "filters" => [
                [ "D.CdChaveAcessoNFEletronica", "s", "=", $post->document_key]
            ]
        ]);

        if( !sizeof($document) ){
            headerResponse((Object)[
                "code" => 404,
                "message" => "Nenhuma nota foi localizada pela chave de acesso informada. Verifique."
            ]);
        }
        else if(@$document->IdMapaCarregamento){
            headerResponse((Object)[
               "code" => 417,
               "message" => "Esse documento já está em uso em outro mapa. Verifique."
            ]);
        }
        else if ($document->StDocumentoCancelado = 'S'){
            headerResponse((Object)[
                "code" => 417,
                "message" => "O documento não pode ser inserido pois o mesmo encontra-se cancelado. Verifique."
            ]);
        }

        $ret = [];
        foreach($document as $document){
            $ret[] = (Object)[
                "document_number" => $document->NrDocumento,
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

   /* case "get": $documents = Model::getList($dafel,(Object)[
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

        Json::get($httpStatus[200], $ret);
        break;*/

}
?>