<?php

    class ProductGroup
    {
        public $product_group_id;
        public $product_group_code;
        public $product_group_name;
        public $product_group_classification;

        public function __construct( $data )
        {
            $this->product_group_id = $data->IdGrupoProduto;
            $this->product_group_code = $data->CdClassificacao;
            $this->product_group_name = $data->NmGrupoProduto;
            $this->product_group_classification = $data->TpClassificacao;
        }

        public static function mountList( $groups )
        {
            $ret = [];

            foreach( $groups as $group )
            {
                $code = explode( ".", $group->product_group_code );
                switch( sizeof($code) ){
                    case "1":
                        if( !@$ret[$code[0]] ){
                            $ret[$code[0]] = (Object)[
                                "group" => $group,
                                "subgroup" => []
                            ];
                        }
                    break;
                    case "2":
                        if( $group->product_group_classification == "S" ){
                            $ret[$code[0]]->subgroup[$code[1]] = (Object)[
                                "group" => $group,
                                "subgroup" => []
                            ];
                        }
                        elseif( $group->product_group_classification == "A" ){
                            $ret[$code[0]]->subgroup[$code[1]] = (Object)[
                                "group" => $group
                            ];
                        }
                    break;
                    case "3":
                        if( $group->product_group_classification == "S" ){
                            $ret[$code[0]]->subgroup[$code[1]]->subgroup[$code[2]] = (Object)[
                                "group" => $group,
                                "subgroup" => []
                            ];
                        }
                        elseif( $group->product_group_classification == "A" ){
                            $ret[$code[0]]->subgroup[$code[1]]->subgroup[$code[2]] = (Object)[
                                "group" => $group
                            ];
                        }
                    break;
                    case "4":
                        if( $group->product_group_classification == "S" ){
                            $ret[$code[0]]->subgroup[$code[1]]->subgroup[$code[2]]->subgroup[$code[3]] = (Object)[
                                "group" => $group,
                                "subgroup" => []
                            ];
                        }
                        elseif( $group->product_group_classification == "A" ){
                            $ret[$code[0]]->subgroup[$code[1]]->subgroup[$code[2]]->subgroup[$code[3]] = (Object)[
                                "group" => $group
                            ];
                        }
                    break;
                }
            }

            return $ret;
        }
    }

?>