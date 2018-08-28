<?php

    class Shopping
    {
        public $company_id;
        public $user_id;
        public $product_id;
        public $group_id;
        public $provider_id;
        public $company_code;
        public $company_name;
        public $user_login;
        public $user_name;
        public $product_code;
        public $product_name;
        public $group_code;
        public $group_name;
        public $provider_code;
        public $provider_name;
        public $unit_code;
        public $quantity;
        public $value;
        public $date;

        public function __construct( $data )
        {
            GLOBAL $companies;

            $this->company_id = $data->CdEmpresa;
            $this->user_id = $data->IdUsuario;
            $this->product_id = $data->IdProduto;
            $this->group_id = $data->IdGrupoProduto;
            $this->provider_id = $data->IdPessoa;

            $this->company_code = substr("0{$data->CdEmpresa}",-2);
            $this->company_name = strtoupper($companies[$data->CdEmpresa]);
            $this->user_login = strtoupper($data->NmLogin);
            $this->user_name = strtoupper($data->NmUsuario);
            $this->product_code = $data->CdProduto;
            $this->product_name = $data->NmProduto;
            $this->group_code = $data->CdClassificacao;
            $this->group_name = $data->NmGrupoProduto;
            $this->provider_code = $data->CdPessoa;
            $this->provider_name = $data->NmPessoa;
            $this->unit_code = $data->CdSigla;
            $this->quantity = (float)$data->Quantidade;
            $this->value = (float)$data->Valor;
            $this->date = $data->DtReferencia;
        }

        public static function group( $data )
        {
            GLOBAL $period;

            $ret = (Object)[
                "company" => (Object)[
                    "table" => (Object)[
                        "header" => [ "Código", "Nome" ],
                        "body" => [ "code", "name" ]
                    ],
                    "data" => (Object)[
                        "none" => (Object)[ "categories" => [], "data" => [] ],
                        "day" => (Object)[ "categories" => [], "data" => [] ],
                        "week" => (Object)[ "categories" => [], "data" => [] ],
                        "month" => (Object)[ "categories" => [], "data" => [] ],
                        "year" => (Object)[ "categories" => [], "data" => [] ]
                    ]
                ],
                "user" => (Object)[
                    "table" => (Object)[
                        "header" => [ "Nome" ],
                        "body" => [ "name" ]
                    ],
                    "data" => (Object)[
                        "none" => (Object)[ "categories" => [], "data" => [] ],
                        "day" => (Object)[ "categories" => [], "data" => [] ],
                        "week" => (Object)[ "categories" => [], "data" => [] ],
                        "month" => (Object)[ "categories" => [], "data" => [] ],
                        "year" => (Object)[ "categories" => [], "data" => [] ]
                    ]
                ],
                "product" => (Object)[
                    "table" => (Object)[
                        "header" => [ "Código", "Produto" ],
                        "body" => [ "code", "name" ]
                    ],
                    "data" => (Object)[
                        "none" => (Object)[ "categories" => [], "data" => [] ],
                        "day" => (Object)[ "categories" => [], "data" => [] ],
                        "week" => (Object)[ "categories" => [], "data" => [] ],
                        "month" => (Object)[ "categories" => [], "data" => [] ],
                        "year" => (Object)[ "categories" => [], "data" => [] ]
                    ]
                ],
                "group" => (Object)[
                    "table" => (Object)[
                        "header" => [ "Código", "Grupo" ],
                        "body" => [ "code", "name" ]
                    ],
                    "data" => (Object)[
                        "none" => (Object)[ "categories" => [], "data" => [] ],
                        "day" => (Object)[ "categories" => [], "data" => [] ],
                        "week" => (Object)[ "categories" => [], "data" => [] ],
                        "month" => (Object)[ "categories" => [], "data" => [] ],
                        "year" => (Object)[ "categories" => [], "data" => [] ]
                    ]
                ],
                "provider" => (Object)[
                    "table" => (Object)[
                        "header" => [ "Código", "Fornecedor" ],
                        "body" => [ "code", "name" ]
                    ],
                    "data" => (Object)[
                        "none" => (Object)[ "categories" => [], "data" => [] ],
                        "day" => (Object)[ "categories" => [], "data" => [] ],
                        "week" => (Object)[ "categories" => [], "data" => [] ],
                        "month" => (Object)[ "categories" => [], "data" => [] ],
                        "year" => (Object)[ "categories" => [], "data" => [] ]
                    ]
                ]
            ];

            $items = (Object)[
                "company" => [],
                "user" => [],
                "product" => [],
                "group" => [],
                "provider" => []
            ];

            foreach( $data as $d ){

                if( !@$ret->company->data->none->data[$d->company_id] ){
                    $ret->company->data->none->data[$d->company_id] = (Object)[
                        "code" => substr( "0{$d->company_id}", -2 ),
                        "name" => $d->company_name,
                        "quantity" => 0,
                        "value" => 0
                    ];
                }
                if( !@$ret->user->data->none->data[$d->user_id] ){
                    $ret->user->data->none->data[$d->user_id] = (Object)[
                        "name" => strtoupper($d->user_name),
                        "quantity" => 0,
                        "value" => 0
                    ];
                }
                if( !@$ret->product->data->none->data[$d->product_id] ){
                    $ret->product->data->none->data[$d->product_id] = (Object)[
                        "code" => $d->product_code,
                        "name" => strtoupper($d->product_name),
                        "quantity" => 0,
                        "value" => 0
                    ];
                }
                if( !@$ret->group->data->none->data[$d->group_id] ){
                    $ret->group->data->none->data[$d->group_id] = (Object)[
                        "code" => $d->group_code,
                        "name" => strtoupper($d->group_name),
                        "quantity" => 0,
                        "value" => 0
                    ];
                }
                if( !@$ret->provider->data->none->data[$d->provider_id] ){
                    $ret->provider->data->none->data[$d->provider_id] = (Object)[
                        "code" => $d->provider_code,
                        "name" => strtoupper($d->provider_name),
                        "quantity" => 0,
                        "value" => 0
                    ];
                }

                $items->company[$d->company_id] = 1;
                $items->user[$d->user_id] = 1;
                $items->product[$d->product_id] = 1;
                $items->group[$d->group_id] = 1;
                $items->provider[$d->provider_id] = 1;

                $ret->company->data->none->data[$d->company_id]->quantity += $d->quantity;
                $ret->company->data->none->data[$d->company_id]->value += $d->value;
                $ret->company->data->none->data[$d->company_id]->unit = $d->unit_code;

                $ret->user->data->none->data[$d->user_id]->quantity += $d->quantity;
                $ret->user->data->none->data[$d->user_id]->value += $d->value;
                $ret->user->data->none->data[$d->user_id]->unit = $d->unit_code;

                $ret->product->data->none->data[$d->product_id]->quantity += $d->quantity;
                $ret->product->data->none->data[$d->product_id]->value += $d->value;
                $ret->product->data->none->data[$d->product_id]->unit = $d->unit_code;

                $ret->group->data->none->data[$d->group_id]->quantity += $d->quantity;
                $ret->group->data->none->data[$d->group_id]->value += $d->value;
                $ret->group->data->none->data[$d->group_id]->unit = $d->unit_code;

                $ret->provider->data->none->data[$d->provider_id]->quantity += $d->quantity;
                $ret->provider->data->none->data[$d->provider_id]->value += $d->value;
                $ret->provider->data->none->data[$d->provider_id]->unit = $d->unit_code;
            }

            foreach( $ret as $key => $r ){
                $r->data->none->categories = [ $period->start->format("d/m/Y") . " á " . $period->end->format("d/m/Y") ];
                $r->data->day = groupByDate((Object)[
                    "data" => $data,
                    "object_id" => "{$key}_id",
                    "object_code" => "{$key}_code",
                    "object_name" => "{$key}_name",
                    "object_value" => "value",
                    "object_quantity" => "quantity",
                    "items" => $items->$key,
                    "start" => $period->start->format("Y-m-d"),
                    "end" => $period->end->format("Y-m-d"),
                    "format" => "d"
                ]);
                $r->data->month = groupByDate((Object)[
                    "data" => $data,
                    "object_id" => "{$key}_id",
                    "object_code" => "{$key}_code",
                    "object_name" => "{$key}_name",
                    "object_value" => "value",
                    "object_quantity" => "quantity",
                    "items" => $items->$key,
                    "start" => $period->start->format("Y-m-d"),
                    "end" => $period->end->format("Y-m-d"),
                    "format" => "m"
                ]);
                $r->data->year = groupByDate((Object)[
                    "data" => $data,
                    "object_id" => "{$key}_id",
                    "object_code" => "{$key}_code",
                    "object_name" => "{$key}_name",
                    "object_value" => "value",
                    "object_quantity" => "quantity",
                    "items" => $items->$key,
                    "start" => $period->start->format("Y-m-d"),
                    "end" => $period->end->format("Y-m-d"),
                    "format" => "y"
                ]);
            }

            foreach( $ret as $k1 => $r1 ){
                foreach ($r1 as $k2 => $r2) {
                    if( $k2 == "data" ) {
                        foreach ($r2 as $k3 => $r3) {
                            foreach ($r3->data as $k4 => $r4) {
                                if (!@$r4->series) {
                                    $r4->quantity_order = substr("000000000" . number_format($r4->quantity, 2, '', ''), -10);
                                    $r4->quantity_formatted = str_replace([",000"], [""], number_format($r4->quantity, 3, ",", ".")) . " {$r4->unit}";
                                    $r4->value_order = substr("000000000" . number_format($r4->value, 2, '', ''), -10);
                                    $r4->value_formatted = "R$ " . number_format($r4->value, 2, ",", ".");
                                }
                            }
                        }
                    }
                }
            }

            return $ret;
        }

        public static function groupByDate( $params )
        {
            $object_id = $params->object_id;
            $object_code = $params->object_code;
            $object_name = $params->object_name;

            $begin = new DateTime( $params->start );
            $end = new DateTime( $params->end . " + 1 day" );

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);

            $ret = (Object)[
                "categories" => [],
                "dates" => [],
                "data" => []
            ];

            foreach( $period as $dt ){
                if( $dt->format("w") != 0 ) {
                    $ret->categories[] = $dt->format('d/m');
                    $ret->dates[$dt->format('Ymd')] = $dt->format('d/m/Y');
                    foreach( $params->items as $id => $value ){
                        if( !@$ret->data[$id] ) {
                            $ret->data[$id] = (Object)[
                                "code" => "",
                                "name" => "",
                                "series" => (Object)[
                                    "quantity" => [],
                                    "value" => []
                                ]
                            ];
                        }
                        $ret->data[$id]->series->quantity[$dt->format('Ymd')] = 0;
                        $ret->data[$id]->series->value[$dt->format('Ymd')] = 0;
                    }
                }
            }

            foreach( $params->data as $d ){
                $date = DateTime::createFromFormat( "d/m/Y", $d->date );
                $date = $date->format("Ymd");
                if( @$d->$object_code ) $ret->data[$d->$object_id]->code = $d->$object_code;
                $ret->data[$d->$object_id]->name = $d->$object_name;
                $ret->data[$d->$object_id]->unit = $d->unit_code;
                $ret->data[$d->$object_id]->series->quantity[$date] += $d->quantity;
                $ret->data[$d->$object_id]->series->value[$date] += $d->value;
            }

            foreach( $ret->dates as $date => $day ){
                foreach( $ret->data as $d2 ) {
                    $value = $d2->series->value[$date];
                    $quantity = $d2->series->quantity[$date];
                    $d2->series->value_order[$date] = substr("000000000" . number_format($value, 2, '', ''), -10);
                    $d2->series->value_formatted[$date] = "R$ " . number_format($value, 2, ",", ".");
                    $d2->series->quantity_order[$date] = substr("000000000" . number_format($quantity, 2, '', ''), -10);
                    $d2->series->quantity_formatted[$date] = str_replace([",000"], [""], number_format($quantity, 3, ",", ".")) . " {$d2->unit}";
                }
            };

            return $ret;

        }
    }

?>