<?php

    include "../../config/start.php";
    Session::checkApi();

    GLOBAL $commercial, $dafel, $config, $login, $headerStatus, $get, $post;

    if( !@$get->action ){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro GET não encontrado."
        ]);
    }

    if( in_array($get->action,["del","edit","insert"]) ) {
        checkAccess();
    }

    if( in_array($get->action,["del","edit","insert","userPass","loginPass"]) ){
        postLog();
    }

    switch( $get->action ) {

        case "del":

            if (!@$post->seller_id) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não informado."
                ]);
            }

            $users = Model::get($commercial, (Object)[
                "tables" => ["user"],
                "fields" => ["user_id"],
                "filters" => [["person_id", "s", "=", $post->seller_id]]
            ]);

            if (@$users) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Existem usuários vinculados ao representante. A exclusão não será permitida."
                ]);
            }

            Model::delete($commercial, (Object)[
                "table" => "seller",
                "filters" => [["seller_id", "s", "=", $post->seller_id]]
            ]);

            $path = PATH_FILES . "person/{$post->seller_id}";
            if (file_exists("{$path}.jpg")) unlink("{$path}.jpg");
            if (file_exists("{$path}.jpeg")) unlink("{$path}.jpeg");
            if (file_exists("{$path}.png")) unlink("{$path}.png");

            Json::get($headerStatus[200], (Object)[
                "message" => "Representante removido com sucesso."
            ]);

        break;

        case "edit":

            if (!@$post->seller_id || !@$post->company_id || !@$post->seller_type || !@$post->seller_active || !@$post->seller_code || !@$post->seller_name || !@$post->seller_email || !@$post->seller_target || !@$post->seller_commission) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::update($commercial, (Object)[
                "table" => "seller",
                "fields" => [
                    ["company_id", "i", $post->company_id],
                    ["seller_type", "s", $post->seller_type],
                    ["seller_code", "s", $post->seller_code],
                    ["seller_active", "s", $post->seller_active],
                    ["seller_name", "s", $post->seller_name],
                    ["seller_email", "s", $post->seller_email],
                    ["seller_target", "s", $post->seller_target],
                    ["seller_commission", "s", $post->seller_commission]
                ],
                "filters" => [["seller_id", "s", "=", $post->seller_id]]
            ]);

            Json::get($headerStatus[200], (Object)[
                "message" => "Representante atualizado com sucesso."
            ]);

            break;

        case "get":

            if (!@$post->seller_id && !@$post->seller_code) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            $seller = Model::get($commercial, (Object)[
                "class" => "Seller",
                "tables" => ["seller"],
                "filters" => [
                    ["seller_id", "s", "=", @$post->seller_id ? $post->seller_id : NULL],
                    ["seller_code", "s", "=", @$post->seller_code ? substr("00000{$post->seller_code}", -6) : NULL]
                ]
            ]);

            if (!@$seller) {
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Representante não encontrado."
                ]);
            }

            Json::get($headerStatus[200], $seller);

        break;

        case "insert":

            if (!@$post->seller_id || !@$post->company_id || !@$post->seller_type || !@$post->seller_active || !@$post->seller_code || !@$post->seller_name || !@$post->seller_email || !@$post->seller_target || !@$post->seller_commission) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Model::insert($commercial, (Object)[
                "table" => "seller",
                "fields" => [
                    ["seller_id", "s", $post->seller_id],
                    ["company_id", "i", $post->company_id],
                    ["seller_type", "s", $post->seller_type],
                    ["seller_code", "s", $post->seller_code],
                    ["seller_active", "s", $post->seller_active],
                    ["seller_name", "s", $post->seller_name],
                    ["seller_email", "s", $post->seller_email],
                    ["seller_target", "s", $post->seller_target],
                    ["seller_commission", "s", $post->seller_commission]
                ]
            ]);

            Json::get($headerStatus[200], (Object)[
                "message" => "Representante cadastrado com sucesso."
            ]);

        break;

        case "getList":

            $sellers = Model::getList($commercial, (Object)[
                "tables" => ["seller s", "company c"],
                "fields" => [
                    "s.seller_id",
                    "c.company_id",
                    "s.seller_active",
                    "s.seller_code",
                    "s.seller_name",
                    "s.seller_target",
                    "s.seller_commission"
                ],
                "filters" => [["s.company_id = c.company_id"]]
            ]);

            foreach ($sellers as $seller) {
                $seller->image = getImage((Object)[
                    "image_id" => $seller->seller_id,
                    "image_dir" => "person"
                ]);
                $seller->company_code = substr("0{$seller->company_id}", -2);
            }

            Json::get($headerStatus[200], $sellers);

        break;

        case "getListERP":

            $sellers = Model::getList($dafel, (Object)[
                "tables" => ["Pessoa P", "PessoaCategoria PC"],
                "fields" => [
                    "P.IdPessoa",
                    "P.NmPessoa",
                    "P.CdChamada"
                ],
                "filters" => [
                    ["P.IdPessoa = PC.IdPessoa"],
                    ["PC.IdCategoria", "s", "in", ["0000000004"]],
                    ["PC.StAtivo", "s", "=", "S"]
                ],
                "order" => "P.NmPessoa"
            ]);

            foreach ($sellers as $seller) {
                $seller->Imagem = getImage((Object)[
                    "image_id" => $seller->IdPessoa,
                    "image_dir" => "person"
                ]);
            }

            Json::get($headerStatus[200], $sellers);

        break;

        case "typeahead":

            $sellers = Model::getList($commercial, (Object)[
                "join" => 1,
                "tables" => ["seller"],
                "fields" => [
                    "seller_id",
                    "seller_code",
                    "seller_name"
                ],
                "filters" => [["seller_active", "s", "=", "Y"]],
                "order" => "seller_name"
            ]);

            $ret = [];
            foreach ($sellers as $seller) {
                $seller->image = getImage((Object)[
                    "image_id" => $seller->seller_id,
                    "image_dir" => "person"
                ]);
                $ret[] = (Object)[
                    "item_id" => $seller->seller_id,
                    "item_code" => $seller->seller_code,
                    "item_name" => $seller->seller_name,
                    "item_image" => $seller->image,
                    "html" => (
                        "<div class='type-ahead-cover'" . (@$seller->image ? (" style='background-image:url({$seller->image})'") : "") . "></div>" .
                        "<b>{$seller->seller_name}</b><br/>" .
                        "Cd. {$seller->seller_code}"
                    )
                ];
            }

            Json::get($headerStatus[200], $ret);

            break;

    }

?>