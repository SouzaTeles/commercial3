$(document).ready(function(){

    Commercial.events();
    if( !!global.login ) Chat.getUsers();

});

Chat = {
    users: [],
    dialogs: [],
    events: function(){

    },
    getUsers: function(){
        global.post({
            url: global.uri.uri_public_api + 'chat.php?action=getList',
            dataType: 'json'
        },function(data){
            Chat.users = data;
            Chat.showList();
        });
    },
    new: function(user){
        $('body').append(
            '<div data-user-id="' + user.user_id + '" class="dialog box-shadow" style="left:' + ( Chat.dialogs.length * 240 + 70 ) + 'px;">' +
                '<div class="dialog-header">' +
                    '<i class="fa fa-circle txt-' + (user.status == 'on' ? 'green' : 'red') + '-light"></i> ' + user.user_name +
                    '<button data-toggle="tooltip" data-title="Fechar" data-action="close" class="btn btn-empty-white"><i class="fa fa-times"></i></button>' +
                    '<button data-toggle="tooltip" data-title="Minimizar" data-action="minimize" class="btn btn-empty-white"><i class="fa fa-window-minimize"></i></button>' +
                    '<button data-toggle="tooltip" data-title="Maximizar" data-action="maximize" class="btn btn-empty-white"><i class="fa fa-window-maximize"></i></button>' +
                '</div>' +
                '<div class="dialog-body"></div>' +
                '<div class="dialog-footer">' +
                    '<form data-user-id="' + user.user_id + '">' +
                        '<input maxlength="255" type="text" class="box-shadow" required />' +
                        '<button data-toggle="tooltip" data-title="Enviar" class="btn btn-green-light"><i class="fa fa-send"></i></button>' +
                '</div>' +
            '</div>'
        );
        var dialog = $('.dialog[data-user-id="' + user.user_id + '"]');
        $(dialog).find('[data-toggle="tooltip"]').tooltip();
        $(dialog).find('button[data-action="minimize"]').click(function(){
            $(dialog).toggleClass('dialog-minimized');
            $(this).hide();
            $(dialog).find('button[data-action="maximize"]').show();
        });
        $(dialog).find('button[data-action="maximize"]').click(function(){
            $(dialog).toggleClass('dialog-minimized');
            $(this).hide();
            $(dialog).find('button[data-action="minimize"]').show();
        });
        $(dialog).find('form').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            $(dialog).find('.dialog-body').append(
                '<div class="balloon my-balloon box-shadow">' + $(this).find('input').val() + '</div>'
            );
            $(this).find('input').val('')
        });
        Chat.dialogs.push({
            user_id: user.user_id,
            user_name: user.user_name,
            timestamp: parseInt(Date.now()/1000),
            messages: []
        });
    },
    showList: function(){
        var panel = $('#chat ul');
        $.each( Chat.users, function(key,user){
            $(panel).append(
                '<li data-key="' + key + '" data-user-id="' + user.user_id + '" data-opened="false">' +
                    '<div class="cover"' + ( user.image ? (' style="background-image:url(' + user.image + ')"') : '' ) + '>' +
                        '<i class="fa fa-circle txt-' + (user.status == 'on' ? 'green' : 'red') + '-light"></i>' +
                    '</div>' +
                    '<div class="name">' + user.user_name + '</div>' +
                    '<div class="text">Loren ipsum let manant...</div>' +
                '</li>'
            );
        });
        $(panel).find('li').click(function(){
            if( $(this).attr('data-opened') == 'false' ){
                var user = Chat.users[$(this).attr('data-key')];
                $(this).attr('data-opened','true');
                Chat.new(user);
            }
        });
    }
};

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
        $('.menu li a[href="#"]').click(function(e){
             e.preventDefault();
        });
        $('button[data-action="chat"]').click(function(){
            $('#chat').toggleClass('open');
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