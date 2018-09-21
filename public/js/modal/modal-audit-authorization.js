$(document).ready(function(){
    ModalAuditAuthorization.events();
});

ModalAuditAuthorization = {
    events: function(){
        $('#form-audit-authorization').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalAuditAuthorization.authorize();
        });
        $('#button-submit').click(function(){
            $('#form-audit-authorization button').click();
        });
    },
    authorize: function(){
        global.post({
            url: global.uri.uri_public_api + 'audit.php?action=authorization',
            data: {
                user_user: $('#modal_user_user').val(),
                user_pass: $('#modal_user_pass').val()
            },
            dataType: 'json'
        },function(){
            ModalAuditAuthorization.success();
            $('#modal-audit-authorization').modal('hide');
        });
    }
};