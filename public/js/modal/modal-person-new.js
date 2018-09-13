$(document).ready(function(){

    ModalPersonNew.events();
    ModalPersonNew.getContactTypes();

    global.mask();
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
        address: {
            address_cep: '',
            address_type: '',
            address_public_place: '',
            address_number: '',
            city_id: '',
            city_name: '',
            district_id: '',
            district_name: '',
            address_icms_type: '2',
            address_ie: 'ISENTO',
            address_note: '',
            contacts: []
        }
    },
    addressTypes: [],
    contactTypes: [],
    add: function(){

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
    getContactTypes: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=getContactTypes',
            dataType: 'json'
        },function(data){
            ModalPersonNew.contactTypes = data;
            ModalPersonNew.showContactTypes();
        });
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
        if( !ModalPersonNew.person.address.address_cep.length ){
            global.validateMessage('O CEP deverá ser informado.',function(){
                $('#modal_address_cep').focus();
            });
            return false;
        }
        // if( !ModalPersonNew.person.address.address_type.length ){
        //     global.validateMessage('O tipo do endereço deverá ser informado.',function(){
        //         $('#modal_address_type').focus();
        //     });
        //     return false;
        // }
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
        if( !ModalPersonNew.person.address.city_name.length ){
            global.validateMessage('A cidade deverá ser informada.',function(){
                $('#modal_city_name').focus();
            });
            return false;
        }
        if( !ModalPersonNew.person.address.district_name.length ){
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
    }
};