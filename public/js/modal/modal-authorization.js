$(document).ready(function(){
    ModalAuthorization.events();
});

ModalAuthorization = {
    events: function(){
        $('#form-authorization').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalAuthorization.authorize();
        });
        $('#button-submit').click(function(){
            $('#form-authorization button').click();
        });
    },
    authorize: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=access',
            data: {
                user_user: $('#modal_user_user').val(),
                user_pass: $('#modal_user_pass').val()
            },
            dataType: 'json'
        },function(data){
            ModalAuthorization.success(data);
            $('#modal-authorization').modal('hide');
        });
    }
};