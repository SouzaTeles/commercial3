$(document).ready(function(){

    ModalAddressNew.events();
    ModalAddressNew.getContactTypes();
    ModalAddressNew.getAddressTypes();
    if( !!ModalAddressNew.address.address_code ){
        ModalAddressNew.address.cep_code = ModalAddressNew.address.address_cep;
        ModalAddressNew.address.public_place = ModalAddressNew.address.address_public_place;
        ModalAddressNew.address.public_place_type = ModalAddressNew.address.address_type;
        ModalAddressNew.setCep(ModalAddressNew.address);
        $('#modal_address_note').val(ModalAddressNew.address.address_note);
        $('#modal_address_number').val(ModalAddressNew.address.address_number);
    }

    global.mask();
    global.tooltip();
    global.selectpicker();

});

ModalAddressNew = {
    address: {
        person_id: '',
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
        address_lat: null,
        address_lng: null,
        contacts: []
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
            url: global.uri.uri_public_api + 'address.php?action=insert',
            data: ModalAddressNew.address,
            dataType: 'json'
        },function(data){
            ModalAddressNew.address.address_main = 'N';
            ModalAddressNew.address.address_code = data.address_code;
            Person.person.address.push(ModalAddressNew.address);
            Address.showList();
            setTimeout(function(){
                global.modal({
                    size: 'small',
                    icon: 'fa-info',
                    title: 'Informação',
                    html: '<p>Endereço cadastrado com sucesso.<br/>Código: ' + data.address_code + '</p>',
                    buttons: [{
                        icon: 'fa-check',
                        title: 'Ok'
                    }]
                });
            },500);
            if( !!ModalAddressNew.afterSave ){
                ModalAddressNew.afterSave();
            }
            $('#modal-address-new').modal('hide');
        });
    },
    edit: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=edit',
            data: ModalAddressNew.address,
            dataType: 'json'
        },function(){
            ModalAddressNew.address.city_name = ModalAddressNew.address.city_name.split(' - ')[0];
            Person.person.address[ModalAddressNew.address.key] = ModalAddressNew.address;
            if( Address.delivery && Address.delivery.address_code == ModalAddressNew.address.address_code ){
                Address.delivery = ModalAddressNew.address;
                Address.showDelivery();
            }
            Address.showList();
            setTimeout(function(){
                global.modal({
                    icon: 'fa-info',
                    title: 'Informação',
                    html: '<p>Endereço ' + ModalAddressNew.address.address_code + ' atualizado com sucesso.</p>',
                    buttons: [{
                        icon: 'fa-check',
                        title: 'Ok'
                    }]
                });
            },500);
            $('#modal-address-new').modal('hide');
        });
    },
    events: function(){
        $('#button-address-geolocation').click(function(){
            ModalAddressNew.geolocation();
        });
        $('#modal_address_cep').on('keyup',function(e){
            var keycode = e.keyCode || e.which;
            if( keycode == '13' ){
                if( $(this).val().length < 9 ){
                    global.validateMessage('O Cep não é válido.');
                    return;
                }
                ModalAddressNew.getCep($(this).val());
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
                    }],
                    shown: function(){
                        $('#modal_search_cep_code').focus();
                        ModalCepSearch.success = function(cep){
                            ModalAddressNew.setCep(cep,function(){
                                $('#modal-cep-search').modal('hide');
                            });
                        }
                    },
                    hidden: function(){
                        $('#modal_address_number').focus();
                    }
                });
            });
        });
        $('#modal_city_name').on('keyup',function(){
            if( $(this).val().length >= ModalAddressNew.typeahead.size && $(this).val() != ModalAddressNew.typeahead.last ){
                clearTimeout(ModalAddressNew.typeahead.timer);
                ModalAddressNew.typeahead.last = $(this).val();
                ModalAddressNew.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#modal_city_name',
                        data: {
                            limit: ModalAddressNew.typeahead.items,
                            city_name: $('#modal_city_name').val()
                        },
                        url: global.uri.uri_public_api + 'city.php?action=typeahead',
                        callBack: function(item){
                            ModalAddressNew.address.uf_id = item.uf_id;
                            ModalAddressNew.address.city_id = item.item_id;
                            ModalAddressNew.address.city_name = item.item_name;
                        }
                    });
                },ModalAddressNew.typeahead.delay);
            }
        });
        $('#button-address-city-remove').click(function(){
            $('#modal_city_name').val('').attr('data-value','');
            ModalAddressNew.address.city_id = null;
            ModalAddressNew.address.city_name = '';
        });
        $('#modal_district_name').on('keyup',function(){
            if( $(this).val().length >= ModalAddressNew.typeahead.size && $(this).val() != ModalAddressNew.typeahead.last ){
                clearTimeout(ModalAddressNew.typeahead.timer);
                ModalAddressNew.typeahead.last = $(this).val();
                ModalAddressNew.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#modal_district_name',
                        data: {
                            limit: ModalAddressNew.typeahead.items,
                            district_name: $('#modal_district_name').val()
                        },
                        url: global.uri.uri_public_api + 'district.php?action=typeahead',
                        callBack: function(item){
                            ModalAddressNew.address.district_id = item.item_id;
                            ModalAddressNew.address.district_name = item.item_name;
                        }
                    });
                },ModalAddressNew.typeahead.delay);
            }
        });
        $('#button-address-district-remove').click(function(){
            $('#modal_district_name').val('').attr('data-value','');
            ModalAddressNew.address.district_id = null;
            ModalAddressNew.address.district_name = '';
        });
        $('#modal_address_icms_type').on('change',function(){
            $('#modal_address_ie').prop('readonly',$(this).val() == '2').val(($(this).val() == '2' ? 'ISENTO' : ''));
        });
    },
    form2data: function(success){
        ModalAddressNew.address.address_cep = $('#modal_address_cep').val();
        ModalAddressNew.address.address_type = $('#modal_address_type').val();
        ModalAddressNew.address.address_public_place = $('#modal_address_public_place').val();
        ModalAddressNew.address.address_number = $('#modal_address_number').val();
        ModalAddressNew.address.city_name = $('#modal_city_name').val();
        ModalAddressNew.address.district_name = $('#modal_district_name').val();
        ModalAddressNew.address.address_icms_type = $('#modal_address_icms_type').val();
        ModalAddressNew.address.address_ie = $('#modal_address_ie').val();
        ModalAddressNew.address.address_ie = $('#modal_address_ie').val();
        ModalAddressNew.address.contacts = [];
        $.each(ModalAddressNew.contactTypes,function(key,contactType){
            var contact = $('input[data-id=' + contactType.contact_type_id + ']').val();
            if( contact.length ){
                ModalAddressNew.address.contacts.push({
                    address_contact_type_id: contactType.contact_type_id,
                    address_contact_label: contactType.contact_type_name,
                    address_contact_value: contact
                });
            }
        });
        ModalAddressNew.address.address_note = $('#modal_address_note').val();
        if( !!success ) success();
    },
    geolocation: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-map-geolocation',
            data: {
                zoom: ModalAddressNew.address.address_lat ? 16 : 4,
                cep: $('#modal_address_cep').val(),
                lat: ModalAddressNew.address.address_lat,
                lng: ModalAddressNew.address.address_lng
            },
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-map-marker',
                id: 'modal-map-geolocation',
                class: 'modal-map-geolocation',
                title: 'Geolocalização',
                html: html,
                buttons: [{
                    unclose: true,
                    icon: 'fa-globe',
                    id: 'button-zip-code',
                    title: 'Localização do CEP'
                },{
                    icon: 'fa-check',
                    title: 'Usar Localização',
                    action: function(){
                        ModalAddressNew.address.address_lat = map.markers[0].getPosition().lat();
                        ModalAddressNew.address.address_lng = map.markers[0].getPosition().lng();
                    }
                }],
                hidden: function(){
                    map.destroy();
                }
            });
        });
    },
    getAddressTypes: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=getAddressTypes',
            dataType: 'json'
        },function(data){
            ModalAddressNew.addressTypes = data;
            ModalAddressNew.showAddressTypes();
        });
    },
    getCep: function(cep_code){
        global.post({
            url: global.uri.uri_public_api + 'cep.php?action=get',
            data: { cep_code: cep_code },
            dataType: 'json'
        },function(data){
            ModalAddressNew.setCep(data);
        });
    },
    getContactTypes: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=getContactTypes',
            dataType: 'json'
        },function(data){
            ModalAddressNew.contactTypes = data;
            ModalAddressNew.showContactTypes();
        });
    },
    setCep: function(cep,success){
        ModalAddressNew.mapZoom = 16;
        ModalAddressNew.address.address_cep = cep.cep_code;
        ModalAddressNew.address.address_type = cep.public_place_type.toUpperCase();
        ModalAddressNew.address.address_public_place = cep.public_place;
        ModalAddressNew.address.uf_id = cep.uf_id;
        ModalAddressNew.address.city_id = cep.city_id;
        ModalAddressNew.address.district_id = cep.district_id;
        ModalAddressNew.address.address_lat = cep.address_lat;
        ModalAddressNew.address.address_lng = cep.address_lng;
        $('#modal_address_cep').val(cep.cep_code);
        $('#modal_address_type').selectpicker('val',cep.public_place_type);
        $('#modal_address_public_place').val(cep.public_place);
        $('#modal_address_number').focus();
        $('#modal_city_name').val(cep.city_name + ' - ' + cep.uf_id).attr('data-value',cep.city_name + ' - ' + cep.uf_id);
        $('#modal_district_name').val(cep.district_name).attr('data-value',cep.district_name);
        $('#modal_address_icms_type').selectpicker('val',ModalAddressNew.address.address_icms_type).trigger('change');
        $('#modal_address_ie').val(ModalAddressNew.address.address_ie);
        if(!!success) success();
    },
    showAddressTypes: function(){
        $.each(ModalAddressNew.addressTypes,function(key,addressType){
            $('#modal_address_type').append($('<option>',{
                'value': addressType.address_type.toUpperCase(),
                'text': addressType.address_type.toUpperCase()
            }));
        });
        if( !!ModalAddressNew.address.address_type ){
            $('#modal_address_type').val(ModalAddressNew.address.address_type);
        }
        $('#modal_address_type').selectpicker('refresh');
    },
    showContactTypes: function(){
        $.each(ModalAddressNew.contactTypes,function(key,contactType){
            $('#modal-contacts').append(
                '<div class="contact">' +
                    '<div class="contact-name">' + contactType.contact_type_name + '</div>' +
                        '<div class="contact-value">' +
                        '<input data-id="' + contactType.contact_type_id + '" type="text" />' +
                    '</div>' +
                '</div>'
            );
        });
        if( !!ModalAddressNew.address.address_code ){
            $.each(ModalAddressNew.address.contacts,function(key,contact){
                $('input[data-id=' + contact.address_contact_type_id + ']').val(contact.address_contact_value);
            });
        }
    },
    submit: function(){
        ModalAddressNew.form2data(function(){
            if( ModalAddressNew.validate() ){
                if( !!ModalAddressNew.address.address_code ){
                    ModalAddressNew.edit();
                } else {
                    ModalAddressNew.add();
                }
            }
        });
    },
    validate: function(){
        if( !ModalAddressNew.address.address_cep.length ){
            global.validateMessage('O CEP deverá ser informado.',function(){
                $('#modal_address_cep').focus();
            });
            return false;
        }
        if( ModalAddressNew.address.address_cep.length != 9 ){
            global.validateMessage('Informe um CEP válido.',function(){
                $('#modal_address_cep').focus();
            });
            return false;
        }
        if( !ModalAddressNew.address.address_type ){
            global.validateMessage('O tipo do endereço deverá ser informado.',function(){
                $('#modal_address_type').focus();
            });
            return false;
        }
        if( !ModalAddressNew.address.address_public_place.length ){
            global.validateMessage('O logradouro deverá ser informado.',function(){
                $('#modal_address_public_place').focus();
            });
            return false;
        }
        if( !ModalAddressNew.address.address_number.length ){
            global.validateMessage('O número deverá ser informado.',function(){
                $('#modal_address_number').focus();
            });
            return false;
        }
        if( !ModalAddressNew.address.uf_id ){
            global.validateMessage('O UF da cidade deverá ser informado.',function(){
                $('#modal_city_name').focus();
            });
            return false;
        }
        if( !ModalAddressNew.address.city_id ){
            global.validateMessage('A cidade deverá ser informada.',function(){
                $('#modal_city_name').focus();
            });
            return false;
        }
        if( !ModalAddressNew.address.district_id ){
            global.validateMessage('O bairro deverá ser informado.',function(){
                $('#modal_district_name').focus();
            });
            return false;
        }
        if( ModalAddressNew.address.address_icms_type == 1 && !ModalAddressNew.address.address_ie.length ){
            global.validateMessage('A inscrição estadual deverá ser informada.',function(){
                $('#modal_address_ie').focus();
            });
            return false;
        }
        return true;
    }
};
