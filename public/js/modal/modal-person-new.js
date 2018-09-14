$(document).ready(function(){

    ModalPersonNew.events();
    ModalPersonNew.getContactTypes();
    ModalPersonNew.getAddressTypes();

    global.mask();
    global.tooltip();
    global.selectpicker();

});

ModalPersonNew = {
    person: {
        person_name: '',
        person_short_name: '',
        person_type: 'F',
        person_document: '',
        person_gender: '',
        person_birth: '',
        person_categories: global.config.person.client_category_id,
        address: {
            address_cep: '',
            address_type: '',
            address_public_place: '',
            address_number: '',
            address_icms_type: '2',
            address_ie: 'ISENTO',
            address_note: '',
            uf_id: null,
            city_id: null,
            city_name: '',
            district_id: null,
            district_name: '',
            contacts: []
        }
    },
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0,
        size: 3
    },
    addressTypes: [],
    contactTypes: [],
    add: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=insert',
            data: ModalPersonNew.person,
            dataType: 'json'
        },function(data){
            ModalPersonNew.person.address.address_main = 'Y';
            ModalPersonNew.person.address.address_code = '01';
            ModalPersonNew.person.address.address_active = 'Y';
            ModalPersonNew.person.address.address_delivery = 'Y';
            Person.person = {
                image: null,
                person_active: 'Y',
                person_credit_limit: 0,
                person_id: data.person_id,
                person_code: data.person_code,
                person_name: ModalPersonNew.person.person_name,
                person_type: ModalPersonNew.person.person_type,
                person_birth: ModalPersonNew.person.person_birth,
                person_gender: ModalPersonNew.person.person_gender,
                person_document: ModalPersonNew.person.person_document,
                person_short_name: ModalPersonNew.person.person_short_name,
                person_cpf: ModalPersonNew.person.person_type == 'F' ? ModalPersonNew.person.person_document : null,
                person_cnpj: ModalPersonNew.person.person_type == 'J' ? ModalPersonNew.person.person_document : null,
                address: [ModalPersonNew.person.address],
                credits: [],
                credit_limit: {
                    delay: 0,
                    balance: 0,
                    expired_value: 0,
                    expiring_value: 0,
                    expired_quantity: 0,
                    expiring_quantity: 0,
                    receivable: []
                },
                attributes: []
            };
            Address.getAddress = true;
            Budget.budget.client_id = data.person_id;
            Address.delivery = ModalPersonNew.person.address;
            Budget.budget.address_code = '01';
            Budget.budget.address_uf_id = ModalPersonNew.person.address.uf_id;
            if( ModalPersonNew.person.address.address_note.length ){
                Budget.budget.budget_note_document =  '\n\nObs de Entrega: ' + ModalPersonNew.person.address.address_note;
            }
            Person.data2form();
            Address.showDelivery();
            $('#modal-person-new').modal('hide');
            setTimeout(function(){
                global.modal({
                    icon: 'fa-info',
                    title: 'Informação',
                    html: '<p>O cadastro do cliente foi realizado com sucesso.<br/><br/>Código: ' + data.person_code +'<br/>Nome: ' + ModalPersonNew.person.person_name + '</p>',
                    buttons: [{
                        icon: 'fa-check',
                        title: 'Ok'
                    }]
                });
            },500);
        });
    },
    events: function(){
        $('#modal_person_type').on('change',function(){
            $('label[for="modal_person_document"]').text($(this).val() == 'F' ? 'CPF' : 'CNPJ');
            $('#modal_person_document').unmask().attr('data-to-mask',($(this).val() == 'F' ? 'cpf' : 'cnpj')).focus().select();
            global.mask();
        });
        $('#modal_person_birth').datepicker({
            format: 'dd/mm/yyyy',
            zIndex: global.modals + 1050
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val('');
            }
        });
        $('#modal_address_cep').on('keyup',function(e){
            var keycode = e.keyCode || e.which;
            if( keycode == '13' ){
                if( $(this).val().length < 9 ){
                    global.validateMessage('O Cep não é válido.');
                    return;
                }
                ModalPersonNew.getCep($(this).val());
            }
        });
        $('#button-cep-search').click(function(){
            global.post({
                url: global.uri.uri_public_api + 'modal.php?modal=modal-cep-search',
                dataType: 'html'
            },function(html){
                global.modal({
                    icon: 'fa-map-marker',
                    id: 'modal-cep-search',
                    class: 'modal-cep-search',
                    title: 'Localizar CEP',
                    html: html,
                    buttons: [{
                        icon: 'fa-times',
                        title: 'Fechar'
                    }]
                });
            });
        });
        $('#modal_city_name').on('keyup',function(){
            if( $(this).val().length >= ModalPersonNew.typeahead.size && $(this).val() != ModalPersonNew.typeahead.last ){
                clearTimeout(ModalPersonNew.typeahead.timer);
                ModalPersonNew.typeahead.last = $(this).val();
                ModalPersonNew.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#modal_city_name',
                        data: {
                            limit: ModalPersonNew.typeahead.items,
                            city_name: $('#modal_city_name').val()
                        },
                        url: global.uri.uri_public_api + 'city.php?action=typeahead',
                        callBack: function(item){
                            ModalPersonNew.person.address.uf_id = item.uf_id;
                            ModalPersonNew.person.address.city_id = item.item_id;
                            ModalPersonNew.person.address.city_name = item.item_name;
                        }
                    });
                },ModalPersonNew.typeahead.delay);
            }
        });
        $('#button-address-city-remove').click(function(){
            $('#modal_city_name').val('').attr('data-value','');
            ModalPersonNew.person.address.city_id = null;
            ModalPersonNew.person.address.city_name = '';
        });
        $('#modal_district_name').on('keyup',function(){
            if( $(this).val().length >= ModalPersonNew.typeahead.size && $(this).val() != ModalPersonNew.typeahead.last ){
                clearTimeout(ModalPersonNew.typeahead.timer);
                ModalPersonNew.typeahead.last = $(this).val();
                ModalPersonNew.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#modal_district_name',
                        data: {
                            limit: ModalPersonNew.typeahead.items,
                            district_name: $('#modal_district_name').val()
                        },
                        url: global.uri.uri_public_api + 'district.php?action=typeahead',
                        callBack: function(item){
                            ModalPersonNew.person.address.district_id = item.item_id;
                            ModalPersonNew.person.address.district_name = item.item_name;
                        }
                    });
                },ModalPersonNew.typeahead.delay);
            }
        });
        $('#button-address-district-remove').click(function(){
            $('#modal_district_name').val('').attr('data-value','');
            ModalPersonNew.person.address.district_id = null;
            ModalPersonNew.person.address.district_name = '';
        });
        $('#modal_address_icms_type').on('change',function(){
            $('#modal_address_ie').prop('readonly',$(this).val() == '2').val(($(this).val() == '2' ? 'ISENTO' : ''));
        });
    },
    form2data: function(success){
        ModalPersonNew.person.person_name = $('#modal_person_name').val();
        ModalPersonNew.person.person_short_name = $('#modal_person_short_name').val();
        ModalPersonNew.person.person_type = $('#modal_person_type').val();
        ModalPersonNew.person.person_document = $('#modal_person_document').val();
        ModalPersonNew.person.person_gender = $('#modal_person_gender').val();
        ModalPersonNew.person.person_birth = $('#modal_person_birth').val().length ? global.date2Us($('#modal_person_birth').val()) : '';
        ModalPersonNew.person.address.address_cep = $('#modal_address_cep').val();
        ModalPersonNew.person.address.address_type = $('#modal_address_type').val();
        ModalPersonNew.person.address.address_public_place = $('#modal_address_public_place').val();
        ModalPersonNew.person.address.address_number = $('#modal_address_number').val();
        ModalPersonNew.person.address.city_name = $('#modal_city_name').val();
        ModalPersonNew.person.address.district_name = $('#modal_district_name').val();
        ModalPersonNew.person.address.address_icms_type = $('#modal_address_icms_type').val();
        ModalPersonNew.person.address.address_ie = $('#modal_address_ie').val();
        ModalPersonNew.person.address.address_ie = $('#modal_address_ie').val();
        ModalPersonNew.person.address.contacts = [];
        $.each(ModalPersonNew.contactTypes,function(key,contactType){
            var contact = $('input[data-id=' + contactType.contact_type_id + ']').val();
            if( contact.length ){
                ModalPersonNew.person.address.contacts.push({
                    contact_type_id: contactType.contact_type_id,
                    contact_value: contact
                });
            }
        });
        ModalPersonNew.person.address.address_note = $('#modal_address_note').val();
        if( !!success ) success();
    },
    getAddressTypes: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=getAddressTypes',
            dataType: 'json'
        },function(data){
            ModalPersonNew.addressTypes = data;
            ModalPersonNew.showAddressTypes();
        });
    },
    getCep: function(cep_code){
        global.post({
            url: global.uri.uri_public_api + 'cep.php?action=get',
            data: { cep_code: cep_code },
            dataType: 'json'
        },function(data){
            ModalPersonNew.person.address.address_cep = data.cep_code;
            ModalPersonNew.person.address.address_type = data.public_place_type.toUpperCase();
            ModalPersonNew.person.address.address_public_place = data.public_place;
            ModalPersonNew.person.address.uf_id = data.uf_id;
            ModalPersonNew.person.address.city_id = data.city_id;
            ModalPersonNew.person.address.district_id = data.district_id;
            $('#modal_address_cep').val(data.cep_code);
            $('#modal_address_type').selectpicker('val',data.public_place_type);
            $('#modal_address_public_place').val(data.public_place);
            $('#modal_city_name').val(data.city_name + ' - ' + data.uf_id).attr('data-value',data.city_name + ' - ' + data.uf_id);
            $('#modal_district_name').val(data.district_name).attr('data-value',data.district_name);
            $('#modal_address_number').focus();
        });
    },
    getContactTypes: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=getContactTypes',
            dataType: 'json'
        },function(data){
            ModalPersonNew.contactTypes = data;
            ModalPersonNew.showContactTypes();
        });
    },
    showAddressTypes: function(){
        $.each(ModalPersonNew.addressTypes,function(key,addressType){
            $('#modal_address_type').append($('<option>',{
                'value': addressType.address_type.toUpperCase(),
                'text': addressType.address_type.toUpperCase()
            }));
        });
        $('#modal_address_type').selectpicker('refresh');
    },
    showContactTypes: function(){
        $.each(ModalPersonNew.contactTypes,function(key,contactType){
            $('#modal-contacts').append(
                '<div class="contact">' +
                    '<div class="contact-name">' + contactType.contact_type_name + '</div>' +
                    '<div class="contact-value">' +
                        '<input data-id="' + contactType.contact_type_id + '" type="text" />' +
                    '</div>' +
                '</div>'
            );
        });
    },
    submit: function(){
        ModalPersonNew.form2data(function(){
            if( ModalPersonNew.validate() ){
                ModalPersonNew.add();
            }
        });
    },
    validate: function(){
        if( !ModalPersonNew.person.person_name.length ){
            global.validateMessage('O nome do cliente deverá ser informado.',function(){
                $('#modal_person_name').focus();
            });
            return false;
        }
        if( !ModalPersonNew.person.person_document.length ){
            global.validateMessage('Informe o ' + ( ModalPersonNew.person.person_type == 'F' ? 'CPF' : 'CNPJ' ) + ' do cliente.',function(){
                $('#modal_person_document').focus();
            });
            return false;
        }
        if( ModalPersonNew.person.person_type == 'F' && !global.validateCPF(ModalPersonNew.person.person_document) ){
            global.validateMessage('O CPF não é válido. Verifique.',function(){
                $('#modal_person_document').focus().select();
            });
            return false;
        }
        if( ModalPersonNew.person.person_type == 'J' && !global.validateCNPJ(ModalPersonNew.person.person_document) ){
            global.validateMessage('O CNPJ não é válido. Verifique.',function(){
                $('#modal_person_document').focus().select();
            });
            return false;
        }
        if( !ModalPersonNew.person.address.address_cep.length ){
            global.validateMessage('O CEP deverá ser informado.',function(){
                $('#modal_address_cep').focus();
            });
            return false;
        }
        if( ModalPersonNew.person.address.address_cep.length != 9 ){
            global.validateMessage('Informe um CEP válido.',function(){
                $('#modal_address_cep').focus();
            });
            return false;
        }
        if( !ModalPersonNew.person.address.address_type ){
            global.validateMessage('O tipo do endereço deverá ser informado.',function(){
                $('#modal_address_type').focus();
            });
            return false;
        }
        if( !ModalPersonNew.person.address.address_public_place.length ){
            global.validateMessage('O logradouro deverá ser informado.',function(){
                $('#modal_address_public_place').focus();
            });
            return false;
        }
        if( !ModalPersonNew.person.address.address_number.length ){
            global.validateMessage('O número deverá ser informado.',function(){
                $('#modal_address_number').focus();
            });
            return false;
        }
        if( !ModalPersonNew.person.address.uf_id ){
            global.validateMessage('O UF da cidade deverá ser informado.',function(){
                $('#modal_city_name').focus();
            });
            return false;
        }
        if( !ModalPersonNew.person.address.city_id ){
            global.validateMessage('A cidade deverá ser informada.',function(){
                $('#modal_city_name').focus();
            });
            return false;
        }
        if( !ModalPersonNew.person.address.district_id ){
            global.validateMessage('O bairro deverá ser informado.',function(){
                $('#modal_district_name').focus();
            });
            return false;
        }
        if( ModalPersonNew.person.address.address_icms_type == 1 && !ModalPersonNew.person.address.address_ie.length ){
            global.validateMessage('A inscrição estadual deverá ser informada.',function(){
                $('#modal_address_ie').focus();
            });
            return false;
        }
        return true;
    },
};