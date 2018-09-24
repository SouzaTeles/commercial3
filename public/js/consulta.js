User = {
    erp: [],
    prices: [],
    user: {},
    users: [],
    table: {
        user: global.table({
            selector: '#table-users',
            paging: 1,
            noControls: [0,5],
            order: [[1, "asc"]]
        }),
        user_companies: global.table({
            selector: '#table-user-companies',
            noControls: [3],
            order: [[0, "asc"]]
        }),
        user_prices: global.table({
            selector: '#table-user-prices',
            noControls: [2],
            order: [[0, "asc"]]
        })
    },
    typeahead: {
        items: 10,
        delay: 500
    },
    action: null,
    actions: function(user){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-custom" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-bars"></i>' +
                '</button>' +
                '<ul class="dropdown-menu dropdown-custom pull-right">' +
                    '<li><a disabled="' + ( global.login.access.user.edit.value == 'N' ) + '" data-action="edit" data-id="' + user.user_id + '" class="dropdown-item" href="#"><i class="fa fa-pencil"></i>Editar</a></li>' +
                    '<li><a disabled="' + ( global.login.access.user.del.value == 'N' ) + '" data-action="del" data-id="' + user.user_id + '" class="dropdown-item" href="#"><i class="fa fa-trash-o"></i>Excluir</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a disabled="' + ( global.login.access.user.user_pass.value == 'N' ) + '" data-action="pass" data-id="' + user.user_id + '" class="dropdown-item" href="#"><i class="fa fa-lock"></i>Alterar Senha</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    add: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=insert',
            data: User.user,
            dataType: 'json'
        }, function(data){
            global.modal({
                icon: 'fa-info',
                title: 'Informação',
                html: '<p>' + data.message + '</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            });
            User.getList();
            User.cancel();
        }, function (data) {
            global.alert({
                class: 'alert-warning',
                message: data.status.description
            });
        });
    },
    cancel: function(){
        setTimeout(function (){
            User.init();
            User.data2form();
            if( global.super ){
                $('#person_id option').remove();
                $('#person_id').selectpicker('refresh');
            }
        }, 200);
        $('a[href="#tab-1-1"]').click();
    },
    data2form: function(){
        $('#user_id').selectpicker('val',User.user.user_id).prop('disabled',!!User.user.user_id).selectpicker('refresh');
        $('#user_person_code').val(User.user.person ? User.user.person.person_code : '').attr('data-value',(User.user.person ? User.user.person.person_code : ''));
        $('#user_person_name').val(User.user.person ? User.user.person.person_name : '').attr('data-value',(User.user.person ? User.user.person.person_name : ''));
        $('#user_active').bootstrapToggle(User.user.user_active == 'Y' ? 'on' : 'off');
        $('#user_name').val(User.user.user_name);
        $('#person_id').selectpicker('val',User.user.person_id);
        $('#user_profile_id').selectpicker('val',User.user.user_profile_id);
        $('#user_mail').val(User.user.user_mail);
        $('#user_user').val(User.user.user_user).prop('readonly',true);
        $('#user_pass').val(User.user.user_id ? '******' : '').prop('readonly',true);
        $('#user_pass_confirm').val(User.user.user_id ? '******' : '').prop('readonly',true);
        $('#file-image-user').filestyle('disabled',!User.user.user_id);
        $('#button-image-user-remove').prop('disabled',!User.user.image);
        User.showUserCompany();
        User.showUserPrice();
        UserImage.show();
    },
    del: function(user_id){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente excluir o cadastro do usuário?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não'
            }, {
                icon: 'fa-check',
                title: 'Sim',
                action: function () {
                    global.post({
                        url: global.uri.uri_public_api + 'user.php?action=del',
                        data: {user_id: user_id}
                    }, function (data) {
                        setTimeout(function(){
                            global.modal({
                                icon: 'fa-info',
                                title: 'Informação',
                                html: '<p>' + data.message + '</p>',
                                buttons: [{
                                    icon: 'fa-check',
                                    title: 'Ok',
                                    dismiss: true
                                }]
                            });
                        },500);
                        User.getList();
                    }, function (data) {
                        setTimeout(function(){
                            global.modal({
                                icon: 'fa-exclamation-triangle',
                                title: 'Erro',
                                html: '<p>' + data.status.description + '</p>',
                                buttons: [{
                                    icon: 'fa-check',
                                    title: 'Ok',
                                    dismiss: true
                                }]
                            });
                        },500)
                    });
                }
            }]
        });
    },
    editPass: function(data){
        if( data.user_pass != data.user_pass_confirm ){
            global.alert({
                class: 'alert-danger',
                message: 'A nova senha não confere com o campo de confirmação.'
            });
        } else {
            global.post({
                url: global.uri.uri_public_api + 'user.php?action=userPass',
                data: data,
                dataType: 'json'
            },function(data){
                $('#modal').modal('hide');
                setTimeout(function(){
                    global.modal({
                        icon: 'fa-info',
                        title: 'Informação',
                        html: '<p>' + data.message + '</p>',
                        buttons: [{
                            icon: 'fa-check',
                            title: 'Ok'
                        }]
                    });
                },500);
            },function(data){
                global.alert({
                    class: 'alert-warning',
                    message: data.status.description
                });
            })
        }
    },
    edit: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=edit',
            data: User.user,
            dataType: 'json'
        }, function(data){
            global.modal({
                icon: 'fa-info',
                title: 'Informação',
                html: '<p>' + data.message + '</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            });
            User.getList();
            User.cancel();
        });
    },
    events: function(){
        $('#button-user-refresh').click(function(){
            User.getList();
        });
        $('#button-user-cancel').click(function(){
            global.modal({
                icon: 'fa-question-circle-o',
                title: 'Confirmação',
                html: '<p>Deseja realmente cancelar ' + ( User.user.user_id ? 'a edição' : 'o cadastro' ) + ' do usuário?</p>',
                buttons: [{
                    icon: 'fa-times',
                    title: 'Não'
                }, {
                    icon: 'fa-check',
                    title: 'Sim',
                    action: function(){
                        User.cancel();
                    }
                }]
            });
        });
        $('#button-user-new').click(function(){
            User.new();
        });
        $('#form-user').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            User.form2data();
            if( User.validate() ) {
                if( User.action == 'add' ){
                    User.add();
                } else {
                    User.edit();
                }
            }
        });
        $('#user_id').change(function(){
            $('#file-image-user').filestyle('disabled',false);
            $('#user_name').val($(this).find('option:selected').text());
        });
        $('#user_person_code').keypress(function (e) {
            var keycode = e.keyCode || e.which;
            if (keycode == '13' && $(this).val().length) {
                e.preventDefault();
                e.stopPropagation();
                if (global.posts < 1) {
                    User.getPerson({
                        person_code: $(this).val()
                    });
                }
            }
        });
        $('#user_person_name').on('keyup',function(){
            if( $(this).val().length >= 3 ){
                clearTimeout(User.timer);
                User.timer = setTimeout(function(){
                    global.autocomplete({
                        items: User.typeahead.items,
                        selector: '#user_person_name',
                        data: {
                            limit: User.typeahead.items,
                            person_name: $('#user_person_name').val(),
                            person_category_id: global.config.person.seller_category_id
                        },
                        url: global.uri.uri_public_api + 'person.php?action=typeahead',
                        callBack: function(person){
                            User.user.person_id = person.item_id;
                            User.user.person = {
                                person_code: person.item_code,
                                person_name: person.item_name
                            };
                            $('#user_person_code').val(person.item_code).attr('data-value',person.item_code);
                        }
                    });
                },User.typeahead.delay);
            }
        });
        $('#button-user-person-remove').click(function(){
            if( !!User.user.person ){
                global.modal({
                    icon: 'fa-question-circle-o',
                    title: 'Confirmação',
                    html: '<p>Deseja realmente remover a pessoa do usuário?</p>',
                    buttons: [{
                        icon: 'fa-times',
                        title: 'Não'
                    },{
                        icon: 'fa-check',
                        title: 'Sim',
                        action: function(){
                            User.user.person = null;
                            User.user.person_id = null;
                            $('#user_person_code, #user_person_name').val('').attr('data-value','');
                        }
                    }]
                });
            }
        });
        $('#button-user-company-add').click(function(){
            if( !$('#user_company_id').val().length ){
                global.validateMessage('A empresa deverá ser selecionada.');
                return;
            }
            User.newUserCompany();
        });
        $('#button-user-price-add').click(function(){
            if( !$('#user_price_id').val().length ){
                global.validateMessage('O preço deverá ser selecionado.');
                return;
            }
            User.newUserPrice();
        });
        User.table.user.on('draw',function(){
            global.tooltip();
            $('#table-users a[data-action="edit"][disabled="false"]').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                User.get($(this).attr('data-id'));
            });
            $('#table-users a[data-action="del"][disabled="false"]').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                User.del($(this).attr('data-id'));
            });
            $('#table-users a[data-action="pass"][disabled="false"]').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                User.passUser($(this).attr('data-id'));
            });
        });
        User.table.user_companies.on('draw',function(){
            global.tooltip();
            $('#table-user-companies a[data-action="main"]').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                var key = $(this).attr('data-key');
                $.each( User.user.companies, function(k,company){
                    company.user_company_main = ( k == key ? 'Y' : 'N' );
                });
                User.showUserCompany();
            });
            $('#table-user-companies a[data-action="del"]').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                var key = $(this).attr('data-key');
                global.modal({
                    icon: 'fa-question-circle-o',
                    title: 'Confirmação',
                    html: '<p>Deseja realmente remover a empresa do usuário?</p>',
                    buttons: [{
                        icon: 'fa-times',
                        title: 'Não'
                    },{
                        icon: 'fa-check',
                        title: 'Sim',
                        action: function(){
                            $('#user_company_id option[value="' + User.user.companies[key].company_id + '"]').prop('disabled',false);
                            User.user.companies.splice(key,1);
                            User.showUserCompany();
                            $('#user_company_id').selectpicker('refresh');
                        }
                    }]
                });
            });
        });
        User.table.user_prices.on('draw',function(){
            global.tooltip();
            $('#table-user-prices a[data-action="del"]').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                var key = $(this).attr('data-key');
                global.modal({
                    icon: 'fa-question-circle-o',
                    title: 'Confirmação',
                    html: '<p>Deseja realmente remover o preço do usuário?</p>',
                    buttons: [{
                        icon: 'fa-times',
                        title: 'Não'
                    }, {
                        icon: 'fa-check',
                        title: 'Sim',
                        action: function(){
                            $('#user_price_id option[value="' + User.user.prices[key].price_id + '"]').prop('disabled',false);
                            User.user.prices.splice(key,1);
                            User.showUserPrice();
                            $('#user_price_id').selectpicker('refresh');
                        }
                    }]
                });
            });
        });
        global.toggle();
        UserImage.events();
    },
    form2data: function(){
        if( User.action == 'add' ) User.user.user_id = $('#user_id').val();
        User.user.user_active = $('#user_active').prop('checked') ? 'Y' : 'N';
        User.user.client_id = $('#user_client_id') ? $('#user_client_id').val() : null;
        User.user.user_name = $('#user_name').val();
        User.user.user_profile_id = $('#user_profile_id').selectpicker('val');
        User.user.user_mail = $('#user_mail').val();
        User.user.user_user = $('#user_user').val();
        User.user.user_pass = $('#user_pass').val();
        User.user.user_pass_confirm = $('#user_pass_confirm').val();
    },
    get: function(user_id){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=get',
            data: {
                user_id: user_id,
                get_user_company: 1,
                get_user_price: 1,
                get_user_person: 1
            },
            dataType: 'json'
        },function(user){
            User.user = user;
            User.show();
            User.action = 'edit';
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=getList',
            dataType: 'json'
        }, function(users){
            User.users = users;
            User.showList();
        }, function(data){
            global.alert({
                class: 'alert-warning',
                message: data.status.description
            });
        });
    },
    getPerson: function(data){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=get',
            data: data,
            dataType: 'json'
        }, function(person){
            User.user.person_id = person.IdPessoa;
            User.user.person = {
                person_code: person.CdChamada,
                person_name: person.NmPessoa
            };
            $('#user_person_code').val(person.CdChamada).attr('data-value',person.CdChamada);
            $('#user_person_name').val(person.NmPessoa).attr('data-value',person.NmPessoa);
        });
    },
    getPrices: function(){
        global.post({
            url: global.uri.uri_public_api + 'price.php?action=getList',
            dataType: 'json'
        }, function(prices){
            User.prices = prices;
            User.showPrices();
        });
    },
    init: function(){
        User.user = {
            image: null,
            user_id: null,
            client_id: null,
            person_id: null,
            user_profile_id: null,
            user_active: 'Y',
            user_name: '',
            user_mail: '',
            user_user: '',
            user_pass: '',
            user_pass_confirm: '',
            companies: [],
            prices: [],
            person: null
        };
    },
    new: function(){
        User.init();
        User.data2form();
        User.action = 'add';
        $('#button-user-add').show();
        $('#button-user-edit').hide();
        $('#user_user, #user_pass, #user_pass_confirm').prop('readonly',false);
        $('a[href="#tab-1-2"]').click();
    },
    newUserCompany: function(){
        var key = $('#user_company_id option:selected').index();
        var company = Company.companies[key-1];
        User.user.companies.push({
            company_id: company.company_id,
            company_short_name: company.company_short_name,
            user_company_main: 'N'
        });
        $('#user_company_id option:selected').prop('disabled',true);
        $('#user_company_id').selectpicker('val','default');
        $('#user_company_id').selectpicker('refresh');
        User.showUserCompany();
    },
    newUserPrice: function(){
        var key = $('#user_price_id option:selected').index();
        var price = User.prices[key-1];
        User.user.prices.push({
            price_id: price.price_id,
            price_code: price.price_code,
            price_name: price.price_name
        });
        $('#user_price_id option:selected').prop('disabled',true);
        $('#user_price_id').selectpicker('val','default');
        $('#user_price_id').selectpicker('refresh');
        User.showUserPrice();
    },
    passUser: function(user_id){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=userPassForm',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-lock',
                title: 'Alteração de Senha',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar'
                },{
                    icon: 'fa-pencil',
                    title: 'Atualizar',
                    unclose: true,
                    action: function(){
                        $('#modal form button').click();
                    }
                }],
                load: function(){
                    $('#modal form').on('submit',function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        User.editPass({
                            user_id: user_id,
                            user_pass: $('#modal_user_pass').val(),
                            user_pass_confirm: $('#modal_user_pass_confirm').val()
                        });
                    });
                }
            });
        },function(){

        });
    },
    show: function(){
        User.data2form();
        User.submit = function(){
            $('#button-user-edit').click();
        };
        $('#button-user-add').hide();
        $('#button-user-edit').show();
        $('a[href="#tab-1-2"]').click();
    },
    showList: function(){
        User.table.user.clear();
        $('#user_id option').prop('disabled',false);
        $.each( User.users, function( key, user ){
            User.table.user.row.add([
                ( user.image ? '<div style="background-image:url(' + user.image + ')"></div>' : '<i class="fa fa-user-circle-o"></i>' ),
                '<i title="' + ( user.user_active == 'Y' ? 'Ativo' : 'Inativo' ) + '" data-toggle="tooltip" class="fa fa-toggle-' + ( user.user_active == 'Y' ? 'on' : 'off' ) + '"></i>',
                user.user_name,
                user.user_profile_name,
                ( user.user_login ? '<span>' + user.user_login + '</span>' + user.user_login_br : '--' ),
                User.actions(user)
            ]);
            $('#user_id option[value="' + user.user_id + '"]').prop('disabled',true);
        });
        User.table.user.draw();
        $('#user_id').selectpicker('refresh');
    },
    showPrices: function(){
        $.each( User.prices, function(key,price){
            $('#user_price_id').append($('<option>',{
                value: price.price_id,
                text: price.price_code + ' - ' + price.price_name
            }));
        });
        $('#user_price_id').selectpicker('refresh');
    },
    showUserCompany: function(){
        User.table.user_companies.clear();
        $('#user_company_id option').prop('disabled',false);
        $.each( User.user.companies, function( key, company ){
            User.table.user_companies.row.add([
                ('0'+company.company_id).slice(-2),
                company.company_short_name,
                '<span>' + company.user_company_main + '</span><a data-toggle="tooltip" title="Principal" data-key="' + key + '" data-action="main" href="#"><i class="fa fa-star' + ( company.user_company_main == 'N' ? '-o' : '' ) + '"></i></a>',
                '<a data-toggle="tooltip" title="Remover Empresa" data-key="' + key + '" data-action="del" href="#"><i class="fa fa-trash-o"></i></a>'
            ]);
            $('#user_company_id option[value="' + company.company_id + '"]').prop('disabled',true);
        });
        User.table.user_companies.draw();
        $('#user_company_id').selectpicker('refresh');
    },
    showUserPrice: function(){
        User.table.user_prices.clear();
        $('#user_price_id option').prop('disabled',false);
        $.each( User.user.prices, function( key, price ){
            User.table.user_prices.row.add([
                price.price_code,
                price.price_name,
                '<a data-toggle="tooltip" title="Remover Preço" data-key="' + key + '" data-action="del" href="#"><i class="fa fa-trash-o"></i></a>'
            ]);
            $('#user_price_id option[value="' + price.price_id + '"]').prop('disabled',true);
        });
        User.table.user_prices.draw();
        $('#user_price_id').selectpicker('refresh');
    },
    validate: function(){
        if( User.action == 'add' && !User.user.user_id ){
            global.validateMessage('O usuário do ERP deverá ser selecionado.');
            return false;
        }
        if( !User.user.user_name.length ){
            global.validateMessage('Informe o nome do usuário.');
            return false;
        }
        if( !User.user.user_profile_id.length ){
            global.validateMessage('O perfil do usuário deverá ser selecionado.');
            return false;
        }
        if( !User.user.user_mail.length ){
            global.validateMessage('Informe o e-mail do usuário.');
            return false;
        }
        if( User.action == 'add' ) {
            if (!User.user.user_user.length) {
                global.validateMessage('Informe o login do usuário.');
                return false;
            }
            if (!User.user.user_pass.length) {
                global.validateMessage('Informe a senha do usuário.');
                return false;
            }
            if (!User.user.user_pass_confirm.length) {
                global.validateMessage('Confirme a senha do usuário.');
                return false;
            }
            if (User.user.user_pass != User.user.user_pass_confirm) {
                global.validateMessage('A senha não confere com o campo de confirmação.');
                return false;
            }
        }
        return true;
    }
};

