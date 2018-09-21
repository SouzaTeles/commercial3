$(document).ready(function(){
    ModalAuditInfo.get();
});

ModalAuditInfo = {
    data: {},
    json: {},
    get: function(){
        global.post({
            url: global.uri.uri_public_api + 'audit.php?action=getJson',
            data: ModalAuditInfo.data,
            dataType: 'json'
        },function(data){
            ModalAuditInfo.json = data;
            ModalAuditInfo.show();
        });
    },
    show: function(){
        $('#modal_text_json').val(JSON.stringify(ModalAuditInfo.json, null, "\t"));
    }
};