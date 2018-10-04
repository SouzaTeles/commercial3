<?php

    class User
    {
        public $user_id;
        public $person_id;
        public $user_profile_id;
        public $user_active;
        public $user_user;
        public $user_name;
        public $user_email;
        public $user_only_session;
        public $user_login;
        public $user_update;
        public $user_date;

        public function __construct( $data, $gets=[] )
        {
            $this->user_id = $data->user_id;
            $this->external_id = @$data->external_id ? $data->external_id : NULL;
            $this->person_id = @$data->person_id ? $data->person_id : NULL;
            $this->user_profile_id = @$data->user_profile_id ? (int)$data->user_profile_id : NULL;
            $this->person_id = @$data->person_id ? $data->person_id : NULL;
            $this->user_active = @$data->user_active ? $data->user_active : NULL;
            $this->user_user = @$data->user_user ? $data->user_user : NULL;
            $this->user_name = $data->user_name;
            $this->user_email = @$data->user_email ? $data->user_email : NULL;
            $this->user_login = @$data->user_login ? $data->user_login : NULL;
            $this->user_update = @$data->user_update ? $data->user_update : NULL;
            $this->user_date = @$data->user_date ? $data->user_date : NULL;

            $this->image = getImage((Object)[
                "image_id" => $data->user_id,
                "image_dir" => "user"
            ]);

            if (!@$this->image && @$data->person_id ){
                $this->image = getImage((Object)[
                    "image_id" => $data->person_id,
                    "image_dir" => "person"
                ]);
            }

            GLOBAL $conn, $commercial, $dafel;

            if( @$gets["get_user_access"] || @$_POST["get_user_access"] ){
                $this->user_access = UserAccess::treeAccess(Model::getList($commercial,(Object)[
                    "class" => "UserAccess",
                    "tables" => [ "UserAccess" ],
                    "fields" => [
                        "user_access_id",
                        "user_access_name",
                        "user_access_value",
                        "user_access_data_type"
                    ],
                    "filters" => [[ "user_id", "i", "=", $data->user_id ]]
                ]));
            }

            if( @$gets["get_user_profile"] || @$_POST["get_user_profile"] ){
                $this->user_profile = Model::get($commercial,(Object)[
                    "class" => "UserProfile",
                    "tables" => [ "UserProfile" ],
                    "fields" => [
                        "user_profile_id",
                        "user_profile_name",
                        "user_profile_update=FORMAT(user_profile_update,'yyyy-MM-dd HH:mm:ss')",
                        "user_profile_date=FORMAT(user_profile_date,'yyyy-MM-dd HH:mm:ss')",
                    ],
                    "filters" => [[ "user_profile_id", "i", "=", $data->user_profile_id ]],
                    "gets" => $gets
                ]);
            }

            if( @$gets["get_user_last_session"] || @$_POST["get_user_last_session"] ){
                $this->user_last_session = Model::get($commercial,(Object)[
                    "class" => "UserSession",
                    "tables" => [ "UserSession" ],
                    "fields" => [
                         "user_session_value",
                         "user_session_date=FORMAT(user_session_date,'yyyy-MM-dd HH:mm:ss')",
                    ],
                    "filters" => [[ "user_id", "s", "=", $data->user_id ]],
                    "order" => "user_session_date DESC",
                    "limit" => 1
                ]);
            }

            if( @$gets["get_user_company"] || @$_POST["get_user_company"] ){
                $companies = Model::getList($commercial,(Object)[
                    "tables" => [ "UserCompany uc", "Company c" ],
                    "fields" => [
                        "c.company_id",
                        "c.company_name",
                        "c.company_color",
                        "c.company_short_name",
                        "c.company_consumer_id",
                        "c.company_budget_message",
                        "c.company_st",
                        "c.company_credit",
                        "uc.user_company_main"
                    ],
                    "filters" => [
                        [ "uc.company_id = c.company_id" ],
                        [ "c.company_active", "s", "=", "Y" ],
                        [ "uc.user_id", "s", "=", $data->user_id ]
                    ],
                    "order" => "c.company_id"
                ]);
                foreach( $companies as $company ){
                    $company->image = getImage((Object)[
                        "image_id" => $company->company_id,
                        "image_dir" => "company"
                    ]);
                    $company->company_consumer_id = @$company->company_consumer_id ? $company->company_consumer_id : NULL;
                }
                $this->companies = $companies;
            }

            if( @$gets["get_user_price"] || @$_POST["get_user_price"] ){
                $this->prices = Model::getList($commercial,(Object)[
                    "tables" => [
                        "{$conn->commercial->table}.dbo.UserPrice UP",
                        "{$conn->dafel->table}.dbo.Preco P"
                    ],
                    "fields" => [
                        "UP.price_id",
                        "price_code=P.CdPreco",
                        "price_name=P.NmPreco"
                    ],
                    "filters" => [
                        [ "UP.price_id = P.IdPreco" ],
                        [ "UP.user_id", "s", "=", $data->user_id ]
                    ],
                    "order" => "P.CdPreco ASC"
                ]);
            }

            if( @$gets["get_user_person"] || @$_POST["get_user_person"] ){
                $this->person = NULL;
                if( @$data->person_id ){
                    $this->person = Model::get($dafel,(Object)[
                        "tables" => [ "Pessoa" ],
                        "fields" => [
                            "person_code=CdChamada",
                            "person_name=NmPessoa"
                        ],
                        "filters" => [[ "IdPessoa", "s", "=", $data->person_id ]]
                    ]);
                }
            }
        }
    }

?>