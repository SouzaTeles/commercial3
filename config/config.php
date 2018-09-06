<?php

    define( "LIB_ROOT", "C:/wamp/www/lib/" );
    define( "PATH_ROOT", "C:/wamp/www/commercial3/" );
    
    include LIB_ROOT . "config/func.php";
    include LIB_ROOT . "config/config.php";
    
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
    define( "URI_PUBLIC_API", URI_PUBLIC . "api/" );
    
    define( "WIDTH_THUMB_MAX", 5000 );
    define( "HEIGHT_THUMB_MAX", 3500 );
    define( "WIDTH_THUMB_MIN", 320 );
    define( "HEIGHT_THUMB_MIN", 240 );
    define( "QUALITY_THUMB", 80 );
    
    define( "PRODUCT_PER_PAGE", 12 );
    
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
        "person" => (Object)[
            "width" => 640,
            "height" => 480,
            "width_small" => 320,
            "height_small" => 240
        ],
        "user" => (Object)[
            "width" => 640,
            "height" => 480,
            "width_small" => 320,
            "height_small" => 240
        ]
    ];

?>