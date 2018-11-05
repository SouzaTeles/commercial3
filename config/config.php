<?php

    define( "PATH_LIB", "C:/wamp/www/lib/" );
    define( "PATH_ROOT", "C:/wamp/www/commercial3/" );
    define( "PATH_METAS", "C:/wamp/www/gestor-online/" );

    include PATH_LIB . "config/func.php";
    include PATH_LIB . "config/config.php";
    include PATH_METAS . "config/business_day.php";
    
    define( "PATH_LOG", PATH_ROOT . "log/" );
    define( "PATH_DATA", PATH_ROOT . "data/" );
    define( "PATH_CLASS", PATH_ROOT . "class/" );
    define( "PATH_MODEL", PATH_ROOT . "model/" );
    define( "PATH_CONFIG", PATH_ROOT . "config/" );
    define( "PATH_PUBLIC", PATH_ROOT . "public/" );
    define( "PATH_FILES", PATH_PUBLIC . "files/" );
    define( "PATH_TEMPLATES", PATH_ROOT . "templates/" );
    define( "PATH_TEMPLATES_ADMIN", PATH_TEMPLATES . "admin/" );
    define( "PATH_TEMPLATES_COMPILED", PATH_TEMPLATES . "compiled/" );
    
    define( "URI_LIB", "http://" . URI . "/lib/" );
    define( "URI_PUBLIC", "http://" . URI . "/commercial3/" );
    define( "URI_PUBLIC_LOGIN", "http://" . URI . "/commercial3/index.php?route=login" );
    define( "URI_FILES", URI_PUBLIC . "files/" );
    define( "URI_PUBLIC_API", URI_PUBLIC . "api/" );

    define( "WIDTH_THUMB_MAX", 5000 );
    define( "HEIGHT_THUMB_MAX", 3500 );
    define( "WIDTH_THUMB_MIN", 320 );
    define( "HEIGHT_THUMB_MIN", 240 );
    define( "QUALITY_THUMB", 80 );

    define( "LITE", in_array( "lite.api", explode( "/", $_SERVER["PHP_SELF"] )) );
    
    $httpStatus = [
        200 => "Ok",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Not Found",
        409 => "Conflict",
        417 => "Expectation Fail",
        420 => "Process Failed",
    ];
    
    $headerStatus = [
        200 => (Object)[
            "code" => 200,
            "message" => "Ok."
        ],
        203 => (Object)[
            "code" => 203,
            "message" => "Acesso negado."
        ],
        400 => (Object)[
            "code" => 400,
            "message" => "Acesso rejeitado."
        ],
        401 => (Object)[
            "code" => 401,
            "message" => "Acesso negado."
        ],
        403 => (Object)[
            "code" => 403,
            "message" => "Proibido."
        ],
        404 => (Object)[
            "code" => 404,
            "message" => "Não encontrado."
        ],
        409 => (Object)[
            "code" => 409,
            "message" => "Conflito."
        ],
        417 => (Object)[
            "code" => 417,
            "message" => "Falha na expectativa."
        ],
        420 => (Object)[
            "code" => 420,
            "message" => "Falha no processamento."
        ],
        500 => (Object)[
            "code" => 500,
            "message" => "Erro no processamento."
        ]
    ];

    $dimensions = [
        "slide" => (Object)[
            "width" => 1200,
            "height" => 600,
            "width_small" => 320,
            "height_small" => 240
        ]
    ];

?>