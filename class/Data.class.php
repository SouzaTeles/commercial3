<?php

    class Data
    {
        public static function rh()
        {
            $data = (Object)[
                "color" => "second",
                "icon" => "fa-users",
                "path" => "rh",
                "page" => (Object)[
                    "page_title" => "RH",
                    "page_file" => "page.html"
                ],
                "pages" => [
                    (Object)[
                        "id" => "month-employer",
                        "icon" => "fa-star",
                        "title" => "Funcionário do Mês"
                    ],
                    (Object)[
                        "id" => "e-mail-signature",
                        "icon" => "fa-envelope-o",
                        "title" => "Assinatura de E-mail"
                    ]
                ]
            ];

            return $data;
        }

        public static function index()
        {
            $data = (Object)[
                "color" => "default",
                "page" => (Object)[
                    "page_title" => "Home",
                    "page_file" => "index.html"
                ]
            ];

            return $data;
        }

        public static function login()
        {
            $data = (Object)[
                "color" => "default",
                "page" => (Object)[
                    "page_title" => "Login",
                    "page_file" => "login.html"
                ]
            ];

            return $data;
        }

        public static function config()
        {
            $data = (Object)[
                "color" => "default",
                "icon" => "fa-gear",
                "path" => "config",
                "page" => (Object)[
                    "page_title" => "Configurações",
                    "page_file" => "page.html"
                ],
                "pages" => [
                    (Object)[
                        "id" => "user",
                        "icon" => "fa-user",
                        "title" => "Usuários"
                    ],
                    (Object)[
                        "id" => "profile",
                        "icon" => "fa-id-badge",
                        "title" => "Perfis de Usuário"
                    ],
                    (Object)[
                        "id" => "shipping",
                        "icon" => "fa-cubes",
                        "title" => "Compras"
                    ],
                    (Object)[
                        "id" => "billing",
                        "icon" => "fa-files-o",
                        "title" => "Faturamento"
                    ],
                    (Object)[
                        "id" => "financial",
                        "icon" => "fa-usd",
                        "title" => "Financeiro"
                    ],
                    (Object)[
                        "id" => "rh",
                        "icon" => "fa-users",
                        "title" => "RH"
                    ],
                ],
            ];

            return $data;
        }

        public static function billing()
        {
            GLOBAL $dafel, $metas;

            $data = Model::getList( $metas, (Object)[
                "tables" => [ "target" ],
                "fields" => [ "business_code", "target_val" ],
                "filters" => [
                    [ "target_type", "i", "=", "1" ],
                    [ "target_date_start", "s", "=", date("Y-m-01") ]
                ]
            ]);

            $companies = [];
            foreach( $data as $key => $company ){
                $companies[$company->business_code] = $company;
                unset($data[$key]);
            }

            $sellers = Model::getList( $metas, (Object)[
                "tables" => [ "seller s", "target t" ],
                "fields" => [ "s.idERP", "s.business_code", "t.target_val" ],
                "filters" => [
                    [ "s.seller_id = t.seller_id" ],
                    [ "t.target_type", "i", "=", "2" ],
                    [ "s.seller_active", "s", "=", "Y" ],
                    [ "t.target_date_start", "s", "=", date("Y-m-01") ]
                ]
            ]);

            $people_id = [];
            foreach( $sellers as $key => $seller ){
                $people_id[] = $seller->idERP;
                $sellers[$seller->idERP] = $seller;
                unset($sellers[$key]);
            }

            $data = (Object)[
                "color" => "fourth",
                "icon" => "fa-files-o",
                "path" => "billing",
                "page" => (Object)[
                    "page_title" => "Faturamento",
                    "page_file" => "page.html"
                ],
                "pages" => [(Object)[
                    "id" => "index",
                    "icon" => "fa-home",
                    "title" => "Dashboard"
                ],(Object)[
                    "id" => "company",
                    "icon" => "fa-building-o",
                    "title" => "Empresas"
                ],(Object)[
                    "id" => "seller",
                    "icon" => "fa-users",
                    "title" => "Representantes"
                ],(Object)[
                    "id" => "group",
                    "icon" => "fa-cubes",
                    "title" => "Grupo de Produto"
                ],(Object)[
                    "id" => "modality",
                    "icon" => "fa-credit-card",
                    "title" => "Modalidades"
                ]],
                "dashboard" => (Object)[
                    "daily" => (Object)[
                        "companies" => Company::getBilling((Object)[
                            "target" => "daily",
                            "companies" => $companies,
                            "dtStart" => date("Y-m-d"),
                            "dtEnd" => date("Y-m-d")
                        ]),
                        "sellers" => PersonSeller::getBilling((Object)[
                            "target" => "daily",
                            "sellers" => $sellers,
                            "people_id" => $people_id,
                            "dtStart" => date("Y-m-d"),
                            "dtEnd" => date("Y-m-d")
                        ])
                    ],
                    "monthly" => (Object)[
                        "companies" => Company::getBilling((Object)[
                            "target" => "monthly",
                            "companies" => $companies,
                            "dtStart" => date("Y-m-01"),
                            "dtEnd" => date("Y-m-d")
                        ]),
                        "sellers" => PersonSeller::getBilling((Object)[
                            "target" => "monthly",
                            "sellers" => $sellers,
                            "people_id" => $people_id,
                            "dtStart" => date("Y-m-01"),
                            "dtEnd" => date("Y-m-d")
                        ])
                    ]
                ],
                "companies" => Model::getList( $dafel, (Object)[
                    "class" => "Company",
                    "tables" => [ "EmpresaERP" ],
                    "fields" => [ "CdEmpresa", "NmEmpresa" ],
                    "filters" => [[ "CdEmpresa", "i", "not in", [ 50,51,101 ] ]]
                ]),
                "groups" => ProductGroup::mountList(Model::getList( $dafel, (Object)[
                    "class" => "ProductGroup",
                    "tables" => [ "GrupoProduto" ],
                    "fields" => [ "IdGrupoProduto", "CdClassificacao", "NmGrupoProduto", "TpClassificacao" ],
                    "order" => "CdClassificacao"
                ])),
                "operations" => Model::getList( $dafel, (Object)[
                    "class" => "Operation",
                    "tables" => [ "Operacao" ],
                    "fields" => [ "IdOperacao", "CdChamada", "NmOperacao", "TpOperacao" ],
                    "filters" => [[ "TpOperacao", "s", "=", "C" ]]
                ])
            ];

            return $data;
        }

        public static function general()
        {
            $global = (Object)[
                "date" => (Object)[
                    "today" => date("Y-m-d"),
                    "today_br" => date("d/m/Y"),
                    "firstOfMonth" => date("Y-m-01"),
                    "firstOfMonth_br" => date("01/m/Y"),
                    "lastOfMonth" => date("Y-m-t"),
                    "lastOfMonth_br" => date("t/m/Y")
                ]
            ];

            return $global;
        }

        public static function shopping()
        {
            GLOBAL $dafel;

            $data = (Object)[
                "color" => "third",
                "icon" => "fa-cubes",
                "path" => "shopping",
                "page" => (Object)[
                    "page_title" => "Compras",
                    "page_file" => "page.html"
                ],
                "pages" => [
                    (Object)[
                        "id" => "shopping",
                        "icon" => "fa-file-text-o",
                        "title" => "Relatório de Compras"
                    ]
                ],
                "companies" => Model::getList( $dafel, (Object)[
                    "class" => "Company",
                    "tables" => [ "EmpresaERP" ],
                    "fields" => [ "CdEmpresa", "NmEmpresa" ],
                    "filters" => [[ "CdEmpresa", "i", "not in", [ 50,51,101 ] ]]
                ]),
                "groups" => ProductGroup::mountList(Model::getList( $dafel, (Object)[
                    "class" => "ProductGroup",
                    "tables" => [ "GrupoProduto" ],
                    "fields" => [ "IdGrupoProduto", "CdClassificacao", "NmGrupoProduto", "TpClassificacao" ],
                    "order" => "CdClassificacao"
                ])),
//                "users" => Model::getList( $dafel, (Object)[
//                    "class" => "User",
//                    "tables" => [ "Usuario" ],
//                    "fields" => [ "IdUsuario", "NmLogin", "NmUsuario" ],
//                ]),
                "operations" => Model::getList( $dafel, (Object)[
                    "class" => "Operation",
                    "tables" => [ "Operacao" ],
                    "fields" => [ "IdOperacao", "CdChamada", "NmOperacao", "TpOperacao" ],
                    "filters" => [[ "TpOperacao", "s", "=", "C" ]]
                ])
            ];

            return $data;
        }

        public static function financial()
        {
            GLOBAL $dafel;

            $data = json_decode(file_get_contents(PATH_DATA . "pages/financial.json"));

            $data->companies = Model::getList( $dafel, (Object)[
                "class" => "Company",
                "tables" => [ "EmpresaERP" ],
                "fields" => [ "CdEmpresa", "NmEmpresa" ],
                "filters" => [[ "CdEmpresa", "i", "not in", [ 50,51,101 ] ]]
            ]);

            return $data;
        }

    }

?>