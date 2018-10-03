<?php

    class Budget
    {
        public $budget_id;
        public $company_id;
        public $user_id;
        public $client_id;
        public $seller_id;
        public $address_code;
        public $term_id;
        public $external_id;
        public $external_type;
        public $external_code;
        public $document_id;
        public $document_type;
        public $document_code;
        public $document_canceled;
        public $budget_value;
        public $budget_aliquot_discount;
        public $budget_value_discount;
        public $budget_value_addition;
        public $budget_value_icms;
        public $budget_value_st;
        public $budget_value_total;
        public $budget_note;
        public $budget_note_document;
        public $budget_credit;
        public $budget_delivery;
        public $budget_status;
        public $budget_origin;
        public $budget_trash;
        public $budget_update;
        public $budget_date;
        public $authorization = [];
        public $credit;

        public function __construct( $data, $gets )
        {
            $this->budget_id = (int)$data->budget_id;
            $this->company_id = (int)$data->company_id;
            $this->user_id = $data->user_id;
            $this->client_id = $data->client_id;
            $this->seller_id = $data->seller_id;
            $this->budget_code = substr("00000{$data->budget_id}",-6);
            $this->address_code = $data->address_code;
            $this->term_id = @$data->term_id ? $data->term_id : NULL;
            $this->external_id = @$data->external_id ? $data->external_id : NULL;
            $this->external_type = @$data->external_type ? $data->external_type : NULL;
            $this->external_code = @$data->external_code ? $data->external_code : NULL;
            $this->document_id = @$data->document_id ? $data->document_id : NULL;
            $this->document_type = @$data->document_type ? $data->document_type : NULL;
            $this->document_code = @$data->document_code ? $data->document_code : NULL;
            $this->document_canceled = @$data->document_canceled ? $data->document_canceled : NULL;
            $this->budget_value = (float)$data->budget_value;
            $this->budget_aliquot_discount = (float)$data->budget_aliquot_discount;
            $this->budget_value_discount = (float)$data->budget_value_discount;
            $this->budget_value_addition = (float)$data->budget_value_addition;
            $this->budget_value_icms = (float)$data->budget_value_icms;
            $this->budget_value_st = (float)$data->budget_value_st;
            $this->budget_value_total = (float)$data->budget_value_total;
            $this->budget_note = @$data->budget_note ? $data->budget_note : NULL;
            $this->budget_note_document = @$data->budget_note_document ? $data->budget_note_document : NULL;
            $this->budget_credit = $data->budget_credit;
            $this->budget_delivery = $data->budget_delivery;
            $this->budget_status = $data->budget_status;
            $this->budget_origin = $data->budget_origin;
            $this->budget_trash = $data->budget_trash;
            $this->budget_delivery_date = @$data->budget_delivery_date ? $data->budget_delivery_date : NULL;
            $this->budget_update = @$data->budget_update ? $data->budget_update : NULL;
            $this->budget_date = $data->budget_date;
            $this->credit = (Object)[
                "value" => 0,
                "payable" => []
            ];

            GLOBAL $conn, $commercial, $dafel, $config;

            if( @$_POST["get_budget_seller"] || @$gets["get_budget_seller"] ){
                $this->seller = Model::get($dafel,(Object)[
                    "tables" => [ "Pessoa" ],
                    "fields" => [
                        "seller_id=IdPessoa",
                        "seller_code=CdChamada",
                        "seller_name=NmPessoa",
                        "seller_short_name=ISNULL(NmCurto,NULL)"
                    ],
                    "filters" => [[ "IdPessoa", "s", "=", $data->seller_id ]]
                ]);
                $this->seller->image = getImage((Object)[
                    "image_id" => $data->seller_id,
                    "image_dir" => "person"
                ]);
            }

            if( @$_POST["get_budget_items"] || @$gets["get_budget_items"] ){
                $this->items = [];
                $items = Model::getList($commercial,(Object)[
                    "join" => 1,
                    "tables" => [
                        "{$conn->commercial->table}.dbo.BudgetItem BI (NoLock)",
                        "INNER JOIN {$conn->dafel->table}.dbo.Produto P (NoLock) ON(P.IdProduto = BI.product_id)",
                        "INNER JOIN {$conn->dafel->table}.dbo.CodigoProduto CP (NoLock) ON(P.IdProduto = CP.IdProduto AND CP.StCodigoPrincipal = 'S')",
                        "INNER JOIN {$conn->dafel->table}.dbo.Unidade U (NoLock) ON(P.IdUnidade = U.IdUnidade)",
                    ],
                    "fields" => [
                        "BI.budget_item_id",
                        "BI.product_id",
                        "BI.price_id",
                        "BI.external_id",
                        "BI.budget_item_quantity",
                        "budget_item_value=CAST(BI.budget_item_value AS FLOAT)",
                        "budget_item_value_discount=CAST(BI.budget_item_value_discount AS FLOAT)",
                        "budget_item_value_total=CAST(BI.budget_item_value_total AS FLOAT)",
                        "budget_item_value_unitary=CAST(BI.budget_item_value_unitary AS FLOAT)",
                        "budget_item_value_icms=CAST(BI.budget_item_value_icms AS FLOAT)",
                        "budget_item_value_st=CAST(BI.budget_item_value_st AS FLOAT)",
                        "budget_item_aliquot_discount=CAST(BI.budget_item_aliquot_discount AS FLOAT)",
                        "ncm_id=P.IdClassificacaoFiscal",
                        "icms_id=P.IdCalculoICMS",
                        "product_code=CP.CdChamada",
                        "product_name=P.NmProduto",
                        "product_discount=ISNULL(P.AlDesconto,0)",
                        "product_commission=ISNULL(P.AlRepasseDuplicata,0)",
                        "product_weight_net=ISNULL(P.VlPesoLiquido,0)",
                        "product_weight_gross=ISNULL(P.VlPesoBruto,0)",
                        "product_cfop=P.CdCFOP",
                        "product_cfop_extra=P.CdCFOPEntreUF",
                        "unit_code=U.CdSigla",
                        "unit_type=U.TpUnidade"
                    ],
                    "filters" => [[ "BI.budget_id", "i", "=", $data->budget_id ]]
                ]);
                if( sizeof($items) ){
                    foreach( $items as $item ){
                        $item->budget_item_id = (int)$item->budget_item_id;
                        $item->product_discount = (float)$item->product_discount;
                        $item->budget_item_value = (float)$item->budget_item_value;
                        $item->product_commission = (float)$item->product_commission;
                        $item->product_weight_net = (float)$item->product_weight_net;
                        $item->budget_item_value_st = (float)$item->budget_item_value_st;
                        $item->product_weight_gross = (float)$item->product_weight_gross;
                        $item->budget_item_quantity = (float)$item->budget_item_quantity;
                        $item->budget_item_value_icms = (float)$item->budget_item_value_icms;
                        $item->external_id = @$item->external_id ? $item->external_id : NULL;
                        $item->budget_item_value_total = (float)$item->budget_item_value_total;
                        $item->product_cfop = @$item->product_cfop ? $item->product_cfop : NULL;
                        $item->budget_item_value_unitary = (float)$item->budget_item_value_unitary;
                        $item->budget_item_value_discount = (float)$item->budget_item_value_discount;
                        $item->budget_item_aliquot_discount = (float)$item->budget_item_aliquot_discount;
                        $item->product_cfop_extra = @$item->product_cfop_extra ? $item->product_cfop_extra : NULL;
                        if( @$gets["get_product_stock"] || @$_POST["get_product_stock"] ){
                            $item->stock_value = 0;
                            $item->stock_date = NULL;
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
                                    [ "P.IdProduto", "s", "=", $item->product_id ],
                                    [ "EE.CdEmpresa", "i", "=", $data->company_id ]
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
                                $item->stock_value = $stock->stock_value;
                                $item->stock_date = $stock->stock_date;
                            }
                        }
                    }
                    $this->items = $items;
                }
            }

            if( @$_POST["get_budget_person"] || @$gets["get_budget_person"] ){
                $this->person = Model::get($dafel,(Object)[
                    "class" => "Person",
                    "join" => 1,
                    "tables" => [
                        "Pessoa P",
                        "INNER JOIN PessoaCategoria PC ON(PC.IdPessoa = P.IdPessoa)",
                        "LEFT JOIN PessoaComplementar PCM ON(PCM.IdPessoa = P.IdPessoa)",
                    ],
                    "fields" => [
                        "P.IdPessoa",
                        "P.CdChamada",
                        "P.NmPessoa",
                        "P.NmCurto",
                        "P.CdCPF_CGC",
                        "P.TpPessoa",
                        "StATivo=ISNULL(PC.StATivo,'N')",
                        "DtNascimento=CONVERT(VARCHAR(10),PCM.DtNascimento,126)",
                        "PCM.TpSexo",
                        "PCM.VlLimiteCredito"
                    ],
                    "filters" => [
                        [ "PC.IdCategoria", "s", "=", $config->person->client_category_id ],
                        [ "P.IdPessoa", "s", "=", $data->client_id ]
                    ]
                ]);
            }

            if( @$_POST["get_budget_payments"] || @$gets["get_budget_payments"] ){
                $this->payments = [];
                $payments = Model::getList($commercial,(Object)[
                    "join" => 1,
                    "tables" => [
                        "{$conn->commercial->table}.dbo.BudgetPayment BP (NoLock)",
                        "INNER JOIN {$conn->dafel->table}.dbo.FormaPagamento FP (NoLock) ON(FP.IdFormaPagamento = BP.modality_id)",
                        "LEFT JOIN {$conn->dafel->table}.dbo.FormaPagamentoItem FPI ON (FPI.IdFormaPagamento = FP.IdFormaPagamento AND FPI.CdEmpresa = $data->company_id)"
                    ],
                    "fields" => [
                        "BP.budget_payment_id",
                        "BP.external_id",
                        "BP.modality_id",
                        "BP.bank_id",
                        "BP.agency_id",
                        "BP.agency_code",
                        "BP.check_number",
                        "budget_payment_value=CAST(BP.budget_payment_value AS FLOAT)",
                        "BP.budget_payment_installment",
                        "BP.budget_payment_entry",
                        "BP.budget_payment_credit",
                        "budget_payment_deadline=CONVERT(VARCHAR(10),BP.budget_payment_deadline,126)",
                        "nature_id=FP.IdNaturezaLancamento",
                        "modality_type=FP.TpFormaPagamento",
                        "modality_description=FP.DsFormaPagamento",
                        "modality_installment=COUNT(FPI.IdFormaPagamentoItem)"
                    ],
                    "filters" => [[ "BP.budget_id", "i", "=", $data->budget_id ]],
                    "group" => "BP.budget_payment_id,BP.external_id,BP.modality_id,BP.bank_id,BP.agency_id,BP.agency_code,BP.check_number,BP.budget_payment_value,BP.budget_payment_installment,BP.budget_payment_entry,BP.budget_payment_credit,BP.budget_payment_deadline,FP.IdNaturezaLancamento,FP.TpFormaPagamento,FP.DsFormaPagamento"
                ]);
                if( sizeof($payments) ){
                    foreach( $payments as $payment ){
                        $payment->budget_payment_id = (int)$payment->budget_payment_id;
                        $payment->budget_payment_value = (float)$payment->budget_payment_value;
                        $payment->budget_payment_installment = (int)$payment->budget_payment_installment;
                        $payment->external_id = @$payment->external_id ? $payment->external_id : NULL;
                        $payment->modality_delay = ( $payment->modality_type == "A" ? 30 : 1 );
                        $payment->modality_entry = ( $payment->modality_type == "D" ? "Y" : "N" );
                        $payment->modality_installment = @(int)$payment->modality_installment ? (int)$payment->modality_installment : ( $payment->modality_type != "A" ? 1 : 0 );
                        $payment->image = getImage((Object)[
                            "image_id" => $payment->modality_id,
                            "image_dir" => "modality"
                        ]);
                        if( !@$payment->image ){
                            $payment->image = getImage((Object)[
                                "image_id" => $payment->modality_type,
                                "image_dir" => "modality/type"
                            ]);
                        }
                    }
                    $this->payments = $payments;
                }
            }

            if( @$_POST["get_budget_address"] || @$gets["get_budget_address"] ){
                $this->address = Model::get($dafel,(Object)[
                    "join" => 1,
                    "class" => "PersonAddress",
                    "tables" => [
                        "PessoaEndereco PE",
                        "INNER JOIN Cidade C ON(C.IdCidade = PE.IdCidade)",
                        "INNER JOIN Bairro B ON(B.IdBairro = PE.IdBairro)"
                    ],
                    "fields" => [
                        "PE.IdPessoa",
                        "PE.IdUF",
                        "PE.IdCidade",
                        "PE.IdBairro",
                        "C.NmCidade",
                        "B.NmBairro",
                        "PE.CdCEP",
                        "PE.CdEndereco",
                        "StATivo=ISNULL(StATivo,'N')",
                        "StEnderecoPrincipal=ISNULL(PE.StEnderecoPrincipal,'N')",
                        "StEnderecoEntrega=ISNULL(PE.StEnderecoEntrega,'N')",
                        "PE.NrInscricaoEstadual",
                        "PE.TpLogradouro",
                        "PE.NmLogradouro",
                        "PE.NrLogradouro",
                        "PE.DsComplemento",
                        "PE.DsObservacao"
                    ],
                    "filters" => [
                        [ "PE.IdPessoa", "s", "=", $data->client_id ],
                        [ "PE.CdEndereco", "s", "=", $data->address_code ],
                    ]
                ]);
            }

            if( @$_POST["get_budget_company"] || @$gets["get_budget_company"] ){
                $company = Model::get($dafel,(Object)[
                    "tables" => [
                        "EmpresaERP (NoLock)",
                    ],
                    "fields" => [
                        "CdEmpresa",
                        "NmEmpresa",
                        "NmEmpresaCurto",
                        "NrCGC",
                        "NrTelefone",
                        "NrCEP",
                        "TpLogradouro",
                        "DsEndereco",
                        "NrLogradouro",
                        "NmBairro",
                        "NmCidade",
                        "CdUF"
                    ],
                    "filters" => [[ "CdEmpresa", "i", "=", $data->company_id ]]
                ]);
                $this->company = (Object)[
                    "company_id" => $company->CdEmpresa,
                    "company_name" => $company->NmEmpresa,
                    "company_short_name" => $company->NmEmpresaCurto,
                    "company_cnpj" => $company->NrCGC,
                    "company_phone" => $company->NrTelefone,
                    "address" => (Object)[
                        "address_cep" => $company->NrCEP,
                        "address_type" => $company->TpLogradouro,
                        "address_public_place" => $company->DsEndereco,
                        "address_number" => $company->NrLogradouro,
                        "district_name" => $company->NmBairro,
                        "city_name" => $company->NmCidade,
                        "uf_id" => $company->CdUF
                    ]
                ];
            }

            if( @$data->term_id && (@$_POST["get_budget_term"] || @$gets["get_budget_term"] )){
                $this->term = Model::get($dafel,(Object)[
                    "class" => "Term",
                    "tables" => [ "Prazo (NoLock)" ],
                    "fields" => [
                        "IdPrazo",
                        "CdChamada",
                        "DsPrazo",
                        "NrParcelas",
                        "NrDias1aParcela",
                        "NrDiasEntrada",
                        "NrDiasEntreParcelas",
                        "StAtivo",
                        "AlEntrada"
                    ],
                    "filters" => [[ "IdPrazo", "s", "=", $data->term_id ]]
                ]);
            }
        }

        public static function taxes()
        {
            GLOBAL $dafel, $budget, $operation;

            if( $budget->export == "dav" ){
                return;
            }

            $params = (Object)[
                "uf_id" => "RJ"
            ];

            foreach( $budget->items as $key => $item ){

                $item = (Object)$item;

                $cfop = Model::get($dafel,(Object)[
                    "tables" => [ "Produto_Empresa_CFOP (NoLock)" ],
                    "fields" => [ "IdCFOPEquivalente" ],
                    "filters" => [
                        [ "IdProduto", "s", "=", $item->product_id ],
                        [ "CdEmpresa", "i", "=", $budget->company_id ],
                        [ "TpOperacao", "s", "=", "V" ],
                        [ "IdUF", "s", "=", $budget->address_uf_id ]
                    ]
                ]);

                if( $params->uf_id == $budget->address_uf_id ){
                    $cfop_start = "5";
                    $cfop_produto = @$item->product_cfop ? $item->product_cfop : NULL;
                    $cfop_operacao = $operation->IdCFOPIntraUF;
                } else {
                    $cfop_start = "6";
                    $cfop_produto = @$item->product_cfop_extra ? $item->product_cfop_extra : NULL;
                    $cfop_operacao = $operation->IdCFOPEntreUF;
                }

                if (@$cfop->IdCFOPEquivalente)
                    $IdCFOP = "{$cfop_start}.{$cfop->IdCFOPEquivalente}";
                else
                    $IdCFOP = @$cfop_produto ? "{$cfop_start}.{$cfop_produto}" : $cfop_operacao;

                $fiscal = Model::get($dafel,(Object)[
                    "tables" => ["CalculoICMS_UF (NoLock)"],
                    "fields" => [
                        "CdSituacaoTributaria",
                        "StCalculaSubstTributariaICMS",
                        "AlICMS",
                        "AlFCP"
                    ],
                    "filters" => [
                        [
                            ["CdEmpresa IS NULL"],
                            ["CdEmpresa", "i", "=", $budget->company_id]
                        ],
                        ["IdUFDestino", "s", "=", $budget->address_uf_id],
                        ["IdUF", "s", "=", $params->uf_id],
                        ["IdCFOP", "s", "=", $IdCFOP],
                        ["IdCalculoICMS", "s", "=", $item->icms_id]
                    ],
                    "order" => "CdEmpresa DESC"
                ]);

                $AlICMS = 0;
                $VlICMS = 0;
                $VlICMSSubstTributaria = 0;
                $VlBaseFCPSubstTributaria = 0;
                $AlFCP = 0;
                $AlFCPSubstTributaria = 0;
                $VlFCP = 0;
                $VlFCPSubstTributaria = 0;
                $VlBaseICMSST = 0;

                if( @$operation->StCalculaICMS && $operation->StCalculaICMS == "S" && @$fiscal->AlICMS ){
                    $AlFCP = @$fiscal->AlFCP ? $fiscal->AlFCP : 0;
                    $AlICMS = $fiscal->AlICMS - $AlFCP;
                    $VlICMS = number_format((($AlICMS/100) * $item->budget_item_value_total), 2, '.', '');
                    $VlFCP = number_format((($AlFCP/100) * $item->budget_item_value_total), 2, '.', '');
                }

                if( @$item->ncm_id && @$operation->StCalculaSubstTributariaICMS && $operation->StCalculaSubstTributariaICMS == "S" && $fiscal->StCalculaSubstTributariaICMS == "S" ){

                    $ncm = Model::get($dafel,(Object)[
                        "tables" => ["ClassificacaoFiscalItem (NoLock)"],
                        "fields" => ["IdClassificacaoFiscal", "AlLucro", "AlICMSInterna", "AlMVASTInterna", "AlICMSSTInterna"],
                        "filters" => [
                            ["IdClassificacaoFiscal", "s", "=", $item->ncm_id],
                            ["CdEmpresa", "i", "=", $budget->company_id]
                        ]
                    ]);

                    if( @$ncm->IdClassificacaoFiscal ){

                        $MVA = @$ncm->AlLucro ? $ncm->AlLucro : ( @$ncm->AlMVASTInterna ? $ncm->AlMVASTInterna : 0 );
                        $AlICMSInterna = @$ncm->AlICMSInterna ? $ncm->AlICMSInterna : ( @$ncm->AlICMSSTInterna ? $ncm->AlICMSSTInterna : 0 );
                        if( !@$MVA || !@$AlICMSInterna ){
                            $VlICMSSubstTributaria = 0;
                            $VlFCPSubstTributaria = 0;
                        } else {
                            $VlBaseICMSST = $item->budget_item_value_total * (1+($MVA/100));
                            $VlICMSSubstTributaria = ($VlBaseICMSST * ($AlICMSInterna/100)) - $VlICMS;
                            $VlBaseFCPSubstTributaria = $VlBaseICMSST;
                            $AlFCPSubstTributaria = $AlFCP;
                            $VlFCPSubstTributaria = ($AlFCP/100) * $VlBaseFCPSubstTributaria;
                            if( $VlFCP < 0 ) $VlFCP = 0;
                            $VlFCPSubstTributaria -= $VlFCP;
                            if( $VlFCPSubstTributaria < 0 ) $VlFCPSubstTributaria = 0;
                            $VlICMSSubstTributaria -= $VlFCP;
                            $VlICMSSubstTributaria -= $VlFCPSubstTributaria;
                        }
                    }
                }

                $budget->items[$key]["AlICMS"] = $AlICMS;
                $budget->items[$key]["AlFCP"] = $AlFCP;
                $budget->items[$key]["VlICMS"] = $VlICMS;
                $budget->items[$key]["VlFCP"] = $VlFCP;
                $budget->items[$key]["VlBaseFCP"] = $item->budget_item_value_total;
                $budget->items[$key]["CdSituacaoTributaria"] = $fiscal->CdSituacaoTributaria;
                $budget->items[$key]["IdCFOP"] = $IdCFOP;
                $budget->items[$key]["VlICMSSubstTributaria"] = (float)number_format($VlICMSSubstTributaria,2,".","");
                $budget->items[$key]["VlBaseFCPSubstTributaria"] = (float)number_format($VlBaseFCPSubstTributaria,2,".","");
                $budget->items[$key]["AlFCPSubstTributaria"] = (float)number_format($AlFCPSubstTributaria,2,".","");
                $budget->items[$key]["VlFCPSubstTributaria"] = (float)number_format($VlFCPSubstTributaria,2,".","");
                $budget->items[$key]["VlBaseICMSST"] = (float)number_format($VlBaseICMSST,2,".","");

                $budget->items[$key]["budget_item_value_icms"] = $VlICMS + $VlFCP;
                $budget->items[$key]["budget_item_value_st"] = $VlICMSSubstTributaria + $VlFCPSubstTributaria;

                $budget->budget_value_icms += $VlICMS + $VlFCP;
                $budget->budget_value_st += $VlICMSSubstTributaria + $VlFCPSubstTributaria;
            }
        }

        public static function export()
        {
            GLOBAL $budget;

            if( $budget->export == "dav" ){
                if( @$budget->external_id ){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "O Dav não poderá ser editado."
                    ]);
                } else {
                    self::insertDav();
                }
            }
            if( $budget->export == "order" ){
                if( @$budget->external_id ){
                    self::editOrder();
                } else {
                    self::insertOrder();
                }
            }
        }

        public static function insertDav()
        {
            GLOBAL $dafel, $login, $config, $budget, $seller, $operation;
            
            $date = date("Y-m-d");
            $dateTime = date("Y-m-d H:i:s");

            $dav = (Object)[
                "IdDocumentoAuxVenda" => Model::nextCode($dafel,(Object)[
                    "table" => "DocumentoAuxVenda",
                    "field" => "IdDocumentoAuxVenda",
                    "increment" => "S",
                    "base36encode" => 1
                ]),
                "NrDocumentoAuxVenda" => Model::nextCode($dafel,(Object)[
                    "table" => NULL,
                    "field" => "NrDAV_Emp_{$budget->company_id}",
                    "increment" => "S"
                ])
            ];

            $dav->NrDocumentoAuxVenda = substr( substr( "00{$budget->company_id}", -3 ) . "0000000", 0, 10 - strlen($dav->NrDocumentoAuxVenda) ) . $dav->NrDocumentoAuxVenda;

            $budget->external_id = $dav->IdDocumentoAuxVenda;
            $budget->external_type = "D";
            $budget->external_code = $dav->NrDocumentoAuxVenda;

            $davParams = (Object)[
                "status" => "O",
                "type" => "D",
                "view" => "L",
                "commission" => "D"
            ];

            Model::insert($dafel,(Object)[
                "table" => "DocumentoAuxVenda",
                "fields" => [
                    [ "IdDocumentoAuxVenda", "s", $dav->IdDocumentoAuxVenda ],
                    [ "CdEmpresa", "i", $budget->company_id ],
                    [ "IdPessoa", "s", $budget->client_id ],
                    [ "NrDocumentoAuxVenda", "s", $dav->NrDocumentoAuxVenda ],
                    [ "DtEmissao", "s", $date ],
                    [ "StDocumentoAuxVenda", "s", $davParams->status ],
                    [ "IdUsuario", "s", $login->external_id ],
                    [ "IdPrazo", "s", @$budget->term_id ? $budget->term_id  : NULL ],
                    [ "AlDesconto", "d", number_format($budget->budget_aliquot_discount, 4, '.', '' ) ],
                    [ "VlDesconto", "d", number_format($budget->budget_value_discount, 2, '.', '' ) ],
                    [ "AlAcrescimo", "d", number_format((($budget->budget_value_addition*100)/$budget->budget_value_total), 4, '.', '') ],
                    [ "VlAcrescimo", "d", number_format( $budget->budget_value_addition, 2, '.', '' ) ],
                    [ "VlDocumento", "d", number_format( $budget->budget_value, 2, '.', '' ) ],
                    [ "TpDocumentoAuxVenda", "s", $davParams->type ],
                    [ "TpVisualizacao", "s", $davParams->view ],
                    [ "DtReferenciaPagamento", "s", $date ],
                    [ "TpRepasse", "s", $davParams->commission ],
                    [ "StCriacaoDAVConcluida", "i", "1" ],
                    [ "DsObservacaoDAV", "s", @$budget->budget_note ? $budget->budget_note  : NULL ],
                    [ "DsObservacaoDocumento", "s", @$budget->budget_doc_note ? $budget->budget_note_document : NULL ],
                    [ "TpIndAtendimentoPresencial", "i", "1" ]
                ]
            ]);

            foreach( $budget->items as $key => $item ){

                $davItem = (Object)[
                    "IdDocumentoAuxVendaItem" => Model::nextCode($dafel,(Object)[
                        "table" => "DocumentoAuxVendaItem",
                        "field" => "IdDocumentoAuxVendaItem",
                        "increment" => "S",
                        "base36encode" => 1
                    ])
                ];

                $item = (Object)$item;
                $budget->items[$key]["external_id"] = $davItem->IdDocumentoAuxVendaItem;

                Model::insert($dafel,(Object)[
                    "table" => "DocumentoAuxVendaItem",
                    "fields" => [
                        [ "IdDocumentoAuxVendaItem", "s", $davItem->IdDocumentoAuxVendaItem ],
                        [ "IdDocumentoAuxVenda", "s", $dav->IdDocumentoAuxVenda ],
                        [ "IdProduto", "s", $item->product_id],
                        [ "QtItem", "d", $item->budget_item_quantity],
                        [ "VlUnitario", "d", number_format($item->budget_item_value_unitary, 2, '.', '')],
                        [ "VlItem", "d", number_format($item->budget_item_value_total, 2, '.', '')],
                        [ "AlDescontoItem", "d", number_format($item->budget_item_aliquot_discount, 2, '.', '')],
                        [ "VlDescontoItem", "d", number_format($item->budget_item_value_discount, 2, '.', '')],
                        [ "VlAcrescimoRateado", "d", number_format( (($item->budget_item_value_total/$budget->budget_value_total)*$budget->budget_value_addition), 2, '.', '')],
                        [ "IdPreco", "s", $item->price_id],
                        [ "StVendaMostruario", "s", "N" ],
                        [ "NrSequencialItem", "i", $key+1 ],
                        [ "TpEntrega", "s", "I" ],
                        [ "StItemCancelado", "s", "0" ],
                        [ "DtCadastro", "s", $dateTime ],
                        [ "TpDescontoItem", "s", $item->budget_item_value_discount ? "V" : NULL ],
                        [ "VlUnitarioTabelaPreco", "d", $item->budget_item_value_unitary ],
                        [ "VlJurosParcelamentoItem", "d", 0 ],
                        [ "VlDescontoRegraComercItem", "d", 0 ]
                    ]
                ]);

                $commission_al = NULL;
                $commission_duplicate = NULL;
                $commission_billing = NULL;
                $commission_duplicate_budget = NULL;
                $commission_billing_budget = NULL;
                if( @$seller && $seller->StComissao == "S" ){
                    if( $seller->TpComissao == "D" ){
                        $commission_duplicate = @$seller->AlComissaoDuplicata ? $seller->AlComissaoDuplicata : $item->product_commission;
                    }
                }

                Model::insert($dafel,(Object)[
                    "table" => "DocumentoAuxVendaItemRepasse",
                    "fields" => [
                        [ "IdDocumentoAuxVendaItem", "s", $davItem->IdDocumentoAuxVendaItem ],
                        [ "IdPessoa", "s", $budget->seller_id ],
                        [ "IdCategoria", "s", $config->person->seller_category_id ],
                        [ "AlRepasseDuplicata", "d", @$commission_duplicate ? $commission_duplicate : NULL ],
                        [ "AlRepasseFaturamento", "d", @$commission_billing ? $commission_billing : NULL ],
                        [ "VlBaseRepasse", "d", $item->budget_item_value_total ]
                    ]
                ]);
            }

            foreach( $budget->payments as $key => $payment ){

                $davPayment = (Object)[
                    "IdDocumentoAuxVendaPagamento" => Model::nextCode($dafel,(Object)[
                        "table" => "DocumentoAuxVendaPagamento",
                        "field" => "IdDocumentoAuxVendaPagamento",
                        "increment" => "S",
                        "base36encode" => 1
                    ])
                ];

                $payment = (Object)$payment;
                $budget->payments[$key]["external_id"] = $davPayment->IdDocumentoAuxVendaPagamento;

                $covenant = NULL;
                if( $payment->modality_type == "A" ){
                    $covenant = Model::get($dafel,(Object)[
                        "tables" => [ "FormaPagamentoItem (NoLock)" ],
                        "fields" => [
                            "NrDiasPrimeiraParcelaVenda",
                            "NrParcelasRecebimento",
                            "NrDiasRecebimento",
                            "NrDiasIntervalo",
                            "AlConvenio"
                        ],
                        "filters" => [
                            [ "IdFormaPagamento", "s", "=", $payment->modality_id ],
                            [ "CdEmpresa", "i", "=", $budget->company_id ],
                            [ "NrParcelas", "i", "=", $payment->budget_payment_installment ]
                        ]
                    ]);
                }

                $days = 0;
                if( @$covenant && @$covenant->NrDiasRecebimento ){
                    $covenant->NrDiasRecebimento;
                } else {
                    $days = countDays( date("Y-m-d"), $payment->budget_payment_deadline ) - 1;
                }

                $nature = NULL;
                if( @$payment->nature_id ){
                    $nature = Model::get($dafel,(Object)[
                        "tables" => [ "NaturezaLancamento (NoLock)" ],
                        "fields" => [
                            "IdNaturezaLancamento",
                            "CdChamada",
                            "NmNaturezaLancamento",
                            "StBaixaInclusao",
                            "IdTipoBaixa"
                        ],
                        "filters" => [[ "IdNaturezaLancamento", "s", "=", $payment->nature_id ]]
                    ]);
                }

                $paymentAliquot = number_format(((100 * $payment->budget_payment_value) / $budget->budget_value), 6, '.', '');
                
                Model::insert($dafel,(Object)[
                    "table" => "DocumentoAuxVendaPagamento",
                    "fields" => [
                        [ "IdDocumentoAuxVendaPagamento", "s", $davPayment->IdDocumentoAuxVendaPagamento ],
                        [ "IdDocumentoAuxVenda", "s", $dav->IdDocumentoAuxVenda ],
                        [ "IdFormaPagamento", "s", $payment->modality_id ],
                        [ "NrDias", "i", $days > 0 ? $days : NULL ],
                        [ "IdTipoBaixa", "s", @$nature->IdTipoBaixa ? $nature->IdTipoBaixa : NULL ],
                        [ "AlParcela", "d", $paymentAliquot ],
                        [ "IdNaturezaLancamento", "s", @$payment->nature_id ? $payment->nature_id : NULL ],
                        [ "StEntrada", "s", @$payment->budget_payment_entry ],
                        [ "IdBanco", "s", @$payment->bank_id ? $payment->bank_id : NULL ],
                        [ "IdAgencia", "s", @$payment->agency_id ? $payment->agency_id : NULL ],
                        [ "NrAgencia", "s", @$payment->agency_code ? substr($payment->agency_code,0,10) : NULL ],
                        [ "NrCheque", "s", @$payment->check_number ? substr($payment->check_number,0,10) : NULL ],
                        [ "DtVencimento", "s", $payment->budget_payment_deadline ],
                        [ "NrParcelas", "i", $payment->budget_payment_installment ],
                        [ "NrParcelasRecebimento", "i", @$covenant && @$covenant->NrParcelasRecebimento ? $covenant->NrParcelasRecebimento : NULL ],
                        [ "NrDiasRecebimento", "i", @$covenant && @$covenant->NrDiasRecebimento ? $covenant->NrDiasRecebimento : NULL ],
                        [ "NrDiasIntervalo", "i", @$covenant && @$covenant->NrDiasIntervalo ? $covenant->NrDiasIntervalo : NULL ],
                        [ "NrDiasPrimeiraParcelaVenda", "i", @$covenant && @$covenant->NrDiasPrimeiraParcelaVenda ? $covenant->NrDiasPrimeiraParcelaVenda : NULL ],
                        [ "StAtualizaFinanceiro", "s", $operation->StAtualizaFinanceiro ],
                        [ "StCartaCredito", "s", $payment->budget_payment_credit == "Y" ? "S" : NULL ],
                        [ "VlParcela", "d", number_format( $payment->budget_payment_value, 2, '.', '') ],
                        [ "VlJurosPrazo", "d", 0 ]
                    ]
                ]);
            }
        }

        public static function editOrder()
        {
            GLOBAL $dafel, $config, $budget, $seller, $operation, $login;

            $date = date("Y-m-d");

            $orderParams = (Object)[
                "status" => "L",
                "type" => "P",
                "commission" => "D",
                "partial_billing" => "N",
                "mounting" => "N",
                "message_id" => "00A000001S",
                "discount_type" => "V",
                "system_id" => "0000000026",
                "personal_assistance" => "1",
                "delivery_type" => "E"
            ];

            Model::update($dafel,(Object)[
                "table" => "PedidoDeVenda",
                "fields" => [
                    [ "IdPessoaCliente", "s", $budget->client_id ],
                    [ "CdEnderecoEntrega", "s", $budget->address_code ],
                    [ "IdPrazo", "s", $budget->term_id ],
                    [ "DsObservacaoPedido", "s", @$budget->budget_note ? strtoupper($budget->budget_note) : NULL ],
                    [ "DsObservacaoDocumento", "s", @$budget->budget_note_document ? strtoupper($budget->budget_note_document) : NULL ],
                    [ "IdPessoaEntrega", "s", $budget->client_id ],
                    [ "StPedidoDeVenda", "s", $orderParams->status ],
                    [ "TpPedidoDeVenda", "s", $orderParams->type ]
                ],
                "filters" => [[ "IdPedidoDeVenda", "s", "=", $budget->external_id ]]
            ]);

            $orderItemParams = (Object)[
                "status" => "L",
                "cfop_start" => "5",
                "cfop_start_extra" => "6",
                "addition_type" => "A",
                "discount_type" => "A",
                "sale_showcase" => "N",
                "origin_type" => "0",
                "delivered" => "N",
                "delivery_type" => "I",
                "mounting" => "N",
                "metreage" => "1",
                "commission_main" => "N"
            ];

            $externalItems = [];
            foreach( $budget->items as $key => $item ) {

                $item = (Object)$item;

                $fields = [
                    ["QtPedida", "d", $item->budget_item_quantity],
                    ["VlItem", "d", $item->budget_item_value_total],
                    ["VlUnitario", "d", $item->budget_item_value_unitary],
                    ["VlDescontoItem", "d", $item->budget_item_value_discount],
                    ["VlAcrescimoRateado", "d", number_format((($item->budget_item_value_total / $budget->budget_value_total) * $budget->budget_value_addition), 2, '.', '')],
                    ["VlPesoLiquido", "d", @$item->product_weight_net ? number_format($item->budget_item_quantity * $item->product_weight_net, 2, '.', '') : NULL],
                    ["VlPesoBruto", "d", @$item->product_weight_gross ? number_format($item->budget_item_quantity * $item->product_weight_gross, 2, '.', '') : NULL],
                    ["AlDescontoItem", "d", $item->budget_item_aliquot_discount],
                    ["DtEntrega", "s", $budget->budget_delivery_date],
                    ["NrDiasEntrega", "s", countDays(date("Y-m-d"), $budget->budget_delivery_date)],
                    [ "TpDescontoItem", "s", $item->budget_item_value_discount ? "V" : NULL ],
                    ["IdPreco", "s", $item->price_id],
                    ["VlUnitarioTabelaPreco", "d", $item->budget_item_value_unitary],
                    ["StPedidoDeVendaItem", "s", $orderItemParams->status],
                    // campos de tributação
                    [ "AlICMS", "d", @$item->AlICMS ? $item->AlICMS : NULL ],
                    [ "AlFCP", "d", @$item->AlFCP ? $item->AlFCP : NULL ],
                    [ "VlICMS", "d", @$item->VlICMS ? $item->VlICMS : NULL ],
                    [ "VlFCP", "d", @$item->VlFCP ? $item->VlFCP : NULL ],
                    [ "VlBaseFCP", "d", @$item->VlBaseFCP ? $item->VlBaseFCP : NULL ],
                    [ "CdSituacaoTributaria", "s", @$item->CdSituacaoTributaria ? $item->CdSituacaoTributaria : NULL ],
                    [ "IdCFOP", "s", @$item->IdCFOP ? $item->IdCFOP : NULL ],
                    [ "VlICMSSubstTributaria", "d", @$item->VlICMSSubstTributaria ? $item->VlICMSSubstTributaria : NULL ],
                    [ "VlBaseFCPSubstTributaria", "d", @$item->VlBaseFCPSubstTributaria ? $item->VlBaseFCPSubstTributaria : NULL ],
                    [ "AlFCPSubstTributaria", "d", @$item->AlFCPSubstTributaria ? $item->AlFCPSubstTributaria : NULL ],
                    [ "VlFCPSubstTributaria", "d", @$item->VlFCPSubstTributaria ? $item->VlFCPSubstTributaria : NULL ]
                ];

                $commission_al = NULL;
                $commission_duplicate = NULL;
                $commission_billing = NULL;
                $commission_duplicate_budget = NULL;
                $commission_billing_budget = NULL;
                if( @$seller && $seller->StComissao == "S" ){
                    if( $seller->TpComissao == "D" ){
                        $commission_duplicate = @$seller->AlComissaoDuplicata ? $seller->AlComissaoDuplicata : $item->product_commission;
                    }
                }

                $fields2 = [
                    [ "IdPessoaRepresentante", "s", $budget->seller_id ],
                    [ "AlComissao", "d", @$commission_al ? $commission_al : NULL ],
                    [ "AlComissaoDuplicata", "d", @$commission_duplicate ? $commission_duplicate : NULL ],
                    [ "AlComissaoFaturamento", "d", @$commission_billing ? $commission_billing : NULL ],
                    [ "IdCategoria", "s", $config->person->seller_category_id ],
                    [ "StAlComissaoDuplicataPedido", "s", @$commission_duplicate_budget ? $commission_duplicate_budget : NULL ],
                    [ "StAlComissaoFaturamentoPedido", "s", @$commission_billing_budget ? $commission_billing_budget : NULL ]
                ];

                if( @$item->external_id ) {

                    $externalItems[] = $item->external_id;

                    Model::update($dafel,(Object)[
                        "table" => "PedidoDeVendaItem",
                        "fields" => $fields,
                        "filters" => [[ "IdPedidoDeVendaItem", "s", "=", $item->external_id ]]
                    ]);

                    Model::update($dafel,(Object)[
                        "table" => "Representante_PedidoDeVendaIte",
                        "fields" => $fields2,
                        "filters" => [[ "IdPedidoDeVendaItem", "s", "=", $item->external_id ]]
                    ]);

                } else {

                    $orderItem = (Object)[
                        "IdPedidoDeVendaItem" => Model::nextCode($dafel, (Object)[
                            "table" => "PedidoDeVendaItem",
                            "field" => "IdPedidoDeVendaItem",
                            "increment" => "S",
                            "base36encode" => 1
                        ])
                    ];

                    $externalItems[] = $orderItem->IdPedidoDeVendaItem;
                    $budget->items[$key]["external_id"] = $orderItem->IdPedidoDeVendaItem;

                    $fields[] = ["IdPedidoDeVendaItem", "s", $orderItem->IdPedidoDeVendaItem];
                    $fields[] = ["IdProduto", "s", $item->product_id];
                    $fields[] = ["IdPedidoDeVenda", "s", $budget->external_id];
                    $fields[] = ["TpAcrescimoItem", "s", $orderItemParams->addition_type];
                    $fields[] = ["StVendaMostruario", "s", $orderItemParams->sale_showcase];
                    $fields[] = ["TpOrigemProduto", "s", $orderItemParams->origin_type];
                    $fields[] = ["StMercadoriaEntregue", "s", $orderItemParams->delivered];
                    $fields[] = ["TpEntrega", "s", $orderItemParams->delivery_type];
                    $fields[] = ["StMontagemItem", "s", $orderItemParams->mounting];
                    $fields[] = ["QtMetragem", "s", $orderItemParams->metreage];
                    $fields[] = ["CdSituacaoTributariaCOFINS", "s", $operation->CDSituacaoTributariaCOFINS];
                    $fields[] = ["CdSituacaoTributariaPIS", "s", $operation->CDSituacaoTributariaPIS];

                    Model::insert($dafel, (Object)[
                        "table" => "PedidoDeVendaItem",
                        "fields" => $fields
                    ]);

                    $fields2[] = [ "IdPedidoDeVendaItem", "s", $orderItem->IdPedidoDeVendaItem ];
                    $fields2[] = [ "StPrincipal", "s", $orderItemParams->commission_main ];

                    Model::insert($dafel,(Object)[
                        "table" => "Representante_PedidoDeVendaIte",
                        "fields" => $fields2
                    ]);

                }
            }

            Model::delete($dafel,(Object)[
                "top" => 999,
                "table" => "PedidoDeVendaItem",
                "filters" => [
                    [ "IdPedidoDeVenda", "s", "=", $budget->external_id ],
                    [ "IdPedidoDeVendaItem", "s", "not in", $externalItems ]
                ]
            ]);

            // ***** CARTA DE CRÉDITO ***** //
            $budget->credit = (Object)$budget->credit;
            if( $budget->credit->value > 0 ){

                $orderPayment = (Object)[
                    "IdPedidoDeVendaPagamento" => Model::nextCode($dafel, (Object)[
                        "table" => "PedidoDeVendaPagamento",
                        "field" => "IdPedidoDeVendaPagamento",
                        "increment" => "S",
                        "base36encode" => 1
                    ])
                ];
                Model::insert($dafel,(Object)[
                    "table" => "PedidoDeVendaPagamento",
                    "fields" => [
                        [ "IdPedidoDeVendaPagamento", "s", $orderPayment->IdPedidoDeVendaPagamento ],
                        [ "IdPedidoDeVenda", "s", $budget->external_id ],
                        [ "IdFormaPagamento", "s", $config->credit->modality_id ],
                        [ "AlParcela", "d", 100 ],
                        [ "IdNaturezaLancamento", "s", $config->credit->entry_id ],
                        [ "VlTitulo", "d", $budget->credit->value],
                        [ "StEntrada", "s", "N" ],
                        [ "StConsideraDesconto", "s", "S" ],
                        [ "NrParcelas", "i", 1 ],
                        [ "StCartaCredito", "s", "S" ]
                    ]
                ]);
                $budget->credit->external_id = $orderPayment->IdPedidoDeVendaPagamento;

                foreach( $budget->credit->payable as $credit ) {

                    $credit = (Object)$credit;

                    $payable = Model::get($dafel,(Object)[
                        "tables" => [ "APagar (NoLock)" ],
                        "fields" => [
                            "IdAPagar",
                            "IdPessoa",
                            "CdEmpresa",
                            "NmTitulo",
                            "NrTitulo",
                            "VlTitulo=CAST(VlTitulo AS FLOAT)",
                            "VlBaixado=CAST(VlBaixado AS FLOAT)",
                            "VlAberto=CAST(VlAberto AS FLOAT)"
                        ],
                        "filters" => [[ "IdAPagar", "s", "=", $credit->payable_id ]]
                    ]);

                    $payable->VlTitulo = (float)$payable->VlTitulo;
                    $payable->VlBaixado = (float)$payable->VlBaixado;
                    $payable->VlAberto = (float)$payable->VlAberto;

                    $payable->VlBaixado += $credit->payable_value;
                    $payable->VlAberto -= $credit->payable_value;
                    $IdTipoBaixa = $config->credit->total_drop;
                    if( $payable->VlAberto > 0 ){
                        $IdTipoBaixa = $config->credit->partial_drop;
                    }

                    $payableLot = (Object)[
                        "IdLoteAPagar" => Model::nextCode($dafel,(Object)[
                            "table" => "LoteAPagar",
                            "field" => "IdLoteAPagar",
                            "increment" => "S",
                            "base36encode" => 1
                        ]),
                        "CdChamada" => Model::nextCode($dafel,(Object)[
                            "table" => "LoteAPagar",
                            "field" => "CdChamada",
                            "increment" => "S"
                        ])
                    ];
                    $payableDrop = (Object)[
                        "IdAPagarBaixa" => Model::nextCode($dafel,(Object)[
                            "table" => "APagarBaixa",
                            "field" => "IdAPagarBaixa",
                            "increment" => "S",
                            "base36encode" => 1
                        ])
                    ];

                    Model::insert($dafel,(Object)[
                        "table" => "LoteAPagar",
                        "fields" => [
                            [ "IdLoteAPagar", "s", $payableLot->IdLoteAPagar ],
                            [ "CdEmpresa", "i", $budget->company_id ],
                            [ "CdChamada", "s", $payableLot->CdChamada ],
                            [ "DsLoteAPagar", "s", "Carta de Credito - Pedido de Venda {$budget->external_code}" ],
                            [ "DtAbertura", "s", $date ],
                            [ "StLoteAPagar", "s", "L" ],
                            [ "IdUsuario", "s", $login->external_id ],
                            [ "TpEdicao", "s", "I" ],
                            [ "IdSistema", "s", $config->budget->system_id ]
                        ]
                    ]);

                    Model::insert($dafel,(Object)[
                        "table" => "APagarBaixa",
                        "fields" => [
                            [ "IdAPagarBaixa", "s", $payableDrop->IdAPagarBaixa ],
                            [ "IdAPagar", "s", $credit->payable_id ],
                            [ "IdTipoBaixa", "s", $IdTipoBaixa ],
                            [ "NrDocumento", "s", $payable->NrTitulo ],
                            [ "DtBaixa", "s", $date ],
                            [ "VlBaixa", "d", $credit->payable_value ],
                            [ "DsObservacao", "s", "Baixa criada pela vinculacao da carta de credito (titulo no FinAPagar {$payable->NrTitulo}) ao Pedido de Venda {$budget->external_code}" ],
                            [ "IdUsuario", "s", $login->external_id ],
                            [ "IdLoteAPagar", "s", $payableLot->IdLoteAPagar ],
                            [ "StAglutinaMovBancario", "s", "N" ],
                            [ "VlBaixaIndexado", "s", $credit->payable_value ],
                            [ "NmEntidadeOrigem", "s", "PedidoDeVendaPagamento" ],
                            [ "IdEntidadeOrigem", "s", $orderPayment->IdPedidoDeVendaPagamento ],
                            [ "DtProcessamento", "s", $date ],
                            [ "DtBaixaEfetiva", "s", $date ]
                        ]
                    ]);
                    Model::update($dafel,(Object)[
                        "table" => "APagar",
                        "fields" => [
                            [ "VlBaixado", "d", $payable->VlBaixado ],
                            [ "DtBaixa", "s", $payable->VlBaixado >= $payable->VlTitulo ? $date : NULL ],
                            [ "VlBaixadoIndexado", "d", $payable->VlBaixado ],
                            [ "DtProcessamento", "s", ( $payable->VlBaixado >= $payable->VlTitulo ? $date : NULL ) ]
                        ],
                        "filters" => [[ "IdAPagar", "s", "=", $credit->payable_id ]]
                    ]);

                    $data = (Object)[
                        "id" => $credit->payable_id,
                        "table" => "##CCredito\${$credit->payable_id}\${$budget->instance_id}\${$login->external_id}\${$config->budget->system_id}\$M",
                        "description" => "Inclusao do Commercial",
                        "date" => date("Y-m-d H:i:s"),
                        "instance_id" => $budget->instance_id,
                        "login_id" => $login->user_id,
                        "login_name" => $login->user_name
                    ];
                    file_put_contents(PATH_ROOT . "public/service/del/{$credit->payable_id}.json", json_encode($data));
                }
            }
            // ***** CARTA DE CRÉDITO ***** //

            $externalPayments = [];
            if( @$budget->payments ) {
                foreach ($budget->payments as $key => $payment) {

                    $payment = (Object)$payment;

                    $covenant = NULL;
                    if ($payment->modality_type == "A") {
                        $covenant = Model::get($dafel, (Object)[
                            "tables" => ["FormaPagamentoItem (NoLock)"],
                            "fields" => [
                                "NrDiasPrimeiraParcelaVenda",
                                "NrParcelasRecebimento",
                                "NrDiasRecebimento",
                                "NrDiasIntervalo",
                                "AlConvenio"
                            ],
                            "filters" => [
                                ["IdFormaPagamento", "s", "=", $payment->modality_id],
                                ["CdEmpresa", "i", "=", $budget->company_id],
                                ["NrParcelas", "i", "=", $payment->budget_payment_installment]
                            ]
                        ]);
                    }

                    $days = 0;
                    if (@$covenant && @$covenant->NrDiasRecebimento) {
                        $covenant->NrDiasRecebimento;
                    } else {
                        $days = countDays(date("Y-m-d"), $payment->budget_payment_deadline);
                    }

                    $nature = NULL;
                    if (@$payment->nature_id) {
                        $nature = Model::get($dafel, (Object)[
                            "tables" => ["NaturezaLancamento (NoLock)"],
                            "fields" => [
                                "IdNaturezaLancamento",
                                "CdChamada",
                                "NmNaturezaLancamento",
                                "StBaixaInclusao",
                                "IdTipoBaixa"
                            ],
                            "filters" => [["IdNaturezaLancamento", "s", "=", $payment->nature_id]]
                        ]);
                    }

                    $paymentAliquot = number_format(((100 * $payment->budget_payment_value) / $budget->budget_value_total), 6, '.', '');

                    $orderPaymentParams = (Object)[
                        "consider_discount" => "S",
                        "lote_status" => "L",
                        "edition_type" => "I",
                        "credit_partial_drop" => "00A000000D",
                        "credit_total_drop" => "00A000000F",
                        "credit_agglutinates_banking_movement" => "N"
                    ];

                    $fields = [
                        ["IdFormaPagamento", "s", $payment->modality_id],
                        ["NrDias", "i", $days],
                        ["IdTipoBaixa", "s", @$nature->IdTipoBaixa ? $nature->IdTipoBaixa : NULL],
                        ["AlParcela", "d", $paymentAliquot],
                        ["IdNaturezaLancamento", "s", @$payment->nature_id ? $payment->nature_id : NULL],
                        ["VlTitulo", "d", $payment->budget_payment_value],
                        ["StEntrada", "s", $payment->budget_payment_entry == "Y" ? "S" : "N"],
                        ["IdBanco", "s", @$payment->bank_id],
                        ["IdAgencia", "s", @$payment->agency_id],
                        ["NrAgencia", "s", @$payment->agency_code],
                        ["NrCheque", "s", @$payment->check_number],
                        ["NrParcelas", "i", $payment->budget_payment_installment],
                        ["NrParcelasRecebimento", "i", @$covenant && @$covenant->NrParcelasRecebimento ? $covenant->NrParcelasRecebimento : NULL],
                        ["NrDiasRecebimento", "i", @$covenant && @$covenant->NrDiasRecebimento ? $covenant->NrDiasRecebimento : NULL],
                        ["NrDiasIntervalo", "i", @$covenant && @$covenant->NrDiasIntervalo ? $covenant->NrDiasIntervalo : NULL],
                        ["NrDiasPrimeiraParcelaVenda", "i", @$covenant && @$covenant->NrDiasPrimeiraParcelaVenda ? $covenant->NrDiasPrimeiraParcelaVenda : NULL],
                        ["AlConvenio", "d", @$covenant && @$covenant->AlConvenio ? $covenant->AlConvenio : NULL]
                    ];

                    if (@$payment->external_id) {

                        $externalPayments[] = $payment->external_id;

                        Model::update($dafel, (Object)[
                            "table" => "PedidoDeVendaPagamento",
                            "fields" => $fields,
                            "filters" => [["IdPedidoDeVendaPagamento", "s", "=", $payment->external_id]]
                        ]);

                    } else {

                        $orderPayment = (Object)[
                            "IdPedidoDeVendaPagamento" => Model::nextCode($dafel, (Object)[
                                "table" => "PedidoDeVendaPagamento",
                                "field" => "IdPedidoDeVendaPagamento",
                                "increment" => "S",
                                "base36encode" => 1
                            ])
                        ];

                        $externalPayments[] = $orderPayment->IdPedidoDeVendaPagamento;
                        $budget->payments[$key]["external_id"] = $orderPayment->IdPedidoDeVendaPagamento;

                        $fields[] = ["IdPedidoDeVendaPagamento", "s", $orderPayment->IdPedidoDeVendaPagamento];
                        $fields[] = ["IdPedidoDeVenda", "s", $budget->external_id];
                        $fields[] = ["StConsideraDesconto", "s", $orderPaymentParams->consider_discount];
                        $fields[] = ["StCartaCredito", "s", $payment->budget_payment_credit == "Y" ? "S" : NULL];

                        Model::insert($dafel, (Object)[
                            "table" => "PedidoDeVendaPagamento",
                            "fields" => $fields
                        ]);

                    }
                }
            }

            Model::delete($dafel,(Object)[
                "top" => 99,
                "table" => "PedidoDeVendaPagamento",
                "filters" => [
                    [ "StCartaCredito", "s", "!=", "S" ],
                    [ "IdPedidoDeVenda", "s", "=", $budget->external_id ],
                    [ "IdPedidoDeVendaPagamento", "s", "not in", @$externalPayments ? $externalPayments : NULL ]
                ]
            ]);

        }

        public static function insertOrder()
        {
            GLOBAL $dafel, $login, $config, $budget, $seller, $operation;

            $date = date("Y-m-d");
            $dateTime = date("Y-m-d H:i:s");

            $order = (Object)[
                "IdPedidoDeVenda" => Model::nextCode($dafel,(Object)[
                    "table" => "PedidoDeVenda",
                    "field" => "IdPedidoDeVenda",
                    "increment" => "S",
                    "base36encode" => 1
                ]),
                "CdChamada" => Model::nextCode($dafel,(Object)[
                    "table" => "PedidoDeVenda",
                    "field" => "CdChamada",
                    "increment" => "S"
                ])
            ];

            $budget->external_id = $order->IdPedidoDeVenda;
            $budget->external_type = "P";
            $budget->external_code = $order->CdChamada;

            $orderParams = (Object)[
                "status" => "L",
                "type" => "P",
                "commission" => "D",
                "partial_billing" => "N",
                "mounting" => "N",
                "message_id" => "00A000001S",
                "discount_type" => "V",
                "system_id" => "0000000026",
                "personal_assistance" => "1",
                "delivery_type" => "E"
            ];

            Model::insert($dafel,(Object)[
                "table" => "PedidoDeVenda",
                "fields" => [
                    [ "IdPedidoDeVenda", "s", $order->IdPedidoDeVenda ],
                    [ "CdChamada", "s", $order->CdChamada ],
                    [ "CdEmpresa", "i", $budget->company_id ],
                    [ "CdEmpresaEstoque", "i", $budget->company_id ],
                    [ "CdEmpresaFinanceiro", "i", $budget->company_id ],
                    [ "DtEmissao", "s", $date ],
                    [ "DtEntrada", "s", $date ],
                    [ "DtEntrega", "s", $budget->budget_delivery_date ],
                    [ "StPedidoDeVenda", "s", $orderParams->status ],
                    [ "IdPessoaCliente", "s", $budget->client_id ],
                    [ "CdEnderecoPrincipal", "s", $budget->address_code ],
                    [ "CdEnderecoCobranca", "s",  $budget->address_code ],
                    [ "CdEnderecoEntrega", "s", $budget->address_code ],
                    [ "IdOperacao", "s", $config->budget->operation_id ],
                    [ "IdOperacaoOE", "s", $config->budget->oe_operation_id ],
                    [ "IdPrazo", "s", @$budget->term_id ? $budget->term_id : NULL ],
                    [ "TpFretePorConta", "s", $orderParams->delivery_type ],
                    [ "StAgendaEntrega", "s", $budget->budget_delivery ],
                    [ "DsObservacaoPedido", "s", @$budget->budget_note ? strtoupper($budget->budget_note) : NULL ],
                    [ "DsObservacaoDocumento", "s", @$budget->budget_note_document ? strtoupper($budget->budget_note_document) : NULL ],
                    [ "IdUsuario", "s", $login->external_id ],
                    [ "StFaturamentoParcial", "s", $orderParams->partial_billing ],
                    [ "StMontagem", "s", $orderParams->mounting ],
                    [ "IdMensagem1", "s", $orderParams->message_id ],
                    [ "TpDesconto", "s", $orderParams->discount_type ],
                    [ "DtReferenciaPagamento", "s", $date ],
                    [ "IdPessoaEntrega", "s", $budget->client_id ],
                    [ "DtReabertura", "s", $dateTime ],
                    [ "TpPedidoDeVenda", "s", $orderParams->type ],
                    [ "IdSistema", "s", $orderParams->system_id ],
                    [ "TpIndAtendimentoPresencial", "s", $orderParams->personal_assistance ]
                ]
            ]);

            Model::insert($dafel,(Object)[
                "table" => "PedidoDeVendaHistorico",
                "fields" => [
                    [ "IdPedidoDeVenda", "s", $order->IdPedidoDeVenda ],
                    [ "DtHistorico", "s", $dateTime ],
                    [ "TpPedidoDeVenda", "s", $orderParams->type ],
                    [ "IdUsuario", "s", $login->external_id ],
                ]
            ]);

            foreach( $budget->items as $key => $item ) {

                $orderItem = (Object)[
                    "IdPedidoDeVendaItem" => Model::nextCode($dafel, (Object)[
                        "table" => "PedidoDeVendaItem",
                        "field" => "IdPedidoDeVendaItem",
                        "increment" => "S",
                        "base36encode" => 1
                    ])
                ];

                $item = (Object)$item;
                $budget->items[$key]["external_id"] = $orderItem->IdPedidoDeVendaItem;

                $orderItemParams = (Object)[
                    "status" => "L",
                    "cfop_start" => "5",
                    "cfop_start_extra" => "6",
                    "addition_type" => "A",
                    "discount_type" => "A",
                    "sale_showcase" => "N",
                    "origin_type" => "0",
                    "delivered" => "N",
                    "delivery_type" => "I",
                    "mounting" => "N",
                    "metreage" => "1",
                    "commission_main" => "N"
                ];

                Model::insert($dafel,(Object)[
                    "table" => "PedidoDeVendaItem",
                    "fields" => [
                        [ "IdPedidoDeVendaItem", "s", $orderItem->IdPedidoDeVendaItem ],
                        [ "IdProduto", "s", $item->product_id ],
                        [ "IdPedidoDeVenda", "s", $order->IdPedidoDeVenda ],
                        [ "QtPedida", "d", $item->budget_item_quantity ],
                        [ "VlItem", "d", $item->budget_item_value_total ],
                        [ "VlUnitario", "d", $item->budget_item_value_unitary ],
                        [ "VlDescontoItem", "d", $item->budget_item_value_discount ],
                        [ "VlAcrescimoRateado", "d", number_format( (($item->budget_item_value_total/$budget->budget_value_total)*$budget->budget_value_addition), 2, '.', '')],
                        [ "StPedidoDeVendaItem", "s", $orderItemParams->status ],
                        [ "TpAcrescimoItem", "s", $orderItemParams->addition_type ],
                        [ "VlPesoLiquido", "d", @$item->product_weight_net ? number_format($item->budget_item_quantity*$item->product_weight_net,2,'.','') : NULL ],
                        [ "VlPesoBruto", "d", @$item->product_weight_gross ? number_format($item->budget_item_quantity*$item->product_weight_gross,2,'.','') : NULL ],
                        [ "AlDescontoItem", "d", $item->budget_item_aliquot_discount ],
                        [ "DtEntrega", "s", $budget->budget_delivery_date ],
                        [ "NrDiasEntrega", "s", countDays( date("Y-m-d"),$budget->budget_delivery_date) ],
                        [ "TpDescontoItem", "s", $item->budget_item_value_discount ? "V" : NULL ],
                        [ "StVendaMostruario", "s", $orderItemParams->sale_showcase ],
                        [ "TpOrigemProduto", "s", $orderItemParams->origin_type ],
                        [ "StMercadoriaEntregue", "s", $orderItemParams->delivered ],
                        [ "IdPreco", "s", $item->price_id ],
                        [ "TpEntrega", "s", $orderItemParams->delivery_type ],
                        [ "VlUnitarioTabelaPreco", "d", $item->budget_item_value_unitary ],
                        [ "StMontagemItem", "s", $orderItemParams->mounting ],
                        [ "QtMetragem", "s", $orderItemParams->metreage ],
                        [ "CdSituacaoTributariaCOFINS", "s", $operation->CDSituacaoTributariaCOFINS ],
                        [ "CdSituacaoTributariaPIS", "s", $operation->CDSituacaoTributariaPIS ],
                        // campos de tributação
                        [ "AlICMS", "d", @$item->AlICMS ? $item->AlICMS : NULL ],
                        [ "AlFCP", "d", @$item->AlFCP ? $item->AlFCP : NULL ],
                        [ "VlICMS", "d", @$item->VlICMS ? $item->VlICMS : NULL ],
                        [ "VlFCP", "d", @$item->VlFCP ? $item->VlFCP : NULL ],
                        [ "VlBaseFCP", "d", @$item->VlBaseFCP ? $item->VlBaseFCP : NULL ],
                        [ "CdSituacaoTributaria", "s", @$item->CdSituacaoTributaria ? $item->CdSituacaoTributaria : NULL ],
                        [ "IdCFOP", "s", @$item->IdCFOP ? $item->IdCFOP : NULL ],
                        [ "VlICMSSubstTributaria", "d", @$item->VlICMSSubstTributaria ? $item->VlICMSSubstTributaria : NULL ],
                        [ "VlBaseFCPSubstTributaria", "d", @$item->VlBaseFCPSubstTributaria ? $item->VlBaseFCPSubstTributaria : NULL ],
                        [ "AlFCPSubstTributaria", "d", @$item->AlFCPSubstTributaria ? $item->AlFCPSubstTributaria : NULL ],
                        [ "VlFCPSubstTributaria", "d", @$item->VlFCPSubstTributaria ? $item->VlFCPSubstTributaria : NULL ]
                    ]
                ]);

                $commission_al = NULL;
                $commission_duplicate = NULL;
                $commission_billing = NULL;
                $commission_duplicate_budget = NULL;
                $commission_billing_budget = NULL;
                if( @$seller && $seller->StComissao == "S" ){
                    if( $seller->TpComissao == "D" ){
                        $commission_duplicate = @$seller->AlComissaoDuplicata ? $seller->AlComissaoDuplicata : $item->product_commission;
                    }
                }

                Model::insert($dafel,(Object)[
                    "table" => "Representante_PedidoDeVendaIte",
                    "fields" => [
                        [ "IdPessoaRepresentante", "s", $budget->seller_id ],
                        [ "IdPedidoDeVendaItem", "s", $orderItem->IdPedidoDeVendaItem ],
                        [ "AlComissao", "d", @$commission_al ? $commission_al : NULL ],
                        [ "AlComissaoDuplicata", "d", @$commission_duplicate ? $commission_duplicate : NULL ],
                        [ "AlComissaoFaturamento", "d", @$commission_billing ? $commission_billing : NULL ],
                        [ "IdCategoria", "s", $config->person->seller_category_id ],
                        [ "StAlComissaoDuplicataPedido", "s", @$commission_duplicate_budget ? $commission_duplicate_budget : NULL ],
                        [ "StAlComissaoFaturamentoPedido", "s", @$commission_billing_budget ? $commission_billing_budget : NULL ],
                        [ "StPrincipal", "s", $orderItemParams->commission_main ]
                    ]
                ]);
            }

            $budget->credit = (Object)$budget->credit;
            if( $budget->credit->value > 0 ){

                $orderPayment = (Object)[
                    "IdPedidoDeVendaPagamento" => Model::nextCode($dafel, (Object)[
                        "table" => "PedidoDeVendaPagamento",
                        "field" => "IdPedidoDeVendaPagamento",
                        "increment" => "S",
                        "base36encode" => 1
                    ])
                ];
                Model::insert($dafel,(Object)[
                    "table" => "PedidoDeVendaPagamento",
                    "fields" => [
                        [ "IdPedidoDeVendaPagamento", "s", $orderPayment->IdPedidoDeVendaPagamento ],
                        [ "IdPedidoDeVenda", "s", $order->IdPedidoDeVenda ],
                        [ "IdFormaPagamento", "s", $config->credit->modality_id ],
                        [ "AlParcela", "d", 100 ],
                        [ "IdNaturezaLancamento", "s", $config->credit->entry_id ],
                        [ "VlTitulo", "d", $budget->credit->value],
                        [ "StEntrada", "s", "N" ],
                        [ "StConsideraDesconto", "s", "S" ],
                        [ "NrParcelas", "i", 1 ],
                        [ "StCartaCredito", "s", "S" ]
                    ]
                ]);
                $budget->credit->external_id = $orderPayment->IdPedidoDeVendaPagamento;

                foreach( $budget->credit->payable as $credit ) {

                    $credit = (Object)$credit;

                    $payable = Model::get($dafel,(Object)[
                        "tables" => [ "APagar (NoLock)" ],
                        "fields" => [
                            "IdAPagar",
                            "IdPessoa",
                            "CdEmpresa",
                            "NmTitulo",
                            "NrTitulo",
                            "VlTitulo=CAST(VlTitulo AS FLOAT)",
                            "VlBaixado=CAST(VlBaixado AS FLOAT)",
                            "VlAberto=CAST(VlAberto AS FLOAT)"
                        ],
                        "filters" => [[ "IdAPagar", "s", "=", $credit->payable_id ]]
                    ]);

                    $payable->VlTitulo = (float)$payable->VlTitulo;
                    $payable->VlBaixado = (float)$payable->VlBaixado;
                    $payable->VlAberto = (float)$payable->VlAberto;

                    $payable->VlBaixado += $credit->payable_value;
                    $payable->VlAberto -= $credit->payable_value;
                    $IdTipoBaixa = $config->credit->total_drop;
                    if( $payable->VlAberto > 0 ){
                        $IdTipoBaixa = $config->credit->partial_drop;
                    }

                    $payableLot = (Object)[
                        "IdLoteAPagar" => Model::nextCode($dafel,(Object)[
                            "table" => "LoteAPagar",
                            "field" => "IdLoteAPagar",
                            "increment" => "S",
                            "base36encode" => 1
                        ]),
                        "CdChamada" => Model::nextCode($dafel,(Object)[
                            "table" => "LoteAPagar",
                            "field" => "CdChamada",
                            "increment" => "S"
                        ])
                    ];
                    $payableDrop = (Object)[
                        "IdAPagarBaixa" => Model::nextCode($dafel,(Object)[
                            "table" => "APagarBaixa",
                            "field" => "IdAPagarBaixa",
                            "increment" => "S",
                            "base36encode" => 1
                        ])
                    ];

                    Model::insert($dafel,(Object)[
                        "table" => "LoteAPagar",
                        "fields" => [
                            [ "IdLoteAPagar", "s", $payableLot->IdLoteAPagar ],
                            [ "CdEmpresa", "i", $budget->company_id ],
                            [ "CdChamada", "s", $payableLot->CdChamada ],
                            [ "DsLoteAPagar", "s", "Carta de Credito - Pedido de Venda {$order->CdChamada}" ],
                            [ "DtAbertura", "s", $date ],
                            [ "StLoteAPagar", "s", "L" ],
                            [ "IdUsuario", "s", $login->external_id ],
                            [ "TpEdicao", "s", "I" ],
                            [ "IdSistema", "s", $config->budget->system_id ]
                        ]
                    ]);

                    Model::insert($dafel,(Object)[
                        "table" => "APagarBaixa",
                        "fields" => [
                            [ "IdAPagarBaixa", "s", $payableDrop->IdAPagarBaixa ],
                            [ "IdAPagar", "s", $credit->payable_id ],
                            [ "IdTipoBaixa", "s", $IdTipoBaixa ],
                            [ "NrDocumento", "s", $payable->NrTitulo ],
                            [ "DtBaixa", "s", $date ],
                            [ "VlBaixa", "d", $credit->payable_value ],
                            [ "DsObservacao", "s", "Baixa criada pela vinculacao da carta de credito (titulo no FinAPagar {$payable->NrTitulo}) ao Pedido de Venda {$order->CdChamada}" ],
                            [ "IdUsuario", "s", $login->external_id ],
                            [ "IdLoteAPagar", "s", $payableLot->IdLoteAPagar ],
                            [ "StAglutinaMovBancario", "s", "N" ],
                            [ "VlBaixaIndexado", "s", $credit->payable_value ],
                            [ "NmEntidadeOrigem", "s", "PedidoDeVendaPagamento" ],
                            [ "IdEntidadeOrigem", "s", $orderPayment->IdPedidoDeVendaPagamento ],
                            [ "DtProcessamento", "s", $date ],
                            [ "DtBaixaEfetiva", "s", $date ]
                        ]
                    ]);
                    Model::update($dafel,(Object)[
                        "table" => "APagar",
                        "fields" => [
                            [ "VlBaixado", "d", $payable->VlBaixado ],
                            [ "DtBaixa", "s", $payable->VlBaixado >= $payable->VlTitulo ? $date : NULL ],
                            [ "VlBaixadoIndexado", "d", $payable->VlBaixado ],
                            [ "DtProcessamento", "s", ( $payable->VlBaixado >= $payable->VlTitulo ? $date : NULL ) ]
                        ],
                        "filters" => [[ "IdAPagar", "s", "=", $credit->payable_id ]]
                    ]);

                    $data = (Object)[
                        "id" => $credit->payable_id,
                        "table" => "##CCredito\${$credit->payable_id}\${$budget->instance_id}\${$login->external_id}\${$config->budget->system_id}\$M",
                        "description" => "Inclusao do Commercial",
                        "date" => date("Y-m-d H:i:s"),
                        "instance_id" => $budget->instance_id,
                        "login_id" => $login->user_id,
                        "login_name" => $login->user_name
                    ];
                    file_put_contents(PATH_ROOT . "public/service/del/{$credit->payable_id}.json", json_encode($data));
                }
            }

            if( @$budget->payments ) {
                foreach ($budget->payments as $key => $payment) {

                    $orderPayment = (Object)[
                        "IdPedidoDeVendaPagamento" => Model::nextCode($dafel, (Object)[
                            "table" => "PedidoDeVendaPagamento",
                            "field" => "IdPedidoDeVendaPagamento",
                            "increment" => "S",
                            "base36encode" => 1
                        ])
                    ];

                    $payment = (Object)$payment;
                    $budget->payments[$key]["external_id"] = $orderPayment->IdPedidoDeVendaPagamento;

                    $covenant = NULL;
                    if ($payment->modality_type == "A") {
                        $covenant = Model::get($dafel, (Object)[
                            "tables" => ["FormaPagamentoItem (NoLock)"],
                            "fields" => [
                                "NrDiasPrimeiraParcelaVenda",
                                "NrParcelasRecebimento",
                                "NrDiasRecebimento",
                                "NrDiasIntervalo",
                                "AlConvenio"
                            ],
                            "filters" => [
                                ["IdFormaPagamento", "s", "=", $payment->modality_id],
                                ["CdEmpresa", "i", "=", $budget->company_id],
                                ["NrParcelas", "i", "=", $payment->budget_payment_installment]
                            ]
                        ]);
                    }

                    $days = 0;
                    if (@$covenant && @$covenant->NrDiasRecebimento) {
                        $covenant->NrDiasRecebimento;
                    } else {
                        $days = countDays(date("Y-m-d"), $payment->budget_payment_deadline);
                    }

                    $nature = NULL;
                    if (@$payment->nature_id) {
                        $nature = Model::get($dafel, (Object)[
                            "tables" => ["NaturezaLancamento (NoLock)"],
                            "fields" => [
                                "IdNaturezaLancamento",
                                "CdChamada",
                                "NmNaturezaLancamento",
                                "StBaixaInclusao",
                                "IdTipoBaixa"
                            ],
                            "filters" => [["IdNaturezaLancamento", "s", "=", $payment->nature_id]]
                        ]);
                    }

                    $paymentAliquot = number_format(((100 * $payment->budget_payment_value) / $budget->budget_value_total), 6, '.', '');

                    $orderPaymentParams = (Object)[
                        "consider_discount" => "S",
                        "lote_status" => "L",
                        "edition_type" => "I",
                        "credit_partial_drop" => "00A000000D",
                        "credit_total_drop" => "00A000000F",
                        "credit_agglutinates_banking_movement" => "N"
                    ];

                    Model::insert($dafel, (Object)[
                        "table" => "PedidoDeVendaPagamento",
                        "fields" => [
                            ["IdPedidoDeVendaPagamento", "s", $orderPayment->IdPedidoDeVendaPagamento],
                            ["IdPedidoDeVenda", "s", $order->IdPedidoDeVenda],
                            ["IdFormaPagamento", "s", $payment->modality_id],
                            ["NrDias", "i", $days],
                            ["IdTipoBaixa", "s", @$nature->IdTipoBaixa ? $nature->IdTipoBaixa : NULL],
                            ["AlParcela", "d", $paymentAliquot],
                            ["IdNaturezaLancamento", "s", @$payment->nature_id ? $payment->nature_id : NULL],
                            ["VlTitulo", "d", $payment->budget_payment_value],
                            ["StEntrada", "s", $payment->budget_payment_entry == "Y" ? "S" : "N"],
                            ["IdBanco", "s", @$payment->bank_id],
                            ["IdAgencia", "s", @$payment->agency_id],
                            ["NrAgencia", "s", @$payment->agency_code],
                            ["NrCheque", "s", @$payment->check_number],
                            ["StConsideraDesconto", "s", $orderPaymentParams->consider_discount],
                            ["NrParcelas", "i", $payment->budget_payment_installment],
                            ["NrParcelasRecebimento", "i", @$covenant && @$covenant->NrParcelasRecebimento ? $covenant->NrParcelasRecebimento : NULL],
                            ["NrDiasRecebimento", "i", @$covenant && @$covenant->NrDiasRecebimento ? $covenant->NrDiasRecebimento : NULL],
                            ["NrDiasIntervalo", "i", @$covenant && @$covenant->NrDiasIntervalo ? $covenant->NrDiasIntervalo : NULL],
                            ["NrDiasPrimeiraParcelaVenda", "i", @$covenant && @$covenant->NrDiasPrimeiraParcelaVenda ? $covenant->NrDiasPrimeiraParcelaVenda : NULL],
                            ["AlConvenio", "d", @$covenant && @$covenant->AlConvenio ? $covenant->AlConvenio : NULL],
                            ["StCartaCredito", "s", $payment->budget_payment_credit == "Y" ? "S" : NULL]
                        ]
                    ]);
                }
            }
        }

    }

?>