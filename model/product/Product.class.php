<?php

    class Product
    {
        public $product_id;
        public $product_code;
        public $product_code_bar;
        public $product_code_provider;
        public $product_name;
        public $product_active;
        public $product_max_discount;
    
        public function __construct( $data, $gets=[] )
        {
            $this->ncm_id = $data->ncm_id;
            $this->icms_id = $data->icms_id;
            $this->unit_id = $data->unit_id;
            $this->product_id = $data->product_id;
            $this->product_code = $data->product_code;
            $this->product_code_bar = @$data->product_code_bar ? $data->product_code_bar : NULL;
            $this->product_code_provider = @$data->product_code_provider ? $data->product_code_provider : NULL;
            $this->product_name = $data->product_name;
            $this->product_active = $data->product_active == "S" ? "Y" : "N";
            $this->product_discount = (float)$data->product_discount;
            $this->product_commission = (float)$data->product_commission;
            $this->product_weight_net = (float)$data->product_weight_net;
            $this->product_weight_gross = (float)$data->product_weight_gross;
            $this->product_cfop = @$data->product_cfop ? $data->product_cfop : NULL;
            $this->product_cfop_extra = @$data->product_cfop_extra ? $data->product_cfop_extra : NULL;
            $this->product_cost = @$data->product_cost ? (float)$data->product_cost : NULL;

            GLOBAL $dafel, $login, $post;

            if( @$gets["get_unit"] || @$_POST["get_unit"] )
            {
                $this->unit = Model::get($dafel,(Object)[
                    "class" => "Unit",
                    "tables" => [ "Unidade (NoLock)" ],
                    "fields" => [
                        "unit_id=IdUnidade",
                        "unit_code=CdSigla",
                        "unit_name=NmUnidade",
                        "unit_type=TpUnidade"
                    ],
                    "filters" => [[ "IdUnidade", "s", "=", $data->unit_id ]]
                ]);
            }
    
            if( @$gets["get_product_stock"] || @$_POST["get_product_stock"] )
            {
                $stock = Model::get($dafel,(Object)[
                    "top" => 1,
                    "join" => 1,
                    "tables" => [
                        "Produto P (NoLock)",
                        "INNER JOIN EstoqueEmpresa EE ON(EE.IdProduto = ISNULL(P.IdProdutoOrigem,P.IdProduto))"
                    ],
                    "fields" => [
                        "parent_id=P.IdProdutoOrigem",
                        "conversion=P.FtConversaoUnidade",
                        "stock_value=EE.QtEstoque",
                        "stock_date=CONVERT(VARCHAR(10),EE.DtReferencia,126)"
                    ],
                    "filters" => [
                        [ "P.IdProduto", "s", "=", $data->product_id ],
                        [ "EE.CdEmpresa", "i", "=", $post->company_id ]
                    ],
                    "order" => "EE.DtReferencia DESC"
                ]);
                if( @$stock ){
                    $stock->stock_value = (float)$stock->stock_value;
                    $stock->parent_id = @$stock->parent_id ? $stock->parent_id : NULL;
                    $stock->conversion = @$stock->conversion ? (float)$stock->conversion : NULL;
                    if( @$stock->parent_id && @$stock->conversion ){
                        $stock->stock_value = $stock->stock_value * (float)$stock->conversion;
                    }
                    $this->stock = $stock;
                }
            }
    
            if( @$gets["get_product_prices"] || @$_POST["get_product_prices"] )
            {
                $this->prices = [];

                $allowed = [];
                foreach( $login->prices as $price ){
                    $allowed[] = $price->price_id;
                }

                $history = Model::getList($dafel,(Object)[
                    "distinct" => 1,
                    "tables" => [ "HistoricoPreco (NoLock)" ],
                    "fields" => [ "price_id=IdPreco" ],
                    "filters" => [
                        [ "ISNULL(VlPreco,0)", "d", ">", 0 ],
                        [ "IdProduto", "s", "=", $data->product_id ],
                        [ "CdEmpresa", "i", "=", $post->company_id ],
                        [ "IdPreco", "s", "in", $allowed  ]
                    ],
                    "order" => "IdPreco"
                ]);

                $prices = [];
                foreach( $history as $price ){
                    $hp = Model::get($dafel,(Object)[
                        "top" => 1,
                        "tables" => [ "HistoricoPreco HP (NoLock)", "Preco P (NoLock)" ],
                        "fields" => [
                            "product_id=HP.IdProduto",
                            "price_id=HP.IdPreco",
                            "price_value=CAST(HP.VlPreco AS FLOAT)",
                            "price_code=P.CdPreco",
                            "price_name=P.NmPreco",
                            "price_date=CONVERT(VARCHAR(10),HP.DtReferencia,126)"
                        ],
                        "filters" => [
                            [ "HP.IdPreco = P.IdPreco" ],
                            [ "HP.IdProduto", "s", "=", $data->product_id ],
                            [ "HP.IdPreco", "s", "=", $price->price_id ],
                            [ "HP.CdEmpresa", "i", "=", $post->company_id ]
                        ],
                        "order" => "HP.DtReferencia DESC"
                    ]);
                    $hp->price_value = (float)number_format($hp->price_value,2,".","");
                    $prices[] = $hp;
                }

                $this->prices = $prices;
            }

            if( @$gets["get_product_cost"] || @$_POST["get_product_cost"] )
            {
                $this->cost = (Object)[
                    "cost_value" => 0,
                    "cost_date" => NULL
                ];

                $cost = Model::get($dafel,(Object)[
                    "top" => 1,
                    "tables" => [ "HistoricoCusto" ],
                    "fields" => [
                        "VlCusto",
                        "DtReferencia=CONVERT(VARCHAR(10),DtReferencia,126)"
                    ],
                    "filters" => [
                        [ "IdProduto", "s", "=", $data->product_id ],
                        [ "CdEmpresa", "i", "=", $post->company_id ]
                    ],
                    "order" => "DtReferencia DESC"
                ]);

                if( @$cost ){
                    $this->cost->cost_value = (float)number_format($cost->VlCusto,2,".","");
                    $this->cost->cost_date = $cost->DtReferencia;
                }
            }
        }
    }

?>