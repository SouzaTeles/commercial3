$(document).ready(function(){
    ModalSeller.events();
});

ModalSeller = {
    seller: {
        seller_id: null,
        seller_code: '',
        seller_name: '',
        seller_image: null
    },
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0,
        min: 3
    },
    events: function(){
        $('#modal_seller_code').keypress(function (e){
            var keycode = e.keyCode || e.which;
            if( keycode == '13' && $(this).val().length ){
                e.preventDefault();
                e.stopPropagation();
                ModalSeller.get({
                    person_code: $(this).val(),
                    person_category_id: global.config.person.seller_category_id
                });
            }
        }).on('blur',function(){
            if( $(this).attr('data-value').length ){
                $(this).val($(this).attr('data-value'));
            }
        }).val(ModalSeller.seller.seller_code).attr('data-value',ModalSeller.seller.seller_code);
        $('#modal_seller_name').on('keyup',function(){
            if( $(this).val().length >= ModalSeller.typeahead.min && $(this).val() != ModalSeller.typeahead.last ){
                clearTimeout(ModalSeller.typeahead.timer);
                ModalSeller.typeahead.last = $(this).val();
                ModalSeller.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#modal_seller_name',
                        data: {
                            limit: ModalSeller.typeahead.items,
                            person_name: $('#modal_seller_name').val(),
                            person_category_id: global.config.person.seller_category_id
                        },
                        url: global.uri.uri_public_api + 'person.php?action=typeahead',
                        callBack: function(item){
                            ModalSeller.seller = {
                                seller_id: item.item_id,
                                seller_code: item.item_code,
                                seller_name: item.item_name,
                                image: item.item_image
                            };
                            $('#modal-person-image').css('background-image','url(' + item.item_image + ')');
                            $('#modal_seller_code').val(item.item_code).attr('data-value',item.item_code);
                            $('#modal_seller_name').val(item.item_name).attr('data-value',item.item_name);
                        }
                    });
                },ModalSeller.typeahead.delay);
            }
        }).val(ModalSeller.seller.seller_name).attr('data-value',ModalSeller.seller.seller_name);
    },
    get: function(data){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=get',
            data: data,
            dataType: 'json'
        },function(person){
            ModalSeller.seller = {
                seller_id: person.person_id,
                seller_code: person.person_code,
                seller_name: person.person_name,
                image: person.image
            };
            $('#modal_seller_code').val(person.person_code).attr('data-value',person.person_code);
            $('#modal_seller_name').val(person.person_name).attr('data-value',person.person_name);
            if( !!person.image ){
                $('#modal-person-image').css('background-image', 'url(' + person.image + ')');
            }
        });
    },
    show: function(){
        if( !!ModalSeller.seller.image ){
            $('#modal-person-image').css('background-image','url(' + ModalSeller.seller.image + ')');
        }
        $('#modal_seller_code').val(ModalSeller.seller.seller_code).attr('data-value',ModalSeller.seller.seller_code);
        $('#modal_seller_name').val(ModalSeller.seller.seller_name).attr('data-value',ModalSeller.seller.seller_name);
    }
};