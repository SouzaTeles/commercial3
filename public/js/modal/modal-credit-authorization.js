$(document).ready(function(){
    ModalCreditAuthorization.show();
    ModalCreditAuthorization.events();
});

ModalCreditAuthorization = {
    data: {
        reason: 0,
        message: 0,
        image: null,
        budget_id: null,
        person_id: null,
        person_code: '',
        person_name: ''
    },
    authorize: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=creditAuthorization',
            data: {
                user_user: $('#modal_user_user').val(),
                user_pass: $('#modal_user_pass').val(),
                data: ModalCreditAuthorization.data
            },
            dataType: 'json'
        },function(data){
            Budget.submit();
            Budget.budget.authorization.push(data.authorization_id);
            $('#modal-credit-authorization').modal('hide');
        });
    },
    events: function(){
        $('#form-credit-authorization').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalCreditAuthorization.authorize();
        });
        $('#modal_user_user').focus();
        $('#person-info').click(function(){
            Person.info();
        });
        $('#button-credit-authorize').click(function(){
            $('#form-credit-authorization button').click();
        });
    },
    show: function(){
        var $modal = $('#modal-credit-authorization');
        $modal.find('.modal-message').html(
            '<i class="fa fa-warning txt-' + ( ModalCreditAuthorization.data.reason == 1 ? 'red' : 'orange' ) + '"></i> ' +
            '<b>' + ( ModalCreditAuthorization.data.reason == 1 ? 'Títulos em aberto' : 'Limite de crédito' ) + '</b>'
        );
        if( !!ModalCreditAuthorization.data.image ){
            $modal.find('.modal-box .cover').css('background-image','url(' + ModalCreditAuthorization.data.image + ')');
        }
        $modal.find('.modal-box .client-info').html(
            ModalCreditAuthorization.data.message + '<br/>' +
            ModalCreditAuthorization.data.person_code + ' - ' + ModalCreditAuthorization.data.person_name
        )
    }
};