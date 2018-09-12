$(document).ready(function(){
    ModalDiscountAuthorization.show();
    ModalDiscountAuthorization.events();
});

ModalDiscountAuthorization = {
    data: {
        item_id: '',
        item_name: '',
        item_quantity: 0,
        item_value_total: 0,
        item_max_discount: 0,
        item_value_discount: 0,
        item_aliquot_discount: 0
    },
    authorize: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=discountItemAuthorization',
            data: {
                user_user: $('#modal_user_user').val(),
                user_pass: $('#modal_user_pass').val(),
                data: ModalDiscountAuthorization.data
            },
            dataType: 'json'
        },function(data){
            Item.discountAliquot(ModalDiscountAuthorization.data.item_aliquot_discount);
            Item.item.authorization_id = data.authorization_id;
            $('#modal-discount-authorization').modal('hide');
        });
    },
    events: function(){
        $('#form-discount-authorization').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalDiscountAuthorization.authorize();
        });
        $('#modal_user_user').focus();
    },
    show: function(){
        var $modal = $('#modal-discount-authorization');
        $modal.find('.modal-box').html(
            '<b>' + ModalDiscountAuthorization.data.item_name + '</b><br/>' +
            'Quantidade: ' + ModalDiscountAuthorization.data.item_quantity + '<br/>' +
            'Desconto: ' + global.float2Br(ModalDiscountAuthorization.data.item_aliquot_discount) + '% / R$' + global.float2Br(ModalDiscountAuthorization.data.item_value_discount) + '<br/>' +
            'Total: R$' + global.float2Br(ModalDiscountAuthorization.data.item_value_total)
        );
    }
};