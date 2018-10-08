var user_id = global.url.searchParams.get('user_id');

$(document).ready(function(){
    User.events();
    Image.events();
    Company.events();
    Price.events();
    User.getExternal();
    User.getProfile();
    User.getPeople();
    if( !!user_id ){
        User.get(user_id);
    } else {
        Company.getList();
        Price.getList();
        User.init();
    }
    global.mask();
    global.unLoader();

});

Image = {
    del: function(){
        if( !User.user.user_id ){
            User.user.image = null;
            Image.show();
            return;
        }
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
                            image_dir: 'user',
                            image_id: User.user.user_id
                        },
                        dataType: 'json'
                    },function(){
                        $('#button-image-remove').prop('disabled',true);
                        User.user.image = null;
                        Image.show();
                    });
                }
            }]
        });
    },
    events: function(){
        $('#file').change(function(){
            if( !!User.user.user_id ){
                Image.up();
            } else {
                Image.preview();
            }
        });
        $('#button-image-remove').click(function(){
            Image.del();
        });
    },
    preview: function(){
        var reader = new FileReader();
        reader.onload = function(e){
            User.user.image = e.target.result;
            Image.show();
        };
        reader.readAsDataURL($('#file')[0].files[0]);
    },
    show: function(){
        $('header .user-image, #user-image-cover').css({
            'background-image': 'url(' + (User.user.image || '../../../commercial3/images/empty-image.png') + ')'
        });
        $('#button-image-remove').prop('disabled',!User.user.image);
    },
    up: function(){
        var data = new FormData();
        data.append('image_id',User.user.user_id);
        data.append('image_dir','user');
        data.append('file[]',$('#file')[0].files[0]);
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=up',
            data: data,
            cache: false,
            dataType: 'json',
            contentType: false,
            processData: false
        },function(data){
            User.user.image = data.images[0].image;
            Image.show();
            $('#button-image-remove').prop('disabled',false);
        });
        $('#file').filestyle('clear');
    }
};

