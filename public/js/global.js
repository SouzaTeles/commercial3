$(document).ready(function(){

    Commercial.events();

});

Commercial = {
    events: function(){
        $('header .dropdown li a').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            if( $(this).attr('href') == '#change-pass' ){
                Commercial.pass();
            } else if( $(this).attr('href') == '#logout' ){
                Commercial.logout();
            }
        });
        $('#button-ticket').click(function(){
            global.window({
                width: 800,
                url: global.uri.uri_public + 'window.php?module=ticket&action=new'
            });
        });
    },
    logout: function(){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente sair do sistema?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Cancelar',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Confirmar',
                action: function(){
                    global.post({
                        url: global.uri.uri_public_api + 'logout.php',
                        dataType: 'json'
                    },function(){
                        global.onLoader();
                        if( typeof(Electron) == 'object' ){
                            Electron.afterLogout();
                        } else {
                            location.href = global.uri.uri_public + 'index.php?route=login';
                        }
                    })
                }
            }]
        });
    },
    pass: function(){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-pass',
            dataType: 'html'
        },function(html){
            global.modal({
                id: 'modal-pass',
                class: 'modal-pass',
                icon: 'fa-lock',
                title: 'Alteração de senha',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left'
                },{
                    icon: 'fa-pencil',
                    title: 'Atualizar',
                    unclose: true,
                    id: 'button-pass-change'
                }]
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
    var ipcRenderer = require('electron').ipcRenderer;
} catch(e){

}