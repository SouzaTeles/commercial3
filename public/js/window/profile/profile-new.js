var profile_id = global.url.searchParams.get('profile_id');

$(document).ready(function(){

    if( !!profile_id ){
        Profile.get(profile_id);
    } else {
        Profile.init();
    }
    Profile.events();
    Profile.getAccess();
    global.toggle();
    global.unLoader();

});

Profile = {
    access: [],
    profile: {},
    add: function(){
        global.post({
            url: global.uri.uri_public_api + 'profile.php?action=insert',
            data: Profile.profile,
            dataType: 'json'
        }, function(data){
            if( !!window.opener ) window.opener.Profile.getList();
            global.modal({
                icon: 'fa-info',
                title: 'Informação',
                html: '<p>' + data.message + '</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    if( !!window.opener ){
                        window.close();
                    } else {
                        location.reload();
                    }
                }
            });
        });
    },
    data2form: function(){
        $('#profile_name').val(Profile.profile.profile_name);
        $('header .profile-name').text(Profile.profile.profile_name);
        $.each(Profile.profile.access,function(k,module){
            $.each(module,function(j,access){
                if( j != 'name' ){
                    $('#' + k + '_' + j).bootstrapToggle(access.value == 'Y' ? 'on' : 'off');
                }
            });
        });
    },
    edit: function(){
        if( !!window.opener ) window.opener.Profile.getList();
        global.post({
            url: global.uri.uri_public_api + 'profile.php?action=edit',
            data: Profile.profile,
            dataType: 'json'
        }, function(data){
            global.modal({
                icon: 'fa-info',
                title: 'Informação',
                html: '<p>' + data.message + '</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    if( !!window.opener ){
                        window.close();
                    } else {
                        location.reload();
                    }
                }
            });
        });
    },
    events: function(){
        $('#button-add').click(function(e){
            Profile.form2data();
            if( Profile.validate() ) {
                Profile.add();
            }
        });
        $('#button-edit').click(function(e){
            Profile.form2data();
            if( Profile.validate() ) {
                Profile.edit();
            }
        });
        $('#button-cancel').click(function(e){
            if( !!window.opener ){
                window.close();
            } else {
                location.reload();
            }
        });
        $('#profile_name').on('keyup',function(){
            $('header .profile-name').text($(this).val());
        });
    },
    form2data: function(){
        Profile.profile.profile_name = $('#profile_name').val();
        $.each(Profile.access,function(k,module){
            $.each(module,function(j){
                if( j != 'name' ){
                    Profile.profile.access[k][j].value = $('#' + k + '_' + j).prop('checked') ? 'Y' : 'N';
                    if( !Profile.profile_id ){
                        Profile.profile.access[k][j].data_type = Profile.access[k][j].data_type;
                    }
                }
            });
        });
    },    
    get: function(profile_id){
        global.post({
            url: global.uri.uri_public_api + 'profile.php?action=get',
            data: {
                profile_id: profile_id,
                get_user_profile_access: 1
            },
            dataType: 'json'
        },function(profile){
            Profile.profile = {
                profile_id: profile.user_profile_id,
                profile_name: profile.user_profile_name,
                access: profile.user_profile_access
            };
            Profile.data2form();
        });
    },
    getAccess: function(){
        Profile.access = access;
    },
    init: function(){
        Profile.profile = {
            profile_id: null,
            profile_name: null,
            access: access
        }
    },
    validate: function(){
        if( !Profile.profile.profile_name.length ){
            global.validateMessage('Informe o nome do perfil.');
            return false;
        }
        return true;
    }
};