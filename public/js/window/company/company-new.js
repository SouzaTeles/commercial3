var company_id = global.url.searchParams.get('company_id');

$(document).ready(function(){
    Person.events();
    Company.events();
    CompanyImage.events();
    Company.getExternal();
    if( !!company_id ){
        Company.get(company_id);
    } else {
        Company.init();
        Company.data2form();
    }
    global.mask();
    global.unLoader();

});

CompanyImage = {
    del: function(){
        if( !Company.company.company_id ){
            Company.company.image = null;
            CompanyImage.show();
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
                            image_dir: 'company',
                            image_id: Company.company.company_id
                        },
                        dataType: 'json'
                    },function(){
                        $('#button-image-remove').prop('disabled',true);
                        Company.company.image = null;
                        CompanyImage.show();
                    });
                }
            }]
        });
    },
    events: function(){
        $('#file').change(function(){
            if( !!Company.company.company_id ){
                CompanyImage.up();
            } else {
                CompanyImage.preview();
            }
        });
        $('#button-image-remove').click(function(){
            CompanyImage.del();
        });
    },
    preview: function(){
        var reader = new FileReader();
        reader.onload = function(e){
            Company.company.image = e.target.result;
            CompanyImage.show();
        };
        reader.readAsDataURL($('#file')[0].files[0]);
    },
    show: function(){
        $('header .company-image, #company-image-cover').css({
            'background-image': 'url(' + (Company.company.image || '../../../commercial3/images/empty-image.png') + ')'
        });
        $('#button-image-remove').prop('disabled',!Company.company.image);
    },
    up: function(){
        var data = new FormData();
        data.append('image_id',Company.company.company_id);
        data.append('image_dir','company');
        data.append('file[]',$('#file')[0].files[0]);
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=up',
            data: data,
            cache: false,
            dataType: 'json',
            contentType: false,
            processData: false
        },function(data){
            Company.company.image = data.images[0].image;
            CompanyImage.show();
            $('#button-image-remove').prop('disabled',false);
        });
        $('#file').filestyle('clear');
    }
};

