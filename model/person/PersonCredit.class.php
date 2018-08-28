<?php

class PersonCredit
{
    public $payable_id;
    public $person_id;
    public $modality_id;
    public $company_id;
    public $payable_code;
    public $modality_description;
    public $credit_value;
    public $credit_value_utilized;
    public $payable_date;
    public $payable_note;
    public $pawn;

    public function __construct( $data )
    {
        GLOBAL $dafel;

        $this->payable_id = $data->IdAPagar;
        $this->person_id = $data->IdPessoa;
        $this->modality_id = $data->IdFormaPagamento;
        $this->company_id = $data->CdEmpresa;
        $this->payable_code = $data->NrTitulo;
        $this->modality_description = $data->DsFormaPagamento;
        $this->credit_value = (float)number_format( $data->VlTitulo, 2, ".", "" );
        $this->credit_value_utilized = (float)number_format( $data->VlUtilizado, 2, ".", "" );
        $this->credit_value_available = (float)number_format( ( $data->VlTitulo - $data->VlUtilizado ), 2, ".", "" );
        $this->payable_date = "{$data->DtEmissao}";
        $this->payable_note = @$data->DsObservacao ? $data->DsObservacao : NULL;

        if( @$data->Empenhado ){
            $this->pawn = Model::get($dafel,(Object)[
                "class" => "PersonCreditPawn",
                "tables" => [ "Usuario U", "Sistema S", "{$data->Empenhado} CC" ],
                "fields" => [ "U.NmUsuario", "S.NmSistema", "CC.Descricao" ],
                "filters" => [
                    [ "U.IdUsuario", "s", "=", substr($data->Empenhado,33,10) ],
                    [ "S.IdSistema", "s", "=", substr($data->Empenhado,44,10) ]
                ]
            ]);
        }
    }
}

?>