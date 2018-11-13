$(document).ready(function(){

    global.mask();
    global.selectpicker();

    ModalBudgetConfirm.seller = Seller.seller;
    ModalBudgetConfirm.showSeller();
    ModalBudgetConfirm.showAddress();
    ModalBudgetConfirm.showNote();
    ModalBudgetConfirm.events();

});

ModalBudgetConfirm = {
    seller: null,
    dateChanged: '',
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0,
        min: 3
    },
    dateSelected: function(value){
        var input = $('#modal_delivery_date');
        ModalBudgetConfirm.dateChanged = $(input).attr('data-value');
        if( value.length == 10 ){
            var date = moment(value,'DD/MM/YYYY');
            if( date.isValid() && global.dateDiff(global.today(),date.format('YYYY-MM-DD')) >= 0){
                if( date.format('YYYY-MM-DD') != ModalBudgetConfirm.dateChanged){
                    if( global.dateDiff(global.today(),date.format('YYYY-MM-DD')) > Company.company.delivery_days){
                        ModalBudgetConfirm.deliveryAuthorization(date.format('YYYY-MM-DD'));
                        $('#modal_delivery_date').val(global.date2Br(ModalBudgetConfirm.dateChanged)).attr('data-value',ModalBudgetConfirm.dateChanged);
                    } else {
                        $(input).attr('data-value',date.format('YYYY-MM-DD'));
                    }
                }
            } else {
                $(input).val(global.date2Br(ModalBudgetConfirm.dateChanged));
            }
        } else {
            $(input).val(global.date2Br(ModalBudgetConfirm.dateChanged));
        }
    },
    deliveryAuthorization: function(date_delivery){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-delivery-authorization',
            data: { date_delivery: date_delivery },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'small',
                icon: 'fa-lock',
                id: 'modal-delivery-authorization',
                class: 'modal-delivery-authorization',
                title: 'Autorização',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    class: 'pull-left btn-red',
                    title: 'Cancelar'
                },{
                    icon: 'fa-unlock',
                    title: 'Autorizar',
                    class: 'btn-green',
                    unclose: true,
                    action: function(){
                        ModalDeliveryAuthorization.authorize();
                    }
                }],
                shown: function(){
                    $('#modal_user_user').focus();
                }
            });
        });
    },
    events: function(){
        $('#modal_seller_code').keypress(function (e){
            var keycode = e.keyCode || e.which;
            if( keycode == '13' && $(this).val().length ){
                e.preventDefault();
                e.stopPropagation();
                ModalBudgetConfirm.getSeller({
                    person_code: $(this).val(),
                    person_category_id: global.config.person.seller_category_id
                });
            }
        }).on('blur',function(){
            if( $(this).attr('data-value').length ){
                $(this).val($(this).attr('data-value'));
            }
        }).val(ModalBudgetConfirm.seller.seller_code).attr('data-value',ModalBudgetConfirm.seller.seller_code);
        $('#modal_seller_name').on('keyup',function(){
            if( $(this).val().length >= ModalBudgetConfirm.typeahead.min && $(this).val() != ModalBudgetConfirm.typeahead.last ){
                clearTimeout(ModalBudgetConfirm.typeahead.timer);
                ModalBudgetConfirm.typeahead.last = $(this).val();
                ModalBudgetConfirm.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#modal_seller_name',
                        data: {
                            limit: ModalBudgetConfirm.typeahead.items,
                            person_name: $('#modal_seller_name').val(),
                            person_category_id: global.config.person.seller_category_id
                        },
                        url: global.uri.uri_public_api + 'person.php?action=typeahead',
                        callBack: function(item){
                            ModalBudgetConfirm.seller = {
                                seller_id: item.item_id,
                                seller_code: item.item_code,
                                seller_name: item.item_name,
                                image: item.item_image
                            };
                            $('#modal-person-image').css('background-image','url(' + item.item_image + ')');
                            $('#modal_seller_code').val(item.item_code).attr('data-value',item.item_code);
                            $('#modal_seller_name').val(item.item_name).attr('data-value',item.item_name);
                            setTimeout(function(){
                                $('#button-seller-select').focus();
                            },500);
                        }
                    });
                },ModalBudgetConfirm.typeahead.delay);
            }
        }).val(ModalBudgetConfirm.seller.seller_name).attr('data-value',ModalBudgetConfirm.seller.seller_name);
        $('#button-seller-select').click(function(){
            if( !ModalBudgetConfirm.seller.seller_id ){
                global.validateMessage('Nenhum vendedor foi informado.',function(){
                    $('#modal_seller_code').focus().select();
                });
            }
            $('#modal-seller-search').modal('hide');
            ModalBudgetConfirm.success(ModalBudgetConfirm.seller);
        });
        $('#modal_delivery_date').datepicker({
            format: 'dd/mm/yyyy',
            zIndex: 1091,
            startDate: '12/11/2018'
        }).on('change',function(){
            ModalBudgetConfirm.dateSelected($(this).val());
        }).on('blur',function(){
                ModalBudgetConfirm.dateSelected($(this).val());
        }).val(global.date2Br(Budget.budget.budget_delivery_date)).attr('data-value',Budget.budget.budget_delivery_date);
        $('#modal_delivery_address').on('changed.bs.select', function(e,clickedIndex){
            Address.delivery = Person.person.address[clickedIndex-1];
            Budget.budget.address_code = Address.delivery.address_code;
            Budget.budget.address_uf_id = Address.delivery.uf_id;
            Budget.budget.budget_note_document = Budget.budget.budget_note_document.split('\n\nObs de Entrega: ')[0];
            if( !!Address.delivery.address_note ){
                Budget.budget.budget_note_document +=  '\n\nObs de Entrega: ' + Address.delivery.address_note;
            }
            Address.showDelivery();
            Address.showList();
            var address = $('#address-selected');
            $(address).find('.address-code').text(Budget.budget.address_code);
            $(address).find('.address-address').html(
                Address.delivery.address_public_place + '<br/>' +
                Address.delivery.uf_id + ' - ' + Address.delivery.city_name + ' - ' + Address.delivery.district_name + '<br/>' +
                'CEP: ' + Address.delivery.address_cep
            );
            ModalBudgetConfirm.showNote();
        });
        $('#button-modal-address-map').click(function(){
            global.post({
                url: global.uri.uri_public_api + 'modal.php?modal=modal-map-single',
                data: Address.delivery,
                dataType: 'html'
            },function(html){
                global.modal({
                    size: 'big',
                    icon: 'fa-map-marker',
                    id: 'modal-map-single',
                    class: 'modal-map-single',
                    title: 'Mapa',
                    html: html,
                    buttons: [{
                        icon: 'fa-check',
                        title: 'Ok'
                    }],
                    hidden: function(){
                        map.destroy();
                    }
                });
            });
        });
        $('#button-modal-address-new').click(function(){
            global.post({
                url: global.uri.uri_public_api + 'modal.php?modal=modal-address-new',
                data: {
                    person_id: Person.person.person_id
                },
                dataType: 'html'
            },function(html){
                global.modal({
                    icon: 'fa-plus',
                    size: 'big',
                    id: 'modal-address-new',
                    class: 'modal-address-new',
                    title: 'Novo Endereço',
                    html: html,
                    buttons: [{
                        icon: 'fa-times',
                        title: 'Cancelar',
                        class: 'pull-left btn-red'
                    },{
                        unclose: true,
                        icon: 'fa-plus',
                        title: 'Cadastrar',
                        class: 'btn-green',
                        action: function(){
                            ModalAddressNew.submit();
                        }
                    }],
                    shown: function(){
                        ModalAddressNew.afterSave = function(){
                            ModalBudgetConfirm.showAddress();
                        }
                    }
                });
            });
        });
        $('#modal_note').on('keyup',function(){
            Budget.budget.budget_note_document = $(this).val();
        });
        $('#button-person-search').click(function(){
            ModalBudgetConfirm.searchSeller();
        });
    },
    getSeller: function(data){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=get',
            data: data,
            dataType: 'json'
        },function(person){
            ModalBudgetConfirm.seller = {
                seller_id: person.person_id,
                seller_code: person.person_code,
                seller_name: person.person_name,
                image: person.image
            };
            Seller.seller = ModalBudgetConfirm.seller;
            Budget.budget.seller_id = person.person_id;
            ModalBudgetConfirm.showSeller();
        });
    },
    showAddress: function(){
        $('#modal_delivery_address').find('option').remove();
        $.each( Person.person.address, function(key,address){
            $('#modal_delivery_address').append($('<option>',{
                'value': address.address_code,
                'selected': address.address_code == Budget.budget.address_code,
                'data-subtext': address.address_public_place + ', ' + address.address_number + ' - ' + address.uf_id + ' - ' + address.city_name + ' - ' + address.district_name + ' - CEP: ' + (address.address_cep || '--'),
                'text': 'Endereço ' + address.address_code
            }));
        });
        $('#modal_delivery_address').selectpicker('refresh');
        var address = $('#address-selected');
        $(address).find('.address-code').text(Budget.budget.address_code);
        $(address).find('.address-address').html(
            Address.delivery.address_public_place + '<br/>' +
            Address.delivery.uf_id + ' - ' + Address.delivery.city_name + ' - ' + Address.delivery.district_name + '<br/>' +
            'CEP: ' + Address.delivery.address_cep
        );
    },
    showNote: function(){
        $('#modal_note').val(Budget.budget.budget_note_document);
    },
    showSeller: function(){
        if( !!ModalBudgetConfirm.seller.image ){
            $('#modal-person-image').css('background-image','url(' + ModalBudgetConfirm.seller.image + ')');
        }
        $('#modal_seller_code').val(ModalBudgetConfirm.seller.seller_code).attr('data-value',ModalBudgetConfirm.seller.seller_code);
        $('#modal_seller_name').val(ModalBudgetConfirm.seller.seller_name).attr('data-value',ModalBudgetConfirm.seller.seller_name);
    },
    searchSeller: function(){
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
                    ModalPersonSearch.data.categories.push(global.config.person.seller_category_id);
                    ModalPersonSearch.success = function(person){
                        $('#modal-person-search').modal('hide');
                        ModalBudgetConfirm.seller = {
                            seller_id: person.person_id,
                            seller_code: person.person_code,
                            seller_name: person.person_name,
                            image: person.image
                        };
                        Seller.seller = ModalBudgetConfirm.seller;
                        Budget.budget.seller_id = person.person_id;
                        ModalBudgetConfirm.showSeller();
                    }
                },
                hidden: function(){
                    $('#person_code').focus();
                }
            });
        });
    }
};