Company = {
    company: {},
    people: [],
    external: [],
    profiles: [],
    add: function(){
        global.post({
            url: global.uri.uri_public_api + 'company.php?action=insert',
            data: Company.company,
            dataType: 'json'
        }, function(data){
            if( !!window.opener ) window.opener.Company.getList();
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
    data2form: function(){
        $('#company_id').val(Company.company.company_id).prop('disabled',!!company_id).selectpicker('refresh');
        $('#parent_id').selectpicker('val',Company.company.parent_id);
        $('#company_active').bootstrapToggle(Company.company.company_active == 'Y' ? 'on' : 'off');
        $('#company_target').bootstrapToggle(Company.company.company_target == 'Y' ? 'on' : 'off');
        $('#company_st').bootstrapToggle(Company.company.company_st == 'Y' ? 'on' : 'off');
        $('#company_credit').bootstrapToggle(Company.company.company_credit == 'Y' ? 'on' : 'off');
        $('#company_color').spectrum({
            color: Company.company.company_color,
            preferredFormat: "hex3"
        });
        $('#company_name').val(Company.company.company_name);
        $('#company_short_name').val(Company.company.company_short_name);
        $('header .company-name').text(Company.company.company_name);
        $('#button-image-company-remove').prop('disabled',!Company.company.image);
        $('#delivery_days').val(Company.company.delivery_days);
        $('#person_code').val(Person.person.person_code).attr('data-value',Person.person.person_code);
        $('#person_name').val(Person.person.person_name);
        $('#company_budget_message').val(Company.company.company_budget_message);
        CompanyImage.show();
        Company.showMap();
    },
    edit: function(){
        global.post({
            url: global.uri.uri_public_api + 'company.php?action=edit',
            data: Company.company,
            dataType: 'json'
        }, function(data){
            if( !!window.opener ) window.opener.Company.getList();
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
            Company.form2data();
            if( Company.validate() ) {
                Company.add();
            }
        });
        $('#button-edit').click(function(e){
            Company.form2data();
            if( Company.validate() ) {
                Company.edit();
            }
        });
        $('#button-cancel').click(function(e){
            if( !!window.opener ){
                window.close();
            } else {
                location.reload();
            }
        });
        $('#company_name').on('keyup',function(){
            $('header .company-name').text($(this).val());
        });
        $('#button-parent-remove').click(function() {
            Company.company.parent_id = null;
            $('#parent_id').selectpicker('val','default');
        });
        $('#company_id').on('change',function(){
            $('#company_name').val(Company.external[$(this).find('option:selected').index()-1].NmEmpresa);
        });
        global.toggle();
    },
    form2data: function(){
        Company.company.company_id = $('#company_id').val();
        Company.company.parent_id = $('#parent_id').val();
        Company.company.company_active = $('#company_active').prop('checked') ? 'Y' : 'N';
        Company.company.company_target = $('#company_target').prop('checked') ? 'Y' : 'N';
        Company.company.company_st = $('#company_st').prop('checked') ? 'Y' : 'N';
        Company.company.company_credit = $('#company_credit').prop('checked') ? 'Y' : 'N';
        Company.company.company_color = $('#company_color').spectrum('get').toHexString();
        Company.company.company_name = $('#company_name').val();
        Company.company.company_short_name = $('#company_short_name').val();
        Company.company.delivery_days = $('#delivery_days').val();
        Company.company.company_budget_message = $('#company_budget_message').val();
        Company.company.company_latitude = map.markers[0].getPosition().lat();
        Company.company.company_longitude = map.markers[0].getPosition().lng();
    },
    get: function(company_id){
        global.post({
            url: global.uri.uri_public_api + 'company.php?action=get',
            data: {
                company_id: company_id,
                get_company_person: 1
            },
            dataType: 'json'
        },function(company){
            Person.person = company.person;
            delete company.person;
            Company.company = company;
            Company.data2form();
        });
    },
    getExternal: function(){
        global.post({
            url: global.uri.uri_public_api + 'company.php?action=external',
            dataType: 'json'
        },function(data){
            Company.external = data;
            Company.showExternal();
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
            Company.people = data;
            Company.showPeople();
        });
    },
    getProfile: function(){
        global.post({
            url: global.uri.uri_public_api + 'profile.php?action=getList',
            dataType: 'json'
        },function(data){
            Company.profiles = data;
            Company.showProfile();
        });
    },
    init: function(){
        Company.company = {
            image: null,
            company_id: null,
            parent_id: null,
            company_active: 'Y',
            company_target: 'Y',
            company_st: 'Y',
            company_credit: 'Y',
            company_color: '#ffffff',
            company_name: '',
            company_short_name: '',
            delivery_days: 3,
            company_consumer_id: null,
            company_budget_message: '',
            company_latitude: -22.6532399,
            company_longitude: -42.9806331
        };
    },
    showExternal: function(){
        $.each( Company.external, function(key,external){
            $('#company_id').append($('<option>',{
                value: external.CdEmpresa,
                text: ('0'+external.CdEmpresa).slice(-2) + ' - ' + external.NmEmpresa
            }));
            $('#parent_id').append($('<option>',{
                value: external.CdEmpresa,
                text: ('0'+external.CdEmpresa).slice(-2) + ' - ' + external.NmEmpresa
            }));
        });
        $('#company_id').selectpicker('refresh');
        $('#parent_id').selectpicker('refresh');
    },
    showMap: function(){
        var point = {
            lat: Company.company.company_latitude,
            lng: Company.company.company_longitude
        };
        map.init({
            selector: 'map',
            mapTypeControl: true,
            zoom: (!!Company.company.company_id ? 15 : 9),
            point: point,
            onClick: function(event){
                map.markers[0].setPosition({
                    lat: event.latLng.lat(),
                    lng: event.latLng.lng()
                });
            }
        });
        map.addMarker({
            draggable: true,
            title: 'Geolocalização',
            point: point
        });
        setTimeout(function(){
            google.maps.event.trigger(map.markers[0], 'click');
            google.maps.event.trigger(map.map, 'resize');
            map.map.setCenter(point);
        },1000);
    },
    validate: function(){
        if( !Company.company.company_id ){
            global.validateMessage('Relacione o cadastro da empresa ao ERP.');
            return false;
        }
        if( !Company.company.company_name.length ){
            global.validateMessage('Informe o nome da empresa.');
            return false;
        }
        if( !Company.company.company_short_name.length ){
            global.validateMessage('Informe um nome curto para a empresa.');
            return false;
        }
        if( !Company.company.delivery_days.length ){
            global.validateMessage('Informe o prazo para entrega.');
            return false;
        }
        if( !Company.company.company_consumer_id ){
            global.validateMessage('Informe o consumidor padrão.');
            return false;
        }
        if( !Company.company.company_budget_message.length ){
            global.validateMessage('Informe a mensagem de impressão do orçamento.');
            return false;
        }
        return true;
    }
};

Person = {
    person: {
        person_code: '',
        person_name: ''
    },
    events: function(){
        $('#person_code').keyup(function(e){
            var keycode = e.keyCode || e.which;
            if( keycode == '13' ){
                e.preventDefault();
                e.stopPropagation();
                var person_id = !$(this).val().length ? Company.company.company_consumer_id : null;
                var person_code = $(this).val().length ? $(this).val() : null;
                if( (!!person_id || !!person_code) && global.posts < 1 ){
                    Person.get({
                        person_id: person_id,
                        person_code: person_code
                    });
                }
            }
        }).on('blur',function(){
            $(this).val($(this).attr('data-value'));
        });
        $('#button-consumer-search').click(function(){
            Person.search();
        });
    },
    get: function(data){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=get',
            data: {
                person_id: data.person_id,
                person_code: data.person_code,
                category_id: global.config.person.client_category_id
            },
            dataType: 'json'
        }, function(person){
            Company.company.company_consumer_id = person.person_id;
            Person.person = person;
            $('#person_code').val(Person.person.person_code).attr('data-value',Person.person.person_code);
            $('#person_name').val(Person.person.person_name);
        });
    },
    search: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-person-search',
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-search',
                id: 'modal-person-search',
                class: 'modal-person-search',
                title: 'Localização de Pessoa',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Fechar'
                }],
                shown: function(){
                    $('#modal_person_name').focus();
                    ModalPersonSearch.data.categories.push(global.config.person.client_category_id);
                    ModalPersonSearch.success = function(person){
                        Person.get({person_id: person.person_id});
                    }
                },
                hidden: function(){
                    $('#consumer_code').focus();
                }
            });
        });
    }
};