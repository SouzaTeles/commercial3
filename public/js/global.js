$(document).ready(function(){

    Compass.events();
    Compass.container();

});

Compass = {
    container: function(){
        global.layout = {
            window: $(window).innerHeight(),
            header: $('header').outerHeight(),
            footer: $('footer').innerHeight()
        };
        $('.container').css({
            'min-height': global.layout.window
        });
    },
    events: function(){
        $('header .dropdown li a').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            if( $(this).attr('href') == '#change-pass' ){
                Compass.modalPass();
            } else if( $(this).attr('href') == '#logout' ){
                Compass.logout();
            }
        });
        $(window).resize(function(){
            Compass.container();
        });
    },
    logout: function(){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente sair do sistema?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Cancelar'
            },{
                icon: 'fa-check',
                title: 'Confirmar',
                action: function(){
                    global.post({
                        url: global.uri.uri_public_api + 'logout.php',
                        dataType: 'json'
                    },function(){
                        global.onLoader();
                        if( !!Electron ) Electron.logout();
                        else location.href = global.uri.uri_public + 'index.php?route=login';
                    })
                }
            }]
        });
    },
    editPass: function(data){
        if( data.user_new_pass != data.user_pass_confirm ){
            global.alert({
                class: 'alert-danger',
                message: 'A nova senha não confere com o campo de confirmação.'
            });
        } else {
            global.post({
                url: global.uri.uri_public_api + 'user.php?action=loginPass',
                data: data,
                dataType: 'json'
            },function(data){
                $('#modal').modal('hide');
                setTimeout(function(){
                    global.modal({
                        icon: 'fa-info',
                        title: 'Informação',
                        html: '<p>' + data.message + '</p>',
                        buttons: [{
                            icon: 'fa-check',
                            title: 'Ok'
                        }]
                    });
                },500);
            });
        }
    },
    modalPass: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=loginPassForm',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-lock',
                title: 'Alteração de Senha',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar'
                },{
                    icon: 'fa-pencil',
                    title: 'Atualizar',
                    unclose: true,
                    action: function(){
                        $('#modal form button').click();
                    }
                }],
                load: function(){
                    $('#modal form').on('submit',function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        Compass.editPass({
                            user_pass: $('#modal_user_pass').val(),
                            user_new_pass: $('#modal_user_new_pass').val(),
                            user_pass_confirm: $('#modal_user_pass_confirm').val()
                        });
                    });
                }
            });
        });
    }
};

try {
    var remote = require('electron').remote;
    var BrowserWindow = remote.BrowserWindow;
    var mainWindow = remote.getGlobal('mainWindow');
    var children = remote.getGlobal('children');
    var Electron = remote.getGlobal('Electron');
} catch(e){
    var Electron = null;
}