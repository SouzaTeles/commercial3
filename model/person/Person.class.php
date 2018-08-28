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

            $this->image = getImage((Object)[
                "image_id" => $data->IdPessoa,
                "image_dir" => "person"
            ]);

            if( @$gets["get_person_credit"] || @$_POST["get_person_credit"] )
            {
                $this->credits = Model::getList($dafel,(Object)[
                    "class" => "PersonCredit",
                    "tables" => [
                        "APagar AP (NoLock)",
                        "FormaPagamento FP (NoLock)"
                    ],
                    "fields" => [
                        "AP.IdAPagar",
                        "AP.IdPessoa",
                        "FP.IdFormaPagamento",
                        "AP.CdEmpresa",
                        "AP.NrTitulo",
                        "FP.DsFormaPagamento",
                        "VlTitulo = (ISNULL(AP.VlTitulo, 0)-ISNULL(AP.VlIRRF, 0)-ISNULL(AP.VlPIS, 0)-ISNULL(AP.VlCOFINS, 0)- ISNULL(AP.VlCSLL, 0)-ISNULL(AP.VlINSS, 0)-ISNULL(AP.VlISS, 0)- ISNULL(AP.VlPIS_COFINS_CSLL, 0)- ISNULL(AP.VlOutros, 0))",
                        "VlUtilizado = ISNULL(( SELECT Sum(ISNULL(APB.VlBaixa, 0)) FROM APagarBaixa APB WHERE APB.IdAPagar = AP.IdAPagar GROUP BY APB.IdAPagar ),0) + ISNULL(( SELECT Sum(ISNULL(LAPB.VlBaixa, 0)) FROM LoteAPagarBaixa LAPB WHERE LAPB.IdAPagar = AP.IdAPagar AND ( NOT EXISTS( SELECT AB.IdAPagarBaixa FROM APagarBaixa AB WHERE ( AB.IdAPagarBaixa = LAPB.IdAPagarBaixa ))) GROUP BY LAPB.IdAPagar ),0)",
                        "AP.DtEmissao",
                        "AP.DsObservacao",
                        "Empenhado = (SELECT Name FROM TempDB..sysObjects WHERE Name like '##CCredito$%' AND (SUBSTRING(Name,23,10) <> '" . ( @$_POST["instance_id"] ? $_POST["instance_id"] : "XXXXXXXXXX" ) . "') AND (SUBSTRING(Name,12,10) = AP.IdApagar))"
                    ],
                    "filters" => [
                        [ "AP.DtExclusao IS NULL" ],
                        [ "AP.DtBaixa IS NULL" ],
                        [ "FP.IdFormaPagamento = AP.IdFormaPagamento" ],
                        [ "AP.IdPessoa", "s", "=", $data->IdPessoa ],
                        [ "AP.IdNaturezaLancamento", "s", "=", $config->credit->entry_id ]
                    ]
                ]);
            }

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
                    "top" => 1,
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
                    "filters" => [[ "PE.IdPessoa", "s", "=", $data->IdPessoa ]],
                    "order" => "PE.StEnderecoPrincipal DESC, PE.CdEndereco"
                ]);
            }
        }
    }

?>