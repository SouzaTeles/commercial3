$(document).ready(function() {

    Login.events();
    global.unLoader();
    $('[name="user_user"]').focus();

});

Login = {
    events: function(){
        $('form').on('submit',function(e){
            e.stopPropagation();
            e.preventDefault();
            Login.login();
        });
    },
    login: function(){
        global.post({
            url: global.uri.uri_public_api + 'login.php',
            data: $('form').serialize(),
            dataType: 'json'
        }, function(){
            global.onLoader();
            location.href = global.uri.uri_public;
        });
    }
};