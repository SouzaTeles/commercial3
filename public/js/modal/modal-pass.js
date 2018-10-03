$(document).ready(function(){
    ModalPass.events();
});

ModalPass = {
    data: {
        pass: '',
        new_pass: '',
        pass_confirm: ''
    },
    events: function(){
        $('#form-pass').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalPass.data = {
                pass: $('#modal_pass').val(),
                new_pass: $('#modal_new_pass').val(),
                pass_confirm: $('#modal_pass_confirm').val()
            };
            if( ModalPass.data.new_pass != ModalPass.data.pass_confirm ){
                global.validateMessage('A nova senha não confere com o campo de confirmação.');
            } else {
                ModalPass.change();
            }
        });
        $('#button-pass-change').click(function(){
            $('#form-pass button').click();
        });
    },
    change: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=pass',
            data: ModalPass.data,
            dataType: 'json'
        },function(data){
            $('#modal-pass').modal('hide');
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