UserImage = {
    del: function(){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente remover a imagem do usuário?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    global.post({
                        url: global.uri.uri_public_api + 'image.php?action=del',
                        data: {
                            image_id: User.user.user_id,
                            image_dir: 'user'
                        }
                    },function(){
                        $('#button-image-user-remove').prop('disabled',true);
                        User.user.image = null;
                        UserImage.show();
                    });
                }
            }]
        });
    },
    events: function(){
        $('#file-image-user').change(function(){
            User.form2data();
            UserImage.up();
        });
        $('#button-image-user-remove').click(function(){
            User.form2data();
            UserImage.del();
        });
    },
    show: function(){
        if( !!User.user.image ) {
            $('#user-image-cover .text').hide();
        } else {
            $('#user-image-cover .text').show();
        }
        $('#user-image-cover').css({
            'background-image': !!User.user.image ? 'url(' + User.user.image + ')' : ''
        });
    },
    up: function(){
        var data = new FormData();
        data.append('image_id',User.user.user_id);
        data.append('image_dir','user');
        data.append('file[]',$('#file-image-user')[0].files[0]);
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=up',
            data: data,
            cache: false,
            dataType: 'json',
            contentType: false,
            processData: false
        },function(data){
            User.user.image = data.images[0].image;
            $('#button-image-user-remove').prop('disabled',false);
            UserImage.show();
        });
        $('#file-image-user').filestyle('clear');
    }
};
