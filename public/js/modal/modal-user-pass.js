$(document).ready(function(){
    ModalUserPass.events();
});

ModalUserPass = {
    data: {
        user_pass: '',
        user_new_pass: '',
        user_pass_confirm: ''
    },
    events: function(){
        $('#form-user-pass').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalUserPass.data = {
                user_pass: $('#modal_user_pass').val(),
                user_new_pass: $('#modal_user_new_pass').val(),
                user_pass_confirm: $('#modal_user_pass_confirm').val()
            };
            if( ModalUserPass.data.user_new_pass != ModalUserPass.data.user_pass_confirm ){
                global.validateMessage('A nova senha não confere com o campo de confirmação.');
            } else {
                ModalUserPass.change();
            }
        });
        $('#button-user-pass-change').click(function(){
            $('#form-user-pass button').click();
        });
    },
    change: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=editPass',
            data: ModalUserPass.data,
            dataType: 'json'
        },function(data){
            $('#modal-user-pass').modal('hide');
            global.modal({
                size: 'small',
                icon: 'fa-info',
                title: 'Informação',
                html: '<p>' + data.message + '</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            });
        });
    }
};