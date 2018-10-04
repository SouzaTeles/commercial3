$(document).ready(function(){
    ModalUserPass.show();
    ModalUserPass.events();
});

ModalUserPass = {
    data: {
        user_new_pass: '',
        user_pass_confirm: ''
    },
    events: function(){
        $('#form-user-pass').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalUserPass.submit();
        });
    },
    submit: function(){
        ModalUserPass.data = {
            user_new_pass: $('#modal_user_new_pass').val(),
            user_pass_confirm: $('#modal_user_pass_confirm').val()
        };
        if( !ModalUserPass.data.user_new_pass.length ){
            global.validateMessage('Preencha a nova senha.');
            return;
        }
        if( !ModalUserPass.data.user_pass_confirm.length ){
            global.validateMessage('Confirme a senha.');
            return;
        }
        if( ModalUserPass.data.user_new_pass != ModalUserPass.data.user_pass_confirm ){
            global.validateMessage('A nova senha não confere com o campo de confirmação.');
            return;
        }
        ModalUserPass.data.user_id = ModalUserPass.user.user_id;
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=userPass',
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
    },
    show: function(){
        var $modal = $('.modal-info');
        if( !!ModalUserPass.user.image ){
            $modal.find('.image').css('background-image','url(' + ModalUserPass.user.image + ')');
        }
        $modal.find('.name').text(ModalUserPass.user.user_name);
        $modal.find('.profile').html('Perfil<br/>' + ModalUserPass.user.user_profile_name);
        $modal.find('.login').html('Último acesso<br/>' + ModalUserPass.user.user_login_br);
    }
};