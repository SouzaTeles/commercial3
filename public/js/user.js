$(document).ready(function() {

    User.events();
    User.getList();
    global.unLoader();

});

User = {
    users: [],
    table: global.table({
        searching: 1,
        noControls: [0,5],
        order: [[2, "asc"]],
        selector: '#table-users'
    }),
    actions: function(key,user_id){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty-blue" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu dropdown-custom pull-right">' +
                    '<li><a disabled="' + ( global.login.access.user.edit.value == 'N' ) + '" data-action="edit" data-key="' + key + '" data-id="' + user_id + '" class="dropdown-item" href="#"><i class="fa fa-pencil txt-blue"></i>Editar</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a disabled="' + ( global.login.access.user.audit.value == 'N' ) + '" data-action="audit" data-key="' + key + '" data-id="' + user_id + '" class="dropdown-item" href="#"><i class="fa fa-shield txt-green"></i>Auditoria</a></li>' +
                    '<li><a disabled="' + ( global.login.access.user.user_pass.value == 'N' ) + '" data-action="pass" data-key="' + key + '" data-id="' + user_id + '" class="dropdown-item" href="#"><i class="fa fa-lock txt-orange"></i>Alterar Senha</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    audit: function(key,user_id){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-audit',
            data: {
                log_script: 'user',
                log_parent_id: user_id
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-audit',
                class: 'modal-audit',
                icon: 'fa-shield',
                title: 'Auditoria do Usuário ' + User.users[key].user_name,
                html: html,
                buttons: [{
                    title: 'Fechar'
                }]
            });
        });
    },
    edit: function(key,user_id){
        if( global.login.access.user.edit.value == 'N' ) return;
        global.window({
            url: global.uri.uri_public + 'window.php?module=user&action=new&user_id=' + user_id
        });
    },
    events: function(){
        $('#user_search').keyup(function(){
            User.table.search(this.value).draw();
        });
        $('#button-refresh').click(function(){
            User.getList();
        });
        $('#button-new').click(function(){
            User.new();
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=getList',
            dataType: 'json'
        }, function(users){
            User.users = users;
            User.showList();
        });
    },
    new: function(){
        global.window({
            url: global.uri.uri_public + 'window.php?module=user&action=new'
        });
    },
    pass: function(key){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-user-pass',
            data: User.users[key],
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'small',
                icon: 'fa-lock',
                id: 'modal-user-pass',
                class: 'modal-user-pass',
                title: 'Atualizar Senha',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'btn-red pull-left'
                },{
                    unclose: true,
                    icon: 'fa-pencil',
                    title: 'Atualizar',
                    class: 'btn-blue',
                    action: function(){
                        ModalUserPass.submit();
                    }
                }]
            })
        });
    },
    showList: function(){
        User.table.clear();
        $.each( User.users, function( key, user ){
            var row = User.table.row.add([
                '<div class="cover box-shadow"' + ( user.image ? 'style="background-image:url(' + user.image + '")' : '' ) + '></div>',
                '<span>' + user.user_active + '</span><i title="' + ( user.user_active == 'Y' ? 'Ativo' : 'Inativo' ) + '" data-toggle="tooltip" class="fa fa-toggle-' + ( user.user_active == 'Y' ? 'on' : 'off' ) + '"></i>',
                user.user_name,
                user.user_profile_name,
                ( user.user_login ? '<span>' + user.user_login + '</span>' + user.user_login_br : '--' ),
                User.actions(key,user.user_id)
            ]).node();
            $(row).on('dblclick',function(){
                 User.edit(key,user.user_id);
            });
        });
        User.table.draw();
        var table = $('#table-users');
        $(table).find('a[data-action="edit"][disabled="false"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            User.edit($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $(table).find('a[data-action="audit"][disabled="false"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            User.audit($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $(table).find('a[data-action="pass"][disabled="false"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            User.pass($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $('footer div').html('<i class="fa fa-users"></i> ' + User.users.length + ' Usuários');
        global.tooltip();
    }
};