<?php

    header("Content-type: text/css");

    if( file_exists("../../config/config.php")){
        include "../../config/config.php";
    } else{
        include "../../../../config/config.php";
    }

    $datas = [
        "pages" => (Array)json_decode(file_get_contents(PATH_ROOT . "data/colors.json")),
        "palette" => (Array)json_decode(file_get_contents(PATH_LIB . "data/colors.json"))
    ];

    $hex = [];
    $rgb = [];
    $colors = [];

    foreach( $datas as $k => $data ){
        foreach( $data as $j => $color ){
            $hex[$k][$j] = $color;
            list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
            $rgb[$k][$j] = (Object)[
                "red" => $r,
                "green" => $g,
                "blue" => $b
            ];
        }
        $colors = (Object)[
            "hex" => (Object)$hex,
            "rgb" => (Object)$rgb
        ];
    }

    $colors->hex = (Object)$colors->hex;
    $colors->hex->pages = (Object)$colors->hex->pages;
    $colors->rgb = (Object)$colors->rgb;
    $colors->rgb->pages = (Object)$colors->rgb->pages;

    function brightness( $hex, $steps )
    {
        $steps = max(-255, min(255, $steps));

        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        }

        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color   = hexdec($color);
            $color   = max(0,min(255,$color + $steps));
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT);
        }

        return $return;
    }

?>