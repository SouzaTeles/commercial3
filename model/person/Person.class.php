<?php

    class Person
    {
        public $person_id;
        public $person_code;
        public $person_name;
        public $person_short_name;
        public $person_cpf;
        public $person_cnpj;
        public $person_document;
        public $person_type;
        public $person_active;
        public $image;

        public function __construct( $data, $gets )
        {
            GLOBAL $dafel, $config;

            $this->person_id = $data->IdPessoa;
            $this->person_code = $data->CdChamada;
            $this->person_name = $data->NmPessoa;
            $this->person_short_name = @$data->NmCurto ? $data->NmCurto : NULL;
            $this->person_cpf = $data->TpPessoa == "F" ? $data->CdCPF_CGC : NULL;
            $this->person_cnpj = $data->TpPessoa == "J" ? $data->CdCPF_CGC : NULL;
            $this->person_document = $data->CdCPF_CGC;
            $this->person_type = $data->TpPessoa;
            $this->person_active = ($data->StATivo == "S" ? "Y" : "N");
            $this->person_gender = @$data->TpSexo ? $data->TpSexo : NULL;
            $this->person_birth = @$data->DtNascimento ? $data->DtNascimento : NULL;
            $this->person_credit_limit = @$data->VlLimiteCredito ? (float)$data->VlLimiteCredito : 0;

            $this->image = getImage((Object)[
                "image_id" => $data->IdPessoa,
                "image_dir" => "person"
            ]);

            if( @$gets["get_person_attribute"] || @$_POST["get_person_attribute"] )
            {
                $this->attributes = Model::getList($dafel,(Object)[
                    "class" => "PersonAttribute",
                    "tables" => [
                        "CaracteristicaPessoa CP (NoLock)",
                        "Pessoa_CaracteristicaPessoa PCP (NoLock)"
                    ],
                    "fields" => [
                        "PCP.IdPessoa",
                        "CP.IdCaracteristicaPessoa",
                        "CP.CdChamada",
                        "CP.CdClassificacao",
                        "CP.TpCaracteristica",
                        "CP.NmCaracteristicaPessoa",
                        "CP.DsObservacao",
                        "DtCaracteristica = CONVERT(VARCHAR(10),PCP.DtCaracteristica,126)"
                    ],
                    "filters" => [
                        [ "PCP.IdPessoa", "s", "=", $data->IdPessoa ],
                        [ "CP.IdCaracteristicaPessoa = PCP.IdCaracteristicaPessoa" ],
                        [ "CP.IdCaracteristicaPessoa", "s", "in", $config->person->attributes ]
                    ]
                ]);
            }

            if( @$gets["get_person_address"] || @$_POST["get_person_address"] )
            {
                $this->address = Model::getList($dafel,(Object)[
                    "top" => LITE ? 99 : 1,
                    "join" => 1,
                    "class" => "PersonAddress",
                    "tables" => [
                        "PessoaEndereco PE (NoLock)",
                        "INNER JOIN Cidade C (NoLock) ON(C.IdCidade = PE.IdCidade)",
	                    "INNER JOIN Bairro B (NoLock) ON(B.IdBairro = PE.IdBairro)"
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
                        "PE.DsObservacao",
                        "VlLatitude=CAST(PE.VlLatitude AS FLOAT)",
                        "VlLongitude=CAST(PE.VlLongitude AS FLOAT)"
                    ],
                    "filters" => [[ "PE.IdPessoa", "s", "=", $data->IdPessoa ]],
                    "order" => "PE.StEnderecoPrincipal DESC, PE.CdEndereco",
                    "gets" => $gets
                ]);
            }

            if( @$_POST["get_person_credit_limit"] ){

                $delay = 0;
                $expired_value = 0;
                $expired_quantity = 0;
                $expiring_value = 0;
                $expiring_quantity = 0;

                $receivable = Model::getList($dafel,(Object)[
                    "join" => 1,
                    "top" => 999,
                    "tables" => [
                        "AReceber AR (NoLock)",
                        "LEFT OUTER JOIN APagar AP (NoLock) ON((AP.NmEntidadeOrigem = 'AReceber') and (AP.IdEntidadeOrigem = AR.IdAReceber))",
                        "LEFT JOIN FormaPagamento FP (NoLock) ON(AR.IdFormaPagamento = FP.IdFormaPagamento)"
                    ],
                    "fields" => [
                        "FP.DsFormaPagamento",
                        "AR.NrTitulo",
                        "DtEmissao=CONVERT(VARCHAR(10),AR.DtEmissao,126)",
                        "DtVencimento=CONVERT(VARCHAR(10),AR.DtVencimento,126)",
                        "VlTitulo=IsNull(AR.VlTitulo,0)",
                        "VlBaixado=IsNull(AR.VlBaixado,0)"
                    ],
                    "filters" => [
                        [ "AR.DtExclusao IS NULL" ],
                        [ "AR.DtBaixa IS NULL" ],
                        [ "AP.IdAPagar IS NULL" ],
                        [ "AR.IdPessoa", "s", "=", $data->IdPessoa ]
                    ]
                ]);

                $ret=[];
                foreach( $receivable as $item ){

                    $diff = (Object)[ "days" => 0 ];
                    if( @$item->DtVencimento ) {
                        if( strtotime($item->DtVencimento) < strtotime(date("Y-m-d")) ) {
                            $deadline = new \DateTime($item->DtVencimento);
                            $now = new \DateTime(date('Y-m-d'));
                            $diff = $deadline->diff($now);
                        }
                    }

                    if( $diff->days > 0 ){
                        $expired_value += (float)$item->VlTitulo;
                        $expired_quantity ++;
                    } else {
                        $expiring_value += (float)$item->VlTitulo;
                        $expiring_quantity ++;
                    }

                    if( $diff->days > $delay ){
                        $delay = $diff->days;
                    }

                    $ret[] = (Object)[
                        "modality_name" => $item->DsFormaPagamento,
                        "receivable_code" => $item->NrTitulo,
                        "receivable_date" => $item->DtEmissao,
                        "receivable_deadline" => $item->DtVencimento,
                        "receivable_value" => (float)$item->VlTitulo,
                        "receivable_dropped" => (float)$item->VlBaixado,
                        "receivable_delay" => $diff->days
                    ];
                }

                $this->credit_limit = (Object)[
                    "delay" => $delay,
                    "receivable" => $ret,
                    "expired_value" => $expired_value,
                    "expired_quantity" => $expired_quantity,
                    "expiring_value" => $expiring_value,
                    "expiring_quantity" => $expiring_quantity,
                    "balance" => (float)$data->VlLimiteCredito - $expired_value - $expiring_value
                ];
            }
        }
    }

?>