<?php

    header("Content-type: text/css");

    $colors = json_decode($_GET["colors"]);

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

    function getWallpaper()
	{
		$ds = DIRECTORY_SEPARATOR;
	    $path = __DIR__ . "{$ds}..{$ds}images{$ds}wallpaper{$ds}";
        //die($path);
		$images = [];
		$files = glob( "{$path}/*.{jpg,png}", GLOB_BRACE );

		foreach( $files as $file ){
			$info = explode( "/", $file );
			$images[] = end($info);
		}

		return $images[rand(0,sizeof($images)-1)];
	}

?>