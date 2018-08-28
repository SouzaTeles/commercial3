<?php

    include "../config/start.php";

    GLOBAL $commercial, $get;

    if( !@$get->token ){
        die("Token não informado.");
    }

    $token = Model::get($commercial,(Object)[
        "tables" => [ "token" ],
        "filters" => [[ "token_value", "s", "=", $get->token ]]
    ]);

    if( !@$token ){
        die("Token não encontrado.");
    }

    $token->token_data = json_decode($token->token_data);

    $user = Model::get($commercial,(Object)[
        "tables" => [ "report_user ru", "user u" ],
        "fields" => [ "u.user_id", "u.user_mail", "u.user_name", "ru.report_user_modules" ],
        "filters" => [
            [ "ru.user_id = u.user_id" ],
            [ "ru.report_id", "i", "=", $token->token_data->report_id ],
            [ "ru.user_id", "i", "=", $token->token_data->user_id ]
        ]
    ]);

    if( !@$user ){
        if( !@$token ){
            die("Você não possui acesso ao relatório.");
        }
    }

    file_put_contents("log/{$user->user_name}.txt","");

    $modules = Model::getList($commercial,(Object)[
        "tables" => [ "report_module" ],
        "fields" => [ "report_module_key", "report_module_name" ],
        "filters" => [[ "report_module_id", "i", "in", explode(":",$user->report_user_modules) ]]
    ]);

    foreach( $modules as $module ){
        $function = "";
        foreach(explode("-",$module->report_module_key) as $key ){
            $function .= ucfirst($key);
        }
        EmailReport::$function((Object)[
            "reference" => substr($token->token_date,0,10)
        ]);
    }

    $config = Config::getList();
    $colors = getColors((Object)[
        "text" => "#FFFFFF",
        "primary" => "#F58634",
        "secondary" => "#38363C",
        "background" => "#38363C"
    ]);

    $smarty->assign( "token", $token );
    $smarty->assign( "config", $config );
    $smarty->assign( "modules", $modules );
    $smarty->assign( "colors", $colors );

    foreach( get_defined_constants(true)["user"] as $constant => $value ){
        $smarty->assign( $constant, $value );
    }

    $smarty->display( PATH_TEMPLATES . "report.html" );

?>