User = {
    erp: [],
    user: {},
    people: [],
    external: [],
    profiles: [],
    add: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=insert',
            data: User.user,
            dataType: 'json'
        }, function(data){
            if( !!window.opener ) window.opener.User.getList();
            global.modal({
                icon: 'fa-info',
                title: 'Informação',
                html: '<p>' + data.message + '</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    if( !!window.opener ){
                        window.close();
                    } else {
                        location.reload();
                    }
                }
            });
        });
    },
    access: function(){
        var access = User.user.user_access;
        $('#max_discount').val(global.float2Br(access.max_discount));
        $('#credit_authorization').bootstrapToggle(access.credit_authorization == 'Y' ? 'on' : 'off');
        $('#only_session').bootstrapToggle(access.only_session == 'Y' ? 'on' : 'off');
        $('#mobile_access').bootstrapToggle(access.mobile_access == 'Y' ? 'on' : 'off');
        $('#mobile_unlock').bootstrapToggle(access.mobile_unlock == 'Y' ? 'on' : 'off');
        $('#budget_delivery').bootstrapToggle(access.budget_delivery == 'Y' ? 'on' : 'off');
        $('#audit').bootstrapToggle(access.audit == 'Y' ? 'on' : 'off');
    },
    data2form: function(){
        $('#external_id').val(User.user.external_id).selectpicker('refresh');
        $('#person_id').val(User.user.person_id).selectpicker('refresh');
        $('#user_active').bootstrapToggle(User.user.user_active == 'Y' ? 'on' : 'off');
        $('#user_name').val(User.user.user_name);
        $('header .user-name').text(User.user.user_name);
        $('#user_profile_id').val(User.user.user_profile_id).selectpicker('refresh');
        $('#user_email').val(User.user.user_email);
        $('#user_user').val(User.user.user_user).prop('readonly',true);
        $('#user_pass').val(User.user.user_id ? '******' : '').prop('readonly',true);
        $('#user_pass_confirm').val(User.user.user_id ? '******' : '').prop('readonly',true);
        $('#button-image-user-remove').prop('disabled',!User.user.image);
        User.access();
        Image.show();
    },
    edit: function(){
        if( !!window.opener ) window.opener.User.getList();
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
                }],
                hidden: function(){
                    if( !!window.opener ){
                        window.close();
                    } else {
                        location.reload();
                    }
                }
            });
        });
    },
    events: function(){
        $('#button-add').click(function(e){
            User.form2data();
            if( User.validate() ) {
                User.add();
            }
        });
        $('#button-edit').click(function(e){
            User.form2data();
            if( User.validate() ) {
                User.edit();
            }
        });
        $('#button-cancel').click(function(e){
            if( !!window.opener ){
                window.close();
            } else {
                location.reload();
            }
        });
        $('#user_name').on('keyup',function(){
            $('header .user-name').text($(this).val());
        });
        global.toggle();
    },
    form2data: function(){
        User.user.external_id = $('#external_id').val();
        User.user.user_active = $('#user_active').prop('checked') ? 'Y' : 'N';
        User.user.client_id = $('#user_client_id') ? $('#user_client_id').val() : null;
        User.user.user_name = $('#user_name').val();
        User.user.user_profile_id = $('#user_profile_id').selectpicker('val');
        User.user.user_email = $('#user_email').val();
        User.user.user_user = $('#user_user').val();
        User.user.user_pass = $('#user_pass').val();
        User.user.user_pass_confirm = $('#user_pass_confirm').val();
        User.user.access = [
            {'name': 'max_discount', 'value': global.br2Float($('#max_discount').val()), 'type': 'bool' },
            {'name': 'credit_authorization', 'value': $('#credit_authorization').prop('checked') ? 'Y' : 'N', 'type': 'bool' },
            {'name': 'only_session', 'value': $('#only_session').prop('checked') ? 'Y' : 'N', 'type': 'bool' },
            {'name': 'mobile_access', 'value': $('#mobile_access').prop('checked') ? 'Y' : 'N', 'type': 'bool' },
            {'name': 'mobile_unlock', 'value': $('#mobile_unlock').prop('checked') ? 'Y' : 'N', 'type': 'bool' },
            {'name': 'budget_delivery', 'value': $('#budget_delivery').prop('checked') ? 'Y' : 'N', 'type': 'bool' },
            {'name': 'audit', 'value': $('#audit').prop('checked') ? 'Y' : 'N', 'type': 'bool' }
        ];
    },
    get: function(user_id){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=get',
            data: {
                user_id: user_id,
                get_user_price: 1,
                get_user_access: 1,
                get_user_company: 1
            },
            dataType: 'json'
        },function(user){
            User.user = user;
            User.data2form();
            Company.getList();
            Price.getList();
        });
    },
    getExternal: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=external',
            dataType: 'json'
        },function(data){
            User.external = data;
            User.showExternal();
        });
    },
    getPeople: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=getList',
            data: {
                limit: 500,
                person_active: 'Y',
                person_category_id: global.config.person.employ_category_id
            },
            dataType: 'json'
        },function(data){
            User.people = data;
            User.showPeople();
        });
    },
    getProfile: function(){
        global.post({
            url: global.uri.uri_public_api + 'profile.php?action=getList',
            dataType: 'json'
        },function(data){
            User.profiles = data;
            User.showProfile();
        });
    },
    init: function(){
        User.user = {
            image: null,
            external_id: null,
            person_id: null,
            user_profile_id: null,
            user_active: 'Y',
            user_name: '',
            user_email: '',
            user_user: '',
            user_pass: '',
            user_pass_confirm: '',
            companies: [],
            prices: [],
            access: []
        };
    },
    showExternal: function(){
        $.each( User.external, function(key,external){
            $('#external_id').append($('<option>',{
                value: external.user_id,
                text: external.user_name
            }));
        });
        $('#external_id').selectpicker('refresh');
    },
    showPeople: function(){
        $.each( User.people, function(key,person){
            $('#person_id').append($('<option>',{
                value: person.person_id,
                text: person.person_code + ' - ' + person.person_name
            }));
        });
        $('#person_id').selectpicker('refresh');
    },
    showProfile: function(){
        $.each( User.profiles, function(key,profile){
            $('#user_profile_id').append($('<option>',{
                value: profile.user_profile_id,
                text: profile.user_profile_name
            }));
        });
        $('#user_profile_id').selectpicker('refresh');
    },
    validate: function(){
        if( !User.user.user_id && !User.user.external_id ){
            global.validateMessage('O usuário externo deverá ser selecionado.');
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
        if( !User.user.user_email.length ){
            global.validateMessage('Informe o e-mail do usuário.');
            return false;
        }
        if( !global.validateEmail(User.user.user_email) ){
            global.validateMessage('Informe um e-mail válido.');
            return false;
        }
        if( !User.user.user_id ) {
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

Company = {
    companies: [],
    table: global.table({
        selector: '#table-companies',
        noControls: [3],
        order: [[0, "asc"]]
    }),
    add: function(){
        var key = $('#companies option:selected').index();
        var company = Company.companies[key-1];
        User.user.companies.push({
            company_id: company.company_id,
            company_short_name: company.company_short_name,
            user_company_main: 'N'
        });
        $('#companies').selectpicker('val','default');
        Company.showSelected();
    },
    del: function(key){
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
                    $('#companies option[value="' + User.user.companies[key].company_id + '"]').prop('disabled',false);
                    User.user.companies.splice(key,1);
                    Company.showSelected();
                }
            }]
        });
    },
    events: function(){
        $('#button-company-add').click(function(){
            if( !$('#companies').val().length ){
                global.validateMessage('A empresa deverá ser selecionada.');
                return;
            }
            Company.add();
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'company.php?action=getList',
            dataType: 'json'
        },function(data){
            Company.companies = data;
            Company.showList();
        });
    },
    main: function(key){
        $.each( User.user.companies, function(k,company){
            company.user_company_main = ( k == key ? 'Y' : 'N' );
        });
        Company.showSelected();
    },
    showList: function(){
        $.each( Company.companies, function(key,company){
            $('#companies').append($('<option>',{
                'value': company.company_id,
                'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + company.company_code + ' - ' + company.company_name
            }));
        });
        $('#companies').selectpicker('refresh');
        if( !!user_id ) Company.showSelected();
    },
    showSelected: function(){
        Company.table.clear();
        $('#user_company_id option').prop('disabled',false);
        $.each( User.user.companies, function( key, company ){
            company.company_code = ('0'+company.company_id).slice(-2);
            Company.table.row.add([
                company.company_code,
                company.company_short_name,
                '<span>' + company.user_company_main + '</span><button class="btn btn-empty-gold" data-toggle="tooltip" title="Principal" data-key="' + key + '" data-action="main"><i class="fa fa-star' + ( company.user_company_main == 'N' ? '-o' : '' ) + '"></i></button>',
                '<button class="btn btn-empty-red-light" data-toggle="tooltip" title="Remover" data-key="' + key + '" data-action="del"><i class="fa fa-trash-o"></i></button>'
            ]);
            $('#companies option[value="' + company.company_id + '"]').prop('disabled',true);
        });
        Company.table.draw();
        $('#companies').selectpicker('refresh');
        var table = $('#table-companies');
        $(table).find('button[data-action="main"]').click(function(){
            Company.main($(this).attr('data-key'));
        });
        $(table).find('button[data-action="del"]').click(function(){
            Company.del($(this).attr('data-key'))
        });
        global.tooltip();
    }
};

