$(document).ready(function(){
    ModalGeneralDiscountAuthorization.events();
});

ModalGeneralDiscountAuthorization = {
    data: {
        discount_aliquot: 0
    },
    authorize: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=generalDiscountAuthorization',
            data: {
                user_user: $('#modal_user_user').val(),
                user_pass: $('#modal_user_pass').val(),
                data: ModalGeneralDiscountAuthorization.data,
                items: Budget.budget.items
            },
            dataType: 'json'
        },function(data){
            ModalBudgetDiscount.authorized(data);
            $('#modal-general-discount-authorization').modal('hide');
        });
    },
    events: function(){
        $('#form-general-discount-authorization').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalGeneralDiscountAuthorization.authorize();
        });
        $('#modal_user_user').focus();
    }
};