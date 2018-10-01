<?php

    /*
     * Versão: 1.1
     * Autor: Alessandro Menezes
     * Descrição: Serviço para empenhar e desempenhar os créditos do cliente no alterdata
     */

    set_time_limit(0);
    date_default_timezone_set("America/Sao_Paulo");

    echo date("Y-m-d H:i:s") . "\niniciando...\n";

    $data = json_decode(file_get_contents( "../../data/conn.json"));
    $data->dafel->pass = base64_decode($data->dafel->pass);

    echo "carregando dados de conexao.\n";

    try{
        $conn = new COM ("ADODB.Connection");
        $conn->open("PROVIDER=SQLOLEDB;SERVER={$data->dafel->host};UID={$data->dafel->user};PWD={$data->dafel->pass};DATABASE={$data->dafel->table}");
    } Catch( Exception $e ){
        die("erro de conexão: {$e->getMessage()}");
    }

    echo "conexao realizada com sucesso.\n";

    $file = [];
    $debug = 0;
    $counter = 0;

    echo "entrando no loop... boa sorte!\n";
    while( !file_exists("die/die.json") ){

        $log = [];
        $del = 0;

        foreach( glob("del/*.json") as $filename ){
            if( file_exists($filename)) {
                $data = json_decode(file_get_contents($filename));
                foreach( $file as $key => $f ){
                    if( $f->id == $data->id ){
                        $del = 1;
                        unset($file[$key]);
                        $conn->execute("If Object_Id('TempDB..{$data->table}','U') IS Not Null begin DROP TABLE {$data->table} end");
                        echo date("Y-m-d H:i:s") . ": excluindo registro: {$data->id}\n";
                        $log[] = (Object)[
                            "date" => date("Y-m-d H:i:s"),
                            "action" => "Exluindo registro",
                            "data" => $data
                        ];
                    }
                }
                unlink($filename);
            }
        }

        $new = 0;
        foreach( glob("new/*.json") as $filename ){
            if( file_exists($filename)){
                $exists = 0;
                $data = json_decode(file_get_contents($filename));
                foreach( $file as $f ){
                    if( $f->id == $data->id ){
                        $exists = 1;
                    }
                }
                if( !$exists ){
                    $file[] = $data;
                    $new = 1;
                    echo date("Y-m-d H:i:s") . ": adicionando registro: {$data->id}\n";
                    $conn->execute("SELECT Descricao='" . $data->description . "' INTO {$data->table}");
                    $log[] = (Object)[
                        "date" => date("Y-m-d H:i:s"),
                        "action" => "Adicionando registro",
                        "data" => $data
                    ];
                }
                unlink($filename);
            }
        }

        if( $new || $del ){
            file_put_contents( "tables.json", json_encode($file) );
        }

        foreach( $file as $f ){
            $date = new DateTime( $f->date );
            $date->modify('+30 minutes');
            if( strtotime(date('Y-m-d H:i:s')) > strtotime('+30 minutes', strtotime($f->date)) ){
                file_put_contents("del/{$f->id}.json", json_encode($f) );
                echo date("Y-m-d H:i:s") . ": registro expirado: {$f->id}\n";
                $log[] = (Object)[
                    "date" => date("Y-m-d H:i:s"),
                    "action" => "Registro expirado",
                    "data" => $f
                ];
            }
        }

        if( sizeof($log) ){
            $pathLog =  "log/" . date("Y/F/d") . "/";
            if( !is_dir($pathLog) ){
                mkdir( $pathLog, 0755, true );
            }
            file_put_contents( $pathLog . date("His") . ".json", json_encode($log) );
        }

        sleep(1);
        $counter++;

        if( $counter % 60 == 0 ){
            $counter = 0;
            $ch = curl_init("http://172.16.0.166/commercial3/service.php?token=r0zUBn6o7tbggzZQXCusGT2DUPJ4wHF3");
            curl_exec($ch);
            echo date("Y-m-d H:i:s") . " Serviços disparados\n";
        }
    }

    echo "servico parado!\n";
    file_put_contents( "tables.json", "[]" );
    unlink("die/die.json");
    $conn->close();

?>