<?php

    class Produto
    {
        public $IdProduto;
        public $CdProduto;
        public $CdEAN;
        public $CdFornecedor;
        public $NmProduto;
        public $CdClassificacao;
        public $StAtivoVenda;
        public $AlRepasseDuplicata;
        public $CdCFOP;
        public $CdCFOPEntreUF;
        public $CdCFOPDevolucaoIntraUF;
        public $CdCFOPDevolucaoEntreUF;
        public $VlPesoLiquido;
        public $VlPesoBruto;
        public $AlDesconto;
        public $IdProdutoOrigem;
        public $FtConversaoUnidade;

        public function __construct( $data, $gets=[] )
        {
            GLOBAL $dafel, $login;

            $this->IdProduto = $data->IdProduto;
            $this->CdEAN = @$data->CdEAN ? $data->CdEAN : NULL;
            $this->CdProduto = $data->CdChamada;
            $this->CdFornecedor = @$data->CdFornecedor ? $data->CdFornecedor : NULL;
            $this->NmProduto = $data->NmProduto;
            $this->CdClassificacao = @$data->CdClassificacao ? $data->CdClassificacao : NULL;
            $this->StAtivoVenda = $data->StAtivoVenda;
            $this->AlRepasseDuplicata = @$data->AlRepasseDuplicata ? (float)$data->AlRepasseDuplicata : NULL;
            $this->CdCFOP = @$data->CdCFOP ? $data->CdCFOP : NULL;
            $this->CdCFOPEntreUF = @$data->CdCFOPEntreUF ? $data->CdCFOPEntreUF : NULL;
            $this->CdCFOPDevolucaoIntraUF = @$data->CdCFOPDevolucaoIntraUF ? $data->CdCFOPDevolucaoIntraUF : NULL;
            $this->CdCFOPDevolucaoEntreUF = @$data->CdCFOPDevolucaoEntreUF ? $data->CdCFOPDevolucaoEntreUF : NULL;
            $this->VlPesoLiquido = @$data->VlPesoLiquido ? (float)$data->VlPesoLiquido : NULL;
            $this->VlPesoBruto = @$data->VlPesoBruto ? (float)$data->VlPesoBruto : NULL;
            $this->AlDesconto = @$data->AlDesconto ? (float)$data->AlDesconto : NULL;
            $this->IdProdutoOrigem = @$data->IdProdutoOrigem ? $data->IdProdutoOrigem : NULL;
            $this->FtConversaoUnidade = @$data->FtConversaoUnidade ? (float)$data->FtConversaoUnidade : NULL;

            if( @$gets["get_unit"] || @$_POST["get_unit"] )
            {
                $this->Unidade = Model::get($dafel,(Object)[
                    "class" => "Unidade",
                    "tables" => [ "Unidade (NoLock)" ],
                    "fields" => [ "IdUnidade", "CdChamada", "CdSigla", "NmUnidade", "TpUnidade" ],
                    "filters" => [[ "IdUnidade", "s", "=", $data->IdUnidade ]]
                ]);
            }

            if( @$gets["get_product_stock"] || @$_POST["get_product_stock"] )
            {
                $this->Estoque = Model::get($dafel,(Object)[
                    "top" => 1,
                    "tables" => [ "EstoqueEmpresa (NoLock)" ],
                    "fields" => [ "IdProduto", "CdEmpresa", "QtEstoque", "DtReferencia=CONVERT(VARCHAR(10),DtReferencia,126)" ],
                    "filters" => [
                        [ "IdProduto", "s", "=", @$data->IdProdutoOrigem ? $data->IdProdutoOrigem : $data->IdProduto ],
                        [ "CdEmpresa", "i", "=", $data->CdEmpresa ]
                    ],
                    "order" => "DtReferencia DESC"
                ]);
                if( @$data->IdProdutoOrigem && @$this->Estoque ){
                    $this->Estoque->QtEstoque = (float)$this->Estoque->QtEstoque * (float)$data->FtConversaoUnidade;
                }
            }

            if( @$gets["get_product_prices"] || @$_POST["get_product_prices"] )
            {
                $allowed = [];
                foreach( $login->prices as $price ){
                    $allowed[] = $price->price_id;
                }

                $prices = Model::getList($dafel,(Object)[
                    "distinct" => 1,
                    "tables" => [ "HistoricoPreco (NoLock)" ],
                    "fields" => [ "IdPreco" ],
                    "filters" => [
                        [ "ISNULL(VlPreco,0)", "d", ">", 0 ],
                        [ "IdProduto", "s", "=", $data->IdProduto ],
                        [ "CdEmpresa", "i", "=", $data->CdEmpresa ],
                        [ "IdPreco", "s", "in", $allowed  ]
                    ],
                    "order" => "IdPreco"
                ]);

                $ret=[];
                foreach( $prices as $price ){
                    $hp = Model::get($dafel,(Object)[
                        "top" => 1,
                        "tables" => [ "HistoricoPreco HP (NoLock)", "Preco P (NoLock)" ],
                        "fields" => [
                            "HP.IdProduto",
                            "HP.IdPreco",
                            "HP.VlPreco",
                            "P.CdPreco",
                            "P.NmPreco",
                            "DtReferencia=CONVERT(VARCHAR(10),HP.DtReferencia,126)"
                        ],
                        "filters" => [
                            [ "HP.IdPreco = P.IdPreco" ],
                            [ "HP.IdProduto", "s", "=", $data->IdProduto ],
                            [ "HP.IdPreco", "s", "=", $price->IdPreco ],
                            [ "HP.CdEmpresa", "i", "=", $data->CdEmpresa ]
                        ],
                        "order" => "HP.DtReferencia DESC"
                    ]);
                    $hp->VlPreco = (float)number_format($hp->VlPreco,2,".","");
                    $ret[] = $hp;
                }

                $this->Precos = $ret;
            }
        }
    }

?>