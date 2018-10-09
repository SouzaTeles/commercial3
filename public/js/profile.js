$(document).ready(function() {
    
    Profile.events();
    Profile.getList();
    global.unLoader();

});

Profile = {
    profiles: [],
    table: global.table({
        searching: 1,
        noControls: [3],
        order: [[0,'asc']],
        selector: '#table-profiles'
    }),
    audit: function(key,profile_id){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-audit',
            data: {
                log_script: 'profile',
                log_parent_id: profile_id
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-audit',
                class: 'modal-audit',
                icon: 'fa-shield',
                title: 'Auditoria do Perfil ' + Profile.profiles[key].user_profile_name,
                html: html,
                buttons: [{
                    title: 'Fechar'
                }]
            });
        });
    },
    actions: function(profile){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty-blue" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu dropdown-custom pull-right">' +
                    '<li><a data-action="edit" data-key="profile.key" data-id="' + profile.user_profile_id + '" disabled="' + ( global.login.access.profile.edit.value == 'N' ) + '" class="dropdown-item" href="#"><i class="fa fa-pencil txt-blue"></i>Editar</a></li>' +
                    '<li><a data-action="del" data-key="profile.key" data-id="' + profile.user_profile_id + '" disabled="' + ( global.login.access.profile.del.value == 'N' ) + '" class="dropdown-item" href="#"><i class="fa fa-trash-o txt-red-light"></i>Excluir</a></li>' +
                    '<li><a data-action="audit" data-key="profile.key" data-id="' + profile.user_profile_id + '" disabled="' + ( global.login.access.profile.audit.value == 'N' ) + '" class="dropdown-item" href="#"><i class="fa fa-shield txt-green"></i>Auditoria</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    edit: function(key,profile_id){
        if( global.login.access.profile.edit.value == 'N' ) return;
        global.window({
            url: global.uri.uri_public + 'window.php?module=profile&action=new&profile_id=' + profile_id
        });
    },
    events: function(){
        $('#profile_search').keyup(function(){
            Profile.table.search(this.value).draw();
        });
        $('#button-refresh').click(function(){
            Profile.getList();
        });
        $('#button-new').click(function(){
            Profile.new();
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'profile.php?action=getList',
            dataType: 'json'
        }, function(data){
            Profile.profiles = data;
            Profile.showList();
        });
    },
    new: function(){
        global.window({
            url: global.uri.uri_public + 'window.php?module=profile&action=new'
        });
    },
    showList: function(){
        Profile.table.clear();
        $.each( Profile.profiles, function(key,profile){
            var row = Profile.table.row.add([
                profile.user_profile_name,
                profile.users,
                '<span>' + profile.user_profile_date + '</span>' + profile.user_profile_date_br,
                Profile.actions(profile)
            ]).node();
            $(row).on('dblclick',function(){
                Profile.edit(key,profile.user_profile_id);
            });
        });
        Profile.table.draw();
        var table = $('#table-profiles');
        $(table).find('a[data-action="del"][disabled="false"]').click(function(e){
            e.preventDefault();
            Profile.del($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $(table).find('a[data-action="edit"][disabled="false"]').click(function(e){
            e.preventDefault();
            Profile.edit($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $(table).find('a[data-action="audit"][disabled="false"]').click(function(e){
            e.preventDefault();
            Profile.audit($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $('footer div').html('<i class="fa fa-user-circle-o"></i> ' + Profile.profiles.length + ' Perfis');
    }
};