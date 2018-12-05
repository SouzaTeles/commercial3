<?php

    $data = json_decode(file_get_contents("567.json"));

    echo "valor: {$data->post->budget_value}<br/>";
    echo "total: {$data->post->budget_value_total}<br/><br/>";

    $value = 0;
    $total = 0;

    foreach($data->post->items as $item){
        $value += $item->budget_item_value;
        $total += $item->budget_item_value_total;
    }

    echo "valor: {$value}<br/>";
    echo "total: {$total}<br/><br/>";

    $dif1 = $value-$data->post->budget_value;
    $dif2 = $total-$data->post->budget_value_total;

    echo "dif value: {$dif1}<br/>";
    echo "dif total: {$dif2}<br/>";

?>