Price = {
    prices: [],
    table: global.table({
        selector: '#table-prices',
        noControls: [2],
        order: [[0, "asc"]]
    }),
    add: function(){
        var key = $('#prices option:selected').index();
        var price = Price.prices[key-1];
        User.user.prices.push({
            price_id: price.price_id,
            price_code: price.price_code,
            price_name: price.price_name
        });
        $('#user_price_id').selectpicker('val','default');
        $('#user_price_id').selectpicker('refresh');
        Price.showSelected();
    },
    del: function(key){
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
                    $('#prices option[value="' + User.user.prices[key].price_id + '"]').prop('disabled',false);
                    User.user.prices.splice(key,1);
                    Price.showSelected();
                    $('#prices').selectpicker('refresh');
                }
            }]
        });
    },
    events: function(){
        $('#button-price-add').click(function(){
            if( !$('#prices').val().length ){
                global.validateMessage('O preço deverá ser selecionado.');
                return;
            }
            Price.add();
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'price.php?action=getList',
            dataType: 'json'
        },function(data){
            Price.prices = data;
            Price.showList();
        });
    },
    showList: function(){
        $.each( Price.prices, function(key,price){
            $('#prices').append($('<option>',{
                value: price.price_id,
                text: price.price_code + ' - ' + price.price_name
            }));
        });
        $('#prices').selectpicker('refresh');
        if( !!user_id ) Price.showSelected();
    },
    showSelected: function(){
        Price.table.clear();
        $('#prices option').prop('disabled',false);
        $.each( User.user.prices, function( key, price ){
            Price.table.row.add([
                price.price_code,
                price.price_name,
                '<button class="btn btn-empty-red-light" data-toggle="tooltip" title="Remover" data-key="' + key + '" data-action="del"><i class="fa fa-trash-o"></i></button>'
            ]);
            $('#prices option[value="' + price.price_id + '"]').prop('disabled',true);
        });
        Price.table.draw();
        $('#prices').selectpicker('refresh');
        global.tooltip();
        var table = $('#table-prices');
        $(table).find('button[data-action="del"]').click(function(){
            Price.del($(this).attr('data-key'));
        });
    }
};