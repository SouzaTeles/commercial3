$(document).ready(function(){
    ModalDeliveryAuthorization.events();
});

ModalDeliveryAuthorization = {
    data: {},
    authorize: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=deliveryAuthorization',
            data: {
                user_user: $('#modal_user_user').val(),
                user_pass: $('#modal_user_pass').val(),
                data: ModalDeliveryAuthorization.data
            },
            dataType: 'json'
        },function(data){
            Budget.budget.budget_delivery_date = ModalDeliveryAuthorization.data.date_delivery;
            $('#modal_delivery_date').val(global.date2Br(ModalDeliveryAuthorization.data.date_delivery));
            Budget.budget.authorization.push(data.authorization_id);
            $('#modal-delivery-authorization').modal('hide');
        });
    },
    events: function(){
        $('#form-delivery-authorization').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalDeliveryAuthorization.authorize();
        });
        $('#modal_user_user').focus();
    }
};